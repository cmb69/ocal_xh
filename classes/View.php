<?php

/**
 * Copyright 2014-2017 Christoph M. Becker
 *
 * This file is part of Ocal_XH.
 *
 * Ocal_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Ocal_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Ocal_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Ocal;

use DateTime;

abstract class View
{
    /**
     * @var Occupancy
     */
    protected $occupancy;

    /**
     * @var int
     */
    protected $month;

    /**
     * @var int
     */
    protected $week;

    /**
     * @var int
     */
    protected $year;

    /**
     * @var int
     */
    protected $isoYear;

    /**
     * @var string
     */
    protected $mode;

    public function __construct(Occupancy $occupancy)
    {
        $now = time();
        $this->month = isset($_GET['ocal_month'])
            ? max(1, min(12, (int) $_GET['ocal_month']))
            : date('n', $now);
        $this->week = isset($_GET['ocal_week'])
            ? max(1, min(53, (int) $_GET['ocal_week']))
            : date('W', $now);
        $this->year = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : date('Y', $now);
        $this->isoYear = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : date('o', $now);
        $this->occupancy = $occupancy;
    }

    protected function emitScriptElements()
    {
        global $pth, $bjs, $plugin_tx;

        $config = array(
            'message_unsaved_changes'
                => $plugin_tx['ocal']['message_unsaved_changes'],
            'isAdmin' => XH_ADM
        );
        $bjs .= '<script type="text/javascript">/* <![CDATA[ */'
            . 'var OCAL = ' . XH_encodeJson($config) . ';'
            . '/* ]]> */</script>'
            . '<script type="text/javascript" src="'
            . $pth['folder']['plugins'] . 'ocal/ocal.js"></script>';
    }

    /**
     * @return string
     */
    protected function renderModeLink()
    {
        global $plugin_tx;

        $mode = $this->mode == 'calendar' ? 'list' : 'calendar';
        $url = $this->modifyUrl(array('ocal_mode' => $mode));
        $label = $this->mode == 'calendar'
            ? $plugin_tx['ocal']['label_list_view']
            : $plugin_tx['ocal']['label_calendar_view'];
        return '<p class="ocal_mode"><a href="' . XH_hsc($url) . '">' . $label
            . '</a></p>';
    }

    /**
     * @return string
     */
    protected function renderLoaderbar()
    {
        global $pth;

        $src = $pth['folder']['plugins'] . 'ocal/images/ajax-loader-bar.gif';
        return '<div class="ocal_loaderbar">'
            . tag('img src="' . $src . '" alt="loading"')
            . '</div>';
    }

    /**
     * @return string
     */
    protected function renderStatusbar()
    {
        return '<div class="ocal_statusbar"></div>';
    }

    /**
     * @return string
     */
    protected function renderMonthPagination()
    {
        return '<p class="ocal_pagination">'
            . $this->renderMonthPaginationLink(0, -1, 'prev_year') . ' '
            . $this->renderMonthPaginationLink(-1, 0, 'prev_month') . ' '
            . $this->renderMonthPaginationLink(false, false, 'today') . ' '
            . $this->renderMonthPaginationLink(1, 0, 'next_month') . ' '
            . $this->renderMonthPaginationLink(0, 1, 'next_year')
            . '</p>';
    }

    /**
     * @param int $month
     * @param int $year
     * @param string $label
     * @return ?string
     */
    protected function renderMonthPaginationLink($month, $year, $label)
    {
        global $plugin_tx;

        $mode = $this->mode == 'list' ? 'list' : 'calendar';
        if ($month === false && $year === false) {
            $date = new DateTime();
            $year = $date->format('Y');
            $month = $date->format('n');
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
            $wantedMonth = 12 * $year + $month;
            if (!$this->isMonthPaginationValid($wantedMonth)) {
                return;
            }
        }
        $url = $this->modifyUrl(
            array(
                'ocal_year' => $year, 'ocal_month' => $month,
                'ocal_mode' => $mode
            )
        );
        return '<a href="' . XH_hsc($url) . '">'
            . $plugin_tx['ocal']['label_'. $label] . '</a>';
    }

    /**
     * @param int $month
     */
    private function isMonthPaginationValid($month)
    {
        global $plugin_cf;

        $date = new DateTime();
        $currentMonth = 12 * $date->format('Y') + $date->format('n');
        return $month >= $currentMonth - $plugin_cf['ocal']['pagination_past']
            && $month <= $currentMonth + $plugin_cf['ocal']['pagination_future'];
    }

    /**
     * @param int $weekCount
     * @return string
     */
    protected function renderWeekPagination($weekCount)
    {
        return '<p class="ocal_pagination">'
            . $this->renderWeekPaginationLink(-$weekCount, 'prev_interval') . ' '
            . $this->renderWeekPaginationLink(false, 'today') . ' '
            . $this->renderWeekPaginationLink($weekCount, 'next_interval')
            . '</p>';
    }

    /**
     * @param int $offset
     * @param string $label
     * @return ?string
     */
    protected function renderWeekPaginationLink($offset, $label)
    {
        global $plugin_tx;

        $params = array('ocal_mode' => $this->mode == 'list' ? 'list' : 'calendar');
        if ($offset) {
            $week = new Week($this->week, $this->year);
            $week = $week->getNextWeek($offset);
            if (!$this->isWeekPaginationValid($week)) {
                return;
            }
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

    private function isWeekPaginationValid(Week $week)
    {
        global $plugin_cf;

        $date = new DateTime();
        $currentWeek = new Week($date->format('W'), $date->format('o'));
        return $week->compare($currentWeek->getNextWeek(-$plugin_cf['ocal']['pagination_past'])) >= 0
            && $week->compare($currentWeek->getNextWeek($plugin_cf['ocal']['pagination_future'])) <= 0;
    }

    /**
     * @param array $newParams
     * @return string
     */
    protected function modifyUrl(array $newParams)
    {
        global $sn;

        parse_str($_SERVER['QUERY_STRING'], $params);
        $params = array_merge($params, $newParams);
        $query = str_replace('=&', '&', http_build_query($params));
        return $sn . '?' . $query;
    }
}
