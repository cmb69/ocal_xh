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

use Plib\SystemChecker;
use Plib\View;
use stdClass;

class DefaultAdminController
{
    /** @var string */
    private $pluginFolder;

    /** @var string */
    private $contentFolder;

    /** @var SystemChecker */
    private $systemChecker;

    /** @var View */
    private $view;

    public function __construct(
        string $pluginFolder,
        string $contentFolder,
        SystemChecker $systemChecker,
        View $view
    ) {
        $this->pluginFolder = $pluginFolder;
        $this->contentFolder = $contentFolder;
        $this->systemChecker = $systemChecker;
        $this->view = $view;
    }

    public function defaultAction(): string
    {
        return $this->view->render('info', [
            'version' => OCAL_VERSION,
            'checks' => $this->getChecks(),
        ]);
    }

    /** @return list<stdClass> */
    private function getChecks(): array
    {
        return array(
            $this->checkPhpVersion('7.1.0'),
            $this->checkExtension('filter'),
            $this->checkExtension('json'),
            $this->checkXhVersion('1.7.0'),
            $this->checkPlibVersion('1.3'),
            $this->checkWritability($this->contentFolder),
            $this->checkWritability("{$this->pluginFolder}config/"),
            $this->checkWritability("{$this->pluginFolder}css/"),
            $this->checkWritability("{$this->pluginFolder}languages/")
        );
    }

    private function checkPhpVersion(string $version): stdClass
    {
        $state = $this->systemChecker->checkVersion(PHP_VERSION, $version) ? 'success' : 'fail';
        $label = $this->view->plain("syscheck_phpversion", $version);
        $stateLabel = $this->view->plain("syscheck_$state");
        return (object) compact('state', 'label', 'stateLabel');
    }

    private function checkExtension(string $extension): stdClass
    {
        $state = $this->systemChecker->checkExtension($extension) ? 'success' : 'fail';
        $label = $this->view->plain("syscheck_extension", $extension);
        $stateLabel = $this->view->plain("syscheck_$state");
        return (object) compact('state', 'label', 'stateLabel');
    }

    private function checkXhVersion(string $version): stdClass
    {
        $state = $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $version") ? 'success' : 'fail';
        $label = $this->view->plain("syscheck_xhversion", $version);
        $stateLabel = $this->view->plain("syscheck_$state");
        return (object) compact('state', 'label', 'stateLabel');
    }

    private function checkPlibVersion(string $version): stdClass
    {
        $state = $this->systemChecker->checkPlugin("plib", $version) ? 'success' : 'fail';
        $label = $this->view->plain("syscheck_plibversion", $version);
        $stateLabel = $this->view->plain("syscheck_$state");
        return (object) compact('state', 'label', 'stateLabel');
    }

    private function checkWritability(string $folder): stdClass
    {
        $state = $this->systemChecker->checkWritability($folder) ? 'success' : 'warning';
        $label = $this->view->plain("syscheck_writable", $folder);
        $stateLabel = $this->view->plain("syscheck_$state");
        return (object) compact('state', 'label', 'stateLabel');
    }
}
