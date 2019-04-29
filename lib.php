<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/../../course/modlib.php');
require_once(dirname(__FILE__).'/../../lib/gradelib.php');
require_once(dirname(__FILE__).'/../../mod/assign/externallib.php');
require_once(dirname(__FILE__).'/../../mod/assign/lib.php');
require_once(dirname(__FILE__).'/../../mod/assign/locallib.php');

// Disable assignment tool while script runs to prevent assignments being added via the front end?
class local_create_assign extends assign {

	public function __construct($coursemodulecontext, $coursemodule, $course) {
		parent::__construct($coursemodulecontext, $coursemodule, $course);
	}
}

function insert_assign($course, $assessment_id, $assessment_description, $availableFrom, $duedate, $sitting, $sitting_desc, $externaldate, $formdata_config){
	global $DB, $CFG;

	$formdata = new stdClass();
	$formdata->id = null;
	$formdata->course = $course->id;
	$formdata->name = $assessment_description;
	$formdata->intro = "";
	$formdata->introformat = 1;
	$formdata->alwaysshowdescription = 1;
	$formdata->nosubmissions = 0;
	$formdata->submissiondrafts = $formdata_config->submissiondrafts;
	$formdata->sendnotifications = $formdata_config->sendnotifications;
	$formdata->sendlatenotifications = $formdata_config->sendlatenotifications;

	if($availableFrom != 0){
		$time = new DateTime('now', core_date::get_user_timezone_object());
		$time = DateTime::createFromFormat('U', $availableFrom);
		$time->setTime(16, 0, 0);
		$timezone = core_date::get_user_timezone($time);
		$dst = dst_offset_on($availableFrom, $timezone);
		$formdata->allowsubmissionsfromdate = $time->getTimestamp() - $dst;
	}else{
		$formdata->allowsubmissionsfromdate = time();
	}

	$time = new DateTime('now', core_date::get_user_timezone_object());
	$time = DateTime::createFromFormat('U', $duedate);
	$time->setTime(16, 0, 0);
	$timezone = core_date::get_user_timezone($time);
	$dst = dst_offset_on($duedate, $timezone);
	$formdata->duedate = $time->getTimestamp() - $dst;

	// Cut off date
	$time = new DateTime('now', core_date::get_user_timezone_object());
	$time = DateTime::createFromFormat('U', $duedate);
	$timezone = core_date::get_user_timezone($time);
	$modifystring = '+' . get_config('local_quercus_tasks', 'cutoffinterval') . ' week';
	$cutoffdate  = 	$time->modify($modifystring);
	$time->setTime(16, 0, 0);
	$cutoffdate = $time->getTimestamp();
	$dst = dst_offset_on($cutoffdate, $timezone);
	$formdata->cutoffdate = $time->getTimestamp() - $dst;

	// Grading due date
	$time = new DateTime('now', core_date::get_user_timezone_object());
	$time = DateTime::createFromFormat('U', $formdata->duedate);
	$timezone = core_date::get_user_timezone($time);
	$modifystring = '+' . get_config('local_quercus_tasks', 'gradingdueinterval') . ' week';
	$gradingduedate  = 	$time->modify($modifystring);
	$time->setTime(16, 0, 0);
	$gradingduedate = $time->getTimestamp();
	$dst = dst_offset_on($gradingduedate, $timezone);
	$formdata->gradingduedate = $time->getTimestamp() - $dst;

	$formdata->grade = get_config('local_quercus_tasks', 'grademarkscale') * -1;
	$formdata->timemodified = 0;
	$formdata->requiresubmissionstatement = $formdata_config->requiresubmissionstatement;
	$formdata->completionsubmit = 1;
	$formdata->teamsubmission = $formdata_config->teamsubmission;
	$formdata->requireallteammemberssubmit = $formdata_config->requireallteammemberssubmit;
	$formdata->teamsubmissiongroupingid = $formdata_config->teamsubmissiongroupingid;
	$formdata->blindmarking = $formdata_config->blindmarking;
	$formdata->revealidentities = 0;
	$formdata->attemptreopenmethod = $formdata_config->attemptreopenmethod;
	$formdata->maxattempts = $formdata_config->maxattempts;
	$formdata->markingworkflow = $formdata_config->markingworkflow;
	$formdata->markingallocation = $formdata_config->markingallocation;
	$formdata->sendstudentnotifications = $formdata_config->sendstudentnotifications;
	$formdata->preventsubmissionnotingroup = $formdata_config->preventsubmissionnotingroup;

	$mod_info = prepare_new_moduleinfo_data($course, 'assign', 1);
	$new_assign = new assign($mod_info, null, $course);
	$new_mod = $new_assign->add_instance($formdata, true);

	//get module
	$mod_assign = $DB->get_record('modules', array('name' => 'assign'), '*', MUST_EXIST);

	// Insert to course_modules table
	$module = new stdClass();
	$module->id = null;
	$module->course = $course->id;
	$module->module = $mod_assign->id;
	$module->modulename = $mod_assign->name;
	$module->instance = $new_mod;
	$module->section = 1; //section id
	$module->idnumber = $assessment_id;
	$module->added = 0;
	$module->score = 0;
	$module->indent = 0;
	if($sitting_desc == 'FIRST_SITTING'){
		$module->visible = 1;
	}else{
		$module->visible = 0;
	}
	$module->visibleold = 0;
	$module->groupmode = 0;
	$module->groupingid = 0;
	if($sitting_desc == 'FIRST_SITTING'){
		$module->completion = 2;
	}else{
		$module->completion = 0;
	}
	$module->completiongradeitemnumber = null;
	$module->completionview = 0;
	$module->completionexpected = $formdata->duedate;
	$module->showdescription = 1;
	$module->availability = null;
	$module->deletioninprogress = 0;
	$module->coursemodule = "";
	$module->add = 'assign';

	$newcmid = add_course_module($module);

	//get course module here
	$newcm = get_coursemodule_from_id('assign', $newcmid, $course->id, false, MUST_EXIST);

	if (!$newcm) {
		return false;
	}

	course_add_cm_to_section($course, $newcmid, 1);
	$new_assign->set_context(context_module::instance($newcm->id)); //add context

	// add sitting data
	$sittingrecord = new stdClass();
	$sittingrecord->assign = $new_mod;
	$sittingrecord->sitting = $sitting;
	$sittingrecord->sitting_desc = $sitting_desc;
	$sittingrecord->externaldate = $externaldate;
	$sittingid = $DB->insert_record('local_quercus_tasks_sittings', $sittingrecord, false);

	if (!$sittingid) {
		return false;
	}

	rebuild_course_cache($course->id);

	return $newcm;
}

