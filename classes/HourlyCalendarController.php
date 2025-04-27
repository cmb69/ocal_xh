<?php

/**
 * Copyright (c) Christoph M. Becker
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
use Ocal\Model\HourlyOccupancy;
use Ocal\Model\Occupancy;
use Ocal\Model\Week;
use Plib\DocumentStore;
use Plib\Request;
use Plib\Response;
use Plib\View;
use stdClass;
use XH\CSRFProtection as CsrfProtector;

class HourlyCalendarController
{
    use CalendarController;

    /** @var ?CsrfProtector */
    private $csrfProtector;

    /** @var array<string,string> */
    private $config;

    /** @var string */
    private $mode;

    /** @var string */
    private $pluginFolder;

    /** @var ListService */
    private $listService;

    /** @var DocumentStore */
    private $store;

    /** @var View */
    private $view;

    /** @var string */
    private $type;

    /**
     * @param array<string,string> $config
     */
    public function __construct(
        string $pluginFolder,
        ?CsrfProtector $csrfProtector,
        array $config,
        ListService $listService,
        DocumentStore $store,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->csrfProtector = $csrfProtector;
        $this->config = $config;
        $this->listService = $listService;
        $this->store = $store;
        $this->view = $view;
        $this->type = "hourly";
    }

    protected function findOccupancy(string $name): ?Occupancy
    {
        return HourlyOccupancy::retrieve($name, $this->store);
    }

    protected function renderCalendarView(Request $request, Occupancy $occupancy, int $count): string
    {
        $data = [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->renderModeLinkView($request),
            'isEditable' => $request->admin(),
            'toolbar' => $this->renderToolbarView(),
            'statusbar' => $this->renderStatusbarView(),
            'weekPagination' => $this->renderPaginationView($request, $count),
            'weekCalendars' => $this->getWeekCalendars($request, $occupancy, $count),
            'js_config' => $this->getJsConfig($request),
            'js_script' => $this->pluginFolder . "ocal.min.js",
        ];
        if ($request->admin()) {
            assert($this->csrfProtector !== null);
            $data['csrfTokenInput'] = $this->csrfProtector->tokenInput();
        }
        return $this->view->render('hourly-calendars', $data);
    }

    /** @return list<string> */
    private function getWeekCalendars(Request $request, Occupancy $occupancy, int $count): array
    {
        $weekCalendars = [];
        foreach (Week::createRange($this->isoYear($request), $this->week($request), $count) as $week) {
            $weekCalendars[] = $this->renderWeekCalendarView($request, $occupancy, $week);
        }
        return $weekCalendars;
    }

    private function renderWeekCalendarView(Request $request, Occupancy $occupancy, Week $week): string
    {
        $now = new DateTimeImmutable("@{$request->time()}");
        $from = $now->setISODate($week->getYear(), $week->getWeek(), 1);
        $to = $now->setISODate($week->getYear(), $week->getWeek(), 7);
        return $this->view->render('hourly-calendar', [
            'date' => $week->getIso(),
            'from' => $from->format($this->view->plain("date_format")),
            'to' => $to->format($this->view->plain("date_format")),
            'daynames' => array_map('trim', explode(',', $this->view->plain("date_days"))),
            'hours' => $this->getDaysOfHours($occupancy, $week),
        ]);
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

    protected function renderListView(Request $request, Occupancy $occupancy, int $count): string
    {
        return $this->view->render('hourly-lists', [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->renderModeLinkView($request),
            'statusbar' => $this->renderStatusbarView(),
            'weekPagination' => $this->renderPaginationView($request, $count),
            'weekLists' => $this->getWeekLists($request, $occupancy, $count),
            'js_config' => $this->getJsConfig($request),
            'js_script' => $this->pluginFolder . "ocal.min.js",
        ]);
    }

    /** @return list<string> */
    private function getWeekLists(Request $request, Occupancy $occupancy, int $count): array
    {
        $weekLists = [];
        foreach (Week::createRange($this->isoYear($request), $this->week($request), $count) as $week) {
            $weekLists[] = $this->renderWeekListView($request, $occupancy, $week);
        }
        return $weekLists;
    }

    private function renderWeekListView(Request $request, Occupancy $occupancy, Week $week): string
    {
        $now = new DateTimeImmutable("@{$request->time()}");
        $from = $now->setISODate($week->getYear(), $week->getWeek(), 1);
        $to = $now->setISODate($week->getYear(), $week->getWeek(), 7);
        return $this->view->render('hourly-list', [
            'from' => $from->format($this->view->plain("date_format")),
            'to' => $to->format($this->view->plain("date_format")),
            'weekList' => $this->getWeekList($occupancy, $week),
        ]);
    }

    /** @return list<stdClass> */
    private function getWeekList(Occupancy $occupancy, Week $week): array
    {
        $weekList = [];
        foreach ($this->listService->getHourlyList($occupancy, $week) as $day) {
            $day->label = $day->date->format($this->view->plain("date_format"));
            $weekList[] = $day;
        }
        return $weekList;
    }

    private function renderPaginationView(Request $request, int $weekCount): string
    {
        return $this->view->render('pagination', [
            'items' => $this->getPaginationItems($request, $weekCount),
        ]);
    }

    /** @return list<stdClass> */
    private function getPaginationItems(Request $request, int $weekCount): array
    {
        $pagination = new HourlyPagination(
            $this->isoYear($request),
            $this->week($request),
            new DateTimeImmutable("@{$request->time()}"),
            (int) $this->config['pagination_past'],
            (int) $this->config['pagination_future']
        );
        $items = $pagination->getItems($weekCount);
        foreach ($items as $item) {
            $item->url = $request->url()->with("ocal_year", $item->year)->with("ocal_week", $item->monthOrWeek)
                ->with("ocal_action", $this->mode)->relative();
        }
        return $items;
    }

    private function isoYear(Request $request): int
    {
        return (int) ($request->get("ocal_year") ?? date("o", $request->time()));
    }

    private function week(Request $request): int
    {
        $week = $request->get("ocal_week");
        return $week !== null
            ? max(1, min(53, (int) $week))
            : (int) idate("W", $request->time());
    }

    protected function saveStates(Request $request, string $name): Response
    {
        $states = json_decode($request->post("ocal_states") ?? "", true);
        if (!is_array($states)) {
            return Response::error(400);
        }
        $occupancy = HourlyOccupancy::update($name, $this->store);
        if ($occupancy === null) {
            return Response::error(500);
        }
        $interval = (int) $this->config['hour_interval'];
        $first = (int) $this->config['hour_first'];
        foreach ($states as $week => $states) {
            if (preg_match('/\d{4}-\d{2}/', $week) && is_array($states)) {
                foreach ($states as $i => $state) {
                    if (is_int($i) && is_int($state)) {
                        $day = $i % 7 + 1;
                        $hour = $interval * intdiv($i, 7) + $first;
                        $date = sprintf('%s-%02d-%02d', $week, $day, $hour);
                        $occupancy->setState($date, $state, (int) $this->config["state_max"]);
                    }
                }
            }
        }
        if (!$this->store->commit()) {
            return Response::create($this->view->message("fail", "message_not_saved"));
        }
        return Response::create($this->view->message("success", "message_saved"));
    }
}
