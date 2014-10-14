<?php

/**
 * The presentation layer.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

/**
 * The controllers.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_Controller
{
    /**
     * Dispatches according to the request.
     *
     * @return void
     */
    public function dispatch()
    {
        if (XH_ADM) {
            if (function_exists('XH_registerStandardPluginMenuItems')) {
                XH_registerStandardPluginMenuItems(false);
            }
            if ($this->isAdministrationRequested()) {
                $this->_handleAdministration();
            }
        } else {
            if (isset($_GET['ocal_week']) || isset($_GET['ocal_month'])
                || isset($_GET['ocal_year'])
            ) {
                XH_afterPluginLoading(array($this, 'disallowIndexing'));
            }
        }
    }

    /**
     * Returns whether the plugin administration is requested.
     *
     * @return bool
     *
     * @global string Whether the plugin administration is requested.
     */
    protected function isAdministrationRequested()
    {
        global $ocal;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('ocal')
            || isset($ocal) && $ocal == 'true';
    }

    /**
     * Handles the plugin administration.
     *
     * @return void
     *
     * @global string The value of the <var>admin</var> GP parameter.
     * @global string The value of the <var>action</var> GP parameter.
     * @global string The (X)HTML fragment to insert into the contents area.
     */
    private function _handleAdministration()
    {
        global $admin, $action, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
        case '':
            $o .= $this->_renderInfo();
            break;
        default:
            $o .= plugin_admin_common($action, $admin, 'ocal');
        }
    }

    /**
     * Renders the plugin info.
     *
     * @return string (X)HTML.
     */
    private function _renderInfo()
    {
        return '<h1>Ocal</h1>'
            . $this->_renderLogo()
            . '<p>Version: ' . OCAL_VERSION . '</p>'
            . $this->_renderCopyright() . $this->_renderLicense();
    }

    /**
     * Renders the license info.
     *
     * @return string (X)HTML.
     */
    private function _renderLicense()
    {
        return <<<EOT
<p class="ocal_license">This program is free software: you can redistribute
it and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.</p>
<p class="ocal_license">This program is distributed in the hope that it will
be useful, but <em>without any warranty</em>; without even the implied warranty
of <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
the GNU General Public License for more details.</p>
<p class="ocal_license">You should have received a copy of the GNU General
Public License along with this program. If not, see <a
href="http://www.gnu.org/licenses/"
target="_blank">http://www.gnu.org/licenses/</a>. </p>
EOT;
    }

    /**
     * Renders the plugin logo.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     * @global array The localization of the plugins.
     */
    private function _renderLogo()
    {
        global $pth, $plugin_tx;

        return tag(
            'img src="' . $pth['folder']['plugins']. 'ocal/ocal.png"'
            . ' class="ocal_logo" alt="' . $plugin_tx['ocal']['alt_logo'] . '"'
        );
    }

    /**
     * Renders the copyright info.
     *
     * @return string (X)HTML.
     */
    private function _renderCopyright()
    {
        return <<<EOT
<p>Copyright &copy; 2014
    <a href="http://3-magi.net/" target="_blank">Christoph M. Becker</a>
</p>
EOT;
    }

    /**
     * Disallow indexing the page by setting meta robots appropriately.
     *
     * @return void
     *
     * @global array The configuration of the core.
     */
    public function disallowIndexing()
    {
        global $cf;

        $cf['meta']['robots'] = 'noindex, nofollow';
    }

    /**
     * Renders a calendar.
     *
     * @param string $name       A calendar name.
     * @param int    $monthCount A month count.
     *
     * @return string (X)HTML.
     *
     * @global array             The localization of the plugins.
     * @global XH_CRSFProtection The CSRF protector.
     */
    public function renderCalendar($name, $monthCount)
    {
        global $plugin_tx, $_XH_csrfProtection;

        if (!preg_match('/^[a-z0-9-]+$/', $name)) {
            return XH_message(
                'fail', $plugin_tx['ocal']['error_occupancy_name']
            );
        }
        if (XH_ADM && isset($_GET['ocal_save']) && $_GET['ocal_name'] == $name) {
            $_XH_csrfProtection->check();
            ob_end_clean(); // necessary, if called from template
            echo $this->saveStates($name);
            exit;
        }
        $db = new Ocal_Db(LOCK_SH);
        $occupancy = $db->findOccupancy($name);
        $db = null;
        $view = $this->getView($occupancy);
        return $view->render($monthCount);
    }

    /**
     * Saves the states.
     *
     * @param string $name A calendar name.
     *
     * @return void
     */
    protected function saveStates($name)
    {
        global $plugin_tx;

        $states = XH_decodeJson($_POST['ocal_states']);
        if (!is_object($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $db = new Ocal_Db(LOCK_EX);
        $occupancy = $db->findOccupancy($name);
        foreach (get_object_vars($states) as $month => $states) {
            foreach ($states as $i => $state) {
                $date = sprintf('%s-%02d', $month, $i + 1);
                $occupancy->setState($date, $state);
            }
        }
        $db->saveOccupancy($occupancy);
        $db = null;
        return XH_message('success', $plugin_tx['ocal']['message_saved']);
    }

    /**
     * Renders a week calendar.
     *
     * @param string $name      A calendar name.
     * @param int    $weekCount A week count.
     *
     * @return string (X)HTML.
     *
     * @global array             The localization of the plugins.
     * @global XH_CRSFProtection The CSRF protector.
     */
    public function renderWeekCalendar($name, $weekCount)
    {
        global $plugin_tx, $_XH_csrfProtection;

        if (!preg_match('/^[a-z0-9-]+$/', $name)) {
            return XH_message(
                'fail', $plugin_tx['ocal']['error_occupancy_name']
            );
        }
        if (XH_ADM && isset($_GET['ocal_save']) && $_GET['ocal_name'] == $name) {
            $_XH_csrfProtection->check();
            ob_end_clean(); // necessary, if called from template
            echo $this->saveHourlyStates($name);
            exit;
        }
        $db = new Ocal_Db(LOCK_SH);
        $occupancy = $db->findOccupancy($name, true);
        $db = null;
        $view = $this->getView($occupancy);
        return $view->render($weekCount);
    }

    /**
     * Saves the states.
     *
     * @param string $name A calendar name.
     *
     * @return void
     *
     * @global array The configuration of the plugins.
     * @global array The localization of the plugins.
     */
    protected function saveHourlyStates($name)
    {
        global $plugin_cf, $plugin_tx;

        $states = XH_decodeJson($_POST['ocal_states']);
        if (!is_object($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $db = new Ocal_Db(LOCK_EX);
        $occupancy = $db->findOccupancy($name, true);
        foreach (get_object_vars($states) as $week => $states) {
            foreach ($states as $i => $state) {
                $day = $i % 7 + 1;
                $hour = $i / 7 + $plugin_cf['ocal']['hour_first'];
                $date = sprintf('%s-%02d-%02d', $week, $day, $hour);
                $occupancy->setState($date, $state);
            }
        }
        $db->saveOccupancy($occupancy);
        $db = null;
        return XH_message('success', $plugin_tx['ocal']['message_saved']);
    }

    /**
     * Returns the requested view.
     *
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return Ocal_View
     */
    protected function getView($occupancy)
    {
        $mode = isset($_GET['ocal_mode']) ? $_GET['ocal_mode'] : 'calendar';
        switch ($mode) {
        case 'list':
            if ($occupancy instanceof Ocal_HourlyOccupancy) {
                return new Ocal_WeekCalendars($occupancy);
            } else {
                return new Ocal_ListView($occupancy);
            }
        default:
            if ($occupancy instanceof Ocal_HourlyOccupancy) {
                return new Ocal_WeekCalendars($occupancy);
            } else {
                return new Ocal_Calendars($occupancy);
            }
        }
    }
}

/**
 * The abstract views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
abstract class Ocal_View
{
    /**
     * The occupancy.
     *
     * @var Ocal_Occupancy
     */
    protected $occupancy;

    /**
     * The month.
     *
     * @var int
     */
    protected $month;

    /**
     * The week.
     *
     * @var int
     */
    protected $week;

    /**
     * The year.
     *
     * @var int
     */
    protected $year;

    /**
     * The ISO 8601 year.
     *
     * @var int
     */
    protected $isoYear;

    /**
     * The mode ('calendar' or 'list').
     *
     * @var string
     */
    protected $mode;

    /**
     * Initializes a new instance.
     *
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Occupancy $occupancy)
    {
        $now = time();
        $this->month = isset($_GET['ocal_month'])
            ? max(1, min(12, (int) $_GET['ocal_month']))
            : date('n', $now);
        $this->week = isset($_GET['ocal_week'])
            ? max(1, min(53, (int) $_GET['ocal_week']))
            : date('W', $now);
        $this->year = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : date('Y', $now);
        $this->isoYear = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : date('o', $now);
        $this->occupancy = $occupancy;
    }

    /**
     * Renders a link to switch the view mode.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global string The requested page URL.
     * @global array  The localization of the plugins.
     */
    protected function renderModeLink()
    {
        global $sn, $su, $plugin_tx;

        $url = $sn . '?' . $su;
        if ($this->mode == 'calendar') {
            $url .= '&amp;ocal_mode=list';
        }
        $label = $this->mode == 'calendar'
            ? $plugin_tx['ocal']['label_list_view']
            : $plugin_tx['ocal']['label_calendar_view'];
        return '<p class="ocal_mode"><a href="' . $url . '">' . $label . '</a></p>';
    }

    /**
     * Renders the pagination.
     *
     * @return string (X)HTML.
     */
    protected function renderPagination()
    {
        return '<p class="ocal_pagination">'
            . $this->renderPaginationLink(0, -1, 'prev_year') . ' '
            . $this->renderPaginationLink(-1, 0, 'prev_month') . ' '
            . $this->renderPaginationLink(false, false, 'today') . ' '
            . $this->renderPaginationLink(1, 0, 'next_month') . ' '
            . $this->renderPaginationLink(0, 1, 'next_year')
            . '</p>';
    }

    /**
     * Renders a pagination link.
     *
     * @param int    $month A month.
     * @param int    $year  A year.
     * @param string $label A label key.
     *
     * @return string (X)HTML.
     *
     * @todo Restrict links to reasonable range, to avoid search engines
     *       searching infinitely.
     */
    protected function renderPaginationLink($month, $year, $label)
    {
        global $sn, $su, $plugin_tx;

        if ($month === false && $year === false) {
            $url = $sn . '?' . $su;
        } else {
            $month = $this->month + $month;
            $year = $this->year + $year;
            if ($month < 1) {
                $month = 12;
                $year -= 1;
            } elseif ($month > 12) {
                $month = 1;
                $year += 1;
            }
            $url = $sn . '?' . $su
                . '&amp;ocal_year=' . $year . '&amp;ocal_month=' . $month;
        }
        if ($this->mode == 'list') {
            $url .= '&amp;ocal_mode=list';
        }
        return '<a href="' . $url . '">' . $plugin_tx['ocal']['label_'. $label]
            . '</a>';
    }
}

/**
 * The calendars.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_Calendars extends Ocal_View
{
    /**
     * Initializes a new instance.
     *
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Occupancy $occupancy)
    {
        parent::__construct($occupancy);
        $this->mode = 'calendar';
    }

    /**
     * Renders the calendars.
     *
     * @param int $monthCount A number of months.
     *
     * @return string (X)HTML.
     *
     * @global XH_CSRFProtection The CSRF protector.
     */
    public function render($monthCount)
    {
        global $_XH_csrfProtection;

        $this->emitScriptElements();
        $html = '<div class="ocal_calendars" data-name="'
            . $this->occupancy->getName() . '">'
            . $this->renderModeLink();
        if (XH_ADM) {
            $html .= $_XH_csrfProtection->tokenInput()
                . $this->renderToolbar()
                . $this->renderLoaderbar()
                . $this->renderStatusbar();
        }
        $month = new Ocal_Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new Ocal_MonthCalendar($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= $this->renderPagination()
            . '</div>';
        return $html;
    }

    /**
     * Emits the script elements.
     *
     * @return void
     *
     * @global array  The paths of system files and folders.
     * @global string The (X)HTML to insert at the bottom of the body element.
     * @global array  The localization of the plugins.
     */
    protected function emitScriptElements()
    {
        global $pth, $bjs, $plugin_tx;

        if (XH_ADM) {
            $config = array(
                'message_unsaved_changes'
                    => $plugin_tx['ocal']['message_unsaved_changes']
            );
            $bjs .= '<script type="text/javascript">/* <![CDATA[ */'
                . 'var OCAL = ' . XH_encodeJson($config) . ';'
                . '/* ]]> */</script>'
                . '<script type="text/javascript" src="'
                . $pth['folder']['plugins'] . 'ocal/ocal.js"></script>';
        }
    }

    /**
     * Renders the toolbar.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    protected function renderToolbar()
    {
        global $plugin_tx;

        $html = '<div class="ocal_toolbar">';
        for ($i = 0; $i <= 3; $i++) {
            $alt = $plugin_tx['ocal']['label_state_' . $i];
            $title = $alt ? ' title="' . $alt . '"' : '';
            $html .= '<span class="ocal_state" data-ocal_state="' . $i . '"'
                . $title . '></span>';
        }
        $html .= '<button type="button" class="ocal_save" disabled="disabled">'
            . $plugin_tx['ocal']['label_save'] . '</button>'
            . '</div>';
        return $html;
    }

    /**
     * Renders the Ajax loader bar.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     */
    protected function renderLoaderbar()
    {
        global $pth;

        $src = $pth['folder']['plugins'] . 'ocal/images/ajax-loader-bar.gif';
        return '<div class="ocal_loaderbar">'
            . tag('img src="' . $src . '" alt="loading"')
            . '</div>';
    }

    /**
     * Renders the status bar.
     *
     * @return string (X)HTML.
     */
    protected function renderStatusbar()
    {
        return '<div class="ocal_statusbar"></div>';
    }
}

/**
 * The week calendars.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_WeekCalendars extends Ocal_Calendars
{
    /**
     * Renders the week calendars.
     *
     * @param int $weekCount A number of weeks.
     *
     * @return string (X)HTML.
     *
     * @global XH_CSRFProtection The CSRF protector.
     */
    public function render($weekCount)
    {
        global $_XH_csrfProtection;

        $this->emitScriptElements();
        $html = '<div class="ocal_week_calendars" data-name="'
            . $this->occupancy->getName() . '">';
        if (XH_ADM) {
            $html .= $_XH_csrfProtection->tokenInput()
                . $this->renderToolbar()
                . $this->renderLoaderbar()
                . $this->renderStatusbar();
        }
        $week = new Ocal_Week($this->week, $this->year);
        $i = $weekCount;
        while ($i) {
            $calendar = new Ocal_WeekCalendar($week, $this->occupancy);
            $html .= $calendar->render();
            $i--;
            $week = $week->getNextWeek();
        }
        $html .= $this->renderPagination2($weekCount) . '</div>';
        return $html;
    }

    /**
     * Renders the pagination.
     *
     * @param int $weekCount A week count.
     *
     * @return string (X)HTML.
     */
    protected function renderPagination2($weekCount)
    {
        return '<p class="ocal_pagination">'
            . $this->renderPaginationLink2(-$weekCount, 'prev_interval') . ' '
            . $this->renderPaginationLink2(false, 'today') . ' '
            . $this->renderPaginationLink2($weekCount, 'next_interval')
            . '</p>';
    }

    /**
     * Renders a pagination link.
     *
     * @param int    $offset A week offset.
     * @param string $label  A label key.
     *
     * @return string (X)HTML.
     *
     * @todo Restrict links to reasonable range, to avoid search engines
     *       searching infinitely.
     */
    protected function renderPaginationLink2($offset, $label)
    {
        global $sn, $su, $plugin_tx;

        $url = $sn . '?' . $su;
        if ($offset) {
            $week = new Ocal_Week($this->week, $this->year);
            $week = $week->getNextWeek($offset);
            $url .= '&amp;ocal_year=' . $week->getYear()
                . '&amp;ocal_week=' . $week->getWeek();
        }
        if ($this->mode == 'list') {
            $url .= '&amp;ocal_mode=list';
        }
        return '<a href="' . $url . '">' . $plugin_tx['ocal']['label_'. $label]
            . '</a>';
    }
}

/**
 * The list views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_ListView extends Ocal_View
{
    /**
     * Initializes a new instance.
     *
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Occupancy $occupancy)
    {
        parent::__construct($occupancy);
        $this->mode = 'list';
    }

    /**
     * Renders the list view.
     *
     * @param int $monthCount A number of months.
     *
     * @return string (X)HTML.
     */
    public function render($monthCount)
    {
        $html = $this->renderModeLink()
            . '<dl class="ocal_list">';
        $month = new Ocal_Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new Ocal_MonthList($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= '</dl>'
            . $this->renderPagination();
        return $html;
    }
}

/**
 * The abstract month views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
abstract class Ocal_MonthView
{
    /**
     * The month.
     *
     * @var Ocal_Month
     */
    protected $month;

    /**
     * The occupancy.
     *
     * @var Ocal_Occupancy $occupancy.
     */
    protected $occupancy;

    /**
     * Initializes a new instance.
     *
     * @param Ocal_Month     $month     A month.
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Month $month, Ocal_Occupancy $occupancy)
    {
        $this->month = $month;
        $this->occupancy = $occupancy;
    }

    /**
     * Returns a formatted date.
     *
     * @param int $day A day number.
     *
     * @return string
     */
    protected function formatDate($day)
    {
        return sprintf(
            '%04d-%02d-%02d', $this->month->getYear(),
            $this->month->getMonth(), $day
        );
    }
}

