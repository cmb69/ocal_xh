<?php

/**
 * The month calendars.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2016 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

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

?>
