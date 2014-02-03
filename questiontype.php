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
 * Тип вопроса Write Regex - написание регулярного выражения
 *
 * @package    qtype
 * @subpackage writeregex
 * @copyright  2013 M. Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/shortanswer/questiontype.php');


/**
 * Тип вопроса Write Regex - написание регулярного выражения
 *
 * @package    qtype
 * @subpackage writeregex
 * @copyright  2013 M. Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex extends qtype_shortanswer {

    public function test_string_answer_format_value() {
        return 1;
    }

    /**
     * Метод получения свойств вопроса.
     * @param object $question Вопрос.
     * @return bool
     */
    public function get_question_options($question){

        $result = parent::get_question_options($question);

        $question->syntaxtreehint = array();
        $question->explgraphhint = array();
        $question->descriptionhint = array();
        $question->teststringshint = array();

        foreach ($question->hints as $key => $hint) {
            // get syntaxtreehint
            if (preg_match("/^\\nsyntaxtreehint#[0-3]\\n/", $question->hints[$key]->options, $value) == 1) {

                $syntaxtreehint = preg_replace('/^\\nsyntaxtreehint#/', '', $value);
                $syntaxtreehint = preg_replace('/\\n$/', '', $syntaxtreehint);

                $question->syntaxtreehint[] = $syntaxtreehint[0];
            }

            // get explgraphhint
            if (preg_match("/\\nexplgraphhint#[0-3]\\n/", $question->hints[$key]->options, $value) == 1) {

                $explgraphhint = preg_replace('/^\\nexplgraphhint#/', '', $value);
                $explgraphhint = preg_replace('/\\n$/', '', $explgraphhint);

                $question->explgraphhint[] = $explgraphhint[0];
            }

            // get descriptionhint
            if (preg_match("/\\ndescriptionhint#[0-3]\\n/", $question->hints[$key]->options, $value) == 1) {

                $descriptionhint = preg_replace('/^\\ndescriptionhint#/', '', $value);
                $descriptionhint = preg_replace('/\\n$/', '', $descriptionhint);

                $question->descriptionhint[] = $descriptionhint[0];
            }

            // get teststringshint
            if (preg_match("/\\nteststringshint#[0-3]\\n/", $question->hints[$key]->options, $value) == 1) {

                $teststringshint = preg_replace('/^\\nteststringshint#/', '', $value);
                $teststringshint = preg_replace('/\\n$/', '', $teststringshint);

                $question->teststringshint[] = $teststringshint[0];
            }
        }

        return $result;
    }

    /**
     * Метод сохранения свойств вопроса.
     * @param object $question Вопрос со свойствами с формы.
     * @return object|stdClass
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
                if ($question->answerformat[$index] == $this->test_string_answer_format_value()) {
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
     * Функция сохранения подсказок в бд.
     * @param $formdata
     * @param bool $withparts
     */
    public function save_hints($formdata, $withparts = false) {

        parent::save_hints($formdata, $withparts);

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
     * Метод формирования stdClass ответа (тестовые строки).
     * @param $answer Тестовая строка.
     * @param $fraction Оценка.
     * @param $questionid Идентифкатор ответа.
     * @return stdClass Ответ.
     */
    private function get_test_string_answer_object ($answer, $fraction, $questionid) {

        $result = new stdClass();

        $result->answer = $answer;
        $result->question = $questionid;
        $result->answerformat = $this->test_string_answer_format_value();
        $result->fraction = $fraction;
        $result->feedback = '';
        $result->feedbackformat = 0;

        return $result;
    }

    /**
     * Метод для выполнения сохранения дополнительных свойств вопроса методами суперкласса вопроса.
     * @return array Массив, состоящий из имени таблицы опций типа вопроса WriteRegEx,
     * и ее столбцов для сохранения данных.
     */
    public function extra_question_fields() {

        return array('qtype_writeregex_options',
            'usecase', 'engine', 'notation', 'syntaxtreehinttype', 'syntaxtreehintpenalty', 'explgraphhinttype',
            'explgraphhintpenalty', 'descriptionhinttype', 'descriptionhintpenalty', 'teststringshinttype',
            'teststringshintpenalty', 'compareregexpercentage', 'compareautomatapercentage', 'compareregexpteststrings'
        );
    }

    /**
     * Метод инициализации экземпляра вопроса.
     * @param question_definition $question Описание вопроса.
     * @param object $questiondata Данные вопроса.
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {

        parent::initialise_question_instance($question, $questiondata);
    }

    /**
     * Метод удаления вопроса.
     * @param $questionid Идентификатор вопроса.
     * @param int $contextid Идентифактор контекста.
     */
    public function delete_question($questionid, $contextid){

        parent::delete_question($questionid, $contextid);
    }

    /**
     * Метод импорта вопроса из xml
     * @param $data Данные.
     * @param $question Вопрос.
     * @param qformat_xml $format xml формат.
     * @param null $extra Дополнительные параметры.
     * @return bool|object РЕзультат.
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

    /**
     * Метод импорта вопроса в xml.
     * @param $question Вопрос.
     * @param qformat_xml $format Формат xml.
     * @param null $extra Дополнительные параметры.
     * @return bool|string Результат.
     */
    public function export_to_xml($question, qformat_xml $format, $extra=null){
        return parent::export_to_xml($question, $format, $extra);
    }

}
