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

use stdClass;

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
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return $view->render('info', [
            'version' => OCAL_VERSION,
            'checks' => $this->getChecks(),
        ]);
    }

    /**
     * @return list<stdClass>
     */
    private function getChecks()
    {
        return array(
            $this->checkPhpVersion('7.1.0'),
            $this->checkExtension('filter'),
            $this->checkExtension('json'),
            $this->checkXhVersion('1.7.0'),
            $this->checkWritability($this->contentFolder),
            $this->checkWritability("{$this->pluginFolder}config/"),
            $this->checkWritability("{$this->pluginFolder}css/"),
            $this->checkWritability("{$this->pluginFolder}languages/")
        );
    }

    /**
     * @param string $version
     * @return stdClass
     */
    private function checkPhpVersion($version)
    {
        $state = $this->systemChecker->checkVersion(PHP_VERSION, $version) ? 'success' : 'fail';
        $label = sprintf($this->lang['syscheck_phpversion'], $version);
        $stateLabel = $this->lang["syscheck_$state"];
        return (object) compact('state', 'label', 'stateLabel');
    }

    /**
     * @param string $extension
     * @return stdClass
     */
    private function checkExtension($extension)
    {
        $state = $this->systemChecker->checkExtension($extension) ? 'success' : 'fail';
        $label = sprintf($this->lang['syscheck_extension'], $extension);
        $stateLabel = $this->lang["syscheck_$state"];
        return (object) compact('state', 'label', 'stateLabel');
    }

    /**
     * @param string $version
     * @return stdClass
     */
    private function checkXhVersion($version)
    {
        $state = $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $version") ? 'success' : 'fail';
        $label = sprintf($this->lang['syscheck_xhversion'], $version);
        $stateLabel = $this->lang["syscheck_$state"];
        return (object) compact('state', 'label', 'stateLabel');
    }

    /**
     * @param string $folder
     * @return stdClass
     */
    private function checkWritability($folder)
    {
        $state = $this->systemChecker->checkWritability($folder) ? 'success' : 'warning';
        $label = sprintf($this->lang['syscheck_writable'], $folder);
        $stateLabel = $this->lang["syscheck_$state"];
        return (object) compact('state', 'label', 'stateLabel');
    }
}
