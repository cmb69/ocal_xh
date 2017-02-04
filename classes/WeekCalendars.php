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
            . $this->occupancy->getName() . '">';
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
        $html .= $this->renderPagination2($weekCount) . '</div>';
        return $html;
    }

    /**
     * @param int $weekCount
     * @return string
     */
    protected function renderPagination2($weekCount)
    {
        return '<p class="ocal_pagination">'
            . $this->renderPaginationLink2(-$weekCount, 'prev_interval') . ' '
            . $this->renderPaginationLink2(false, 'today') . ' '
            . $this->renderPaginationLink2($weekCount, 'next_interval')
            . '</p>';
    }

    /**
     * @param int $offset
     * @param string $label
     * @return string
     */
    protected function renderPaginationLink2($offset, $label)
    {
        global $plugin_tx;

        $params = array('ocal_mode' => $this->mode == 'list' ? 'list' : 'calendar');
        if ($offset) {
            $week = new Week($this->week, $this->year);
            $week = $week->getNextWeek($offset);
            $params['ocal_year'] = $week->getYear();
            $params['ocal_week'] = $week->getWeek();
        } else {
            $date = new DateTime();
            $params['ocal_year'] = $date->format('o');
            $params['ocal_week'] = $date->format('W');
        }
        $url = $this->modifyUrl($params);
        return '<a href="' . XH_hsc($url) . '">'
            . $plugin_tx['ocal']['label_'. $label] . '</a>';
    }
}
