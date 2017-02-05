<?php

/**
 * @copyright 2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

use DateTime;

class WeekList extends WeekView
{
    /**
     * @return string
     */
    public function render()
    {
        global $plugin_tx;

        $html = '';
        $date = new DateTime();
        for ($i = 1; $i <= 7; $i++) {
            $date->setISODate($this->week->getYear(), $this->week->getWeek(), $i);
            $dayLabel = $date->format($plugin_tx['ocal']['date_format']);
            $outerDl = '<dt>' . $dayLabel . '</dt><dd><dl>';
            $innerDl = '';
            foreach ($this->getListOfDay($i) as $range => $state) {
                $label = $plugin_tx['ocal']['label_state_' . $state];
                if ($label != '') {
                    $innerDl .= '<dt>' . $range . '</dt>'
                        . '<dd>' . $label . '</dd>';
                }
            }
            if ($innerDl) {
                $html .= $outerDl . $innerDl . '</dl></dd>';
            }
        }
        return $html;
    }

    /**
     * @param int $weekday
     * @return array
     */
    protected function getListOfDay($weekday)
    {
        global $plugin_cf;

        $pcf = $plugin_cf['ocal'];
        $list = array();
        $currentRange = array();
        $currentState = -1;
        for ($hour = $pcf['hour_first']; $hour <= $pcf['hour_last']; $hour += $pcf['hour_interval']) {
            $date = $this->formatDate($weekday, $hour);
            $state = $this->occupancy->getState($date);
            if ($currentState == -1 || $state == $currentState) {
                $currentRange[] = $hour;
            } else {
                $list[$this->formatRange($currentRange)] = $currentState;
                $currentRange = array($hour);
            }
            $currentState = $state;
        }
        $list[$this->formatRange($currentRange)] = $currentState;
        return $list;
    }

    /**
     * @return string
     */
    protected function formatRange(array $range)
    {
        global $plugin_cf;

        $start = $range[0];
        if (count($range) > 1) {
            $end = $range[count($range) - 1];
        } else {
            $end = $range[0];
        }
        $end += $plugin_cf['ocal']['hour_interval'] - 1;
        return sprintf('%02d:00&ndash;%02d:59', $start, $end);
    }
}
