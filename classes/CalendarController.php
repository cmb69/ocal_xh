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

use Ocal\Model\Occupancy;
use Plib\Request;
use Plib\Response;

trait CalendarController
{
    public function __invoke(Request $request, string $name, int $count): Response
    {
        if (!preg_match('/^[a-z0-9-]+$/', $name)) {
            return Response::create($this->view->message("fail", "error_occupancy_name"));
        }
        switch ($request->get("ocal_action") ?? "") {
            default:
                return $this->defaultAction($request, $name, $count);
            case "list":
                return $this->listAction($request, $name, $count);
            case "save":
                return $this->saveAction($request, $name);
        }
    }

    private function defaultAction(Request $request, string $name, int $count): Response
    {
        $this->mode = 'calendar';
        $occupancy = $this->findOccupancy($name);
        if ($occupancy !== null) {
            $html = $this->renderCalendarView($request, $occupancy, $count);
        } else {
            $html = $this->view->message("fail", "message_not_{$this->type}", $name);
        }
        if ($request->header("X-CMSimple-XH-Request") !== 'ocal') {
            return Response::create($html);
        }
        if ($request->get("ocal_name") === $name) {
            return Response::create($html)->withContentType("text/html");
        }
        return Response::create();
    }

    private function listAction(Request $request, string $name, int $count): Response
    {
        $this->mode = 'list';
        $occupancy = $this->findOccupancy($name);
        if ($occupancy !== null) {
            $html = $this->renderListView($request, $occupancy, $count);
        } else {
            $html = $this->view->message("fail", "message_not_{$this->type}", $name);
        }
        if ($request->header("X-CMSimple-XH-Request") !== 'ocal') {
            return Response::create($html);
        }
        if ($request->get("ocal_name") === $name) {
            return Response::create($html)->withContentType("text/html");
        }
        return Response::create();
    }

    abstract protected function findOccupancy(string $name): ?Occupancy;

    abstract protected function renderCalendarView(Request $request, Occupancy $occupancy, int $count): string;

    abstract protected function renderListView(Request $request, Occupancy $occupancy, int $count): string;

    private function saveAction(Request $request, string $name): Response
    {
        $this->mode = 'calendar';
        if (!$request->admin() || $request->get("ocal_name") !== $name || $this->csrfProtector === null) {
            return Response::create();
        }
        $this->csrfProtector->check();
        $message = $this->saveStates($request, $name);
        if ($message === null) {
            return Response::error(400);
        }
        return Response::create($message)->withContentType("text/html");
    }

    abstract protected function saveStates(Request $request, string $name): ?string;

    /** @return array<string,mixed> */
    private function getJsConfig(Request $request): array
    {
        return [
            'message_unsaved_changes' => $this->view->plain("message_unsaved_changes"),
            'isAdmin' => $request->admin(),
        ];
    }

    private function renderModeLinkView(Request $request): string
    {
        return $this->view->render('mode-link', [
            'mode' => $mode = $this->mode === 'calendar' ? 'list' : 'calendar',
            'url' => $request->url()->with("ocal_action", $mode)->relative(),
        ]);
    }

    private function renderStatusbarView(): string
    {
        return $this->view->render('statusbar', [
            'image' => "{$this->pluginFolder}images/ajax-loader-bar.gif",
        ]);
    }

    private function renderToolbarView(): string
    {
        return $this->view->render('toolbar', [
            'states' => range(0, $this->config['state_max']),
        ]);
    }
}
