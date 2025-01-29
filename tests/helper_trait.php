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
 * Sets up the plugin with sensible settings and objects that can used in testing.
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2021 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_quercus_tasks;

use context_course;
use stdClass;

/**
 * Some reusable test functions
 */
trait helper_trait {

    /**
     * Sets various settings required for mininum function
     *
     * @return void
     */
    public function bitnbobs() {
        if (class_exists('plagiarism_plugin_turnitin')) {
            set_config('enableplagiarism', 1);
            set_config('enabled', 1, 'plagiarism_turnitin');
            set_config('plagiarism_turnitin_mod_assign', 1, 'plagiarism_turnitin');
            $this->turnitin_defaults();
        }
        $this->set_configs();
        $this->assign_config();
    }

    /**
     * Default settings that make sense for our tasks.
     *
     * @return void
     */
    public function set_configs() {
        // These are non-default assignment settings that we have changed.
        set_config('allowsubmissionsfromdate', 0, 'assign');
        set_config('duedate', 185 * 3600, 'assign');
        set_config('cutoffdate', 353 * 3600, 'assign');
        set_config('gradingduedate', 689 * 3600, 'assign');
        set_config('submissiondrafts', 1, 'assign');
        set_config('requiresubmissionstatement', 1 , 'assign');
        set_config('preventsubmissionnotingroup', 1, 'assign');
        set_config('blindmarking', 1, 'assign');
        set_config('markingworkflow', 1, 'assign');
        set_config('markingallocation', 1, 'assign');

        // Quercus default settings.
        set_config('cutoffinterval', 1, 'local_quercus_tasks');
        set_config('cutoffintervalsecondplus', 1, 'local_quercus_tasks');
        set_config('gradingdueinterval', 1, 'local_quercus_tasks');

        // Scales.
        $grademarkexemptscale = $this->getDataGenerator()->create_scale([
                'name' => 'grademarkexemptscale',
                'scale' => implode(',', range(0, 100)),
            ]);
        set_config('grademarkexemptscale', $grademarkexemptscale->id, 'local_quercus_tasks');
        $grademarkscale = $this->getDataGenerator()->create_scale([
                'name' => 'grademarkscale',
                'scale' => 'N,S,F3,F2,F1,D3,D2,D1,C3,C2,C1,B3,B2,B1,A4,A3,A2,A1',
            ]);
        set_config('grademarkscale', $grademarkscale->id, 'local_quercus_tasks');

        // Feedback settings.
        set_config('default', 1, 'assignfeedback_doublemark');
    }

    /**
     * Gets default assignment settings that are used when creating an assignment.
     *
     * @return object
     */
    public function assign_config() {
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
     * Creates turnitin config settings for the course module.
     * This should be part of our core plugin, but trying here first.
     *
     * @param int $cmid
     * @param int $courseid
     * @return void
     */
    public function turnitin_config($cmid, $courseid) {
        global $DB;
        $tiiform = new stdClass();
        $tiiform->modulename = 'assign';
        $tiiform->coursemodule = $cmid;
        $tii = new \plagiarism_plugin_turnitin();
        $defaults = $tii->get_settings();
        foreach ($defaults as $name => $value) {
            $tiiform->$name = $value;
        }
        // This is how default tii settings are set on the assignment.
        $tii->save_form_data($tiiform);
        $course = $DB->get_record('course', ['id' => $courseid]);
        // IRL we would be calling get_course_data and this will set up the course
        // if it doesn't exist. For testing purposes we're going to spoof this.
        // $tii->get_course_data($cmid, $courseid); // Though this should get called
        // when an assignment is being submitted.
        if (!$DB->record_exists('plagiarism_turnitin_courses', ['courseid' => $courseid])) {
            $tiicourse = new stdClass();
            $tiicourse->courseid = $courseid;
            $tiicourse->turnitin_ctl = $course->fullname . ' (Moodle PP)';
            $tiicourse->turnitin_cid = 1234;
            $DB->insert_record('plagiarism_turnitin_courses', $tiicourse);
        }
    }

    /**
     * Turnitin doesn't create any default settings until you first save their settings page,
     * which doesn't happen in unittests.
     * This sets up some defaults that suit us.
     *
     * @return void
     */
    public function turnitin_defaults() {
        global $DB;
        $fields = [
            'use_turnitin' => 1,
            'use_turnitin_lock' => 1,
            'plagiarism_show_student_report' => 1,
            'plagiarism_draft_submit' => 0,
            'plagiarism_allow_non_or_submissions' => 0,
            'plagiarism_submitpapersto' => 0,
            'plagiarism_compare_student_papers' => 1,
            'plagiarism_compare_internet' => 1,
            'plagiarism_compare_journals' => 1,
            'plagiarism_report_gen' => 1,
            'plagiarism_exclude_biblio' => 0,
            'plagiarism_exclude_quoted' => 0,
            'plagiarism_exclude_matches' => 0,
        ];
        foreach ($fields as $key => $value) {
            $entry = new stdClass();
            $entry->cm = null;
            $entry->name = $key;
            $entry->value = $value;
            $entry->config_hash = '_' . $key;
            $DB->insert_record('plagiarism_turnitin_config', $entry);
        }
    }
}
