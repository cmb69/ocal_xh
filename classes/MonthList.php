<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

class MonthList extends MonthView
{
    /**
     * @return string
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
     * @return string
     */
    protected function renderHeading()
    {
        global $plugin_tx;

        $monthnames = explode(',', $plugin_tx['ocal']['date_months']);
        return  '<dt>' . $monthnames[$this->month->getMonth() - 1]
            . ' ' . $this->month->getYear() . '</dt>';
    }

    /**
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
     * @return string
     */
    protected function formatRange(array $range)
    {
        $string = $range[0] . '.';
        if (count($range) > 1) {
            $string .= '&ndash;' . $range[count($range) - 1] . '.';
        }
        return $string;
    }
}
