<?php

/**
 * Testing the calendar views.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Bcal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Bcal_XH
 */

require_once './classes/Domain.php';
require_once './classes/Presentation.php';

/**
 * Testing the calendar views.
 *
 * @category Testing
 * @package  Bcal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Bcal_XH
 */
class CalendarViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array The localization of the plugins.
     */
    public function setUp()
    {
        global $plugin_tx;

        $plugin_tx['bcal'] = array(
            'date_months' => 'January,February,March,April,May,June,July,August,'
                . 'September,October,November,December',
            'date_days' => 'M,T,W,T,F,S,S',
            'label_prev_year' => 'Previous Year',
            'label_prev_month' => 'Previous Month',
            'label_today' => 'Today',
            'label_next_month' => 'Next Month',
            'label_next_year' => 'Next Year'
        );
    }

    /**
     * Tests that a calendar is rendered.
     *
     * @return void
     */
    public function testRendersCalendar()
    {
        $this->subject = new Bcal_Controller();
        $this->assertTag(
            array(
                'tag' => 'table',
                'attributes' => array('class' => 'bcal_calendar')
            ), $this->subject->renderCalendar(3)
        );
    }
}

?>
