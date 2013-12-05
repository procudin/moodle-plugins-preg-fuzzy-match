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

require_once($CFG->dirroot . '/question/type/questiontypebase.php');


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

    private function form_options($question) {

        $result = new stdClass();

//        echo '<pre>';
//        print_r($question);
//        echo '</pre>';

        $result->id                      = 0;
        $result->questionid              = $question->id;
        $result->notation                = $question->wre_notation;
        $result->syntaxtreehinttype      = $question->wre_st;
        $result->syntaxtreehintpenalty   = $question->wre_st_penalty;
        $result->explgraphhinttype       = $question->wre_eg;
        $result->explgraphhintpenalty    = $question->wre_eg_penalty;
        $result->descriptionhinttype     = $question->wre_d;
        $result->descriptionhintpenalty  = $question->wre_d_penalty;
        $result->teststringshinttype     = $question->wre_td;
        $result->teststringshintpenalty  = $question->wre_td_penalty;
        $result->compareregexpercentage  = $question->wre_cre_percentage;
        $result->compareautomatercentage = $question->wre_acre_percentage;

        return $result;
    }

    public function get_question_options($question){

        error_log("[get_question_options]\n", 3, "writeregex_log.txt");

        global $DB;

        $result = new stdClass();

        $result->options = $DB->get_record('qtype_writeregex_options', array('questionid' => $question->id));
        $result->answers = $DB->get_records('question_answers', array('question' => $question->id));

        return $result;
    }

    private function save_regexp_answers($question) {

        global $DB;

        $answers_value = $question->wre_regexp_answers_answer;
        $answers_fraction = $question->wre_regexp_answers_fraction;
        $answers_feedback = $question->wre_regexp_answers_feedback;

        $index = 0;

        foreach ($answers_value as $item) {

            if (!empty($item)) {

                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $item;
                $answer->answerformat = 1;
                $answer->fraction = $answers_fraction[$index];
                $answer->feedback = $answers_feedback[$index]['text'];
                $answer->feedbackformat = $answers_feedback[$index]['format'];
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            $index++;
        }
    }

    /**
     * Метод сохранения regexp ответа в бд.
     * @param $value Значение ответа.
     * @param $fraction Оценка ответа.
     * @param $feedback Описание ответа.
     * @param $feedbackformat Формат описания ответа.
     * @param $questionid Идентифкатор вопроса.
     */
    protected function save_regexp_answer ($value, $fraction, $feedback, $feedbackformat, $questionid) {

        global $DB;

        $result = $this->form_regexp_answer($value, $fraction, $feedback, $feedbackformat, $questionid);

        $result->id = $DB->insert_record('question_answers', $result);
    }

    /**
     * Метод сохранения ответа тестовых строк в бд.
     * @param $value Значение ответа.
     * @param $fraction Оценка ответа.
     * @param $questionid Идентификатор вопроса.
     */
    protected function save_test_string_answer ($value, $fraction, $questionid) {

        global $DB;

        $result = $this->form_test_string_answer($value, $fraction, $questionid);

        $result->id = $DB->insert_record('question_answers', $result);
    }

    /**
     * Метод обновления regexp ответа в бд.
     * @param $value Значение ответа.
     * @param $fraction Оценка ответа.
     * @param $feedback Описание ответа.
     * @param $feedbackformat Формат описания ответа.
     * @param $id Идентификатор ответа.
     * @param $questionid Идентификатор вопроса.
     */
    protected function update_regexp_answer ($value, $fraction, $feedback, $feedbackformat, $id, $questionid) {

        global $DB;

        $result = $this->form_regexp_answer($value, $fraction, $feedback, $feedbackformat, $questionid);

        $result->id = $id;

        $DB->update_record('question_answers', $result);
    }

    /**
     * Метод изменения тестовой строки в бд.
     * @param $value Значение строки.
     * @param $fraction Оценка.
     * @param $id Идентификатор строки.
     * @param $questionid Идентификатор вопроса.
     */
    protected function update_test_string_answer ($value, $fraction, $id, $questionid) {

        global $DB;

        $result = $this->form_test_string_answer($value, $fraction, $questionid);

        $result->id = $id;

        $DB->update_record('question_answers', $result);
    }

    /**
     * Метод формирования stdClass ответа в виде regexp.
     * @param $value Значение ответа.
     * @param $fraction Оценка.
     * @param $feedback Описание.
     * @param $feedbackformat Формат описания.
     * @param $questionid Идентификатор вопроса.
     * @return stdClass Ответ в виде класса для сохранения в бд.
     */
    protected function  form_regexp_answer ($value, $fraction, $feedback, $feedbackformat, $questionid) {
        $result = new stdClass();

        $result->answer = $value;
        $result->question = $questionid;
        $result->answerformat = 1;
        $result->fraction = $fraction;
        $result->feedback = $feedback;
        $result->feedbackformat = $feedbackformat;

        return $result;
    }

    /**
     * Метод формирования stdClass ответа тестовых строк.
     * @param $value Значение ответа.
     * @param $fraction Оценка ответа.
     * @param $questionid Идентификатор вопроса.
     * @return stdClass Ответ в виде класса ждя сохранения в бд.
     */
    protected function form_test_string_answer ($value, $fraction, $questionid) {

        $result = new stdClass();

        $result->answer = $value;
        $result->question = $questionid;
        $result->answerformat = 2;
        $result->fraction = $fraction;
        $result->feedback = '';
        $result->feedbackformat = 0;

        return $result;
    }

    /**
     * Метод добавления/изменения regexp ответов на вопрос.
     * @param $question Вопрос.
     */
    protected function save_update_regexp_answers ($question) {

        $answers_value = $question->wre_regexp_answers_answer;
        $answers_fraction = $question->wre_regexp_answers_fraction;
        $answers_feedback = $question->wre_regexp_answers_feedback;
        $answers_id = $question->regexp_id;

        $index = 0;

        foreach ($answers_value as $item) {

            if (!empty($item)) {

                if ($answers_id[$index] == 'qwe') {
                    $this->save_regexp_answer($item, $answers_fraction[$index],
                        $answers_feedback[$index]['text'], $answers_feedback[$index]['format'], $question->id);
               } else {
                    $this->update_regexp_answer($item, $answers_fraction[$index],
                        $answers_feedback[$index]['text'], $answers_feedback[$index]['format'], $answers_id[$index], $question->id);
                }

            }

            $index++;
        }
    }

    /**
     * Метод добавления/изменения ответов на вопрос типа тестовая строка.
     * @param $question Вопрос.
     */
    protected function save_update_test_string_answers ($question) {

        $answers_value = $question->wre_regexp_ts_answer;
        $answers_fraction = $question->wre_regexp_ts_fraction;
        $answers_id = $question->test_string_id;

        $index = 0;

        foreach ($answers_value as $item) {

            if (!empty($item)) {

                if ($answers_id[$index] == 'qwe') {
                    $this->save_test_string_answer($item, $answers_fraction[$index], $question->id);
                } else {
                    $this->update_test_string_answer($item, $answers_fraction[$index],
                        $answers_id[$index], $question->id);
                }
            }

            $index++;
        }
    }

    protected function update_regexp_answers ($question) {

        global $DB;

        $answers_value = $question->wre_regexp_answers_answer;
        $answers_fraction = $question->wre_regexp_answers_fraction;
        $answers_feedback = $question->wre_regexp_answers_feedback;
        $answers_id = $question->regexp_id;

        $index = 0;

        foreach ($answers_value as $item) {

            if (!empty($item)) {

                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $item;
                $answer->answerformat = 1;
                $answer->fraction = $answers_fraction[$index];
                $answer->feedback = $answers_feedback[$index]['text'];
                $answer->feedbackformat = $answers_feedback[$index]['format'];
                $answer->id = $answers_id[$index];

                $DB->update_record('question_answer', $answer);
            }

            $index++;
        }
    }

    private function save_test_string_answers($question) {

        global $DB;

        $answers_value = $question->wre_regexp_ts_answer;
        $answers_fraction = $question->wre_regexp_ts_fraction;

        $index = 0;

        foreach ($answers_value as $item) {

            if (!empty($item)) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $item;
                $answer->answerformat = 2;
                $answer->fraction = $answers_fraction[$index];
                $answer->feedback = '';
                $answer->feedbackformat = 0;
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            $index++;
        }
    }

    /**
     * Метод изменения параметров и ответов для типа вопроса.
     * @param $question Вопрос.
     * @return string
     */
    protected function update_question_options ($question) {

        $std_question = $this->form_options($question);

        $std_question->id = $question->wre_id;

        global $DB;

        $DB->update_record('qtype_writeregex_options', $std_question);

        // Step 1: update regexp
        $this->save_update_regexp_answers($question);

        // Step 2: update test string
        $this->save_update_test_string_answers($question);

        return '';
    }

    public function save_question_options($question) {

        if ($question->wre_id != 'qwe') {

            $result = $this->update_question_options($question);

            return $result;
        }

        $std_question = $this->form_options($question);

        global $DB;

        echo '<pre>';
        print_r($question);
        echo '</pre>';

        $DB->insert_record('qtype_writeregex_options', $std_question);

        $parentresult = parent::save_question_options($question);
        if ($parentresult !== null) {
            // Parent function returns null if all is OK.
            return $parentresult;
        }

        // Step 1: save regexp
        $this->save_update_regexp_answers($question);

        // Step 2: save test strings
        $this->save_update_test_string_answers($question);

        return '';
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {

        parent::initialise_question_instance($question, $questiondata);

        error_log("[initialise_question_instance]\n", 3, "writeregex_log.txt");
    }

    public function delete_question($questionid, $contextid){

        parent::delete_question($questionid, $contextid);

        global $DB;

        $DB->delete_records('qtype_writeregex_options', array('questionid' => $questionid));

        error_log("[delete_question]\n", 3, "writeregex_log.txt");
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra=null){

        error_log("[import_from_xml]\n", 3, "writeregex_log.txt");
    }

    public function export_to_xml($question, qformat_xml $format, $extra=null){

        error_log("[export_to_xml]\n", 3, "writeregex_log.txt");
    }

}
