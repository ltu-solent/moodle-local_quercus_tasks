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
 * Tasks
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname' => 'local_quercus_tasks\task\add_new_assignments',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\export_grades',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\delete_courses',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\get_new_courses',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\get_new_modules',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\create_new_modules',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\update_modules',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\get_new_grades',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\staff_enrolments',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    ),
    array(
        'classname' => 'local_quercus_tasks\task\update_dates',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '23',
        'day' => '*',
        'dayofweek' => '6',
        'month' => '*'
    )
);
