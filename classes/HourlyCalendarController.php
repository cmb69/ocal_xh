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

class HourlyCalendarController extends CalendarController
{
    /** @var int */
    private $week;

    /** @var int */
    private $isoYear;

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
        bool $isAdmin
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
            $isAdmin
        );
        $this->week = isset($_GET['ocal_week'])
            ? max(1, min(53, (int) $_GET['ocal_week']))
            : (int) $now->format('W');
        $this->isoYear = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : (int) $now->format('o');
    }

    protected function findOccupancy(string $name): Occupancy
    {
        $this->db->lock(false);
        $result = $this->db->findOccupancy($name, true);
        $this->db->unlock();
        return $result;
    }

    protected function renderCalendarView(Occupancy $occupancy, int $count): HtmlString
    {
        $this->emitScriptElements();
        
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        $data = [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->renderModeLinkView(),
            'isEditable' => $this->isAdmin,
            'toolbar' => $this->renderToolbarView(),
            'statusbar' => $this->renderStatusbarView(),
            'weekPagination' => $this->renderPaginationView($count),
            'weekCalendars' => $this->getWeekCalendars($occupancy, $count),
        ];
        if ($this->isAdmin) {
            $data['csrfTokenInput'] = new HtmlString($this->csrfProtector->tokenInput());
        }
        return new HtmlString($view->render('hourly-calendars', $data));
    }

    /** @return list<HtmlString> */
    private function getWeekCalendars(Occupancy $occupancy, int $count): array
    {
        $weekCalendars = [];
        foreach (Week::createRange($this->isoYear, $this->week, $count) as $week) {
            $weekCalendars[] = $this->renderWeekCalendarView($occupancy, $week);
        }
        return $weekCalendars;
    }

    private function renderWeekCalendarView(Occupancy $occupancy, Week $week): HtmlString
    {
        $from = $this->now->setISODate($week->getYear(), $week->getWeek(), 1);
        $to = $this->now->setISODate($week->getYear(), $week->getWeek(), 7);
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('hourly-calendar', [
            'date' => $week->getIso(),
            'from' => $from->format($this->lang['date_format']),
            'to' => $to->format($this->lang['date_format']),
            'daynames' => array_map('trim', explode(',', $this->lang['date_days'])),
            'hours' => $this->getDaysOfHours($occupancy, $week),
        ]));
    }

    /** @return list<list<stdClass>> */
    private function getDaysOfHours(Occupancy $occupancy, Week $week): array
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

    protected function renderListView(Occupancy $occupancy, int $count): HtmlString
    {
        $this->emitScriptElements();
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('hourly-lists', [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->renderModeLinkView(),
            'statusbar' => $this->renderStatusbarView(),
            'weekPagination' => $this->renderPaginationView($count),
            'weekLists' => $this->getWeekLists($occupancy, $count),
        ]));
    }

    /** @return list<HtmlString> */
    private function getWeekLists(Occupancy $occupancy, int $count): array
    {
        $weekLists = [];
        foreach (Week::createRange($this->isoYear, $this->week, $count) as $week) {
            $weekLists[] = $this->renderWeekListView($occupancy, $week);
        }
        return $weekLists;
    }

    private function renderWeekListView(Occupancy $occupancy, Week $week): HtmlString
    {
        $from = $this->now->setISODate($week->getYear(), $week->getWeek(), 1);
        $to = $this->now->setISODate($week->getYear(), $week->getWeek(), 7);
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('hourly-list', [
            'from' => $from->format($this->lang['date_format']),
            'to' => $to->format($this->lang['date_format']),
            'weekList' => $this->getWeekList($occupancy, $week),
        ]));
    }

    /** @return list<stdClass> */
    private function getWeekList(Occupancy $occupancy, Week $week): array
    {
        $weekList = [];
        foreach ($this->listService->getHourlyList($occupancy, $week) as $day) {
            $day->label = $day->date->format($this->lang['date_format']);
            $weekList[] = $day;
        }
        return $weekList;
    }

    private function renderPaginationView(int $weekCount): HtmlString
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('pagination', [
            'items' => $this->getPaginationItems($weekCount),
        ]));
    }

    /** @return list<stdClass> */
    private function getPaginationItems(int $weekCount): array
    {
        $pagination = new HourlyPagination(
            $this->isoYear,
            $this->week,
            $this->now,
            (int) $this->config['pagination_past'],
            (int) $this->config['pagination_future']
        );
        $items = $pagination->getItems($weekCount);
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
    protected function saveStates(string $name)
    {
        $states = json_decode($_POST['ocal_states'], true);
        if (!is_array($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $this->db->lock(true);
        $occupancy = $this->db->findOccupancy($name, true);
        foreach ($states as $week => $states) {
            foreach ($states as $i => $state) {
                $day = $i % 7 + 1;
                $hour = (int) $this->config['hour_interval'] * (int) ($i / 7) + (int) $this->config['hour_first'];
                $date = sprintf('%s-%02d-%02d', $week, $day, $hour);
                $occupancy->setState($date, $state);
            }
        }
        $this->db->saveOccupancy($occupancy);
        $this->db->unlock();
        return XH_message('success', $this->lang['message_saved']);
    }
}
