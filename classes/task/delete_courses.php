<?php

namespace local_quercus_tasks\task;

class delete_courses extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
        return get_string('deletecourses', 'local_quercus_tasks');
    }

    public function execute() {
		global $CFG;
        require_once($CFG->dirroot.'/local/quercus_tasks/lib.php');
        delete_courses();
    }
}
