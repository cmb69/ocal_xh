<?php

/**
 * Copyright 2014-2023 Christoph M. Becker
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
use Plib\Response;
use Plib\View;
use XH\CSRFProtection as CsrfProtector;

abstract class CalendarController
{
    /** @var bool */
    protected static $isJavaScriptEmitted = false;

    /** @var ?CsrfProtector */
    protected $csrfProtector;

    /** @var array<string,string> */
    protected $config;

    /** @var array<string,string> */
    protected $lang;

    /** @var string */
    protected $mode;

    /** @var Url */
    protected $url;

    /** @var string */
    protected $pluginFolder;

    /** @var DateTimeImmutable */
    protected $now;

    /** @var ListService */
    protected $listService;

    /** @var Db */
    protected $db;

    /** @var bool */
    protected $isAdmin;

    /** @var View */
    protected $view;

    /** @var string */
    protected $type;

    /**
     * @param array<string,string> $config
     * @param array<string,string> $lang
     */
    public function __construct(
        Url $url,
        string $pluginFolder,
        ?CsrfProtector $csrfProtector,
        array $config,
        array $lang,
        DateTimeImmutable $now,
        ListService $listService,
        Db $db,
        bool $isAdmin,
        string $type
    ) {
        $this->url = $url;
        $this->pluginFolder = $pluginFolder;
        $this->csrfProtector = $csrfProtector;
        $this->config = $config;
        $this->lang = $lang;
        $this->now = $now;
        $this->listService = $listService;
        $this->db = $db;
        $this->isAdmin = $isAdmin;
        $this->type = $type;
        $this->view = new View("{$this->pluginFolder}views/", $this->lang);
    }

    public function defaultAction(string $name, int $count): Response
    {
        $this->mode = 'calendar';
        $occupancy = $this->findOccupancy($name);
        if ($occupancy !== null) {
            $html = $this->renderCalendarView($occupancy, $count);
        } else {
            $html = $this->view->message("fail", "message_not_{$this->type}", $name);
        }
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? null) !== 'XMLHttpRequest') {
            return Response::create($html);
        }
        if (($_GET['ocal_name'] ?? null) === $name) {
            return Response::create($html)->withContentType("text/html");
        }
        return Response::create();
    }

    public function listAction(string $name, int $count): Response
    {
        $this->mode = 'list';
        $occupancy = $this->findOccupancy($name);
        if ($occupancy !== null) {
            $html = $this->renderListView($occupancy, $count);
        } else {
            $html = $this->view->message("fail", "message_not_{$this->type}", $name);
        }
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? null) !== 'XMLHttpRequest') {
            return Response::create($html);
        }
        if (($_GET['ocal_name'] ?? null) === $name) {
            return Response::create($html)->withContentType("text/html");
        }
        return Response::create();
    }

    abstract protected function findOccupancy(string $name): ?Occupancy;

    abstract protected function renderCalendarView(Occupancy $occupancy, int $count): string;

    abstract protected function renderListView(Occupancy $occupancy, int $count): string;

    public function saveAction(string $name): Response
    {
        $this->mode = 'calendar';
        if (!$this->isAdmin || ($_GET['ocal_name'] ?? null) !== $name || $this->csrfProtector === null) {
            return Response::create();
        }
        $this->csrfProtector->check();
        $message = $this->saveStates($name);
        if ($message === null) {
            return Response::error(400);
        }
        return Response::create($message)->withContentType("text/html");
    }

    abstract protected function saveStates(string $name): ?string;

    /** @return void */
    protected function emitScriptElements()
    {
        global $bjs;

        if (self::$isJavaScriptEmitted) {
            return;
        }
        $config = array(
            'message_unsaved_changes' => $this->lang['message_unsaved_changes'],
            'isAdmin' => $this->isAdmin
        );
        $bjs .= '<script>'
            . 'var OCAL = ' . json_encode($config) . ';'
            . '</script>'
            . '<script src="'
            . $this->pluginFolder . 'ocal.min.js"></script>';
        self::$isJavaScriptEmitted = true;
    }

    protected function renderModeLinkView(): string
    {
        return $this->view->render('mode-link', [
            'mode' => $mode = $this->mode === 'calendar' ? 'list' : 'calendar',
            'url' => $this->url->replace(['ocal_action' => $mode]),
        ]);
    }

    protected function renderStatusbarView(): string
    {
        return $this->view->render('statusbar', [
            'image' => "{$this->pluginFolder}images/ajax-loader-bar.gif",
        ]);
    }

    protected function renderToolbarView(): string
    {
        return $this->view->render('toolbar', [
            'states' => range(0, $this->config['state_max']),
        ]);
    }
}
