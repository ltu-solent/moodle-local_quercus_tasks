<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language file
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['acadyear'] = 'Academic year in view to filter data by';
$string['addnewassign'] = 'Add new assignments';
$string['assign_filterwarning'] = '<i class="fa fa-warning"></i> <strong>Warning:</strong> You are not displaying all users and will not be able to release your grades. {$a->msg}';
$string['assign_formativeinfo'] = '<i class="fa fa-info-circle"></i> The marks for these assignments will not be uploaded to Quercus as this is not a Quercus Assignment.';
$string['assign_resetprefs'] = ' <a href="{$a->url}">"Reset your table preferences"</a>.';
$string['assign_resetworkflow'] = ' <a href="{$a->url}">Set all Options to "No filter"</a>.';
$string['assignmentsettings'] = 'Assignment settings';

$string['boardbuffer'] = 'Days after resit board that grades can be released';

$string['connectiondatabase'] = 'Quercus connection database';
$string['connectiondatabaserror'] = 'There was a problem connecting to the database: {$a}';
$string['connectionhost'] = 'Quercus connection host';
$string['connectionpassword'] = 'Quercus connection password';
$string['connectionsettings'] = 'Data import connection settings';
$string['connectionsettingserror'] = 'The settings for the connection are not correct.';
$string['courselimit'] = 'Number of courses to delete per batch';
$string['coursesview'] = 'Courses view name';
$string['createmodulelimit'] = 'Number of modules to create per batch';
$string['createmodulessettings'] = 'Create new modules settings';
$string['createnewmodules'] = 'Create new modules';
$string['cutoffinterval'] = 'Cut off date interval in weeks';
$string['cutoffintervalsecondplus'] = 'Cut off date interval in weeks for second/third+ sittings';

$string['datafile'] = 'Assignment XML data file path';
$string['dataviews'] = 'Quercus data views';
$string['deletecategories'] = 'Comma separated list of category IDs to delete courses from';
$string['deletecourses'] = 'Delete courses';
$string['deleted'] = 'Deleted {$a->shortname} - {$a->fullname}';
$string['deleting'] = 'Deleting {$a->shortname} - {$a->fullname}';

$string['emaillt'] = 'LT email address';
$string['enrolmentview'] = 'Enrolment view name';
$string['errordeleting'] = 'Error deleting {$a->shortname} - {$a->fullname}';
$string['exportgrades'] = 'Export grades';
$string['exportmodulelimit'] = 'Number of modules to export per batch';
$string['exportsettings'] = 'Export connection details';

$string['getnewcourses'] = 'Get new courses';
$string['getnewgrades'] = 'Get new grades';
$string['getnewmodules'] = 'Get new modules';
$string['grademarkexemptscale'] = 'ID of grademark exempt gradescale';
$string['grademarkscale'] = 'ID of grademark gradescale';
$string['gradingdueinterval'] = 'Grading due date interval in weeks';
$string['gradinghelpurl'] = 'Grading help guide URL';

$string['invalidstudent'] = 'Student ID invalid of {$a->idnumber} for {$a->firstname} {$a->lastname}';

$string['modulesview'] = 'Modules view name';
$string['moduletemplate'] = 'Module template id';

$string['nodelete'] = 'Nothing to delete';
$string['noendpointset'] = 'No endpoint set';
$string['nomodules'] = 'No new modules';
$string['nopatherror'] = 'Cannot resolve category path ';
$string['notcreatederror'] = '{$a->type} not created ';

$string['ociconnectiontests'] = 'OCI connection tests';
$string['ocierror'] = 'OCI is not installed';

$string['pluginname'] = 'Quercus Tasks';

$string['quercus_tasks:releasegrades'] = 'Release grades to Quercus';

$string['releaseroles'] = 'Comma separated list of role IDs that can release grades (no spaces)';

$string['srsgws'] = 'SRS-GWS URL';
$string['staffenrolments'] = 'Staff external database enrolments';
$string['supportsettings'] = 'Support settings';

$string['testconnection'] = 'Test connection';

$string['updatedates'] = 'Update assignment dates';
$string['updatemodules'] = 'Update module dates';
