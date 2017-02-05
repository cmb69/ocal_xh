<?php

/**
 * @copyright 2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

use DateTime;

class WeekListView extends ListView
{
    /**
     * @param int $weekCount
     * @return string
     */
    public function render($weekCount)
    {
        $this->emitScriptElements();
        $html = '<div data-name="' . $this->occupancy->getName() . '">' . $this->renderModeLink()
            . $this->renderLoaderbar() . $this->renderStatusbar()
            . '<dl class="ocal_list">';
        $week = new Week($this->week, $this->year);
        $i = $weekCount;
        while ($i) {
            $calendar = new WeekList($week, $this->occupancy);
            $html .= $calendar->render();
            $i--;
            $week = $week->getNextWeek();
        }
        $html .= '</dl>'
            . $this->renderWeekPagination() . '</div>';
        return $html;
    }
}
