<?php

/**
 * Copyright 2014-2017 Christoph M. Becker
 *
 * This file is part of Ocal_XH.
 *
 * Ocal_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Ocal_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Ocal_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Ocal;

use PHPUnit_Framework_TestCase;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class CalendarViewTest extends PHPUnit_Framework_TestCase
{
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

    public function testRendersCalendar()
    {
        $this->subject = new Controller();
        @$this->assertTag(
            array(
                'tag' => 'table',
                'attributes' => array('class' => 'ocal_calendar')
            ),
            $this->subject->renderCalendar('test', 3)
        );
    }
}
