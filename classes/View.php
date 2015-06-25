<?php

/**
 * The abstract views.
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
 * The abstract views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
abstract class Ocal_View
{
    /**
     * The occupancy.
     *
     * @var Ocal_Occupancy
     */
    protected $occupancy;

    /**
     * The month.
     *
     * @var int
     */
    protected $month;

    /**
     * The week.
     *
     * @var int
     */
    protected $week;

    /**
     * The year.
     *
     * @var int
     */
    protected $year;

    /**
     * The ISO 8601 year.
     *
     * @var int
     */
    protected $isoYear;

    /**
     * The mode ('calendar' or 'list').
     *
     * @var string
     */
    protected $mode;

    /**
     * Initializes a new instance.
     *
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Occupancy $occupancy)
    {
        $now = time();
        $this->month = isset($_GET['ocal_month'])
            ? max(1, min(12, (int) $_GET['ocal_month']))
            : date('n', $now);
        $this->week = isset($_GET['ocal_week'])
            ? max(1, min(53, (int) $_GET['ocal_week']))
            : date('W', $now);
        $this->year = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : date('Y', $now);
        $this->isoYear = isset($_GET['ocal_year'])
            ? (int) $_GET['ocal_year']
            : date('o', $now);
        $this->occupancy = $occupancy;
    }

    /**
     * Renders a link to switch the view mode.
     *
     * @return string (X)HTML.
     *
     * @global string The script name.
     * @global string The requested page URL.
     * @global array  The localization of the plugins.
     */
    protected function renderModeLink()
    {
        global $sn, $su, $plugin_tx;

        $url = $sn . '?' . $su;
        if ($this->mode == 'calendar') {
            $url .= '&amp;ocal_mode=list';
        }
        $label = $this->mode == 'calendar'
            ? $plugin_tx['ocal']['label_list_view']
            : $plugin_tx['ocal']['label_calendar_view'];
        return '<p class="ocal_mode"><a href="' . $url . '">' . $label . '</a></p>';
    }

    /**
     * Renders the pagination.
     *
     * @return string (X)HTML.
     */
    protected function renderPagination()
    {
        return '<p class="ocal_pagination">'
            . $this->renderPaginationLink(0, -1, 'prev_year') . ' '
            . $this->renderPaginationLink(-1, 0, 'prev_month') . ' '
            . $this->renderPaginationLink(false, false, 'today') . ' '
            . $this->renderPaginationLink(1, 0, 'next_month') . ' '
            . $this->renderPaginationLink(0, 1, 'next_year')
            . '</p>';
    }

    /**
     * Renders a pagination link.
     *
     * @param int    $month A month.
     * @param int    $year  A year.
     * @param string $label A label key.
     *
     * @return string (X)HTML.
     *
     * @todo Restrict links to reasonable range, to avoid search engines
     *       searching infinitely.
     */
    protected function renderPaginationLink($month, $year, $label)
    {
        global $sn, $su, $plugin_tx;

        if ($month === false && $year === false) {
            $url = $sn . '?' . $su;
        } else {
            $month = $this->month + $month;
            $year = $this->year + $year;
            if ($month < 1) {
                $month = 12;
                $year -= 1;
            } elseif ($month > 12) {
                $month = 1;
                $year += 1;
            }
            $url = $sn . '?' . $su
                . '&amp;ocal_year=' . $year . '&amp;ocal_month=' . $month;
        }
        if ($this->mode == 'list') {
            $url .= '&amp;ocal_mode=list';
        }
        return '<a href="' . $url . '">' . $plugin_tx['ocal']['label_'. $label]
            . '</a>';
    }
}

?>
