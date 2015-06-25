<?php

/**
 * The calendars.
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
 * The calendars.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_Calendars extends Ocal_View
{
    /**
     * Initializes a new instance.
     *
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Occupancy $occupancy)
    {
        parent::__construct($occupancy);
        $this->mode = 'calendar';
    }

    /**
     * Renders the calendars.
     *
     * @param int $monthCount A number of months.
     *
     * @return string (X)HTML.
     *
     * @global XH_CSRFProtection The CSRF protector.
     */
    public function render($monthCount)
    {
        global $_XH_csrfProtection;

        $this->emitScriptElements();
        $html = '<div class="ocal_calendars" data-name="'
            . $this->occupancy->getName() . '">'
            . $this->renderModeLink();
        if (XH_ADM) {
            $html .= $_XH_csrfProtection->tokenInput()
                . $this->renderToolbar()
                . $this->renderLoaderbar()
                . $this->renderStatusbar();
        }
        $month = new Ocal_Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new Ocal_MonthCalendar($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= $this->renderPagination()
            . '</div>';
        return $html;
    }

    /**
     * Emits the script elements.
     *
     * @return void
     *
     * @global array  The paths of system files and folders.
     * @global string The (X)HTML to insert at the bottom of the body element.
     * @global array  The localization of the plugins.
     */
    protected function emitScriptElements()
    {
        global $pth, $bjs, $plugin_tx;

        if (XH_ADM) {
            $config = array(
                'message_unsaved_changes'
                    => $plugin_tx['ocal']['message_unsaved_changes']
            );
            $bjs .= '<script type="text/javascript">/* <![CDATA[ */'
                . 'var OCAL = ' . XH_encodeJson($config) . ';'
                . '/* ]]> */</script>'
                . '<script type="text/javascript" src="'
                . $pth['folder']['plugins'] . 'ocal/ocal.js"></script>';
        }
    }

    /**
     * Renders the toolbar.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     */
    protected function renderToolbar()
    {
        global $plugin_tx;

        $html = '<div class="ocal_toolbar">';
        for ($i = 0; $i <= 3; $i++) {
            $alt = $plugin_tx['ocal']['label_state_' . $i];
            $title = $alt ? ' title="' . $alt . '"' : '';
            $html .= '<span class="ocal_state" data-ocal_state="' . $i . '"'
                . $title . '></span>';
        }
        $html .= '<button type="button" class="ocal_save" disabled="disabled">'
            . $plugin_tx['ocal']['label_save'] . '</button>'
            . '</div>';
        return $html;
    }

    /**
     * Renders the Ajax loader bar.
     *
     * @return string (X)HTML.
     *
     * @global array The paths of system files and folders.
     */
    protected function renderLoaderbar()
    {
        global $pth;

        $src = $pth['folder']['plugins'] . 'ocal/images/ajax-loader-bar.gif';
        return '<div class="ocal_loaderbar">'
            . tag('img src="' . $src . '" alt="loading"')
            . '</div>';
    }

    /**
     * Renders the status bar.
     *
     * @return string (X)HTML.
     */
    protected function renderStatusbar()
    {
        return '<div class="ocal_statusbar"></div>';
    }
}

?>
