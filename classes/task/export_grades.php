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
        // Get records with response of null for processing
        $dataarray = get_retry_list();

        if(count($dataarray) > 0){
          foreach ($dataarray as $key => $value) {
            $dataready =  json_encode($value);
            $response = export_grades($dataready);

            if($response != null){
              $moduleinstanceid = update_log($response);
              mtrace('Released grades have been processed for ' . $moduleinstanceid);
            }else{
              mtrace('Can\'t connect to Quercus');
            }
          }
        }else{
          mtrace('No grades have been released');
        }
    }
}
