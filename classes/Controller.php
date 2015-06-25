<?php

/**
 * The controllers.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

/**
 * The controllers.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_Controller
{
    /**
     * Dispatches according to the request.
     *
     * @return void
     */
    public function dispatch()
    {
        if (XH_ADM) {
            if (function_exists('XH_registerStandardPluginMenuItems')) {
                XH_registerStandardPluginMenuItems(false);
            }
            if ($this->isAdministrationRequested()) {
                $this->_handleAdministration();
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
     * Returns whether the plugin administration is requested.
     *
     * @return bool
     *
     * @global string Whether the plugin administration is requested.
     */
    protected function isAdministrationRequested()
    {
        global $ocal;

        return function_exists('XH_wantsPluginAdministration')
            && XH_wantsPluginAdministration('ocal')
            || isset($ocal) && $ocal == 'true';
    }

    /**
     * Handles the plugin administration.
     *
     * @return void
     *
     * @global string The value of the <var>admin</var> GP parameter.
     * @global string The value of the <var>action</var> GP parameter.
     * @global string The (X)HTML fragment to insert into the contents area.
     */
    private function _handleAdministration()
    {
        global $admin, $action, $o;

        $o .= print_plugin_admin('off');
        switch ($admin) {
        case '':
            $o .= $this->_renderInfo();
            break;
        default:
            $o .= plugin_admin_common($action, $admin, 'ocal');
        }
    }

    /**
     * Renders the plugin info.
     *
     * @return string (X)HTML.
     */
    private function _renderInfo()
    {
        return '<h1>Ocal</h1>'
            . $this->_renderLogo()
            . '<p>Version: ' . OCAL_VERSION . '</p>'
            . $this->_renderCopyright() . $this->_renderLicense();
    }

    /**
     * Renders the license info.
     *
     * @return string (X)HTML.
     */
    private function _renderLicense()
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
     * Renders the plugin logo.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     * @global array The localization of the plugins.
     */
    private function _renderLogo()
    {
        global $pth, $plugin_tx;

        return tag(
            'img src="' . $pth['folder']['plugins']. 'ocal/ocal.png"'
            . ' class="ocal_logo" alt="' . $plugin_tx['ocal']['alt_logo'] . '"'
        );
    }

    /**
     * Renders the copyright info.
     *
     * @return string (X)HTML.
     */
    private function _renderCopyright()
    {
        return <<<EOT
<p>Copyright &copy; 2014
    <a href="http://3-magi.net/" target="_blank">Christoph M. Becker</a>
</p>
EOT;
    }

    /**
     * Disallow indexing the page by setting meta robots appropriately.
     *
     * @return void
     *
     * @global array The configuration of the core.
     */
    public function disallowIndexing()
    {
        global $cf;

        $cf['meta']['robots'] = 'noindex, nofollow';
    }

    /**
     * Renders a calendar.
     *
     * @param string $name       A calendar name.
     * @param int    $monthCount A month count.
     *
     * @return string (X)HTML.
     *
     * @global array             The localization of the plugins.
     * @global XH_CRSFProtection The CSRF protector.
     */
    public function renderCalendar($name, $monthCount)
    {
        global $plugin_tx, $_XH_csrfProtection;

        if (!preg_match('/^[a-z0-9-]+$/', $name)) {
            return XH_message(
                'fail', $plugin_tx['ocal']['error_occupancy_name']
            );
        }
        if (XH_ADM && isset($_GET['ocal_save']) && $_GET['ocal_name'] == $name) {
            $_XH_csrfProtection->check();
            ob_end_clean(); // necessary, if called from template
            echo $this->saveStates($name);
            exit;
        }
        $db = new Ocal_Db(LOCK_SH);
        $occupancy = $db->findOccupancy($name);
        $db = null;
        $view = $this->getView($occupancy);
        return $view->render($monthCount);
    }

    /**
     * Saves the states.
     *
     * @param string $name A calendar name.
     *
     * @return void
     */
    protected function saveStates($name)
    {
        global $plugin_tx;

        $states = XH_decodeJson($_POST['ocal_states']);
        if (!is_object($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $db = new Ocal_Db(LOCK_EX);
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
     * Renders a week calendar.
     *
     * @param string $name      A calendar name.
     * @param int    $weekCount A week count.
     *
     * @return string (X)HTML.
     *
     * @global array             The localization of the plugins.
     * @global XH_CRSFProtection The CSRF protector.
     */
    public function renderWeekCalendar($name, $weekCount)
    {
        global $plugin_tx, $_XH_csrfProtection;

        if (!preg_match('/^[a-z0-9-]+$/', $name)) {
            return XH_message(
                'fail', $plugin_tx['ocal']['error_occupancy_name']
            );
        }
        if (XH_ADM && isset($_GET['ocal_save']) && $_GET['ocal_name'] == $name) {
            $_XH_csrfProtection->check();
            ob_end_clean(); // necessary, if called from template
            echo $this->saveHourlyStates($name);
            exit;
        }
        $db = new Ocal_Db(LOCK_SH);
        $occupancy = $db->findOccupancy($name, true);
        $db = null;
        $view = $this->getView($occupancy);
        return $view->render($weekCount);
    }

    /**
     * Saves the states.
     *
     * @param string $name A calendar name.
     *
     * @return void
     *
     * @global array The configuration of the plugins.
     * @global array The localization of the plugins.
     */
    protected function saveHourlyStates($name)
    {
        global $plugin_cf, $plugin_tx;

        $states = XH_decodeJson($_POST['ocal_states']);
        if (!is_object($states)) {
            header('HTTP/1.0 400 Bad Request');
            exit;
        }
        $db = new Ocal_Db(LOCK_EX);
        $occupancy = $db->findOccupancy($name, true);
        foreach (get_object_vars($states) as $week => $states) {
            foreach ($states as $i => $state) {
                $day = $i % 7 + 1;
                $hour = $i / 7 + $plugin_cf['ocal']['hour_first'];
                $date = sprintf('%s-%02d-%02d', $week, $day, $hour);
                $occupancy->setState($date, $state);
            }
        }
        $db->saveOccupancy($occupancy);
        $db = null;
        return XH_message('success', $plugin_tx['ocal']['message_saved']);
    }

    /**
     * Returns the requested view.
     *
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return Ocal_View
     */
    protected function getView($occupancy)
    {
        $mode = isset($_GET['ocal_mode']) ? $_GET['ocal_mode'] : 'calendar';
        switch ($mode) {
        case 'list':
            if ($occupancy instanceof Ocal_HourlyOccupancy) {
                return new Ocal_WeekCalendars($occupancy);
            } else {
                return new Ocal_ListView($occupancy);
            }
        default:
            if ($occupancy instanceof Ocal_HourlyOccupancy) {
                return new Ocal_WeekCalendars($occupancy);
            } else {
                return new Ocal_Calendars($occupancy);
            }
        }
    }
}

?>
