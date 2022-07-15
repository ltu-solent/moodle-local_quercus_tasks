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
 * Generator class for local_quercus_tasks
 *
 * @package   local_quercus_tasks
 * @author    Mark Sharp <mark.sharp@solent.ac.uk>
 * @copyright 2021 Solent University {@link https://www.solent.ac.uk}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_quercus_tasks_generator extends component_generator_base
{

    public $assigncount = 0;
    /**
     * Reset process.
     *
     * Do not call directly.
     *
     * @return void
     */
    public function reset() {
        $this->assigncount = 0;
    }

    public function create_quercusdata_item(array $record) {
        $this->assigncount++;
        $i = $this->assigncount;

        if (!isset($record['module'])) {
            $record['module'] = "ABC{$i}";
        }

        if (!isset($record['moduleInstance'])) {
            $record['moduleInstance'] = "ABC{$i}_123456789";
        }

        if (!isset($record['assessmentCode'])) {
            $record['assessmentCode'] = "PROJ1";
        }

        if (!isset($record['academicYear'])) {
            $record['academicYear'] = date('Y');
        }

        if (!isset($record['sitting'])) {
            $record['sitting'] = '11600003';
        }

        if (!isset($record['sittingDescription'])) {
            $record['sittingDescription'] = 'FIRST_SITTING';
        }

        if (!isset($record['assessmentDescription'])) {
            $record['assessmentDescription'] = 'Project 1';
        }

        if (!isset($record['weighting'])) {
            $record['weighting'] = 1;
        }

        if (!isset($record['gradeMarkExempt'])) {
            $record['gradeMarkExempt'] = 'N';
        }
        
        if (!isset($record['externalDate'])) {
            $record['externalDate'] = null;
        }

        if (!isset($record['dueDate'])) {
            $record['dueDate'] = 0;
        }

        return $record;
    }

    /**
     * This takes an array as if it's come from the Quercus xml datafile,
     * and does some preprocessing to mimic the create_assignments function.
     * This will be ready for inserting as $quercusdata in the insert_assign() function.
     *
     * @param array $quercusdata
     * @return stdClass
     */
    public function preprocess_quercusdata(array $quercusdata) {
        $record = new stdClass();
        $weighting = (float)$quercusdata["weighting"] * 100;
        $record->sitting = $quercusdata["sitting"];
		$record->sittingdescription = $quercusdata["sittingDescription"];
        $record->grademarkexempt = $quercusdata["gradeMarkExempt"];

        if (isset($quercusdata["externalDate"]) && $quercusdata["sittingDescription"] != 'FIRST_SITTING') {
            $record->externaldate = $quercusdata["externalDate"];
        } else {
            $record->externaldate = null;
        }
        if (isset($quercusdata["availableFrom"])){
            $record->availablefrom = $quercusdata["availableFrom"];
        } else {
            $record->availablefrom = 0;
        }

        if (isset($quercusdata["dueDate"])){
            $record->duedate = $quercusdata["dueDate"];
        } else {
            $record->duedate = 0;
        }

        if ($record->sittingdescription == 'FIRST_SITTING'){
            $record->assessmentdescription = $quercusdata["assessmentDescription"] . ' ('. $weighting . '%)';
        } else {
            $append = ucfirst(strtolower(strtok($record->sittingdescription, '_')));
            $record->assessmentdescription = $quercusdata["assessmentDescription"] . ' ('. $weighting . '%) - ' . $append . ' Attempt';
        }
        $record->assessmentcode = $quercusdata["assessmentCode"];
        $record->assignmentidnumber = $quercusdata["academicYear"]  . '_' . $quercusdata["assessmentCode"];
        return $record;
    }

    /**
     * Creates an entry in the local_quercus_module table.
     *
     * @param array $record
     * @return object
     */
    public function create_quercus_module(array $record) {
        global $DB;
        $record = (object)$record;
        $record->id = $DB->insert_record('local_quercus_modules', $record);
        return $record;
    }
}
