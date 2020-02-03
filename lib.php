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

// function insert_assign($course, $assignmentidnumber, $assessmentdescription, $availablefrom, $duedate, $sitting, $sittingdescription, $formdataconfig){
function insert_assign($course, $quercusdata, $formdataconfig){
	global $DB, $CFG;

	$formdata = new stdClass();
	$formdata->id = null;
	$formdata->course = $course->id;
	$formdata->name = $quercusdata->assessmentdescription;
	$formdata->intro = "";
	$formdata->introformat = 1;
	$formdata->alwaysshowdescription = 1;
	$formdata->nosubmissions = 0;
	$formdata->submissiondrafts = $formdataconfig->submissiondrafts;
	$formdata->sendnotifications = $formdataconfig->sendnotifications;
	$formdata->sendlatenotifications = $formdataconfig->sendlatenotifications;

	if($quercusdata->availablefrom == 0){
		$formdata->allowsubmissionsfromdate = 0;
	}else{
		$time = new DateTime('now', core_date::get_user_timezone_object());
		$time = DateTime::createFromFormat('U', $quercusdata->availablefrom);
		$time->setTime(16, 0, 0);
		$timezone = core_date::get_user_timezone($time);
		$dst = dst_offset_on($quercusdata->availablefrom, $timezone);
		$formdata->allowsubmissionsfromdate = $time->getTimestamp() - $dst;
	}

	$duedate = new DateTime('now', core_date::get_user_timezone_object());
	$duedate = DateTime::createFromFormat('U', $quercusdata->duedate);
	$duedate->setTime(16, 0, 0);
	$timezone = core_date::get_user_timezone($duedate);
	$dst = dst_offset_on($quercusdata->duedate, $timezone);
	$formdata->duedate = $duedate->getTimestamp() - $dst;

	// Cut off date
	if($quercusdata->sittingdescription == 'FIRST_SITTING'){
		$time = new DateTime('now', core_date::get_user_timezone_object());
		$time = DateTime::createFromFormat('U', $quercusdata->duedate);
		$timezone = core_date::get_user_timezone($time);
		$modifystring = '+' . get_config('local_quercus_tasks', 'cutoffinterval') . ' week';
		$cutoffdate  = 	$time->modify($modifystring);
		$time->setTime(16, 0, 0);
		$cutoffdate = $time->getTimestamp();
		$dst = dst_offset_on($cutoffdate, $timezone);
		$formdata->cutoffdate = $time->getTimestamp() - $dst;
	}else{
		$formdata->cutoffdate = $duedate->getTimestamp() - $dst;
	}

	// Grading due date
	$time = new DateTime('now', core_date::get_user_timezone_object());
	$time = DateTime::createFromFormat('U', $quercusdata->duedate);
	$timezone = core_date::get_user_timezone($time);
	$modifystring = '+' . get_config('local_quercus_tasks', 'gradingdueinterval') . ' week';
	$gradingduedate  = 	$time->modify($modifystring);
	$time->setTime(16, 0, 0);
	$gradingduedate = $time->getTimestamp();
	$dst = dst_offset_on($gradingduedate, $timezone);
	$formdata->gradingduedate = $time->getTimestamp() - $dst;

	if($quercusdata->grademarkexempt == 'Y'){
		$formdata->grade = get_config('local_quercus_tasks', 'grademarkexemptscale') * -1;
	}	else {
		$formdata->grade = get_config('local_quercus_tasks', 'grademarkscale') * -1;
	}

	$formdata->timemodified = 0;
	$formdata->requiresubmissionstatement = $formdataconfig->requiresubmissionstatement;
	$formdata->completionsubmit = 1;
	$formdata->teamsubmission = $formdataconfig->teamsubmission;
	$formdata->requireallteammemberssubmit = $formdataconfig->requireallteammemberssubmit;
	$formdata->teamsubmissiongroupingid = $formdataconfig->teamsubmissiongroupingid;
	$formdata->blindmarking = $formdataconfig->blindmarking;
	$formdata->revealidentities = 0;
	$formdata->attemptreopenmethod = $formdataconfig->attemptreopenmethod;
	$formdata->maxattempts = $formdataconfig->maxattempts;
	$formdata->markingworkflow = $formdataconfig->markingworkflow;
	$formdata->markingallocation = $formdataconfig->markingallocation;
	$formdata->sendstudentnotifications = $formdataconfig->sendstudentnotifications;
	$formdata->preventsubmissionnotingroup = $formdataconfig->preventsubmissionnotingroup;
	$formdata->assignfeedback_comments_enabled = $formdataconfig->assignfeedback_comments_enabled;
	$formdata->assignfeedback_comments_commentinline = $formdataconfig->assignfeedback_comments_commentinline;
	$formdata->assignfeedback_doublemark_enabled = $formdataconfig->assignfeedback_doublemark_enabled;
	$formdata->assignfeedback_file_enabled = $formdataconfig->assignfeedback_file_enabled;
	$formdata->assignfeedback_misconduct_enabled = $formdataconfig->assignfeedback_misconduct_enabled;
	$formdata->assignfeedback_penalties_enabled = $formdataconfig->assignfeedback_penalties_enabled;
	$formdata->assignfeedback_sample_enabled = $formdataconfig->assignfeedback_sample_enabled;
	$formdata->coursemodule = '';

	$mod_info = prepare_new_moduleinfo_data($course, 'assign', 1);
	$newassign = new assign($mod_info, null, $course);
	$newmod = $newassign->add_instance($formdata, true);

	//get module
	$modassign = $DB->get_record('modules', array('name' => 'assign'), '*', MUST_EXIST);

	// Insert to course_modules table
	$module = new stdClass();
	$module->id = null;
	$module->course = $course->id;
	$module->module = $modassign->id;
	$module->modulename = $modassign->name;
	$module->instance = $newmod;
	$module->section = 1; //section id
	$module->idnumber = $quercusdata->assignmentidnumber;
	$module->added = 0;
	$module->score = 0;
	$module->indent = 0;
	if($quercusdata->sittingdescription == 'FIRST_SITTING'){
		$module->visible = 1;
		$module->completion = 2;
	}else{
		$module->visible = 0;
		$module->completion = 0;
	}
	$module->visibleold = 0;
	$module->groupmode = 0;
	$module->groupingid = 0;
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
	$modcontext = $newassign->set_context(context_module::instance($newcm->id)); //add context

	$eventdata = clone $newcm;
	$eventdata->modname = $eventdata->modname;
	$eventdata->id = $eventdata->id;
	$event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
	$event->trigger();

	// add sitting data
	$sittingrecord = new stdClass();
	$sittingrecord->assign = $newmod;
	$sittingrecord->sitting = $quercusdata->sitting;
	$sittingrecord->sitting_desc = $quercusdata->sittingdescription;
	$sittingrecord->externaldate = $quercusdata->externaldate;
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

		$assign_config->assignfeedback_comments_enabled = get_config('assignfeedback_comments', 'default');
		$assign_config->assignfeedback_comments_commentinline = get_config('assignfeedback_comments', 'inline');
		$assign_config->assignfeedback_doublemark_enabled = get_config('assignfeedback_doublemark', 'default');
		$assign_config->assignfeedback_file_enabled = get_config('assignfeedback_file', 'default');
		$assign_config->assignfeedback_misconduct_enabled = get_config('assignfeedback_misconduct', 'default');
		$assign_config->assignfeedback_penalties_enabled = get_config('assignfeedback_penalties', 'default');
		$assign_config->assignfeedback_sample_enabled = get_config('assignfeedback_sample', 'default');

		foreach($array as $arr=>$elem){
			foreach($elem as $key=>$value){

				$unitinstance = $value['moduleInstance']; //course
				$course = $DB->get_record('course', array('idnumber' => $unitinstance));

				$quercusdata = new stdClass;

				$assessmentCode = $value["assessmentCode"]; //assignment id
				$academicYear = $value["academicYear"];
				$quercusdata->sitting = $value["sitting"];
				$quercusdata->sittingdescription = $value["sittingDescription"];
				$weighting = (float)$value["weighting"] * 100;
				$quercusdata->grademarkexempt = $value["gradeMarkExempt"];

				if (isset($value["externalDate"]) && $value["sittingDescription"] != 'FIRST_SITTING') {
					$quercusdata->externaldate = $value["externalDate"];
				} else {
					$quercusdata->externaldate = null;
				}

				if(isset($value["availableFrom"])){
					$quercusdata->availablefrom = $value["availableFrom"];
				}else{
					$quercusdata->availablefrom = 0;
				}

				if(isset($value["dueDate"])){
					$quercusdata->duedate = $value["dueDate"];
				}else{
					$quercusdata->duedate = 0;
				}

				if($quercusdata->sittingdescription == 'FIRST_SITTING'){
					$quercusdata->assessmentdescription = $value["assessmentDescription"] . ' ('. $weighting . '%)';
				}else{
					$append = ucfirst(strtolower(strtok($quercusdata->sittingdescription, '_')));
					$quercusdata->assessmentdescription = $value["assessmentDescription"] . ' ('. $weighting . '%) - ' . $append . ' Attempt';
				}
				$quercusdata->assignmentidnumber = $academicYear  . '_' . $assessmentCode;
				if($quercusdata->duedate != 0){
					if($course){
						$module = $DB->get_records_sql('SELECT *
														FROM {course_modules} cm
														INNER JOIN {local_quercus_tasks_sittings} s ON s.assign = cm.instance
														WHERE cm.course = ?
														AND cm.idnumber = ?
														AND s.sitting = ?', array($course->id, $quercusdata->assignmentidnumber, $quercusdata->sitting));

						if (!$module){
							$newcm = insert_assign($course, $quercusdata, $assign_config);
							if($newcm){
								mtrace("Created " . $quercusdata->assignmentidnumber . " " . $quercusdata->sittingdescription . " in unit " . $unitinstance);
							}else{
								mtrace("Error creating assessment " . $quercusdata->assignmentidnumber. " " . $quercusdata->sittingdescription . " in unit " . $unitinstance);
							}
						}else{
							mtrace("Cannot create " . $quercusdata->assignmentidnumber . " " . $quercusdata->sittingdescription . " in unit " . $unitinstance . " - Assignment already exists");
						}
					}else{
						mtrace("Cannot create " . $quercusdata->assignmentidnumber . " " . $quercusdata->sittingdescription . " in unit " . $unitinstance . " - Unit does not exist");
					}
			}else{
				mtrace("Cannot create " . $quercusdata->assignmentidnumber . " " . $quercusdata->sittingdescription . " in unit " . $unitinstance . " - No due date provided");
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

function insert_log($cm, $sitting, $course, $gradeinfo, $grade, $student){
	global $DB;
	$exists = $DB->get_record_sql('SELECT COUNT(*) AS total FROM {local_quercus_grades} WHERE assign = ? and student = ?', array($cm->instance, $student->id));

	if($exists->total != 1){
		//Insert into table
		$record = new stdClass();
		$record->assign  = $cm->instance;
		$record->sitting = $sitting->id;
		$record->course = $course->id;
		$record->course_module = $cm->id;
		$record->grader = $gradeinfo->id;
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

	$coursemodule =	array(
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
		$coursemodule["moodlestudentid"] = $v->moodlestudentid;
		$coursemodule["studentid"] = $v->studentid;
		$coursemodule["name"] = $v->name;
		$coursemodule["surname"] = $v->surname;
		$coursemodule["modulecode"] = $v->modulecode;
		$coursemodule["moduleinstanceid"] =  $v->moduleinstanceid;
		$coursemodule["moduledescription"] = $v->moduledescription;
		$coursemodule["assessmentsittingcode"] = $v->assessmentsittingcode;
		$coursemodule["academicsession"] = $v->academicsession;
		$coursemodule["assessmentdescription"] = $v->assessmentdescription;
		$coursemodule["assessmentcode"] = $v->assessmentcode;
		$coursemodule["assessmentresult"] = $v->assessmentresult;
		$coursemodule["unitleadername"] = $v->unitleadername;
		$coursemodule["unitleadersurname"] = $v->unitleadersurname;
		$coursemodule["unitleaderemail"] = $v->unitleaderemail;
		$dataarray[] = $coursemodule;
	}

	if(isset($dataarray)){
			return $dataarray;
	}else{
			return null;
	}
}

function match_grades($allgrades, $student, $gradeinfo){
	$grade = (string) 0;
	foreach($allgrades as $gk=>$gv){
		foreach ($gv['grades'] as $key => $value) {
			if($value['userid'] == $student->id){
				$grade = (string) convert_grade($gradeinfo->scaleid, substr($value['grade'], 0, strpos($value['grade'], ".")));
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
		$coursemodule =	array(
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
			//Get sittingdescription
			$sitting = $DB->get_record_sql('SELECT id, sitting_desc FROM {local_quercus_tasks_sittings} WHERE assign = ?', array($v->iteminstance));
			//Get user that locked the grades and scale id
			$gradeinfo = $DB->get_record_sql('SELECT u.id, u.firstname, u.lastname, u.email, MAX(h.timemodified), h.scaleid
																					FROM {grade_items_history} h
																					JOIN {user} u ON u.id = h.loggeduser
																					WHERE (h.itemmodule = ? AND h.iteminstance = ?)
																					AND locked > ?', array('assign', $cm->instance, $lastruntime));

			$coursemodule["modulecode"] = substr($course->shortname, 0, strpos($course->shortname, "_"));
			$coursemodule["moduleinstanceid"] =  $course->shortname;
			$coursemodule["moduledescription"] = $course->fullname;
			$coursemodule["assessmentsittingcode"] = $sitting->sitting_desc;
			$coursemodule["academicsession"] = substr($cm->idnumber, 0, strpos($cm->idnumber, "_"));
			$coursemodule["assessmentdescription"] = $cm->name;
			$coursemodule["assessmentcode"] = substr($cm->idnumber, strpos($cm->idnumber, "_") + 1);
			$coursemodule["unitleadername"] = $gradeinfo->firstname;
			$coursemodule["unitleadersurname"] = $gradeinfo->lastname;
			$coursemodule["unitleaderemail"] = $gradeinfo->email;

			$users = get_role_users(5, context_course::instance($course->id), false, 'u.id, u.lastname, u.firstname, idnumber', 'idnumber, u.lastname, u.firstname');

			// Get all grades for assignment
			unset($assignment);
			$assignment[] = $v->iteminstance;
			$allgrades = mod_assign_external::get_grades($assignment);
			$allgrades = $allgrades['assignments'];

			foreach($users as $key => $student){
				if(is_numeric($student->idnumber)){
					$coursemodule["moodlestudentid"] = $student->id; //NEW
					$coursemodule["studentid"] = $student->idnumber;
					$coursemodule["name"] = $student->firstname;
					$coursemodule["surname"] = $student->lastname;
					$grade = match_grades($allgrades, $student, $gradeinfo);
					if($grade == -1){
						$coursemodule["assessmentresult"] = 0;
					}else{
						$coursemodule["assessmentresult"] = $grade;
					}
					$dataarray[] = $coursemodule;

					if($grade == -1){
						//Send grade of 0 to Quercus
						$insertid = insert_log($cm, $sitting, $course, $gradeinfo, 0, $student);
						//Send email to helpdesk as tutor has added grade to Turnitin
						$message .= get_string('emailmessagestudent', 'local_quercus_tasks', ['idnumber'=>$student->idnumber, 'firstname'=>$student->firstname ,'lastname'=>$student->lastname]) . "\r\n\n";

					}else{
						$insertid = insert_log($cm, $sitting, $course, $gradeinfo, $grade, $student);

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
				//$to      = $gradeinfo->email;
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

	if(isset($dataarray)){
		return $dataarray;
	}else{
		return null;
	}

}

function export_grades($dataready){
	//Send data
	$ch = curl_init();
	$url = get_config('local_quercus_tasks', 'srsgws');

	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    'Content-Type: application/json',
	    'Content-Length: ' . strlen($dataready))
	);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $dataready);
	curl_setopt($ch,CURLOPT_FAILONERROR,true);

	$result = curl_exec($ch);
	$errormsg = curl_error($ch);
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

				$unitinstance = $value['moduleInstance']; //course
				$academicYear = $value["academicYear"];
				$assessmentCode = $value["assessmentCode"]; //assignment id
				$sitting = $value["sitting"];
				$assignmentidnumber = $academicYear  . '_' . $assessmentCode;

				$course = $DB->get_record('course', array('idnumber' => $unitinstance));

				if($course){

					if($course->startdate > '1533081600'){
						$assign = $DB->get_record_sql('	SELECT a.*, cm.id cm_id, cm.instance cm_instance, s.id sitting_id, s.externaldate
															FROM {course_modules} cm
															INNER JOIN {local_quercus_tasks_sittings} s ON s.assign = cm.instance
															INNER JOIN {assign} a ON a.id = cm.instance
															WHERE cm.course = ?
															AND cm.idnumber = ?
															AND s.sitting = ?', array($course->id, $assignmentidnumber, $sitting));

						if ($assign) {
							// Available from date
							if (isset($value["availableFrom"])) {
								$availablefrom = $value["availableFrom"];

								$time = new DateTime('now', core_date::get_user_timezone_object());
								$time = DateTime::createFromFormat('U', $availablefrom);
								$timezone = core_date::get_user_timezone($time);
								$dst = dst_offset_on($availablefrom, $timezone);
								$availablefrom = $time->getTimestamp() - $dst;
							}else{
								$availablefrom = 0;
							}

							if ($availablefrom != $assign->allowsubmissionsfromdate) {
									//Update assignment date
									$newdate = new stdClass();
									$newdate->id = $assign->id;
									$newdate->allowsubmissionsfromdate = $availablefrom;
									$update = $DB->update_record('assign', $newdate, $bulk=false);

									mtrace($assign->name . ' in ' . $unitinstance .
											' - Available from: ' . 	date( "d/m/Y h:i", $assign->allowsubmissionsfromdate) . ' -> ' . date( "d/m/Y h:i", $availablefrom));
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

								// Cut off date for second sittings
								if($value["sittingDescription"] != 'FIRST_SITTING'){
									$cutoffdate = $duedate;
								}

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

							if($duedate != $assign->duedate){
								//Update assignment dates
								$newdates = new stdClass();
								$newdates->id = $assign->id;
								$newdates->duedate = $duedate;
								// $newdates->allowsubmissionsfromdate = $availablefrom;
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
								mtrace($assign->name . ' in ' . $unitinstance .
										' - Due: ' . 		date( "d/m/Y h:i", $assign->duedate) . ' -> ' . date( "d/m/Y h:i", $duedate) .
										' * Cut off: ' . 	date( "d/m/Y h:i", $assign->cutoffdate) . ' -> ' . date( "d/m/Y h:i", $cutoffdate) .
										' * Grade: ' . 		date( "d/m/Y h:i", $assign->gradingduedate) . ' -> ' . date( "d/m/Y h:i", $gradingduedate) );
							}

							if (isset($value["externalDate"])){
								if ($value["externalDate"] != $assign->externaldate){
									//Update board date
									$newboard = new stdClass();
									$newboard->id = $assign->sitting_id;
									$newboard->externaldate = $value["externalDate"];
									$update = $DB->update_record('local_quercus_tasks_sittings', $newboard, $bulk=false);

									mtrace($assign->name . ' in ' . $unitinstance .
											' - Board: ' . 	date( "d/m/Y h:i", $assign->externaldate) . ' -> ' . date( "d/m/Y h:i", $value["externalDate"]));
								}
							}
						}
					}
				}
			}
		}
	}
}
