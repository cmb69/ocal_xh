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

class DefaultAdminController
{
    /**
     * @var string
     */
    private $pluginFolder;

    /**
     * @var array
     */
    private $lang;

    public function __construct()
    {
        global $pth, $plugin_tx;

        $this->pluginFolder = "{$pth['folder']['plugins']}ocal";
        $this->lang = $plugin_tx['ocal'];
    }

    public function defaultAction()
    {
        $view = new View('info');
        $view->logo = "$this->pluginFolder/ocal.png";
        $view->version = OCAL_VERSION;
        $view->checks = (new SystemCheckService)->getChecks();
        $view->stateLabel = function ($state) {
            return $this->lang["syscheck_$state"];
        };
        $view->render();
    }
}
