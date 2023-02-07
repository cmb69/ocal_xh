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
use XH\CSRFProtection as CsrfProtector;

class HourlyCalendarController extends CalendarController
{
    /**
     * @var int
     */
    private $week;

    /**
     * @var int
     */
    private $isoYear;

    /**
     * @param array<string,string> $config
     * @param array<string,string> $lang
     * @param string $name
     * @param int $count
     */
    public function __construct(
        string $scriptName,
        string $pluginFolder,
        CsrfProtector $csrfProtector,
        array $config,
        array $lang,
        DateTime $now,
        ListService $listService,
        $name,
        $count
    ) {
        parent::__construct(
            $scriptName,
            $pluginFolder,
            $csrfProtector,
            $config,
            $lang,
            $now,
            $listService,
            $name,
            $count
        );
        $this->week = isset($_GET['ocal_week'])
            ? max(1, min(53, (int) $_GET['ocal_week']))
            : (int) $now->format('W');
        $this->isoYear = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : (int) $now->format('o');
    }

    /**
     * @return Occupancy
     */
    protected function findOccupancy()
    {
        $db = new Db(LOCK_SH);
        return $db->findOccupancy($this->name, true);
    }

    /**
     * @return View
     */
    protected function getCalendarView(Occupancy $occupancy)
    {
        $this->emitScriptElements();
        
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'hourly-calendars');
        $data = [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->prepareModeLinkView(),
            'isEditable' => defined('XH_ADM') && XH_ADM,
            'toolbar' => $this->prepareToolbarView(),
            'statusbar' => $this->prepareStatusbarView(),
            'weekPagination' => $this->preparePaginationView($this->count),
            'weekCalendars' => $this->getWeekCalendars($occupancy),
        ];
        if (defined('XH_ADM') && XH_ADM) {
            $data['csrfTokenInput'] = new HtmlString($this->csrfProtector->tokenInput());
        }
        $view->setData($data);
        return $view;
    }

    /**
     * @return View[]
     */
    private function getWeekCalendars(Occupancy $occupancy)
    {
        $weekCalendars = [];
        foreach (Week::createRange($this->isoYear, $this->week, $this->count) as $week) {
            $weekCalendars[] = $this->prepareWeekCalendarView($occupancy, $week);
        }
        return $weekCalendars;
    }

    /**
     * @return View
     */
    private function prepareWeekCalendarView(Occupancy $occupancy, Week $week)
    {
        $from = clone $this->now;
        $from->setISODate($week->getYear(), $week->getWeek(), 1);
        $to = clone $this->now;
        $to->setISODate($week->getYear(), $week->getWeek(), 7);
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'hourly-calendar');
        $view->setData([
            'date' => $week->getIso(),
            'from' => $from->format($this->lang['date_format']),
            'to' => $to->format($this->lang['date_format']),
            'daynames' => array_map('trim', explode(',', $this->lang['date_days'])),
            'hours' => $this->getDaysOfHours($occupancy, $week),
        ]);
        return $view;
    }

    /**
     * @return object[][]
     */
    private function getDaysOfHours(Occupancy $occupancy, Week $week)
    {
        $daysOfHours = [];
        $hours = range(
            (int) $this->config['hour_first'],
            (int) $this->config['hour_last'],
            (int) $this->config['hour_interval']
        );
        foreach ($hours as $hour) {
            $days = [];
            foreach (range(1, 7) as $day) {
                $days[]  = (object) array(
                    'hour' => $hour,
                    'state' => $occupancy->getHourlyState($week->getYear(), $week->getWeek(), $day, $hour)
                );
            }
            $daysOfHours[] = $days;
        }
        return $daysOfHours;
    }

    /**
     * @return View
     */
    protected function getListView(Occupancy $occupancy)
    {
        $this->emitScriptElements();
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'hourly-lists');
        $view->setData([
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->prepareModeLinkView(),
            'statusbar' => $this->prepareStatusbarView(),
            'weekPagination' => $this->preparePaginationView($this->count),
            'weekLists' => $this->getWeekLists($occupancy),
        ]);
        return $view;
    }

    /**
     * @return View[]
     */
    private function getWeekLists(Occupancy $occupancy)
    {
        $weekLists = [];
        foreach (Week::createRange($this->isoYear, $this->week, $this->count) as $week) {
            $weekLists[] = $this->prepareWeekListView($occupancy, $week);
        }
        return $weekLists;
    }

    /**
     * @return View
     */
    private function prepareWeekListView(Occupancy $occupancy, Week $week)
    {
        $from = clone $this->now;
        $from->setISODate($week->getYear(), $week->getWeek(), 1);
        $to = clone $this->now;
        $to->setISODate($week->getYear(), $week->getWeek(), 7);
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'hourly-list');
        $view->setData([
            'from' => $from->format($this->lang['date_format']),
            'to' => $to->format($this->lang['date_format']),
            'weekList' => $this->getWeekList($occupancy, $week),
        ]);
        return $view;
    }

    /**
     * @return object[]
     */
    private function getWeekList(Occupancy $occupancy, Week $week)
    {
        $weekList = [];
        foreach ($this->listService->getHourlyList($occupancy, $week) as $day) {
            $day->label = $day->date->format($this->lang['date_format']);
            $weekList[] = $day;
        }
        return $weekList;
    }

    /**
     * @param int $weekCount
     * @return View
     */
    private function preparePaginationView($weekCount)
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'pagination');
        $view->setData([
            'items' => $this->getPaginationItems($weekCount),
        ]);
        return $view;
    }

    /**
     * @param int $weekCount
     * @return object[]
     */
    private function getPaginationItems($weekCount)
    {
        $items = (new HourlyPagination($this->isoYear, $this->week, $this->now))->getItems($weekCount);
        foreach ($items as $item) {
            $item->url = $this->modifyUrl(array(
                'ocal_year' => $item->year,
                'ocal_week' => $item->monthOrWeek,
                'ocal_action' => $this->mode
            ));
        }
        return $items;
    }

    /** @return string|never */
    protected function saveStates()
    {
        $states = json_decode($_POST['ocal_states'], true);
        if (!is_array($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $db = new Db(LOCK_EX);
        $occupancy = $db->findOccupancy($this->name, true);
        foreach ($states as $week => $states) {
            foreach ($states as $i => $state) {
                $day = $i % 7 + 1;
                $hour = (int) $this->config['hour_interval'] * (int) ($i / 7) + (int) $this->config['hour_first'];
                $date = sprintf('%s-%02d-%02d', $week, $day, $hour);
                $occupancy->setState($date, $state);
            }
        }
        $db->saveOccupancy($occupancy);
        $db = null;
        return XH_message('success', $this->lang['message_saved']);
    }
}