/**
 * The month calendars.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_MonthCalendar extends Ocal_MonthView
{
    /**
     * Renders the month calendar.
     *
     * @return string (X)HTML.
     */
    public function render()
    {
        $day = $this->month->getDayOffset();
        $html = '<table class="ocal_calendar" data-ocal_date="'
            . $this->month->getIso() . '">'
            . $this->renderHeading()
            . $this->renderDaynames();
        for ($row = 0; $row < 6; $row++) {
            $html .= $this->renderWeekStartingWith($day);
            $day += 7;
            if ($day > $this->month->getLastDay()) {
                break;
            }
        }
        $html .= '</table>';
        return $html;
    }

    /**
     * Renders the heading.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    protected function renderHeading()
    {
        global $plugin_tx;

        $monthnames = explode(',', $plugin_tx['ocal']['date_months']);
        return '<tr><th colspan="7">' . $monthnames[$this->month->getMonth() - 1]
            . ' ' . $this->month->getYear() . '</th></tr>';
    }

    /**
     * Renders the daynames.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    protected function renderDaynames()
    {
        global $plugin_tx;

        $daynames = explode(',', $plugin_tx['ocal']['date_days']);
        $html = '<tr>';
        foreach ($daynames as $dayname) {
            $html .= '<th>' . $dayname . '</th>';
        }
        $html .= '</tr>';
        return $html;
    }

    /**
     * Renders a week table row.
     *
     * @param int $day A day.
     *
     * @return string (X)HTML.
     */
    protected function renderWeekStartingWith($day)
    {
        $html = '<tr>';
        for ($col = 0; $col < 7; $col++) {
            $html .= $this->renderDay($day);
            $day++;
        }
        $html .= '</tr>';
        return $html;
    }

    /**
     * Renders a day table cell.
     *
     * @param int $day A day.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    protected function renderDay($day)
    {
        global $plugin_tx;

        if ($day >= 1 && $day <= $this->month->getLastDay()) {
            $date = $this->formatDate($day);
            $state = $this->occupancy->getState($date);
            $today = ($date == date('Y-m-d')) ? ' ocal_today' : '';
            $alt = $plugin_tx['ocal']['label_state_' . $state];
            $title = $alt ? ' title="' . $alt . '"' : '';
            return '<td class="ocal_state' . $today . '" data-ocal_state="'
                . $state . '"' . $title . '>' . $day . '</td>';
        } else {
            return '<td>&nbsp;</td>';
        }
    }
}

/**
 * The month lists.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_MonthList extends Ocal_MonthView
{
    /**
     * Renders the month list.
     *
     * @return string (X)HTML.
     */
    public function render()
    {
        global $plugin_tx;

        $html = $this->renderHeading() . '<dd><dl>';
        foreach ($this->getList() as $range => $state) {
            $label = $plugin_tx['ocal']['label_state_' . $state];
            if ($label != '') {
                $html .= '<dt>' . $range . '</dt>'
                    . '<dd>' . $label . '</dd>';
            }
        }
        $html .= '</dl></dd>';
        return $html;
    }

    /**
     * Renders the heading.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    protected function renderHeading()
    {
        global $plugin_tx;

        $monthnames = explode(',', $plugin_tx['ocal']['date_months']);
        return  '<dt>' . $monthnames[$this->month->getMonth() - 1]
            . ' ' . $this->month->getYear() . '</dt>';
    }

    /**
     * Returns a map from formatted day ranges to states.
     *
     * @return array
     */
    protected function getList()
    {
        $list = array();
        $currentRange = array();
        $currentState = -1;
        for ($day = 1; $day <= $this->month->getLastDay(); $day++) {
            $date = $this->formatDate($day);
            $state = $this->occupancy->getState($date);
            if ($currentState == -1 || $state == $currentState) {
                $currentRange[] = $day;
            } else {
                $list[$this->formatRange($currentRange)] = $currentState;
                $currentRange = array($day);
            }
            $currentState = $state;
        }
        $list[$this->formatRange($currentRange)] = $currentState;
        return $list;
    }

    /**
     * Returns a formatted day range.
     *
     * @param array $range An array of successive days.
     *
     * @return string
     */
    protected function formatRange($range)
    {
        $string = $range[0] . '.';
        if (count($range) > 1) {
            $string .= '&ndash;' . $range[count($range) - 1] . '.';
        }
        return $string;
    }
}

