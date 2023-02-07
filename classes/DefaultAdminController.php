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

    /** @var string */
    private $contentFolder;

    /** @var array<string,string> $lang */
    private $lang;

    /** @var SystemChecker */
    private $systemChecker;

    /** @param array<string,string> $lang */
    public function __construct(string $pluginFolder, string $contentFolder, array $lang, SystemChecker $systemChecker)
    {
        $this->pluginFolder = $pluginFolder;
        $this->contentFolder = $contentFolder;
        $this->lang = $lang;
        $this->systemChecker = $systemChecker;
    }

    public function defaultAction(): string
    {
        $view = new View('info');
        $systemCheckService = new SystemCheckService(
            $this->pluginFolder,
            $this->contentFolder,
            $this->lang,
            $this->systemChecker
        );
        $view->setData([
            'logo' => "{$this->pluginFolder}ocal.png",
            'version' => OCAL_VERSION,
            'checks' => $systemCheckService->getChecks(),
        ]);
        ob_start();
        $view->render();
        return (string) ob_get_clean();
    }
}
