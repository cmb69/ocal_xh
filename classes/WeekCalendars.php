<?php

/**
 * The week calendars.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2015 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

/**
 * The week calendars.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_WeekCalendars extends Ocal_Calendars
{
    /**
     * Renders the week calendars.
     *
     * @param int $weekCount A number of weeks.
     *
     * @return string (X)HTML.
     *
     * @global XH_CSRFProtection The CSRF protector.
     */
    public function render($weekCount)
    {
        global $_XH_csrfProtection;

        $this->emitScriptElements();
        $html = '<div class="ocal_week_calendars" data-name="'
            . $this->occupancy->getName() . '">';
        if (XH_ADM) {
            $html .= $_XH_csrfProtection->tokenInput()
                . $this->renderToolbar();
        }
        $html .= $this->renderLoaderbar() . $this->renderStatusbar();
        $week = new Ocal_Week($this->week, $this->year);
        $i = $weekCount;
        while ($i) {
            $calendar = new Ocal_WeekCalendar($week, $this->occupancy);
            $html .= $calendar->render();
            $i--;
            $week = $week->getNextWeek();
        }
        $html .= $this->renderPagination2($weekCount) . '</div>';
        return $html;
    }

    /**
     * Renders the pagination.
     *
     * @param int $weekCount A week count.
     *
     * @return string (X)HTML.
     */
    protected function renderPagination2($weekCount)
    {
        return '<p class="ocal_pagination">'
            . $this->renderPaginationLink2(-$weekCount, 'prev_interval') . ' '
            . $this->renderPaginationLink2(false, 'today') . ' '
            . $this->renderPaginationLink2($weekCount, 'next_interval')
            . '</p>';
    }

    /**
     * Renders a pagination link.
     *
     * @param int    $offset A week offset.
     * @param string $label  A label key.
     *
     * @return string (X)HTML.
     *
     * @global array The localization of the plugins.
     *
     * @todo Restrict links to reasonable range, to avoid search engines
     *       searching infinitely.
     */
    protected function renderPaginationLink2($offset, $label)
    {
        global $plugin_tx;

        $params = array('ocal_mode' => $this->mode == 'list' ? 'list' : 'calendar');
        if ($offset) {
            $week = new Ocal_Week($this->week, $this->year);
            $week = $week->getNextWeek($offset);
            $params['ocal_year'] = $week->getYear();
            $params['ocal_week'] = $week->getWeek();
        } else {
            $date = new DateTime();
            $params['ocal_year'] = $date->format('o');
            $params['ocal_week'] = $date->format('W');
        }
        $url = $this->modifyUrl($params);
        return '<a href="' . XH_hsc($url) . '">'
            . $plugin_tx['ocal']['label_'. $label] . '</a>';
    }
}

?>
