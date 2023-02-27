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
 * Test OCI connection
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();
require_admin();
require_capability('moodle/site:config', $context);
$PAGE->set_context($context);
$PAGE->set_url('/local/quercus_tasks/testconnection.php');

echo $OUTPUT->header();

$settings = get_config('local_quercus_tasks');

echo html_writer::tag('h2', get_string('testconnection', 'local_quercus_tasks'));
$errors = [];
echo html_writer::tag('h3', get_string(['ociconnectiontests', 'local_quercus_tasks']));

if (!$settings->connectionhost || !$settings->connectionpassword || !$settings->connectiondatabase) {
    $errors['ociconnection'] = get_string('connectionsettingserror', 'local_quercus_tasks');
}

if (!function_exists('oci_connect')) {
    $errors['oci_connect'] = get_string('ocierror', 'local_quercus_tasks');
}
$html = '';
if (count($errors) == 0) {
    // Connects to the XE service (i.e. database) on the "localhost" machine.
    $conn = oci_connect($settings->connectiondatabase, $settings->connectionpassword, $settings->connectionhost);

    if (!$conn) {
        $e = oci_error();
        $errors['ocidb'] = get_string('connectiondatabaserror', 'local_quercus_tasks', $e['message']);
    } else {
        $stid = oci_parse($conn, 'select * from STAFF_ENROLMENTS OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY');
        oci_execute($stid);
        $table = new html_table();
        $table->attributes = ['border' => '1'];
        $rows = [];

        $columnscount = oci_num_fields($stid);
        $cells = [];
        for ($i = 1; $i <= $columnscount; $i++) {
            $colname = oci_field_name($stid, $i);
            $cell = new html_table_cell(htmlspecialchars($colname, ENT_QUOTES | ENT_SUBSTITUTE));
            $cell->header = true;
            $cells[] = $cell;
        }
        $rows[] = new html_table_row($cells);

        while ($items = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $cells = [];
            foreach ($items as $item) {
                $text = ($item !== null ? htmlentities($item, ENT_QUOTES) : "&nbsp;");
                $cell = new html_table_cell($text);
                $cells[] = $cell;
            }
            $rows[] = new html_table_row($cells);
        }
        $table->data = $rows;
        $html = html_writer::table($table);
    }
}

if (count($errors) > 0) {
    $notify = new \core\output\notification(html_writer::alist($errors), \core\output\notification::NOTIFY_ERROR);
    echo $OUTPUT->render($notify);
    echo $html;
}

echo $OUTPUT->footer();
