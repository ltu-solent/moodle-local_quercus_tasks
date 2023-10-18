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

    if ($oldversion < 2020020206) {
        // Define table local_quercus_staff_1 to be created.
        $table = new xmldb_table('local_quercus_staff_1');

        // Adding fields to table local_quercus_staff_1.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('role', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('useridnumber', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseidnumber', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_quercus_staff_1.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('useridnumber', XMLDB_KEY_FOREIGN, ['useridnumber'], 'user', ['idnumber']);
        $table->add_key('courseidnumber', XMLDB_KEY_FOREIGN, ['courseidnumber'], 'course', ['idnumber']);

        // Conditionally launch create table for local_quercus_staff_1.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Clone table.
        $table2 = clone($table);
        $table2->setName('local_quercus_staff_2');
        if (!$dbman->table_exists($table2)) {
            $dbman->create_table($table2);
        }

        // Insert dummy record.
        $dataobject[] = [
            'role' => 'courseleader',
            'useridnumber' => 000000,
            'courseidnumber' => 'AAAAAA',
        ];

            $dataobject = new stdClass();
            $dataobject->role = 'courseleader';
            $dataobject->useridnumber = 000000;
            $dataobject->courseidnumber = 'AAAAAA';
            $DB->insert_record('local_quercus_staff_1', $dataobject);

        // Quercus_tasks savepoint reached.
        upgrade_plugin_savepoint(true, 2020020206, 'local', 'quercus_tasks');
    }

    if ($oldversion < 2020101404) {

        // Define table local_quercus_modules to be created.
        $table = new xmldb_table('local_quercus_modules');

        // Adding fields to table local_quercus_modules.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('acadyear', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fullname', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('summary', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('category_path', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startdate', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enddate', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_quercus_modules.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('idnumber', XMLDB_KEY_FOREIGN, ['idnumber'], 'course', ['idnumber']);

        // Conditionally launch create table for local_quercus_modules.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Quercus_tasks savepoint reached.
        upgrade_plugin_savepoint(true, 2020101404, 'local', 'quercus_tasks');
    }

    if ($oldversion < 2020103000) {

        // Define table local_quercus_courses to be created.
        $table = new xmldb_table('local_quercus_courses');

        // Adding fields to table local_quercus_courses.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('acadyear', XMLDB_TYPE_CHAR, '5', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fullname', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null);
        $table->add_field('shortname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('summary', XMLDB_TYPE_TEXT, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('category_path', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('startdate', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('enddate', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_quercus_courses.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('idnumber', XMLDB_KEY_FOREIGN, ['idnumber'], 'course', ['idnumber']);

        // Conditionally launch create table for local_quercus_courses.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Quercus_tasks savepoint reached.
        upgrade_plugin_savepoint(true, 2020103000, 'local', 'quercus_tasks');
    }

    return true;
}
