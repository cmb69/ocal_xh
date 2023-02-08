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

use DateTimeImmutable;
use stdClass;
use XH\CSRFProtection as CsrfProtector;

class DailyCalendarController extends CalendarController
{
    /** @var int */
    private $month;

    /** @var int */
    private $year;

    /**
     * @param array<string,string> $config
     * @param array<string,string> $lang
     */
    public function __construct(
        string $scriptName,
        string $pluginFolder,
        CsrfProtector $csrfProtector,
        array $config,
        array $lang,
        DateTimeImmutable $now,
        ListService $listService,
        Db $db,
        bool $isAdmin,
        string $name,
        int $count
    ) {
        parent::__construct(
            $scriptName,
            $pluginFolder,
            $csrfProtector,
            $config,
            $lang,
            $now,
            $listService,
            $db,
            $isAdmin,
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

    protected function findOccupancy(): Occupancy
    {
        $this->db->lock(false);
        $result = $this->db->findOccupancy($this->name);
        $this->db->unlock();
        return $result;
    }

    protected function renderCalendarView(Occupancy $occupancy): HtmlString
    {
        $this->emitScriptElements();

        $view = new View("{$this->pluginFolder}views/", $this->lang);
        $data = [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->renderModeLinkView(),
            'isEditable' => $this->isAdmin,
            'toolbar' => $this->renderToolbarView(),
            'statusbar' => $this->renderStatusbarView(),
            'monthPagination' => $this->renderPaginationView(),
            'monthCalendars' => $this->getMonthCalendars($occupancy),
        ];
        if ($this->isAdmin) {
            $data['csrfTokenInput'] = new HtmlString($this->csrfProtector->tokenInput());
        }
        return new HtmlString($view->render('daily-calendars', $data));
    }

    /** @return list<HtmlString> */
    private function getMonthCalendars(Occupancy $occupancy): array
    {
        $monthCalendars = [];
        foreach (Month::createRange($this->year, $this->month, $this->count) as $month) {
            $monthCalendars[] = $this->renderMonthCalendarView($occupancy, $month);
        }
        return $monthCalendars;
    }

    private function renderMonthCalendarView(Occupancy $occupancy, Month $month): HtmlString
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('daily-calendar', [
            'isoDate' => $month->getIso(),
            'year' => $month->getYear(),
            'monthname' => $this->getMonthName($month->getMonth()),
            'daynames' => array_map('trim', explode(',', $this->lang['date_days'])),
            'weeks' => $this->getWeeks($occupancy, $month),
        ]));
    }

    private function getMonthName(int $month): string
    {
        $monthnames = array_map('trim', explode(',', $this->lang['date_months']));
        return $monthnames[$month - 1];
    }

    /** @return list<list<stdClass|null>> */
    private function getWeeks(Occupancy $occupancy, Month $month): array
    {
        $weeks = [];
        foreach ($month->getDaysOfWeeks() as $week) {
            $weeks[] = $this->getWeekDays($occupancy, $month, $week);
        }
        return $weeks;
    }

    /**
     * @param list<int|null> $week
     * @return list<stdClass|null>
     */
    private function getWeekDays(Occupancy $occupancy, Month $month, array $week): array
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

    protected function renderListView(Occupancy $occupancy): HtmlString
    {
        $this->emitScriptElements();
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('daily-lists', [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->renderModeLinkView(),
            'statusbar' => $this->renderStatusbarView(),
            'monthLists' => $this->getMonthLists($occupancy),
            'monthPagination' => $this->renderPaginationView(),
        ]));
    }

    /** @return list<HtmlString> */
    private function getMonthLists(Occupancy $occupancy): array
    {
        $monthLists = [];
        foreach (Month::createRange($this->year, $this->month, $this->count) as $month) {
            $monthLists[] = $this->renderMonthListView($occupancy, $month);
        }
        return $monthLists;
    }

    private function renderMonthListView(Occupancy $occupancy, Month $month): HtmlString
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        $monthnames = explode(',', $this->lang['date_months']);
        return new HtmlString($view->render('daily-list', [
            'heading' => $monthnames[$month->getMonth() - 1]
                . ' ' . $month->getYear(),
            'monthList' => $this->listService->getDailyList($occupancy, $month),
        ]));
    }

    private function renderPaginationView(): HtmlString
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('pagination', [
            'items' => $this->getPaginationItems(),
        ]));
    }

    /** @return array<stdClass> */
    private function getPaginationItems(): array
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
        $this->db->lock(true);
        $occupancy = $this->db->findOccupancy($this->name);
        foreach ($states as $month => $states) {
            foreach ($states as $i => $state) {
                $date = sprintf('%s-%02d', $month, $i + 1);
                $occupancy->setState($date, $state);
            }
        }
        $this->db->saveOccupancy($occupancy);
        $this->db->unlock();
        return XH_message('success', $this->lang['message_saved']);
    }
}
