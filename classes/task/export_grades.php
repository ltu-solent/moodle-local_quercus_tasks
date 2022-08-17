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
 * Export grades
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quercus_tasks\task;

class export_grades extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('exportgrades', 'local_quercus_tasks');
    }

    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/quercus_tasks/lib.php');

        // Get records with response of null for processing.
        $dataarray = get_retry_list();
        if (count($dataarray) > 0) {
            foreach ($dataarray as $key => $value) {
                $dataready = json_encode($value);
                $response = export_grades($dataready);

                if ($response != null) {
                    $moduleinstanceid = update_log($response);
                    mtrace('Released grades have been processed for ' . $moduleinstanceid);
                } else {
                    mtrace('Can\'t connect to Quercus');
                }
            }
        } else {
            mtrace('No grades have been released');
        }
        \core\task\manager::clear_static_caches();
    }
}
