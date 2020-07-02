<?php

namespace local_quercus_tasks\task;

class get_new_grades extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
        return get_string('getnewgrades', 'local_quercus_tasks');
    }

    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/quercus_tasks/lib.php');
        //Get new grades
        $lastruntime = $DB->get_field_sql('SELECT max(timecreated) FROM {local_quercus_grades}');

        $data_array = get_new_grades($lastruntime);
        if(isset($data_array)){
          mtrace('New grades have been logged');
        }else{
          mtrace('No grades have been released');
        }
    }
}
