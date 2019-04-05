<?php
$string['pluginname'] = 'Quercus Tasks';
$string['add_new_assign'] = 'Add new assignments';
$string['add_resits'] = 'Add resits';
$string['export_grades'] = 'Export grades';
$string['update_dates'] = 'Update dates';
$string['emailfrom'] = 'noreply@learn.ac.uk';
$string['emailsubject'] = 'Grade entered in Turnitin for {$a->shortname}';
$string['emailmessage'] = 'A grade has been entered incorrectly for {$a->firstname} {$a->lastname} for {$a->name}';

$string['add_new_assign'] = 'Add new assignments';
$string['add_resits'] = 'Add resits';
$string['emailmessageintro'] = '<p>Marks of -1 have been sent to Quercus and are showing as A1 in SOL for the students below, due to grade values being incorrectly entered directly to Turnitin.</p><p>Please \'Reply to all\' in this email and add the true marks to the table below (alphanumeric Solent Gradescale, unless exempt), so that Quercus and SOL can be manually updated.</p><p>To further avoid this error, please be sure to add all grades to SOL and not Turnitin; the help guide for grading assessments can be found here: {$a->gradinghelpurl}.</p>';
$string['emailmessagestudent'] = '<tr><td>{$a->idnumber}</td><td>{$a->firstname} {$a->lastname}</td><td>  </td></tr>';
$string['emailsubject'] = 'Invalid marks uploaded to Quercus - {$a->shortname} - {$a->assign}';
$string['export_grades'] = 'Export grades';
//$string['gradinghelp'] = 'The help guide for grading assessments can be found here: {$a->gradinghelpurl}<br /><br />';
$string['pluginname'] = "Quercus Tasks";
$string['tablefooter'] = '</table>';
$string['tableheader'] = '<table border="1"><tr><th>Student ID</th><th>Student name</th><th>True grade</th></tr>';
$string['update_dates'] = 'Update dates';