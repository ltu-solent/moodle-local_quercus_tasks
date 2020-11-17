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
 * This file defines the admin settings for this plugin
 *
 * @package   local_quercus_tasks
 * @copyright 2018 Southampton Solent University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings = new admin_settingpage('local_quercus_tasks', new lang_string('pluginname', 'local_quercus_tasks'));

$settings->add(new admin_setting_heading('exportsettings', get_string('exportsettings', 'local_quercus_tasks'), ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/srsgws', get_string('srsgws', 'local_quercus_tasks'), '', ''));

$settings->add(new admin_setting_heading('assignmentsettings', get_string('assignmentsettings', 'local_quercus_tasks'), ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/cutoffinterval', get_string('cutoffinterval', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/gradingdueinterval', get_string('gradingdueinterval', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/grademarkscale', get_string('grademarkscale', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/grademarkexemptscale', get_string('grademarkexemptscale', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/boardbuffer', get_string('boardbuffer', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/releaseroles', get_string('releaseroles', 'local_quercus_tasks'), '', ''));

$settings->add(new admin_setting_heading('supportsettings', get_string('supportsettings', 'local_quercus_tasks'), ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/gradinghelpurl', get_string('gradinghelpurl', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/emaillt', get_string('emaillt', 'local_quercus_tasks'), '', ''));

$settings->add(new admin_setting_heading('connectionsettings', get_string('connectionsettings', 'local_quercus_tasks'), ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/datafile', get_string('datafile', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/connectionhost', get_string('connectionhost', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/connectionpassword', get_string('connectionpassword', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/connectiondatabase', get_string('connectiondatabase', 'local_quercus_tasks'), '', ''));

$settings->add(new admin_setting_heading('dataviews', get_string('dataviews', 'local_quercus_tasks'), ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/enrolmentview', get_string('enrolmentview', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/modulesview', get_string('modulesview', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/coursesview', get_string('coursesview', 'local_quercus_tasks'), '', ''));

$settings->add(new admin_setting_heading('createmodulessettings', get_string('createmodulessettings', 'local_quercus_tasks'), ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/acadyear', get_string('acadyear', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/moduletemplate', get_string('moduletemplate', 'local_quercus_tasks'), '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/modulelimit', get_string('modulelimit', 'local_quercus_tasks'), '', ''));

$ADMIN->add('localplugins', $settings);
