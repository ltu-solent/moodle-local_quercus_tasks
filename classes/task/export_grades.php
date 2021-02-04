<?php

namespace local_quercus_tasks\task;

class export_grades extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens
        return get_string('exportgrades', 'local_quercus_tasks');
    }

    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/quercus_tasks/lib.php');
        // Get records with response of null for processing
        $dataarray = get_retry_list();
        if(count($dataarray) > 0){
          foreach ($dataarray as $key => $value) {
		
			// 5 seconds.
			$timeout = 5;
			// A namespace for the locks. Must be prefixed with the component name to prevent conflicts.
			$locktype = 'local_quercus_tasks_exportgrades'; 
			// Resource key - needs to uniquely identify the resource that is to be locked. E.g. If you
			// want to prevent a user from running multiple course backups - include the userid in the key.
			$resource = key($dataarray);
			 
			// Get an instance of the currently configured lock_factory.
			$lockfactory = \core\lock\lock_config::get_lock_factory($locktype);
			 
			// Get a new lock for the resource, wait for it if needed.
			if ($lock = $lockfactory->get_lock($resource, $timeout)) {
				// We have exclusive access to the resource, do the export						 
				try {
					$dataready =  json_encode($value);
					$response = export_grades($dataready);

					if($response != null){
					  $moduleinstanceid = update_log($response);
					  mtrace('Released grades have been processed for ' . $moduleinstanceid);
					}else{
					  mtrace('Can\'t connect to Quercus');
					}
				} catch (\Throwable $e) {
					throw $e;
				} finally {
					$lock->release();
				}
			 
			} else {
				// We did not get access to the resource in time, give up.
				throw new moodle_exception('locktimeout');
			}
          }
        }else{
          mtrace('No grades have been released');
        }
    }
}
