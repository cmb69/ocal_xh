<?php

/**
 * The week calendars.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

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
