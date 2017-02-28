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

class DailyCalendarController extends CalendarController
{
    public function defaultAction()
    {
        global $plugin_tx, $_XH_csrfProtection;

        if (!$this->validateName()) {
            return;
        }
        if (XH_ADM && isset($_GET['ocal_save']) && $_GET['ocal_name'] == $this->name) {
            $_XH_csrfProtection->check();
            while (ob_get_level()) {
                ob_end_clean();
            }
            echo $this->saveStates();
            exit;
        }
        $db = new Db(LOCK_SH);
        $occupancy = $db->findOccupancy($this->name);
        $db = null;
        $view = $this->getView($occupancy);
        $html = $view->render($this->count);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $this->name) {
                header('Content-Type: text/html; charset=UTF-8');
                while (ob_get_level()) {
                    ob_end_clean();
                }
                echo $html;
                exit;
            } else {
                return;
            }
        } else {
            echo $html;
            return;
        }
    }

    /**
     * @return ?string
     */
    private function saveStates()
    {
        global $plugin_tx;

        $states = XH_decodeJson($_POST['ocal_states']);
        if (!is_object($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $db = new Db(LOCK_EX);
        $occupancy = $db->findOccupancy($this->name);
        foreach (get_object_vars($states) as $month => $states) {
            foreach ($states as $i => $state) {
                $date = sprintf('%s-%02d', $month, $i + 1);
                $occupancy->setState($date, $state);
            }
        }
        $db->saveOccupancy($occupancy);
        $db = null;
        return XH_message('success', $plugin_tx['ocal']['message_saved']);
    }
}
