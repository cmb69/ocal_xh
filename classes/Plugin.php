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

class Plugin
{
    public function dispatch()
    {
        if (defined('XH_ADM') && XH_ADM) {
            if (function_exists('XH_registerStandardPluginMenuItems')) {
                XH_registerStandardPluginMenuItems(false);
            }
            if ($this->isAdministrationRequested()) {
                $this->handleAdministration();
            }
        } else {
            if (isset($_GET['ocal_week']) || isset($_GET['ocal_month'])
                || isset($_GET['ocal_year'])
            ) {
                XH_afterPluginLoading(array($this, 'disallowIndexing'));
            }
        }
    }

    /**
     * @return bool
     */
    protected function isAdministrationRequested()
    {
        global $ocal;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('ocal')
            || isset($ocal) && $ocal === 'true';
    }

    private function handleAdministration()
    {
        global $admin, $action, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
            case '':
                $o .= $this->renderInfo();
                break;
            default:
                $o .= plugin_admin_common($action, $admin, 'ocal');
        }
    }

    /**
     * @return string
     */
    private function renderInfo()
    {
        $controller = new DefaultAdminController();
        ob_start();
        $controller->defaultAction();
        return ob_get_clean();
    }

    public function disallowIndexing()
    {
        global $cf;

        $cf['meta']['robots'] = 'noindex, nofollow';
    }
}
