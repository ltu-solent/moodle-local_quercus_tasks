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
 * Delete courses
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quercus_tasks\task;

/**
 * Delete courses
 */
class delete_courses extends \core\task\scheduled_task {
    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('deletecourses', 'local_quercus_tasks');
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/quercus_tasks/lib.php');
        delete_courses();
    }
}
