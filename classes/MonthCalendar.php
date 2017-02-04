<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

class MonthCalendar extends MonthView
{
    /**
     * @return string
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
     * @return string
     */
    protected function renderHeading()
    {
        global $plugin_tx;

        $monthnames = explode(',', $plugin_tx['ocal']['date_months']);
        return '<tr><th colspan="7">' . $monthnames[$this->month->getMonth() - 1]
            . ' ' . $this->month->getYear() . '</th></tr>';
    }

    /**
     * @return string
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
     * @param int $day
     * @return string
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
     * @param int $day
     * @return string
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
