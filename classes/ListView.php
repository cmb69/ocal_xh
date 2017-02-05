<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

class ListView extends View
{
    public function __construct(Occupancy $occupancy)
    {
        parent::__construct($occupancy);
        $this->mode = 'list';
    }

    /**
     * @param int $monthCount
     * @return string
     */
    public function render($monthCount)
    {
        $this->emitScriptElements();
        $html = '<div data-name="' . $this->occupancy->getName() . '">' . $this->renderModeLink()
            . $this->renderLoaderbar() . $this->renderStatusbar()
            . '<dl class="ocal_list">';
        $month = new Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new MonthList($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= '</dl>'
            . $this->renderMonthPagination() . '</div>';
        return $html;
    }
}
