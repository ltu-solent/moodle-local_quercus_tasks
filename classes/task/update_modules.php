<?php

namespace local_quercus_tasks\task;

class update_modules extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
        return get_string('updatemodules', 'local_quercus_tasks');
    }

    public function execute() {
		global $CFG;
        require_once($CFG->dirroot.'/local/quercus_tasks/lib.php');
        update_modules();
    }
}
