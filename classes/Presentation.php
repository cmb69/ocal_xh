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
     *
     * @global string Whether the plugin administration is requested.
     */
    public function dispatch()
    {
        global $ocal;

        if (XH_ADM) {
            if (function_exists('XH_registerStandardPluginMenuItems')) {
                XH_registerStandardPluginMenuItems(false);
            }
            if (isset($ocal) && $ocal == 'true') {
                $this->_handleAdministration();
            }
        }
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
     * Renders a calendar.
     *
     * @param int $monthCount A month count.
     *
     * @return string (X)HTML.
     */
    public function renderCalendar($monthCount)
    {
        if (XH_ADM && isset($_GET['ocal_save'])) {
            $this->saveStates();
            exit;
        }
        $db = new Ocal_Db();
        $occupancy = $db->findOccupancy();
        $view = new Ocal_Calendars($occupancy);
        return $view->render($monthCount);
    }

    /**
     * Saves the states.
     *
     * @return void
     */
    protected function saveStates()
    {
        $payload = file_get_contents('php://input');
        $states = XH_decodeJson($payload);
        $db = new Ocal_Db();
        $occupancy = $db->findOccupancy();
        foreach (get_object_vars($states) as $month => $states) {
            foreach ($states as $i => $state) {
                $date = sprintf('%s-%02d', $month, $i + 1);
                $occupancy->setState($date, $state);
            }
        }
        $db->saveOccupancy($occupancy);
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
class Ocal_Calendars
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
     * The year.
     *
     * @var int
     */
    protected $year;

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
            ? $_GET['ocal_month']
            : date('n', $now);
        $this->year = isset($_GET['ocal_year'])
            ? $_GET['ocal_year']
            : date('Y', $now);
        $this->occupancy = $occupancy;
    }

    /**
     * Renders the calendars.
     *
     * @param int $monthCount A number of months.
     *
     * @return string (X)HTML.
     */
    public function render($monthCount)
    {
        $this->emitScriptElements();
        $html = '<div class="ocal_calendars">'
            . $this->renderPagination();
        if (XH_ADM) {
            $html .= $this->renderToolbar();
        }
        $month = new Ocal_Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new Ocal_MonthCalendar($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Emits the script elements.
     *
     * @return void
     *
     * @global array The paths of system files and folders.
     * @global string The (X)HTML to insert at the bottom of the body element.
     */
    protected function emitScriptElements()
    {
        global $pth, $bjs;

        if (XH_ADM) {
            $bjs .= '<script type="text/javascript" src="'
                . $pth['folder']['plugins'] . 'ocal/ocal.js"></script>';
        }
    }

    /**
     * Renders the pagination.
     *
     * @return string (X)HTML.
     */
    protected function renderPagination()
    {
        return '<div class="ocal_pagination">'
            . $this->renderPaginationLink(0, -1, 'prev_year') . ' '
            . $this->renderPaginationLink(-1, 0, 'prev_month') . ' '
            . $this->renderPaginationLink(false, false, 'today') . ' '
            . $this->renderPaginationLink(1, 0, 'next_month') . ' '
            . $this->renderPaginationLink(0, 1, 'next_year')
            . '</div>';
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
            $url = $sn . '?' . $su . '&amp;ocal_year=' . $year
                . '&amp;ocal_month=' . $month;
        }
        return '<a href="' . $url . '">' . $plugin_tx['ocal']['label_'. $label]
            . '</a>';
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
            $html .= '<span class="ocal_state_' . $i . '"></span>';
        }
        $html .= '<button type="button" class="ocal_save" disabled="disabled">'
            . $plugin_tx['ocal']['label_save'] . '</button>'
            . '</div>';
        return $html;
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
class Ocal_MonthCalendar
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
     * Renders the month calendar.
     *
     * @return string (X)HTML.
     */
    public function render()
    {
        $day = $this->month->getDayOffset();
        $html = '<table class="ocal_calendar" data-month="'
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
        return '<th colspan="7">' . $monthnames[$this->month->getMonth() - 1]
            . ' ' . $this->month->getYear() . '</th>';
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
     */
    protected function renderDay($day)
    {
        if ($day >= 1 && $day <= $this->month->getLastDay()) {
            $date = sprintf(
                '%04d-%02d-%02d', $this->month->getYear(),
                $this->month->getMonth(), $day
            );
            $state = $this->occupancy->getState($date);
            return '<td class="ocal_state_' . $state . '">' . $day . '</td>';
        } else {
            return '<td>&nbsp;</td>';
        }
    }
}

?>
