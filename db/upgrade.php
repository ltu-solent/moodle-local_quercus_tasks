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
 * Upgrade code for install
 *
 * @package    local_quercus_tasks
 * @copyright  2019 Solent University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for upgrade code
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_quercus_tasks_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2019051501) {

        if ($dbman->table_exists('local_quercus_tasks_sittings')) {
            // Define field externaldate to be added to local_quercus_tasks_sittings.
            $table = new xmldb_table('local_quercus_tasks_sittings');
            $field = new xmldb_field('externaldate', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

            // Conditionally launch add field externaldate.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2019051501, 'local', 'quercus_tasks');
    }

    return true;
}
