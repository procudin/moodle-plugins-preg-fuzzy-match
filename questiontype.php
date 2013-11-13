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
 * Question type class for the short answer question type.
 *
 * @package    qtype
 * @subpackage shortanswer
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

//require_once($CFG->libdir . '/questionlib.php');
//require_once($CFG->dirroot . '/question/engine/lib.php');
//require_once($CFG->dirroot . '/question/type/shortanswer/question.php');


/**
 * The short answer question type.
 *
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex extends question_type {

    /**
     * Generate new id of question's option.
     * @return int Actual vaule of id.
     */
    public function generate_new_id() {

        error_log("[generate_new_id]\n", 3, "writeregex_log.txt");
        global $CFG;

        global $DB; // Database
        $result = 1;
        $records = $DB->get_records('qtype_writeregex_options', null, 'id ASC', 'id');

        foreach ($records as $record) {
            error_log('<' . $result . '>', 3, $CFG->dirroot . 'writeregex_log.txt');
            if ($record->id == $result) {
                $result++;
            } else {
                break;
            }
        }

        return $result;
    }

    public function get_question_options($question){

        error_log("[get_question_options]\n", 3, "writeregex_log.txt");
    }

    public function save_question_options($question) {

        error_log("[save_question_options]\n", 3, "writeregex_log.txt");
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {

        error_log("[initialise_question_instance]\n", 3, "writeregex_log.txt");
    }

    public function delete_question($questionid, $contextid){

        error_log("[delete_question]\n", 3, "writeregex_log.txt");
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null){

        error_log("[import_from_xml]\n", 3, "writeregex_log.txt");
    }

    public function export_to_xml($question, qformat_xml $format, $extra=null){

        error_log("[export_to_xml]\n", 3, "writeregex_log.txt");
    }

}
