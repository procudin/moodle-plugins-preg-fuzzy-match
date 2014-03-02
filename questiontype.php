<?php
// This file is part of WriteRegex question type - https://code.google.com/p/oasychev-moodle-plugins/

//

// WriteRegex is free software: you can redistribute it and/or modify

// it under the terms of the GNU General Public License as published by

// the Free Software Foundation, either version 3 of the License, or

// (at your option) any later version.

//

// WriteRegex is distributed in the hope that it will be useful,

// but WITHOUT ANY WARRANTY; without even the implied warranty of

// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the

// GNU General Public License for more details.

//

// You should have received a copy of the GNU General Public License

// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Question type class for the write regex question type.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/shortanswer/questiontype.php');

/**
 * Question type class for the write regex question type.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex extends qtype_shortanswer {

    /** Format value of test string answers. */
    const TEST_STRING_ANSWER_FORMAT_VALUE = 1;

    /**
     * Get question options.
     * @param object $question Question object.
     * @return bool Indicates success or failure.
     */
    public function get_question_options($question){

        $result = parent::get_question_options($question);

        $question->syntaxtreehint = array();
        $question->explgraphhint = array();
        $question->descriptionhint = array();
        $question->teststringshint = array();

        return $result;
    }

    /**
     * Save question options.
     * @param object $question Question object.
     * @return object $result->error or $result->noticeyesno or $result->notice
     */
    public function save_question_options($question) {
        global $DB;
        $result = new stdClass();

        // remove all answers
        $DB->delete_records('question_answers', array('question' => $question->id));

        if (!isset($question->wre_regexp_ts_answer)) {

            $answers = array();
            $answersstrings = array();
            $fraction = array();
            $fractionstrings = array();

            foreach ($question->answer as $index => $item) {
                if ($question->answerformat[$index] == qtype_writeregex::TEST_STRING_ANSWER_FORMAT_VALUE) {
                    $answersstrings[] = $item;
                    $fractionstrings[] = $question->fraction[$index];
                } else {
                    $answers[] = $item;
                    $fraction[] = $question->fraction[$index];
                }
            }

            $question->answer = $answers;
            $question->fraction = $fraction;
            $question->wre_regexp_ts_answer = $answersstrings;
            $question->wre_regexp_ts_fraction = $fractionstrings;
        }

        // insert regexp answers
        parent::save_question_options($question);


        // insert test string answers
        foreach ($question->wre_regexp_ts_answer as $key => $answer) {

            if (trim($answer) == '' && $question->wre_regexp_ts_fraction[$key] == 0) {
                continue;
            }

            $record = $this->get_test_string_answer_object($answer,
                $question->wre_regexp_ts_fraction[$key], $question->id);

            $DB->insert_record('question_answers', $record);
        }

        return $result;
    }

    /**
     * Save additional question type data into the hint optional field.
     * @param object $formdata the data from the form.
     * @param int $number number of hint to get options from.
     * @param bool $withparts whether question have parts.
     * @return string value to save into the options field of question_hints table.
     */
    protected function save_hint_options($formdata, $number, $withparts) {

        // add syntaxtreehint
        $result = $formdata->syntaxtreehint[$number];

        // add explgraphhint
        $result .= '\n' . $formdata->explgraphhint[$number];

        // add descriptionhint
        $result .= '\n' . $formdata->descriptionhint[$number];

        // add teststringshint
        $result .= '\n' . $formdata->teststringshint[$number];

        return $result;
    }

    /**
     * Determine if the hint with specified number is not empty and should be saved.
     * @param object $formdata the data from the form.
     * @param int $number number of hint under question.
     * @param bool $withparts whether to take into account clearwrong and shownumcorrect options.
     * @return bool is this particular hint data empty.
     */
    protected function is_hint_empty_in_form_data($formdata, $number, $withparts) {
        $result = parent::is_hint_empty_in_form_data($formdata, $number, $withparts);

        if ($result and $formdata->syntaxtreehint[$number] == 0 and $formdata->explgraphhint[$number] == 0
            and $formdata->descriptionhint[$number] == 0 and $formdata->teststringshint[$number] == 0)
            return true;
        else
            return false;
    }

    /**
     * Get test string answer object.
     * @param $answer stdClass Test string.
     * @param $fraction float Fraction value.
     * @param $questionid int Questions id.
     * @return stdClass stdClass of answer.
     */
    private function get_test_string_answer_object ($answer, $fraction, $questionid) {

        $result = new stdClass();

        $result->answer = $answer;
        $result->question = $questionid;
        $result->answerformat = qtype_writeregex::TEST_STRING_ANSWER_FORMAT_VALUE;
        $result->fraction = $fraction;
        $result->feedback = '';
        $result->feedbackformat = 0;

        return $result;
    }

    /**
     * Get extra question fields.
     * @return array Array of tables columns.
     */
    public function extra_question_fields() {

        return array('qtype_writeregex_options',
            'usecase',                   // Regex use case
            'engine',                    // Regex engine
            'notation',                  // Regex notation
            'syntaxtreehinttype',        // Value of syntax tree hint type
            'syntaxtreehintpenalty',     // Value of syntax tree hint penalty
            'explgraphhinttype',         // Value of explain graph hint type
            'explgraphhintpenalty',      // Value of explain graph hint penalty
            'descriptionhinttype',       // Value of description hint type
            'descriptionhintpenalty',    // Value of description hint penalty
            'teststringshinttype',       // Value of test strings hint type
            'teststringshintpenalty',    // Value of test strings hint penalty
            'compareregexpercentage',    // Percentage value of compare regex
            'compareautomatapercentage', // Percentage value of compare regex by automata
            'compareregexpteststrings'   // Percentage value of compare regex by testing strings
        );
    }

    /**
     * Import from xml
     * @param $data array Data from file.
     * @param $question object Question object.
     * @param qformat_xml $format xml Format.
     * @param null $extra Extra options.
     * @return bool|object Question objects.
     */
    public function import_from_xml($data, $question, qformat_xml $format, $extra=null){

        $question_type = $data['@']['type'];
        if ($question_type != $this->name()) {
            return false;
        }

        $extraquestionfields = $this->extra_question_fields();
        if (!is_array($extraquestionfields)) {
            return false;
        }

        // Omit table name.
        array_shift($extraquestionfields);
        $qo = $format->import_headers($data);
        $qo->qtype = $question_type;

        foreach ($extraquestionfields as $field) {
            $qo->$field = $format->getpath($data, array('#', $field, 0, '#'), '');
        }

        // import hints
        $hints = $data['#']['hint'];
        $h_count = 0;
        foreach ($hints as $hint) {
            $qo->hint[$h_count]['format'] = 1; // TODO Поправить
            $qo->hint[$h_count]['text'] = $hint['#']['text']['0']['#'];
            $hint_options = explode('\n', $hint['#']['options']['0']['#']);
            if (count($hint_options) == 4) {

                $qo->syntaxtreehint[] = $hint_options[0];
                $qo->explgraphhint[] = $hint_options[1];
                $qo->descriptionhint[] = $hint_options[2];
                $qo->teststringshint[] = $hint_options[3];
            } else {
                $qo->syntaxtreehint[] = 0;
                $qo->explgraphhint[] = 0;
                $qo->descriptionhint[] = 0;
                $qo->teststringshint[] = 0;
            }
        }

        // Run through the answers.
        $answers = $data['#']['answer'];
        $a_count = 0;
        $extraanswersfields = $this->extra_answer_fields();
        if (is_array($extraanswersfields)) {
            array_shift($extraanswersfields);
        }
        foreach ($answers as $answer) {
            $ans = $format->import_answer($answer, true);
            if (!$this->has_html_answers()) {
                $qo->answer[$a_count] = $ans->answer['text'];
            } else {
                $qo->answer[$a_count] = $ans->answer;
            }
            $qo->answerformat[$a_count] = $ans->answer['format'];
            $qo->fraction[$a_count] = $ans->fraction;
            $qo->feedback[$a_count] = $ans->feedback;
            if (is_array($extraanswersfields)) {
                foreach ($extraanswersfields as $field) {
                    $qo->{$field}[$a_count] =
                        $format->getpath($answer, array('#', $field, 0, '#'), '');
                }
            }
            ++$a_count;
        }
        return $qo;
    }

    /** Overload hints functions to be able to work with interactivehints*/
    protected function make_hint($hint) {
        return qtype_poasquestion_moodlehint_adapter::load_from_record($hint);
    }


}
