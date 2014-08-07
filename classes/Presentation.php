<?php

/**
 * The presentation layer.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Bcal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Bcal_XH
 */

/**
 * The controllers.
 *
 * @category CMSimple_XH
 * @package  Bcal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Bcal_XH
 */
class Bcal_Controller
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
        global $bcal;

        if (XH_ADM) {
            if (function_exists('XH_registerStandardPluginMenuItems')) {
                XH_registerStandardPluginMenuItems(false);
            }
            if (isset($bcal) && $bcal == 'true') {
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
            $o .= plugin_admin_common($action, $admin, 'bcal');
        }
    }

    /**
     * Renders the plugin info.
     *
     * @return string (X)HTML.
     */
    private function _renderInfo()
    {
        return '<h1>Bcal</h1>'
            . $this->_renderLogo()
            . '<p>Version: ' . BCAL_VERSION . '</p>'
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
<p class="bcal_license">This program is free software: you can redistribute
it and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.</p>
<p class="bcal_license">This program is distributed in the hope that it will
be useful, but <em>without any warranty</em>; without even the implied warranty
of <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
the GNU General Public License for more details.</p>
<p class="bcal_license">You should have received a copy of the GNU General
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
            'img src="' . $pth['folder']['plugins']. 'bcal/bcal.png"'
            . ' class="bcal_logo" alt="' . $plugin_tx['bcal']['alt_logo'] . '"'
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
        $db = new BCal_Db();
        $occupancy = $db->findOccupancy();
        $view = new Bcal_Calendars($occupancy);
        return $view->render($monthCount);
    }
}

/**
 * The calendars.
 *
 * @category CMSimple_XH
 * @package  Bcal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Bcal_XH
 */
class Bcal_Calendars
{
    /**
     * The occupancy.
     *
     * @var Bcal_Occupancy
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
     * @param Bcal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Bcal_Occupancy $occupancy)
    {
        $now = time();
        $this->month = isset($_GET['bcal_month'])
            ? $_GET['bcal_month']
            : date('n', $now);
        $this->year = isset($_GET['bcal_year'])
            ? $_GET['bcal_year']
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
        $html = '<div class="bcal_calendars">'
            . $this->renderPagination();
        $month = new Bcal_Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new Bcal_MonthCalendar($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * Renders the pagination.
     *
     * @return string (X)HTML.
     */
    protected function renderPagination()
    {
        return '<div class="bcal_pagination">'
            . $this->renderPaginationLink(0, -1, 'prev_year')
            . $this->renderPaginationLink(-1, 0, 'prev_month')
            . $this->renderPaginationLink(false, false, 'today')
            . $this->renderPaginationLink(1, 0, 'next_month')
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
            $url = $sn . '?' . $su . '&amp;bcal_year=' . $year
                . '&amp;bcal_month=' . $month;
        }
        return '<a href="' . $url . '">' . $plugin_tx['bcal']['label_'. $label]
            . '</a>';
    }
}


/**
 * The month calendars.
 *
 * @category CMSimple_XH
 * @package  Bcal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Bcal_XH
 */
class Bcal_MonthCalendar
{
    /**
     * The month.
     *
     * @var Bcal_Month
     */
    protected $month;

    /**
     * The occupancy.
     *
     * @var Bcal_Occupancy $occupancy.
     */
    protected $occupancy;

    /**
     * Initializes a new instance.
     *
     * @param Bcal_Month     $month     A month.
     * @param Bcal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Bcal_Month $month, Bcal_Occupancy $occupancy)
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
        $html = '<table class="bcal_calendar">'
            . $this->renderHeading()
            . $this->renderDaynames();
        for ($row = 0; $row < 6; $row++) {
            $html .= $this->renderWeekStartingWith($day);
            $day += 7;
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

        $monthnames = explode(',', $plugin_tx['bcal']['date_months']);
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

        $daynames = explode(',', $plugin_tx['bcal']['date_days']);
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
            return '<td class="bcal_state_' . $state . '">' . $day . '</td>';
        } else {
            return '<td>&nbsp;</td>';
        }
    }
}

?>
