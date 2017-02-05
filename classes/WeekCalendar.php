<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

use DateTime;

class WeekCalendar extends WeekView
{
    /**
     * @return string
     */
    public function render()
    {
        global $plugin_cf;

        $pcf = $plugin_cf['ocal'];
        $html = '<table class="ocal_calendar" data-ocal_date="'
            . $this->week->getIso() . '">';
        $html .= '<thead>' . $this->renderHeading() . $this->renderDaynames() . '</thead><tbody>';
        for ($i = $pcf['hour_first']; $i <= $pcf['hour_last']; $i += $plugin_cf['ocal']['hour_interval']) {
            $html .= '<tr>';
            for ($j = 1; $j <= 7; $j++) {
                $html .= $this->renderHour($j, $i);
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * @return string
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
     * @param int $hour
     * @return string
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
