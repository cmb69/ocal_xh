<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

use DateTime;

abstract class View
{
    /**
     * @var Occupancy
     */
    protected $occupancy;

    /**
     * @var int
     */
    protected $month;

    /**
     * @var int
     */
    protected $week;

    /**
     * @var int
     */
    protected $year;

    /**
     * @var int
     */
    protected $isoYear;

    /**
     * @var string
     */
    protected $mode;

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
     * @return string
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
     * @return string
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
     * @return string
     */
    protected function renderStatusbar()
    {
        return '<div class="ocal_statusbar"></div>';
    }

    /**
     * @return string
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
     * @param int $month
     * @param int $year
     * @param string $label
     * @return string
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
     * @param array $newParams
     * @return string
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
