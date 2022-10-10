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
 * Get new grades
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quercus_tasks\task;

/**
 * Get new grades
 */
class get_new_grades extends \core\task\scheduled_task {
    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('getnewgrades', 'local_quercus_tasks');
    }

    /**
     * {@inheritDoc}
     *
     * @return void
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/quercus_tasks/lib.php');
        // Get new grades.
        $lastruntime = $DB->get_field_sql('SELECT max(timecreated) FROM {local_quercus_grades}');

        $processed = get_new_grades($lastruntime);

        if ($processed == true) {
            mtrace('New grades have been logged');
        } else {
            mtrace('No grades have been released');
        }
    }
}
