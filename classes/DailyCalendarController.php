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
use Ocal\Dto\PaginationItem;
use Ocal\Model\DailyOccupancy;
use Ocal\Model\Month;
use Ocal\Model\Occupancy;
use Plib\CsrfProtector;
use Plib\DocumentStore;
use Plib\Request;
use Plib\Response;
use Plib\View;

class DailyCalendarController
{
    use CalendarController;

    /** @var CsrfProtector */
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
        CsrfProtector $csrfProtector,
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
        $this->type = "daily";
    }

    protected function findOccupancy(string $name): ?Occupancy
    {
        return DailyOccupancy::retrieve($name, $this->store);
    }

    protected function renderCalendarView(Request $request, Occupancy $occupancy, int $count): string
    {
        $data = [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->renderModeLinkView($request),
            'isEditable' => $request->admin(),
            'toolbar' => $this->renderToolbarView(),
            'statusbar' => $this->renderStatusbarView(),
            'monthPagination' => $this->renderPaginationView($request),
            'monthCalendars' => $this->getMonthCalendars($request, $occupancy, $count),
            'js_config' => $this->getJsConfig($request),
            'js_script' => $this->jsScript($request),
            'csrf_token' => $this->csrfProtector->token(),
            'checksum' => $occupancy->checksum(),
        ];
        return $this->view->render('daily-calendars', $data);
    }

    /** @return list<string> */
    private function getMonthCalendars(Request $request, Occupancy $occupancy, int $count): array
    {
        $monthCalendars = [];
        foreach (Month::createRange($this->year($request), $this->month($request), $count) as $month) {
            $monthCalendars[] = $this->renderMonthCalendarView($occupancy, $month);
        }
        return $monthCalendars;
    }

    private function renderMonthCalendarView(Occupancy $occupancy, Month $month): string
    {
        return $this->view->render('daily-calendar', [
            'isoDate' => $month->getIso(),
            'year' => $month->getYear(),
            'monthname' => $this->getMonthName($month->getMonth()),
            'daynames' => array_map('trim', explode(',', $this->view->plain("date_days"))),
            'weeks' => $this->getWeeks($occupancy, $month),
        ]);
    }

    private function getMonthName(int $month): string
    {
        $monthnames = array_map('trim', explode(',', $this->view->plain("date_months")));
        return $monthnames[$month - 1];
    }

    /** @return list<list<?object{day:?int,state:int,todayClass:string,titleKey:string}>> */
    private function getWeeks(Occupancy $occupancy, Month $month): array
    {
        $weeks = [];
        foreach ($month->getDaysOfWeeks() as $week) {
            $weeks[] = $this->getWeekDays($occupancy, $month, $week);
        }
        return $weeks;
    }

    /**
     * @param list<?int> $week
     * @return list<?object{day:?int,state:int,todayClass:string,titleKey:string}>
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

    protected function renderListView(Request $request, Occupancy $occupancy, int $count): string
    {
        return $this->view->render('daily-lists', [
            'occupancyName' => $occupancy->getName(),
            'modeLink' => $this->renderModeLinkView($request),
            'statusbar' => $this->renderStatusbarView(),
            'monthLists' => $this->getMonthLists($request, $occupancy, $count),
            'monthPagination' => $this->renderPaginationView($request),
            'js_config' => $this->getJsConfig($request),
            'js_script' => $this->jsScript($request),
        ]);
    }

    /** @return list<string> */
    private function getMonthLists(Request $request, Occupancy $occupancy, int $count): array
    {
        $monthLists = [];
        foreach (Month::createRange($this->year($request), $this->month($request), $count) as $month) {
            $monthLists[] = $this->renderMonthListView($occupancy, $month);
        }
        return $monthLists;
    }

    private function renderMonthListView(Occupancy $occupancy, Month $month): string
    {
        $monthnames = explode(',', $this->view->plain("date_months"));
        return $this->view->render('daily-list', [
            'heading' => $monthnames[$month->getMonth() - 1]
                . ' ' . $month->getYear(),
            'monthList' => $this->listService->getDailyList($occupancy, $month),
        ]);
    }

    private function renderPaginationView(Request $request): string
    {
        return $this->view->render('pagination', [
            'items' => $this->getPaginationItems($request),
        ]);
    }

    /** @return list<PaginationItem> */
    private function getPaginationItems(Request $request): array
    {
        $pagination = new DailyPagination(
            $this->year($request),
            $this->month($request),
            new DateTimeImmutable("@{$request->time()}"),
            (int) $this->config['pagination_past'],
            (int) $this->config['pagination_future']
        );
        $paginationItems = $pagination->getItems();
        foreach ($paginationItems as $item) {
            $item->url = $request->url()->with("ocal_year", (string) $item->year)
                ->with("ocal_month", (string) $item->monthOrWeek)
                ->with("ocal_action", $this->mode)
                ->relative();
        }
        return $paginationItems;
    }

    private function year(Request $request): int
    {
        return (int) ($request->get("ocal_year") ?? idate("Y", $request->time()));
    }

    private function month(Request $request): int
    {
        $month = $request->get("ocal_month");
        return $month !== null
            ? max(1, min(12, (int) $month))
            : (int) idate("n", $request->time());
    }

    protected function saveStates(Request $request, string $name): Response
    {
        $states = json_decode($request->post("ocal_states") ?? "", true);
        if (!is_array($states)) {
            return Response::error(400);
        }
        $occupancy = DailyOccupancy::update($name, $this->store);
        if ($occupancy === null) {
            return Response::error(500);
        }
        if ($occupancy->checksum() !== $request->post("ocal_checksum")) {
            $this->store->rollback();
            return Response::error(409, $this->view->message("warning", "error_conflicts"));
        }
        foreach ($states as $month => $states) {
            if (preg_match('/\d{4}-\d{2}/', $month) && is_array($states)) {
                foreach ($states as $i => $state) {
                    if (is_int($i) && is_int($state)) {
                        $date = sprintf('%s-%02d', $month, $i + 1);
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