function create_assignments(){
	global $CFG, $DB;
	//Get data
	$result = file_get_contents($CFG->dataroot . get_config('local_quercus_tasks', 'datafile'));

	if($result){
		$xml = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);

		// Use defaults held in DB where possible - get once before running rest of script
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

		foreach($array as $arr=>$elem){
			foreach($elem as $key=>$value){

				$unit_instance = $value['moduleInstance']; //course
				$course = $DB->get_record('course', array('idnumber' => $unit_instance));

				$assessmentCode = $value["assessmentCode"]; //assignment id
				$academicYear = $value["academicYear"];
				$sitting = $value["sitting"];
				$sitting_desc = $value["sittingDescription"];
				$externaldate = $value["externalDate"];
				$weighting = (float)$value["weighting"] * 100;

				if(isset($value["availableFrom"])){
					$availableFrom = $value["availableFrom"];
				}else{
					$availableFrom = 0;
				}

				if(isset($value["dueDate"])){
					$duedate = $value["dueDate"];
				}else{
					$duedate = 0;
				}

				if($sitting_desc == 'FIRST_SITTING'){
					$assessment_description = $value["assessmentDescription"] . ' ('. $weighting . '%)';
				}else{
					$append = ucfirst(strtolower(strtok($sitting_desc, '_')));
					$assessment_description = $value["assessmentDescription"] . ' ('. $weighting . '%) - ' . $append . ' Attempt';
				}
				$assessment_id = $academicYear  . '_' . $assessmentCode;
				if($duedate != 0){
					if($course){
						$module = $DB->get_records_sql('SELECT *
														FROM {course_modules} cm
														INNER JOIN {local_quercus_tasks_sittings} s ON s.assign = cm.instance
														WHERE cm.course = ?
														AND cm.idnumber = ?
														AND s.sitting = ?', array($course->id, $assessment_id, $sitting));

						if (!$module){
							$newcm = insert_assign($course, $assessment_id, $assessment_description, $availableFrom, $duedate, $sitting, $sitting_desc, $externaldate, $assign_config);
							if($newcm){
								mtrace("Created " . $assessment_id . " " . $sitting_desc . " in unit " . $unit_instance);
							}else{
								mtrace("Error creating assessment " . $assessment_id. " " . $sitting_desc . " in unit " . $unit_instance);
							}
						}else{
							mtrace("Cannot create " . $assessment_id . " " . $sitting_desc . " in unit " . $unit_instance . " - Assignment already exists");
						}
					}else{
						mtrace("Cannot create " . $assessment_id . " " . $sitting_desc . " in unit " . $unit_instance . " - Unit does not exist");
					}
			}else{
				mtrace("Cannot create " . $assessment_id . " " . $sitting_desc . " in unit " . $unit_instance . " - No due date provided");
			}
			}
		}
	}else{
		mtrace('Data file does not exist');
	}
}

