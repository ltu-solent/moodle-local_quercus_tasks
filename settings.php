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

$settings->add(new admin_setting_configtext('local_quercus_tasks/datafile', 'Data file path', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/srsgws', 'SRS-GWS URL', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/cutoffinterval', 'Cut off date interval in weeks', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/gradingdueinterval', 'Grading due date interval in weeks', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/grademarkscale', 'ID of grademark gradescale', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/grademarkexemptscale', 'ID of grademark exempt gradescale', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/boardbuffer', 'Days after resit board that grades can be released', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/releaseroles', 'Comma separated list of role IDs that can release grades (no spaces)', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/gradinghelpurl', 'Grading help guide URL', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/senderrorto', 'Email to send Turnitin errors to', '', ''));
$settings->add(new admin_setting_configtext('local_quercus_tasks/emailfrom', 'Email from address', '', ''));


$ADMIN->add('localplugins', $settings);
