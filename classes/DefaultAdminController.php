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

class DefaultAdminController implements Controller
{
    /**
     * @var string
     */
    private $pluginFolder;

    public function __construct()
    {
        global $pth;

        $this->pluginFolder = "{$pth['folder']['plugins']}ocal";
    }

    /** @return void */
    public function defaultAction()
    {
        $view = new View('info');
        $view->setData([
            'logo' => "$this->pluginFolder/ocal.png",
            'version' => OCAL_VERSION,
            'checks' => (new SystemCheckService)->getChecks(),
        ]);
        $view->render();
    }
}
