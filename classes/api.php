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
 * API helper class with handy functions
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2022 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_quercus_tasks;

class api {

    /**
     * Returns all the grade items for a course, or a single assignment.
     *
     * @param integer $courseid
     * @param integer $assignid
     * @return array|false
     */
    public static function get_quercus_gradeitems($courseid, $assignid = 0) {
        global $DB;
        $params = [
            'courseid' => $courseid
        ];
        if ($assignid > 0) {
            $assignsql = ' AND gi.iteminstance = :assignid ';
            $params['assignid'] = $assignid;
        }
        
        $sql = "SELECT gi.*
        FROM {grade_items} gi
        JOIN {course_modules} cm ON cm.instance = gi.iteminstance
        JOIN {modules} m ON m.id = cm.module AND m.name = 'assign'
        WHERE gi.courseid = :courseid $assignsql AND gi.itemmodule = 'assign' AND cm.idnumber != ''";
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Given a courseid, gets all assignments with an idnumber set (Quercus)
     * Sitting will be null if it doesn't exist.
     *
     * @param int $courseid
     * @return array|false
     */
    public static function get_quercus_assignments($courseid) {
        global $DB;
        $sql = "SELECT a.*, cm.idnumber, qs.sitting, qs.sitting_desc, qs.externaldate
        FROM {assign} a
        JOIN {course_modules} cm ON cm.instance = a.id
        JOIN {modules} m ON m.id = cm.module AND m.name = 'assign'
        LEFT JOIN {local_quercus_tasks_sittings} qs ON qs.assign = .a.id
        WHERE a.course = :courseid AND cm.idnumber <> ''";
        return $DB->get_records_sql($sql, ['courseid' => $courseid]);
    }

    /**
     * Given an assignmentid gets assignment data from table plus sitting data.
     *
     * @param int $assignid
     * @return stdClass|false
     */
    public static function get_quercus_assignment($assignid) {
        global $DB;
        $sql = "SELECT a.*, cm.idnumber, qs.sitting, qs.sitting_desc, qs.externaldate
        FROM {assign} a
        JOIN {course_modules} cm ON cm.instance = a.id
        JOIN {modules} m ON m.id = cm.module AND m.name = 'assign'
        LEFT JOIN {local_quercus_tasks_sittings} qs ON qs.assign = a.id
        WHERE a.id = :assignid AND cm.idnumber <> ''";
        return $DB->get_record_sql($sql, ['assignid' => $assignid], MUST_EXIST);
    }

    /**
     * Gets the scale record, and explodes the scale item for easier handling.
     *
     * @param int $scaleid
     * @return stdClass|null
     */
    public static function get_scale($scaleid) {
        global $DB;
        $scale = $DB->get_record('scale', ['id' => $scaleid]);
        if (!$scale) {
            return null;
        }
        $scale->items = explode(',', $scale->scale);
        return $scale;
    }
}

