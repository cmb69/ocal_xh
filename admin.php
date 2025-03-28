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

use Ocal\Dic;

/**
 * @var string $admin
 * @var string $o
 */

XH_registerStandardPluginMenuItems(false);

if (XH_wantsPluginAdministration('ocal')) {
    $o .= print_plugin_admin('off');
    switch ($admin) {
        case '':
            $o .= Dic::makeDefaultAdminController()->defaultAction();
            break;
        default:
            $o .= plugin_admin_common();
    }
}
