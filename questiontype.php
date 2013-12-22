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

    /**
     * Метод получения свойств вопроса.
     * @param object $question Вопрос.
     * @return bool
     */
    public function get_question_options($question){

        return parent::get_question_options($question);
    }

    /**
     * Метод сохранения свойств вопроса.
     * @param object $question Вопрос со свойствами с формы.
     * @return object|stdClass
     */
    public function save_question_options($question) {

        $result = parent::save_question_options($question);

        // save test strings
        $this->save_test_strings_answers($question);

        return $result;
    }

    /**
     * Метод сохранения ответов (тестовых строк).
     * @param $question Вопрос.
     */
    private function save_test_strings_answers ($question) {

        global $DB;
        $answers = $question->wre_regexp_ts_answer;
        $answersfractions = $question->wre_regexp_ts_fraction;
        $index = 0;

        $tableanswers = $DB->get_records('question_answers', array('question' => $question->id, 'answerformat' => 2));

        foreach ($answers as $item) {

            if (!empty($item)) {

                $answer = $this->get_test_string_answer_object($item, $answersfractions[$index], $question->id);

                $index++;

                if (!isset($tableanswers[$index])) {
                    $DB->insert_record('question_answers', $answer);
                } else {
                    $DB->update_record('question_answers', $answer);
                }
            }
        }

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
        $result->answerformat = 2;
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
            'notation', 'syntaxtreehinttype', 'syntaxtreehintpenalty', 'explgraphhinttype', 'explgraphhintpenalty',
            'descriptionhinttype', 'descriptionhintpenalty', 'teststringshinttype', 'teststringshintpenalty',
            'compareregexpercentage', 'compareautomatercentage'
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
        return parent::import_from_xml($data, $question, $format, $extra);
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
