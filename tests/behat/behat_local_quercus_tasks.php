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

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

use Behat\Gherkin\Node\TableNode;
use local_quercus_tasks\helper_trait;

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat steps in plugin local_quercus_tasks
 *
 * @package    local_quercus_tasks
 * @category   test
 * @author     Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright  2023 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_quercus_tasks extends behat_base {

    /**
     * Create new Quercus assignmnet
     *
     * @Given /^the following Quercus assignment exists:$/
     * @param TableNode $data
     * @return void
     */
    public function the_following_quercus_assign_exists(TableNode $data) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/quercus_tasks/lib.php');
        $assigndata = $data->getRowsHash();
        if (!isset($assigndata['course'])) {
            throw new Exception('The course shortname must be provided in the course field');
        }
        $course = $DB->get_record('course', ['idnumber' => $assigndata['course']]);
        $assigndata['courseid'] = $course->id;
        unset($assigndata['course']);

        /** @var local_quercus_tasks_generator $dg */
        $dg = behat_util::get_data_generator()->get_plugin_generator('local_quercus_tasks');
        $record = $dg->create_quercusdata_item($assigndata);
        $quercusdata = $dg->preprocess_quercusdata($record);
        $config = $this->assign_config();
        insert_assign($course, $quercusdata, $config);
    }

    /**
     * Gets default assignment settings that are used when creating an assignment.
     *
     * @return object
     */
    private function assign_config() {
        $assignconfig = new stdClass();
        $assignconfig->submissiondrafts = get_config('assign', 'submissiondrafts');
        $assignconfig->sendnotifications = get_config('assign', 'sendnotifications');
        $assignconfig->sendlatenotifications = get_config('assign', 'sendlatenotifications');
        $assignconfig->requiresubmissionstatement = get_config('assign', 'requiresubmissionstatement');
        $assignconfig->teamsubmission = get_config('assign', 'teamsubmission');
        $assignconfig->requireallteammemberssubmit = get_config('assign', 'requireallteammemberssubmit');
        $assignconfig->teamsubmissiongroupingid = get_config('assign', 'teamsubmissiongroupingid');
        $assignconfig->blindmarking = get_config('assign', 'blindmarking');
        $assignconfig->attemptreopenmethod = get_config('assign', 'attemptreopenmethod');
        $assignconfig->maxattempts = get_config('assign', 'maxattempts');
        $assignconfig->markingworkflow = get_config('assign', 'markingworkflow');
        $assignconfig->markingallocation = get_config('assign', 'markingallocation');
        $assignconfig->sendstudentnotifications = get_config('assign', 'sendstudentnotifications');
        $assignconfig->preventsubmissionnotingroup = get_config('assign', 'preventsubmissionnotingroup');

        $assignconfig->assignfeedback_comments_enabled = get_config('assignfeedback_comments', 'default');
        $assignconfig->assignfeedback_comments_commentinline = get_config('assignfeedback_comments', 'inline');
        $assignconfig->assignfeedback_doublemark_enabled = get_config('assignfeedback_doublemark', 'default');
        $assignconfig->assignfeedback_file_enabled = get_config('assignfeedback_file', 'default');
        $assignconfig->assignfeedback_misconduct_enabled = get_config('assignfeedback_misconduct', 'default');
        $assignconfig->assignfeedback_penalties_enabled = get_config('assignfeedback_penalties', 'default');
        $assignconfig->assignfeedback_sample_enabled = get_config('assignfeedback_sample', 'default');

        return $assignconfig;
    }

    /**
     * Creates or updates entries in Quercus assign table.
     *
     * @Given /^the following Quercus grades are stored for "([^"]*)":$/
     * @param string $idnumber Assignment idnumber
     * @param TableNode $data
     * @return void
     */
    public function the_following_quercus_srsstatus_responses_are_stored($idnumber, TableNode $data) {
        global $DB, $USER;
        $rows = $data->getHash();
        $cmid = $DB->get_field('course_modules', 'id', ['idnumber' => $idnumber]);
        if (!$cmid) {
            throw new moodle_exception('Quercus assignmnet doesn\'t exist');
        }
        [$course, $cm] = get_course_and_cm_from_cmid($cmid, 'assign');
        $parentrequestid = \core\uuid::generate();
        foreach ($rows as $row) {
            $student = $DB->get_record('user', ['username' => $row['user']]);
            $graderecord = $DB->get_record('local_quercus_grades', [
                'course_module' => $cmid,
                'student' => $student->id,
            ]);
            if (!$graderecord) {
                $graderecord = new stdClass();
                $graderecord->assign = $cm->instance;
                $graderecord->course = $course->id;
                $graderecord->sitting = $row['sitting'] ?? null;
                $graderecord->grader = $USER->id;
                $graderecord->student = $student->id;
                $graderecord->converted_grade = $row['converted_grade'] ?? 0;
                $graderecord->response = $row['response'] ?? null;
                $graderecord->parent_request_id = $row['parent_request_id'] ?? null;
                $graderecord->request_id = $row['request_id'] ?? null;
                $graderecord->processed = $row['message'] ?? null;
                $graderecord->timecreated = $row['timecreated'] ?? time();
                $graderecord->timemodified = $row['timemodified'] ?? time();
                $DB->insert_record('local_quercus_grades', $graderecord);
            } else {
                $graderecord->converted_grade = $row['converted_grade'] ?? $graderecord->converted_grade;
                $graderecord->response = $row['response'] ?? $graderecord->response;
                if (isset($row['response'])) {
                    $graderecord->parent_request_id = $parentrequestid;
                    $graderecord->request_id = \core\uuid::generate();
                }
                $graderecord->processed = $row['processed'] ?? $graderecord->processed;
                $graderecord->timecreated = $row['timecreated'] ?? $graderecord->timecreated;
                $graderecord->timemodified = $row['timemodified'] ?? time();
                $DB->update_record('local_quercus_grades', $graderecord);
            }
        }
    }
}
