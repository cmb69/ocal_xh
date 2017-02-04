<?php

/**
 * The list views.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

namespace Ocal;

/**
 * The list views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class ListView extends View
{
    /**
     * Initializes a new instance.
     *
     * @param Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Occupancy $occupancy)
    {
        parent::__construct($occupancy);
        $this->mode = 'list';
    }

    /**
     * Renders the list view.
     *
     * @param int $monthCount A number of months.
     *
     * @return string (X)HTML.
     */
    public function render($monthCount)
    {
        $this->emitScriptElements();
        $html = '<div>' . $this->renderModeLink()
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
            . $this->renderPagination() . '</div>';
        return $html;
    }
}

?>
