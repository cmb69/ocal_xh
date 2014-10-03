<?php

/**
 * Testing the calendar views.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

require_once './vendor/autoload.php';
require_once '../../cmsimple/classes/CSRFProtection.php';
require_once './classes/DataSource.php';
require_once './classes/Domain.php';
require_once './classes/Presentation.php';

use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

/**
 * Testing the calendar views.
 *
 * @category Testing
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class CalendarViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global array             The paths of system files and folders.
     * @global array             The localization of the plugins.
     * @global XH_CSRFProtection The CSRF protector.
     */
    public function setUp()
    {
        global $pth, $plugin_tx, $_XH_csrfProtection;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth['folder'] = array(
            'base' => vfsStream::url('test/'),
            'plugins' => vfsStream::url('test/')
        );
        $plugin_tx['ocal'] = array(
            'date_months' => 'January,February,March,April,May,June,July,August,'
                . 'September,October,November,December',
            'date_days' => 'M,T,W,T,F,S,S',
            'label_calendar_view' => 'Calendar view',
            'label_list_view' => 'List view',
            'label_prev_year' => 'Previous Year',
            'label_prev_month' => 'Previous Month',
            'label_today' => 'Today',
            'label_next_month' => 'Next Month',
            'label_next_year' => 'Next Year',
            'label_save' => 'Save',
            'label_state_0' => '',
            'label_state_1' => 'available',
            'label_state_2' => 'reserved',
            'label_state_3' => 'booked',
            'message_unsaved_changes' => 'Unsaved changes will be lost'
        );
        $_XH_csrfProtection = $this->getMockBuilder('XH_CSRFProtection')
            ->disableOriginalConstructor()->getMock();
    }

    /**
     * Tests that a calendar is rendered.
     *
     * @return void
     */
    public function testRendersCalendar()
    {
        $this->subject = new Ocal_Controller();
        @$this->assertTag(
            array(
                'tag' => 'table',
                'attributes' => array('class' => 'ocal_calendar')
            ), $this->subject->renderCalendar('test', 3)
        );
    }
}

?>