//Functions for exporting grades
function convert_grade($scaleid, $grade){
	if($scaleid == get_config('local_quercus_tasks', 'grademarkscale')){ // Solent gradescale
	    $converted = -1;
	    switch ($grade){
        case 18:
                $converted = 100; 	// A1
				break;
        case 17:
                $converted = 92;		// A2
                break;
        case 16:
                $converted = 83;	// A3
                break;
        case 15:
                $converted = 74;	// A4
                break;
        case 14:
                $converted = 68;	// B1
                break;
        case 13:
                $converted = 65;	// B2
                break;
        case 12:
                $converted = 62;	// B3
                break;
        case 11:
                $converted = 58;	// C1
                break;
        case 10:
                $converted = 55;	// C2
                break;
        case 9:
                $converted = 52;	// C3
                break;
        case 8:
                $converted = 48;	// D1
                break;
        case 7:
                $converted = 45;	// D2
                break;
        case 6:
                $converted = 42;	// D3
                break;
        case 5:
                $converted = 35;	// F1
                break;
        case 4:
                $converted = 20;	// F2
                break;
        case 3:
                $converted = 15;	// F3
                break;
        case 2:
                $converted = 1;		// S
                break;
        case 1:
                $converted = 0;		// N
                break;
		default:
				$converted = -1;
				break;
	    }
	}elseif($scaleid == get_config('local_quercus_tasks', 'grademarkexemptscale')){
		if($grade == NULL || $grade == 0 || $grade == -1){
			$converted = 0;
		}else{
			$converted = (int)unformat_float($grade) -1;
		}
	}
    return $converted;
}

function insert_log($cm, $sitting, $course, $grade_info, $grade, $student){
	global $DB;
	$exists = $DB->get_record_sql('SELECT COUNT(*) AS total FROM {local_quercus_grades} WHERE assign = ? and student = ?', array($cm->instance, $student->id));

	if($exists->total != 1){
		//Insert into table
		$record = new stdClass();
		$record->assign  = $cm->instance;
		$record->sitting = $sitting->id;
		$record->course = $course->id;
		$record->course_module = $cm->id;
		$record->grader = $grade_info->id;
		$record->student = $student->id;
		$record->converted_grade = $grade;
		$record->timecreated = time();

		$lastinsertid = $DB->insert_record('local_quercus_grades', $record, false);
	}

	if(isset($lastinsertid)){
		return $lastinsertid;
	}else{
		return null;
	}
}

