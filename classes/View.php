<?php

/**
 * The abstract views.
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

use DateTime;

/**
 * The abstract views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
abstract class View
{
    /**
     * The occupancy.
     *
     * @var Occupancy
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
     * @param Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Occupancy $occupancy)
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

        $config = array(
            'message_unsaved_changes'
                => $plugin_tx['ocal']['message_unsaved_changes'],
            'isAdmin' => XH_ADM
        );
        $bjs .= '<script type="text/javascript">/* <![CDATA[ */'
            . 'var OCAL = ' . XH_encodeJson($config) . ';'
            . '/* ]]> */</script>'
            . '<script type="text/javascript" src="'
            . $pth['folder']['plugins'] . 'ocal/ocal.js"></script>';
    }

    /**
     * Renders a link to switch the view mode.
     *
     * @return string (X)HTML.
     *
     * @global array  The localization of the plugins.
     */
    protected function renderModeLink()
    {
        global $plugin_tx;

        $mode = $this->mode == 'calendar' ? 'list' : 'calendar';
        $url = $this->modifyUrl(array('ocal_mode' => $mode));
        $label = $this->mode == 'calendar'
            ? $plugin_tx['ocal']['label_list_view']
            : $plugin_tx['ocal']['label_calendar_view'];
        return '<p class="ocal_mode"><a href="' . XH_hsc($url) . '">' . $label
            . '</a></p>';
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
     * @global array The localization of the plugins.
     *
     * @todo Restrict links to reasonable range, to avoid search engines
     *       searching infinitely.
     */
    protected function renderPaginationLink($month, $year, $label)
    {
        global $plugin_tx;

        $mode = $this->mode == 'list' ? 'list' : 'calendar';
        if ($month === false && $year === false) {
            $date = new DateTime();
            $year = $date->format('Y');
            $month = $date->format('n');
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
        }
        $url = $this->modifyUrl(
            array(
                'ocal_year' => $year, 'ocal_month' => $month,
                'ocal_mode' => $mode
            )
        );
        return '<a href="' . XH_hsc($url) . '">'
            . $plugin_tx['ocal']['label_'. $label] . '</a>';
    }

    /**
     * Returns the current URL with modified parameters.
     *
     * @param array $newParams An array of parameters to modify.
     *
     * @return string
     *
     * @global string The script name.
     */
    protected function modifyUrl(array $newParams)
    {
        global $sn;

        parse_str($_SERVER['QUERY_STRING'], $params);
        unset($params['ocal_ajax']);
        $params = array_merge($params, $newParams);
        $query = str_replace('=&', '&', http_build_query($params));
        return $sn . '?' . $query;
    }
}

?>
