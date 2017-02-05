<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

use DateTime;

class WeekCalendars extends Calendars
{
    /**
     * @param int $weekCount
     * @return string
     */
    public function render($weekCount)
    {
        global $_XH_csrfProtection;

        $this->emitScriptElements();
        $html = '<div class="ocal_week_calendars" data-name="'
            . $this->occupancy->getName() . '">'
            . $this->renderModeLink();
        if (XH_ADM) {
            $html .= $_XH_csrfProtection->tokenInput()
                . $this->renderToolbar();
        }
        $html .= $this->renderLoaderbar() . $this->renderStatusbar();
        $week = new Week($this->week, $this->year);
        $i = $weekCount;
        while ($i) {
            $calendar = new WeekCalendar($week, $this->occupancy);
            $html .= $calendar->render();
            $i--;
            $week = $week->getNextWeek();
        }
        $html .= $this->renderWeekPagination($weekCount) . '</div>';
        return $html;
    }
}
