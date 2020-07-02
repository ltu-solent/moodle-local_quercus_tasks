<?php

namespace local_quercus_tasks\task;

class export_grades extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
        return get_string('export_grades', 'local_quercus_tasks');
    }

    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/quercus_tasks/lib.php');
        // Get records with error status of 1 or null for re-processing
        $data_array = get_retry_list();

        if(isset($data_array)){
          $data_ready =  json_encode($data_array);
          $response = export_grades($data_ready);
          if($response != null){
            $update = update_log($response);
            mtrace('Released grades have been processed');
          }else{
            mtrace('Can\'t connect to Quercus');
          }
        }else{
        mtrace('No grades have been released');
        }
    }
}