/**
 * The abstract week views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
abstract class Ocal_WeekView
{
    /**
     * The week.
     *
     * @var Ocal_Week
     */
    protected $week;

    /**
     * The occupancy.
     *
     * @var Ocal_Occupancy $occupancy.
     */
    protected $occupancy;

    /**
     * Initializes a new instance.
     *
     * @param Ocal_Week      $week      A week.
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Week $week, Ocal_Occupancy $occupancy)
    {
        $this->week = $week;
        $this->occupancy = $occupancy;
    }

    /**
     * Returns a formatted date.
     *
     * @param int $day  A day.
     * @param int $hour An hour.
     *
     * @return string
     */
    protected function formatDate($day, $hour)
    {
        return sprintf(
            '%04d-%02d-%02d-%02d', $this->week->getYear(),
            $this->week->getWeek(), $day, $hour
        );
    }
}

/**
 * The week calendars.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_WeekCalendar extends Ocal_WeekView
{
    /**
     * Renders the week calendar.
     *
     * @return string (X)HTML.
     *
     * @global array The configuration of the plugins.
     */
    public function render()
    {
        global $plugin_cf;

        $pcf = $plugin_cf['ocal'];
        $html = '<table class="ocal_calendar" data-ocal_date="'
            . $this->week->getIso() . '">';
        $html .= $this->renderHeading() . $this->renderDaynames();
        for ($i = $pcf['hour_first']; $i <= $pcf['hour_last']; $i++) {
            $html .= '<tr>';
            for ($j = 1; $j <= 7; $j++) {
                $html .= $this->renderHour($j, $i);
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    /**
     * Renders the heading.
     *
     * @return string (X)HTML.
     */
    protected function renderHeading()
    {
        $date = new DateTime();
        $date->setISODate($this->week->getYear(), $this->week->getWeek(), 1);
        $from = $date->format('j.n.Y');
        $date->setISODate($this->week->getYear(), $this->week->getWeek(), 7);
        $to = $date->format('j.n.Y');
        return '<tr><th colspan="7">' . $from
            . '&ndash;' . $to . '</th></tr>';
    }

    /**
     * Renders the daynames.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    protected function renderDaynames()
    {
        global $plugin_tx;

        $daynames = explode(',', $plugin_tx['ocal']['date_days']);
        $html = '<tr>';
        foreach ($daynames as $dayname) {
            $html .= '<th>' . $dayname . '</th>';
        }
        $html .= '</tr>';
        return $html;
    }

    /**
     * Renders an hour table cell.
     *
     * @param int $day  A day.
     * @param int $hour An hour.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    protected function renderHour($day, $hour)
    {
        global $plugin_tx;

        $state = $this->occupancy->getState($this->formatDate($day, $hour));
        $alt = $plugin_tx['ocal']['label_state_' . $state];
        $title = $alt ? ' title="' . $alt . '"' : '';
        return '<td class="ocal_state" data-ocal_state="' . $state . '"'
            . $title . '>' . $hour . '</td>';
    }
}

?>
