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
     * @param string $name
     * @param int $count
     */
    public function __construct($name, $count)
    {
        parent::__construct($name, $count);
        $now = new DateTime();
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

        $view = new View('daily-calendars');
        $view->occupancyName = $occupancy->getName();
        $view->modeLinkView = $this->prepareModeLinkView();
        $view->isEditable = XH_ADM;
        $view->csrfTokenInput = new HtmlString($this->csrfProtector->tokenInput());
        $view->toolbarView = $this->prepareToolbarView();
        $view->statusbarView = $this->prepareStatusbarView();
        $view->monthPaginationView = $this->preparePaginationView();
        $view->months = Month::createRange($this->year, $this->month, $this->count);
        $view->monthCalendarView = function (Month $month) use ($occupancy) {
            return $this->prepareMonthCalendarView($occupancy, $month);
        };
        return $view;
    }

    /**
     * @return View
     */
    private function prepareMonthCalendarView(Occupancy $occupancy, Month $month)
    {
        $view = new View('daily-calendar');
        $view->isoDate = $month->getIso();
        $view->year = $month->getYear();
        $monthnames = array_map('trim', explode(',', $this->lang['date_months']));
        $view->monthname = $monthnames[$month->getMonth() - 1];
        $view->daynames = array_map('trim', explode(',', $this->lang['date_days']));
        $view->weeks = $month->getDaysOfWeeks();
        $view->state = function ($day) use ($occupancy, $month) {
            return $occupancy->getDailyState($month->getYear(), $month->getMonth(), $day);
        };
        $view->todayClass = function ($day) use ($month) {
            $date = sprintf('%04d-%02d-%02d', $month->getYear(), $month->getMonth(), $day);
            return $date === date('Y-m-d') ? ' ocal_today' : '';
        };
        $view->titleKey = function ($day) use ($occupancy, $month) {
            $state = $occupancy->getDailyState($month->getYear(), $month->getMonth(), $day);
            return "label_state_$state";
        };
        return $view;
    }

    /**
     * @return View
     */
    protected function getListView(Occupancy $occupancy)
    {
        $this->emitScriptElements();
        $view = new View('daily-lists');
        $view->occupancyName = $occupancy->getName();
        $view->modeLinkView = $this->prepareModeLinkView();
        $view->statusbarView = $this->prepareStatusbarView();
        $view->months = Month::createRange($this->year, $this->month, $this->count);
        $view->monthListView = function ($month) use ($occupancy) {
            return $this->prepareMonthListView($occupancy, $month);
        };
        $view->monthPaginationView = $this->preparePaginationView();
        return $view;
    }

    /**
     * @return View
     */
    private function prepareMonthListView(Occupancy $occupancy, Month $month)
    {
        $view = new View('daily-list');
        $monthnames = explode(',', $this->lang['date_months']);
        $view->heading = $monthnames[$month->getMonth() - 1]
            . ' ' . $month->getYear();
        $view->monthList = (new ListService)->getDailyList($occupancy, $month);
        return $view;
    }

    /**
     * @return View
     */
    private function preparePaginationView()
    {
        $view = new View('pagination');
        $view->items = (new DailyPagination($this->year, $this->month, new DateTime()))->getItems();
        $view->url = function ($year, $month) {
            return $this->modifyUrl(['ocal_year' => $year, 'ocal_month' => $month, 'ocal_action' => $this->mode]);
        };
        return $view;
    }

    /**
     * @return ?string
     */
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
