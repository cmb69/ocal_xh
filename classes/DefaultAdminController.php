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

    /** @var SystemChecker */
    private $systemChecker;

    public function __construct(string $pluginFolder, SystemChecker $systemChecker)
    {
        $this->pluginFolder = $pluginFolder;
        $this->systemChecker = $systemChecker;
    }

    public function defaultAction(): string
    {
        $view = new View('info');
        $view->setData([
            'logo' => "{$this->pluginFolder}ocal.png",
            'version' => OCAL_VERSION,
            'checks' => (new SystemCheckService($this->systemChecker))->getChecks(),
        ]);
        ob_start();
        $view->render();
        return (string) ob_get_clean();
    }
}
