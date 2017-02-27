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
        if (XH_ADM) {
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
            || isset($ocal) && $ocal == 'true';
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
        return '<h1>Ocal</h1>'
            . $this->renderLogo()
            . '<p>Version: ' . OCAL_VERSION . '</p>'
            . $this->renderCopyright() . $this->renderLicense();
    }

    /**
     * @return string
     */
    private function renderLicense()
    {
        return <<<EOT
<p class="ocal_license">This program is free software: you can redistribute
it and/or modify it under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.</p>
<p class="ocal_license">This program is distributed in the hope that it will
be useful, but <em>without any warranty</em>; without even the implied warranty
of <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
the GNU General Public License for more details.</p>
<p class="ocal_license">You should have received a copy of the GNU General
Public License along with this program. If not, see <a
href="http://www.gnu.org/licenses/"
target="_blank">http://www.gnu.org/licenses/</a>. </p>
EOT;
    }

    /**
     * @return string
     */
    private function renderLogo()
    {
        global $pth, $plugin_tx;

        return tag(
            'img src="' . $pth['folder']['plugins']. 'ocal/ocal.png"'
            . ' class="ocal_logo" alt="' . $plugin_tx['ocal']['alt_logo'] . '"'
        );
    }

    /**
     * @return string
     */
    private function renderCopyright()
    {
        return <<<EOT
<p>Copyright &copy; 2014-2017
    <a href="http://3-magi.net/" target="_blank">Christoph M. Becker</a>
</p>
EOT;
    }

    public function disallowIndexing()
    {
        global $cf;

        $cf['meta']['robots'] = 'noindex, nofollow';
    }

    /**
     * @param string $name
     * @param int $monthCount
     * @return string
     */
    public function renderCalendar($name, $monthCount)
    {
        global $plugin_tx, $_XH_csrfProtection;

        if (!preg_match('/^[a-z0-9-]+$/', $name)) {
            return XH_message('fail', $plugin_tx['ocal']['error_occupancy_name']);
        }
        if (XH_ADM && isset($_GET['ocal_save']) && $_GET['ocal_name'] == $name) {
            $_XH_csrfProtection->check();
            ob_end_clean(); // necessary, if called from template
            echo $this->saveStates($name);
            exit;
        }
        $db = new Db(LOCK_SH);
        $occupancy = $db->findOccupancy($name);
        $db = null;
        $view = $this->getView($occupancy);
        $html = $view->render($monthCount);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $name) {
                header('Content-Type: text/html; charset=UTF-8');
                echo $html;
                exit;
            } else {
                return;
            }
        } else {
            return $html;
        }
    }

    /**
     * @param string $name
     */
    protected function saveStates($name)
    {
        global $plugin_tx;

        $states = XH_decodeJson($_POST['ocal_states']);
        if (!is_object($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $db = new Db(LOCK_EX);
        $occupancy = $db->findOccupancy($name);
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

    /**
     * @param string $name
     * @param int  $weekCount
     * @return string
     */
    public function renderWeekCalendar($name, $weekCount)
    {
        global $plugin_tx, $_XH_csrfProtection;

        if (!preg_match('/^[a-z0-9-]+$/', $name)) {
            return XH_message('fail', $plugin_tx['ocal']['error_occupancy_name']);
        }
        if (XH_ADM && isset($_GET['ocal_save']) && $_GET['ocal_name'] == $name) {
            $_XH_csrfProtection->check();
            ob_end_clean(); // necessary, if called from template
            echo $this->saveHourlyStates($name);
            exit;
        }
        $db = new Db(LOCK_SH);
        $occupancy = $db->findOccupancy($name, true);
        $db = null;
        $view = $this->getView($occupancy);
        $html = $view->render($weekCount);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $name) {
                header('Content-Type: text/html; charset=UTF-8');
                echo $html;
                exit;
            } else {
                return;
            }
        } else {
            return $html;
        }
    }

    /**
     * @param string $name
     */
    protected function saveHourlyStates($name)
    {
        global $plugin_cf, $plugin_tx;

        $states = XH_decodeJson($_POST['ocal_states']);
        if (!is_object($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $db = new Db(LOCK_EX);
        $occupancy = $db->findOccupancy($name, true);
        foreach (get_object_vars($states) as $week => $states) {
            foreach ($states as $i => $state) {
                $day = $i % 7 + 1;
                $hour = $plugin_cf['ocal']['hour_interval'] * (int) ($i / 7) + $plugin_cf['ocal']['hour_first'];
                $date = sprintf('%s-%02d-%02d', $week, $day, $hour);
                $occupancy->setState($date, $state);
            }
        }
        $db->saveOccupancy($occupancy);
        $db = null;
        return XH_message('success', $plugin_tx['ocal']['message_saved']);
    }

    /**
     * @return View
     */
    protected function getView(Occupancy $occupancy)
    {
        $mode = isset($_GET['ocal_mode']) ? $_GET['ocal_mode'] : 'calendar';
        switch ($mode) {
            case 'list':
                if ($occupancy instanceof HourlyOccupancy) {
                    return new WeekListView($occupancy);
                } else {
                    return new ListView($occupancy);
                }
                break;
            default:
                if ($occupancy instanceof HourlyOccupancy) {
                    return new WeekCalendars($occupancy);
                } else {
                    return new Calendars($occupancy);
                }
        }
    }
}
