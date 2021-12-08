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

defined('MOODLE_INTERNAL') || die();

trait local_quercus_tasks_helper_trait {

    public $faketiicomms;

    public function bitnbobs() {
        set_config('enableplagiarism', 1);
        set_config('enabled', 1, 'plagiarism_turnitin');
        set_config('plagiarism_turnitin_mod_assign', 1, 'plagiarism_turnitin');
        $this->turnitin_defaults();
        $this->faketiicomms = $this->getMockBuilder(turnitin_comms::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $grademarkexemptscale = $this->getDataGenerator()->create_scale(['name' => 'grademarkexemptscale', 'scale' => implode(',', range(0,100))]);
        set_config('grademarkexemptscale', $grademarkexemptscale->id, 'local_quercus_tasks');
        $grademarkscale = $this->getDataGenerator()->create_scale(['name' => 'grademarkscale', 'scale' => 'N,S,F3,F2,F1,D3,D2,D1,C3,C2,C1,B3,B2,B1,A4,A3,A2,A1']);
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
        $assign_config = new stdClass;
		$assign_config->submissiondrafts = get_config('assign', 'submissiondrafts');
		$assign_config->sendnotifications = get_config('assign', 'sendnotifications');
		$assign_config->sendlatenotifications = get_config('assign', 'sendlatenotifications');
		$assign_config->requiresubmissionstatement = get_config('assign', 'requiresubmissionstatement');
		$assign_config->teamsubmission = get_config('assign', 'teamsubmission');
		$assign_config->requireallteammemberssubmit = get_config('assign', 'requireallteammemberssubmit');
		$assign_config->teamsubmissiongroupingid = get_config('assign', 'teamsubmissiongroupingid');
		$assign_config->blindmarking = get_config('assign', 'blindmarking');
		$assign_config->attemptreopenmethod = get_config('assign', 'attemptreopenmethod');
		$assign_config->maxattempts = get_config('assign', 'maxattempts');
		$assign_config->markingworkflow = get_config('assign', 'markingworkflow');
		$assign_config->markingallocation = get_config('assign', 'markingallocation');
		$assign_config->sendstudentnotifications = get_config('assign', 'sendstudentnotifications');
		$assign_config->preventsubmissionnotingroup = get_config('assign', 'preventsubmissionnotingroup');

		$assign_config->assignfeedback_comments_enabled = get_config('assignfeedback_comments', 'default');
		$assign_config->assignfeedback_comments_commentinline = get_config('assignfeedback_comments', 'inline');
		$assign_config->assignfeedback_doublemark_enabled = get_config('assignfeedback_doublemark', 'default');
		$assign_config->assignfeedback_file_enabled = get_config('assignfeedback_file', 'default');
		$assign_config->assignfeedback_misconduct_enabled = get_config('assignfeedback_misconduct', 'default');
		$assign_config->assignfeedback_penalties_enabled = get_config('assignfeedback_penalties', 'default');
		$assign_config->assignfeedback_sample_enabled = get_config('assignfeedback_sample', 'default');
        
        return $assign_config;
    }

    /**
     * Creates turnitin config settings for the course module.
     * This should be part of our core plugin, but trying here first.
     *
     * @param int $cmid
     * @return void
     */
    public function turnitin_config($cmid) {
        global $DB;
        $tiiform = new stdClass();
        $tiiform->modulename = 'assign';
        $tiiform->coursemodule = $cmid;
        $tii = new plagiarism_plugin_turnitin();
        $defaults = $tii->get_settings();
        foreach ($defaults as $name => $value) {
            $tiiform->$name = $value;
        }
        $tii->save_form_elements($tiiform);
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
        $fields = array(
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
            'plagiarism_exclude_matches' => 0);
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