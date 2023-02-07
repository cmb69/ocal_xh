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
use stdClass;
use XH\CSRFProtection as CsrfProtector;

class DailyCalendarController extends CalendarController
{
    /**
     * @var int
     */
    private $month;

    /**
     * @var int
     */
    private $year;

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
        $this->month = isset($_GET['ocal_month'])
            ? max(1, min(12, (int) $_GET['ocal_month']))
            : (int) $now->format('n');
        $this->year = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : (int) $now->format('Y');
    }

    /**
     * @return Occupancy
     */
    protected function findOccupancy()
    {
        $db = new Db(LOCK_SH);
        return $db->findOccupancy($this->name);
    }

    /**
     * @return View
     */
    protected function getCalendarView(Occupancy $occupancy)
    {
        $this->emitScriptElements();

        $view = new View("{$this->pluginFolder}views/", $this->lang, 'daily-calendars');
        $data = [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->prepareModeLinkView(),
            'isEditable' => defined('XH_ADM') && XH_ADM,
            'toolbar' => $this->prepareToolbarView(),
            'statusbar' => $this->prepareStatusbarView(),
            'monthPagination' => $this->preparePaginationView(),
            'monthCalendars' => $this->getMonthCalendars($occupancy),
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
    private function getMonthCalendars(Occupancy $occupancy)
    {
        $monthCalendars = [];
        foreach (Month::createRange($this->year, $this->month, $this->count) as $month) {
            $monthCalendars[] = $this->prepareMonthCalendarView($occupancy, $month);
        }
        return $monthCalendars;
    }

    /**
     * @return View
     */
    private function prepareMonthCalendarView(Occupancy $occupancy, Month $month)
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'daily-calendar');
        $view->setData([
            'isoDate' => $month->getIso(),
            'year' => $month->getYear(),
            'monthname' => $this->getMonthName($month->getMonth()),
            'daynames' => array_map('trim', explode(',', $this->lang['date_days'])),
            'weeks' => $this->getWeeks($occupancy, $month),
        ]);
        return $view;
    }

    /**
     * @param int $month
     * @return string
     */
    private function getMonthName($month)
    {
        $monthnames = array_map('trim', explode(',', $this->lang['date_months']));
        return $monthnames[$month - 1];
    }

    /**
     * @return object[][]
     */
    private function getWeeks(Occupancy $occupancy, Month $month)
    {
        $weeks = [];
        foreach ($month->getDaysOfWeeks() as $week) {
            $weeks[] = $this->getWeekDays($occupancy, $month, $week);
        }
        return $weeks;
    }

    /**
     * @param list<int|null> $week
     * @return object[]
     */
    private function getWeekDays(Occupancy $occupancy, Month $month, array $week)
    {
        $days = [];
        foreach ($week as $day) {
            if (isset($day)) {
                $state = $occupancy->getDailyState($month->getYear(), $month->getMonth(), $day);
                $date = sprintf('%04d-%02d-%02d', $month->getYear(), $month->getMonth(), $day);
                $days[] = (object) array(
                    'day' => $day,
                    'state' => $state,
                    'todayClass' => $date === date('Y-m-d') ? ' ocal_today' : '',
                    'titleKey' => "label_state_$state"
                );
            } else {
                $days[] = null;
            }
        }
        return $days;
    }

    /**
     * @return View
     */
    protected function getListView(Occupancy $occupancy)
    {
        $this->emitScriptElements();
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'daily-lists');
        $view->setData([
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->prepareModeLinkView(),
            'statusbar' => $this->prepareStatusbarView(),
            'monthLists' => $this->getMonthLists($occupancy),
            'monthPagination' => $this->preparePaginationView(),
        ]);
        return $view;
    }

    /**
     * @return View[]
     */
    private function getMonthLists(Occupancy $occupancy)
    {
        $monthLists = [];
        foreach (Month::createRange($this->year, $this->month, $this->count) as $month) {
            $monthLists[] = $this->prepareMonthListView($occupancy, $month);
        }
        return $monthLists;
    }

    /**
     * @return View
     */
    private function prepareMonthListView(Occupancy $occupancy, Month $month)
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'daily-list');
        $monthnames = explode(',', $this->lang['date_months']);
        $view->setData([
            'heading' => $monthnames[$month->getMonth() - 1]
                . ' ' . $month->getYear(),
            'monthList' => $this->listService->getDailyList($occupancy, $month),
        ]);
        return $view;
    }

    /**
     * @return View
     */
    private function preparePaginationView()
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'pagination');
        $view->setData([
            'items' => $this->getPaginationItems(),
        ]);
        return $view;
    }

    /**
     * @return array<stdClass>
     */
    private function getPaginationItems()
    {
        $paginationItems = (new DailyPagination($this->year, $this->month, $this->now))->getItems();
        foreach ($paginationItems as $item) {
            $item->url = $this->modifyUrl(array(
                'ocal_year' => $item->year,
                'ocal_month' => $item->monthOrWeek,
                'ocal_action' => $this->mode
            ));
        }
        return $paginationItems;
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
        $occupancy = $db->findOccupancy($this->name);
        foreach ($states as $month => $states) {
            foreach ($states as $i => $state) {
                $date = sprintf('%s-%02d', $month, $i + 1);
                $occupancy->setState($date, $state);
            }
        }
        $db->saveOccupancy($occupancy);
        $db = null;
        return XH_message('success', $this->lang['message_saved']);
    }
}