function update_log($response){
	global $DB;
	foreach($response['Payload'] as $key => $val){

		$sql = "UPDATE mdl_local_quercus_grades
						SET response = ?, parent_request_id = ?, request_id = ?, processed = ?, payload_error = ?, timemodified = ?
						WHERE id =
						(
							  SELECT id FROM
							  (
							    SELECT g.id
								FROM {local_quercus_grades} g
								JOIN {course} c ON c.id = g.course
								JOIN {course_modules} cm ON cm.id = g.course_module
								JOIN {local_quercus_tasks_sittings} s ON s.id = g.sitting
								JOIN {user} u ON u.id = g.student
								WHERE c.idnumber = ?
								AND cm.idnumber = ?
								AND u.id = ?
								AND s.sitting_desc = ?
								LIMIT 1
							) AS g
						)";

		$error = $val['error'][0]['detail'] ? $val['error'][0]['detail'] : null;

		$params = array($response['ErrorCode'],  $response['ParentRequestId'], $val['requestid'], $val['processed'] ,$error , time(),
							$val['moduleinstanceid'], $val['academicsession'] . '_' . $val['assessmentcode'], $val['moodlestudentid'], $val['assessmentsittingcode'] );
		$recordid = $DB->execute($sql, $params);
	}
}

function get_retry_list(){
	global $DB;
	//Get grades to be re-processed and add to array
	$reprocess = $DB->get_records_sql('SELECT
										qg.id, u.id "moodlestudentid", u.idnumber "studentid", u.firstname "name", u.lastname "surname",
										SUBSTRING_INDEX(c.shortname,"_",1) modulecode, c.shortname moduleinstanceid, c.fullname moduledescription,
										qs.sitting_desc assessmentsittingcode, SUBSTRING_INDEX(cm.idnumber,"_",1) academicsession,
										a.name assessmentdescription, SUBSTRING_INDEX(cm.idnumber,"_",-1) assessmentcode,
										qg.converted_grade assessmentresult,
										(SELECT u1.firstname FROM {user} u1 WHERE u1.id = qg.grader) unitleadername,
										(SELECT u2.lastname FROM {user} u2 WHERE u2.id = qg.grader) unitleadersurname,
										(SELECT u3.email FROM {user} u3 WHERE u3.id = qg.grader) unitleaderemail
										FROM {local_quercus_grades} qg
										JOIN {user} u ON u.id = qg.student
										JOIN {course} c on c.id = qg.course
										JOIN {local_quercus_tasks_sittings} qs ON qs.id = qg.sitting
										JOIN {course_modules} cm ON cm.id = qg.course_module
										JOIN {assign} a ON a.id = qg.assign
										WHERE response = ?
										OR response IS NULL', array(1));

	$course_module =	array(
									"moodlestudentid"=> "",
									"studentid"=> "",
									"name" => "" ,
									"surname" => "",
									"modulecode" => "",
									"moduleinstanceid" => "",
									"moduledescription" => "",
									"assessmentsittingcode" => "",
									"academicsession" => "",
									"assessmentdescription" => "",
									"assessmentcode" => "",
									"assessmentresult" => "",
									"unitleadername" => "",
									"unitleadersurname" => "",
									"unitleaderemail" => ""
								);
	foreach($reprocess as $k => $v){
		$course_module["moodlestudentid"] = $v->moodlestudentid;
		$course_module["studentid"] = $v->studentid;
		$course_module["name"] = $v->name;
		$course_module["surname"] = $v->surname;
		$course_module["modulecode"] = $v->modulecode;
		$course_module["moduleinstanceid"] =  $v->moduleinstanceid;
		$course_module["moduledescription"] = $v->moduledescription;
		$course_module["assessmentsittingcode"] = $v->assessmentsittingcode;
		$course_module["academicsession"] = $v->academicsession;
		$course_module["assessmentdescription"] = $v->assessmentdescription;
		$course_module["assessmentcode"] = $v->assessmentcode;
		$course_module["assessmentresult"] = $v->assessmentresult;
		$course_module["unitleadername"] = $v->unitleadername;
		$course_module["unitleadersurname"] = $v->unitleadersurname;
		$course_module["unitleaderemail"] = $v->unitleaderemail;
		$data_array[] = $course_module;
	}

	if(isset($data_array)){
			return $data_array;
	}else{
			return null;
	}
}

function match_grades($allgrades, $student, $grade_info){
	$grade = (string) 0;
	foreach($allgrades as $gk=>$gv){
		foreach ($gv['grades'] as $key => $value) {
			if($value['userid'] == $student->id){
				$grade = (string) convert_grade($grade_info->scaleid, substr($value['grade'], 0, strpos($value['grade'], ".")));
			}
		}
	}
	return $grade;
}

function get_new_grades($lastruntime){
  global $CFG, $DB;
	// Get assign ids for new assignments
	$assignids = $DB->get_records_sql('SELECT iteminstance FROM {grade_items} where itemmodule = ? AND idnumber != ? AND (locked > ? AND locktime = ?)', array('assign', '', $lastruntime, 0)); //change to $time 1517317200

	if(isset($assignids)){
		// Create JSON array structure
		$course_module =	array(
										"moodlestudentid"=> "",
										"studentid"=> "",
										"name" => "" ,
										"surname" => "",
										"modulecode" => "",
										"moduleinstanceid" => "",
										"moduledescription" => "",
										"assessmentsittingcode" => "",
										"academicsession" => "",
										"assessmentdescription" => "",
										"assessmentcode" => "",
										"assessmentresult" => "",
										"unitleadername" => "",
										"unitleadersurname" => "",
										"unitleaderemail" => ""
									);

		foreach($assignids as $k=>$v){
			// Setup email values
			$message = null;
			$messageintro = get_string('emailmessageintro', 'local_quercus_tasks', ['gradinghelpurl'=>get_config('local_quercus_tasks', 'gradinghelpurl')]);
			$tableheader = get_string('tableheader', 'local_quercus_tasks');
			$tablefooter = get_string('tablefooter', 'local_quercus_tasks');
			// Get course module
			$cm = get_coursemodule_from_instance('assign', $v->iteminstance, 0);
			//Get course
			$course = get_course($cm->course);
			//Get sitting_desc
			$sitting = $DB->get_record_sql('SELECT id, sitting_desc FROM {local_quercus_tasks_sittings} WHERE assign = ?', array($v->iteminstance));
			//Get user that locked the grades and scale id
			$grade_info = $DB->get_record_sql('SELECT u.id, u.firstname, u.lastname, u.email, MAX(h.timemodified), h.scaleid
																					FROM {grade_items_history} h
																					JOIN {user} u ON u.id = h.loggeduser
																					WHERE (h.itemmodule = ? AND h.iteminstance = ?)
																					AND locked > ?', array('assign', $cm->instance, $lastruntime));

			$course_module["modulecode"] = substr($course->shortname, 0, strpos($course->shortname, "_"));
			$course_module["moduleinstanceid"] =  $course->shortname;
			$course_module["moduledescription"] = $course->fullname;
			$course_module["assessmentsittingcode"] = $sitting->sitting_desc;
			$course_module["academicsession"] = substr($cm->idnumber, 0, strpos($cm->idnumber, "_"));
			$course_module["assessmentdescription"] = $cm->name;
			$course_module["assessmentcode"] = substr($cm->idnumber, strpos($cm->idnumber, "_") + 1);
			$course_module["unitleadername"] = $grade_info->firstname;
			$course_module["unitleadersurname"] = $grade_info->lastname;
			$course_module["unitleaderemail"] = $grade_info->email;

			$users = get_role_users(5, context_course::instance($course->id), false, 'u.id, u.lastname, u.firstname, idnumber', 'idnumber, u.lastname, u.firstname');

			// Get all grades for assignment
			unset($assignment);
			$assignment[] = $v->iteminstance;
			$allgrades = mod_assign_external::get_grades($assignment);
			$allgrades = $allgrades['assignments'];

			foreach($users as $key => $student){
				if(is_numeric($student->idnumber)){
					$course_module["moodlestudentid"] = $student->id; //NEW
					$course_module["studentid"] = $student->idnumber;
					$course_module["name"] = $student->firstname;
					$course_module["surname"] = $student->lastname;
					$grade = match_grades($allgrades, $student, $grade_info);
					if($grade == -1){
						$course_module["assessmentresult"] = 0;
					}else{
						$course_module["assessmentresult"] = $grade;
					}
					$data_array[] = $course_module;

					if($grade == -1){
						//Send grade of 0 to Quercus
						$insertid = insert_log($cm, $sitting, $course, $grade_info, 0, $student);
						//Send email to helpdesk as tutor has added grade to Turnitin
						$message .= get_string('emailmessagestudent', 'local_quercus_tasks', ['idnumber'=>$student->idnumber, 'firstname'=>$student->firstname ,'lastname'=>$student->lastname]) . "\r\n\n";

					}else{
						$insertid = insert_log($cm, $sitting, $course, $grade_info, $grade, $student);

						if($grade == -1){
							//send email to helpdesk as tutor has added grade to Turnitin
							$to      = $USER->email;
							$subject = get_string('emailsubject', 'local_quercus_tasks', ['shortname'=>$_POST['shortname']]);
							$message = get_string('emailmessage', 'local_quercus_tasks', ['firstname'=>$student->firstname ,'lastname'=>$student->lastname, 'assign'=>$cm->name]) . "\r\n\n";
							$headers = "From: " . get_config('local_quercus_tasks', 'emailfrom') . "\r\n";
							$headers .= "MIME-Version: 1.0\r\n";
							$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
							mail($to, $subject, $message, $headers);
						}
					}
				}
			}

			if(isset($message)){
				$messagebody = $messageintro . "\r\n\n";
				$messagebody .= $tableheader;
				$messagebody .= $message;
				$messagebody .= $tablefooter;
				// Unit leader email
				//$to      = $grade_info->email;
				// Support emails
				// $to      .= ',' . get_config('local_quercus_tasks', 'senderrorto');
				$to      = get_config('local_quercus_tasks', 'senderrorto');


				$subject = get_string('emailsubject', 'local_quercus_tasks', ['shortname'=>$course->shortname, 'assign'=>$cm->idnumber]);
				$headers = "From: " . get_config('local_quercus_tasks', 'emailfrom') . "\r\n";
				$headers .= "Reply-To: " . get_config('local_quercus_tasks', 'senderrorto') . "\r\n";
				$headers .= "MIME-Version: 1.0\r\n";
				$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
				mail($to, $subject, $messagebody, $headers);

				$message = null;
			}
		}
	}

	if(isset($data_array)){
		return $data_array;
	}else{
		return null;
	}

}

function export_grades($data_ready){
	//Send data
	$ch = curl_init();
	$url = get_config('local_quercus_tasks', 'srsgws');

	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    'Content-Type: application/json',
	    'Content-Length: ' . strlen($data_ready))
	);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_ready);
	curl_setopt($ch,CURLOPT_FAILONERROR,true);

	$result = curl_exec($ch);
	$error_msg = curl_error($ch);
	$response = json_decode($result, true);
	curl_close($ch);

	if(isset($response)){

		return $response;
	}else{
		return null;
	}
}

