<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Test quercus tasks lib functions
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2021 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once(__DIR__ . '/helper_trait.php');
require_once($CFG->dirroot . '/local/quercus_tasks/lib.php');

class local_quercus_tasks_lib_testcase extends advanced_testcase {

    use local_quercus_tasks_helper_trait;

    public function check_if_exam() {

    }

    public function test_insert_assign() {
        global $DB;
        $this->resetAfterTest();
        $this->bitnbobs();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course([
            'idnumber' => 'ABC101_123456789'
        ]);
        $generator = $this->getDataGenerator()->get_plugin_generator('local_quercus_tasks');
        $quercusdataitem = $generator->create_quercusdata_item([
            'unitInstance' => 'ABC101_123456789',
            'module' => 'ABC101'
        ]);
        $quercusdata = $generator->preprocess_quercusdata($quercusdataitem);
        $assigncm = insert_assign($course, $quercusdata, $this->assign_config());
        $this->assertIsObject($assigncm);
        $this->turnitin_config($assigncm->id);
        
        $useturnitin = $DB->get_field('plagiarism_turnitin_config', 'value', ['cm' => $assigncm->id, 'name' => 'use_turnitin']);
        $this->assertEquals(1, $useturnitin);

        $assign = new assign(null, $assigncm, $course);
        $doublemark = $assign->get_feedback_plugin_by_type('doublemark');
        $this->assertEquals('Double Marking', $doublemark->get_name());
        $this->assertEquals(1, $doublemark->is_enabled());
    }
}