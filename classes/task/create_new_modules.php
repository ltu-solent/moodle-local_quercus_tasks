<?php

namespace local_quercus_tasks\task;

class create_new_modules extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
        return get_string('createnewmodules', 'local_quercus_tasks');
    }

    public function execute() {
		global $CFG;
        require_once($CFG->dirroot.'/local/quercus_tasks/lib.php');
        create_new_modules();
    }
}
