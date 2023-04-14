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

namespace local_quercus_tasks;

use advanced_testcase;
use assign;
use DateTime;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once(__DIR__ . '/helper_trait.php');
require_once($CFG->dirroot . '/local/quercus_tasks/lib.php');

/**
 * Test lib file
 * @group sol
 */
class lib_test extends advanced_testcase {

    use helper_trait;

    /**
     * Test inserting an assignment
     *
     * @covers \insert_assign
     * @return void
     */
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
        $this->turnitin_config($assigncm->id, $course->id);

        $useturnitin = $DB->get_field('plagiarism_turnitin_config', 'value', ['cm' => $assigncm->id, 'name' => 'use_turnitin']);
        $this->assertEquals(1, $useturnitin);
        $tiicourse = $DB->get_record('plagiarism_turnitin_courses', ['courseid' => $course->id]);
        $this->assertIsObject($tiicourse);

        $assign = new assign(null, $assigncm, $course);
        $doublemark = $assign->get_feedback_plugin_by_type('doublemark');
        $this->assertEquals('Double Marking', $doublemark->get_name());
        $this->assertEquals(1, $doublemark->is_enabled());
    }

    /**
     * Test creating new modules.
     *
     * @dataProvider new_modules_provider
     * @param object $row
     * @param int $acadyear
     * @param bool $status
     * @param string $expectedoutput
     * @covers \create_new_modules
     *
     * @return void
     */
    public function test_create_new_modules($row, $acadyear, $status, $expectedoutput) {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        // Create a Faculty category.
        $category = $this->getDataGenerator()->create_category(['idnumber' => 'Faculty1']);
        // Create template course.
        $template = $this->getDataGenerator()->create_course(['fullname' => 'Module Template 2021']);
        // Add an activity to the template.
        $this->getDataGenerator()->create_module('label', [
            'course' => $template->id,
            'intro' => 'Label from Template.'
        ]);
        // Create a quercus module - for testing purposes we don't get it directly from Quercus.
        $generator = $this->getDataGenerator()->get_plugin_generator('local_quercus_tasks');
        $quercusmodule = $generator->create_quercus_module($row);
        // Set the academic year from the dataProvider - not necessarily the same as the module.
        set_config('acadyear', $acadyear, 'local_quercus_tasks');
        set_config('createmodulelimit', 1, 'local_quercus_tasks');
        // Store the template id in config.
        set_config('moduletemplate', $template->id, 'local_quercus_tasks');
        create_new_modules();
        $newmodule = $DB->get_record('course', ['idnumber' => $row['idnumber']]);
        if ($status == true) {
            $this->assertNotFalse($newmodule);
            $this->assertEquals($category->id, $newmodule->category);
            // Check course summary text.
            $this->assertSame($row['summary'], $newmodule->summary);
            // Check it has some content from the template.
            $labelintemplates = $DB->get_record('label', ['course' => $newmodule->id, 'name' => 'Label from Template.']);
            $this->assertNotFalse($labelintemplates);
        } else {
            $this->assertFalse($newmodule);
        }
        $this->expectOutputString($expectedoutput);
    }

    /**
     * Provider for create_new_modules
     *
     * @return array
     */
    public function new_modules_provider() {
        return [
            'Success' => [
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '09/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => random_string(6) . '_' . random_int(1000000, 9999999),
                    'startdate' => '09-09-2021',
                    'enddate' => '31-12-2021'
                ],
                '2021',
                true,
                "Module1 created.\n"
            ],
            'Bad dates' => [
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '09/12/2021 - 25/09/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => random_string(6) . '_' . random_int(1000000, 9999999),
                    'startdate' => '09-12-2021',
                    'enddate' => '25-09-2021'
                ],
                '2021',
                false,
                "Error: Module1 is scheduled to end (25-09-2021) before it began (09-12-2021).\n"
            ],
            'Not current year' => [
                [
                    'acadyear' => '2022',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '09/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => random_string(6) . '_' . random_int(1000000, 9999999),
                    'startdate' => '09-09-2022',
                    'enddate' => '31-12-2022'
                ],
                '2021',
                false,
                "No new modules\n"
            ],
            'Faculty does not exist' => [
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '09/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty2',
                    'idnumber' => random_string(6) . '_' . random_int(1000000, 9999999),
                    'startdate' => '09-09-2021',
                    'enddate' => '31-12-2021'
                ],
                '2021',
                false,
                "Cannot resolve category path Faculty2\n"
            ],
        ];
    }

    /**
     * Tests that module dates get updated if they change.
     *
     * @dataProvider update_module_dates_provider
     * @param array $old
     * @param array $new
     * @param string $expectedoutput
     * @return void
     * @covers \update_module_dates
     */
    public function test_update_module_dates($old, $new, $expectedoutput) {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        // By default, Moodle's unit tests have a timezone of "Australia/Perth".
        // This is done to prevent baked in timezoning. But we need to have that
        // baked in for these tests as part of the processing uses the DB to process
        // timezone data.
        // It may be that these tests need to be tweaked for GMT/BST.
        $this->setTimezone('Europe/London');
        // Create Faculty dept.
        $category = $this->getDataGenerator()->create_category($old['category_path']);

        // Need to preprocess start end dates to ensure correct timezone in testing.
        [$sday, $smonth, $syear] = explode('-', $old['startdate']);
        [$eday, $emonth, $eyear] = explode('-', $old['enddate']);
        $stime = new DateTime();
        $stime->setDate($syear, $smonth, $sday);
        $stime->setTime(1, 0, 0);
        $etime = new DateTime();
        $etime->setDate($eyear, $emonth, $eday);
        $etime->setTime(0, 0, 0);
        // Don't need to worry about templates.
        // No need to use create_quercus_module as this muddles the output.
        $oldcourse = $this->getDataGenerator()->create_course([
            'fullname' => $old['fullname'],
            'shortname' => $old['shortname'],
            'summary' => $old['summary'],
            'category' => $category->id,
            'idnumber' => $old['idnumber'],
            'startdate' => $stime->getTimestamp(),
            'enddate' => $etime->getTimestamp()
        ]);

        $generator = $this->getDataGenerator()->get_plugin_generator('local_quercus_tasks');
        // The quercus_modules table is truncated between runs, so no need to create the
        // original entry.
        // Create new entry in quercus_modules table.
        $newmodule = $generator->create_quercus_module($new);

        // Actual function under test.
        update_modules();

        $course = $DB->get_record('course', ['idnumber' => $old['idnumber']]);
        [$sday, $smonth, $syear] = explode('-', $new['startdate']);
        [$eday, $emonth, $eyear] = explode('-', $new['enddate']);
        $stime = new DateTime();
        $stime->setDate($syear, $smonth, $sday);
        $stime->setTime(1, 0, 0);
        $etime = new DateTime();
        $etime->setDate($eyear, $emonth, $eday);
        $etime->setTime(0, 0, 0);

        $this->expectOutputString($expectedoutput);
        $this->assertSame($newmodule->summary, $course->summary);
        $this->assertEquals($course->startdate, $stime->getTimestamp());
        $this->assertEquals($course->enddate, $etime->getTimestamp());
    }

    /**
     * DataProvider for test_update_module_dates
     *
     * @return array
     */
    public function update_module_dates_provider() {
        $idnumber = random_string(6) . '_' . random_int(1000000, 9999999);
        return [
            'New start date' => [
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '09/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => $idnumber,
                    'startdate' => '09-09-2021',
                    'enddate' => '31-12-2021'
                ],
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '01/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => $idnumber,
                    'startdate' => '01-09-2021',
                    'enddate' => '31-12-2021'
                ],
                "Module1 - Start date: 09-09-2021 -> 01/09/2021 End date: 31-12-2021 -> 31/12/2021\n"
            ],
            'New end date' => [
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '01/09/2021 - 09/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => $idnumber,
                    'startdate' => '01-09-2021',
                    'enddate' => '09-12-2021'
                ],
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '01/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => $idnumber,
                    'startdate' => '01-09-2021',
                    'enddate' => '31-12-2021'
                ],
                "Module1 - Start date: 01-09-2021 -> 01/09/2021 End date: 09-12-2021 -> 31/12/2021\n"
            ],
            'New start and end date' => [
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '09/09/2021 - 09/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => $idnumber,
                    'startdate' => '09-09-2021',
                    'enddate' => '09-12-2021'
                ],
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '01/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => $idnumber,
                    'startdate' => '01-09-2021',
                    'enddate' => '31-12-2021'
                ],
                "Module1 - Start date: 09-09-2021 -> 01/09/2021 End date: 09-12-2021 -> 31/12/2021\n"
            ],
            'No change' => [
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '01/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => $idnumber,
                    'startdate' => '01-09-2021',
                    'enddate' => '31-12-2021'
                ],
                [
                    'acadyear' => '2021',
                    'fullname' => 'Module1',
                    'shortname' => 'Module1',
                    'summary' => '01/09/2021 - 31/12/2021',
                    'category_path' => 'Faculty1',
                    'idnumber' => $idnumber,
                    'startdate' => '01-09-2021',
                    'enddate' => '31-12-2021'
                ],
                "No dates need updating.\n"
            ]
        ];
    }
}