function update_dates(){
	global $CFG, $DB;
	$result = file_get_contents($CFG->dataroot . get_config('local_quercus_tasks', 'datafile'));

	if($result){
		$xml = simplexml_load_string($result, "SimpleXMLElement", LIBXML_NOCDATA);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);

		foreach($array as $arr=>$elem){
			foreach($elem as $key=>$value){

				$unit_instance = $value['moduleInstance']; //course
				$academicYear = $value["academicYear"];
				$assessmentCode = $value["assessmentCode"]; //assignment id
				$sitting = $value["sitting"];
				$assessment_id = $academicYear  . '_' . $assessmentCode;

				$course = $DB->get_record('course', array('idnumber' => $unit_instance));

				if($course){

					if($course->startdate > '1533081600'){
						$assign = $DB->get_record_sql('	SELECT a.*, cm.id cm_id, cm.instance cm_instance, s.id sitting_id, s.externaldate
															FROM {course_modules} cm
															INNER JOIN {local_quercus_tasks_sittings} s ON s.assign = cm.instance
															INNER JOIN {assign} a ON a.id = cm.instance
															WHERE cm.course = ?
															AND cm.idnumber = ?
															AND s.sitting = ?', array($course->id, $assessment_id, $sitting));

						if($assign){

							// Available from date - date range may have been implemented
							if(isset($value["availableFrom"])){
								$availableFrom = $value["availableFrom"];

								$time = new DateTime('now', core_date::get_user_timezone_object());
								$time = DateTime::createFromFormat('U', $availableFrom);
								$time->setTime(16, 0, 0);
								$timezone = core_date::get_user_timezone($time);
								$dst = dst_offset_on($availableFrom, $timezone);
								$availableFrom = $time->getTimestamp() - $dst;
							}else{
								$availableFrom = $assign->allowsubmissionsfromdate;
							}

							if(isset($value["dueDate"])){
								// Due date - Update regardless
								$duedate = $value["dueDate"];
								$time = new DateTime('now', core_date::get_user_timezone_object());
								$time = DateTime::createFromFormat('U', $duedate);
								$time->setTime(16, 0, 0);
								$timezone = core_date::get_user_timezone($time);
								$dst = dst_offset_on($duedate, $timezone);
								$duedate = $time->getTimestamp() - $dst;

								// Cut off date	- Update regardless
								$time = new DateTime('now', core_date::get_user_timezone_object());
								$time = DateTime::createFromFormat('U', $value["dueDate"]);
								$timezone = core_date::get_user_timezone($time);
								$modifystring = '+' . get_config('local_quercus_tasks', 'cutoffinterval') . ' week';
								$cutoffdate  = 	$time->modify($modifystring);
								$time->setTime(16, 0, 0);
								$cutoffdate = $time->getTimestamp();
								$dst = dst_offset_on($cutoffdate, $timezone);
								$cutoffdate = $time->getTimestamp() - $dst;

								// Grading due date	- Update regardless
								$time = new DateTime('now', core_date::get_user_timezone_object());
								$time = DateTime::createFromFormat('U', $value["dueDate"]);
								$timezone = core_date::get_user_timezone($time);
								$modifystring = '+' . get_config('local_quercus_tasks', 'gradingdueinterval') . ' week';
								$gradingduedate  = 	$time->modify($modifystring);
								//Set time after date is adjusted in case of dst changeover
								$time->setTime(16, 0, 0);
								$gradingduedate = $time->getTimestamp();
								$dst = dst_offset_on($gradingduedate, $timezone);
								$gradingduedate = $time->getTimestamp() - $dst;

							}

							if (isset($value["externalDate"])){
								if ($value["externalDate"] != $assign->externaldate){
									//Update board date
									$newboard = new stdClass();
									$newboard->id = $assign->sitting_id;
									$newboard->externaldate = $value["externalDate"];
									$update = $DB->update_record('local_quercus_tasks_sittings', $newboard, $bulk=false);
								}
							}

							if($duedate != $assign->duedate || $newboard != $assign->externaldate){

								//Update assignment dates
								$newdates = new stdClass();
								$newdates->id = $assign->id;
								$newdates->duedate = $duedate;
								$newdates->allowsubmissionsfromdate = $availableFrom;
								$newdates->cutoffdate = $cutoffdate;
								$newdates->gradingduedate = $gradingduedate;
								$update = $DB->update_record('assign', $newdates, $bulk=false);

								//Update completion date
								$newcompletion = new stdClass();
								$newcompletion->id = $assign->cm_id;
								$newcompletion->completionexpected = $duedate;
								$update = $DB->update_record('course_modules', $newcompletion, $bulk=false);

								//Update assignment calendar events
								$assignobj = $DB->get_record('assign', array('id' => $assign->id));
								$courseobj = $DB->get_record('course', array('id' => $course->id));
								$cmobj = $DB->get_record('course_modules', array('id' => $assign->cm_id));
								$cmobj->modname = 'assign';
								$refreshevent = course_module_calendar_event_update_process($assignobj, $cmobj);
								// Output result to cron
								mtrace('Updated ' . $assign->name . ' in ' . $unit_instance .
										' - Board: ' . 	date( "d/m/Y h:i", $assign->externaldate) . ' -> ' . date( "d/m/Y h:i", $value["externalDate"]) .
										' - Due: ' . 		date( "d/m/Y h:i", $assign->duedate) . ' -> ' . date( "d/m/Y h:i", $duedate) .
										' * Cut off: ' . 	date( "d/m/Y h:i", $assign->cutoffdate) . ' -> ' . date( "d/m/Y h:i", $cutoffdate) .
										' * Grade: ' . 		date( "d/m/Y h:i", $assign->gradingduedate) . ' -> ' . date( "d/m/Y h:i", $gradingduedate) );

							}
						}
					}
				}
			}
		}
	}
}
