<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

class Calendars extends View
{
    public function __construct(Occupancy $occupancy)
    {
        parent::__construct($occupancy);
        $this->mode = 'calendar';
    }

    /**
     * @param int $monthCount
     * @return string
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
                . $this->renderToolbar();
        }
        $html .= $this->renderLoaderbar() . $this->renderStatusbar();
        $month = new Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new MonthCalendar($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= $this->renderPagination()
            . '</div>';
        return $html;
    }

    /**
     * @return string
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
}
