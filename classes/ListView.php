<?php

/**
 * The list views.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2015 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

/**
 * The list views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_ListView extends Ocal_View
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
        $html = $this->renderModeLink()
            . '<dl class="ocal_list">';
        $month = new Ocal_Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new Ocal_MonthList($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= '</dl>'
            . $this->renderPagination();
        return $html;
    }
}

?>
