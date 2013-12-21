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
 * Опреление формы изменения вопроса типа Write Regex.
 *
 * @package    qtype
 * @subpackage writeregex
 * @copyright  2013 M. Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/shortanswer/edit_shortanswer_form.php');

/**
 * Опреление формы изменения вопроса типа Write Regex.
 *
 * @package    qtype
 * @subpackage writeregex
 * @copyright  2013 M. Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex_edit_form extends qtype_shortanswer_edit_form {

    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function definition_inner($mform) {

        parent::definition_inner($mform);
    }

    /**
     * Get the list of form elements to repeat, one for each answer.
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @param $gradeoptions the possible grades for each answer.
     * @param $repeatedoptions reference to array of repeated options to fill
     * @param $answersoption reference to return the name of $question->options
     *      field holding an array of answers
     * @return array of form fields.
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption) {
        return parent::get_per_answer_fields($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);
    }

    /**
     * Подготовка ответов.
     * @param object $question Вопрос.
     * @param bool $withanswerfiles Наличие файлов.
     * @return object Объект вопроса.
     */
    protected function data_preprocessing_answers($question, $withanswerfiles = false) {

        return parent::data_preprocessing_answers($question, $withanswerfiles);
    }

    /**
     * Проверка полей ввода.
     * @param array $data Данные с формы.
     * @param array $files Файлы пользователя.
     * @return array Массив ошибок.
     */
    public function validation($data, $files) {

        return parent::validation($data, $files);
    }

    /**
     * Метод добавления полей ответа.
     * @param object $mform Форма.
     * @param the $label Подпись.
     * @param the $gradeoptions Дополнительные опции.
     * @param int|the $minoptions Дополнительные опции.
     * @param int|the $addoptions Дополнительные опции.
     */
    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
                                             $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {

        parent::add_per_answer_fields($mform, $label, $gradeoptions, $minoptions, $addoptions);
    }

    protected function get_more_choices_string() {

        return get_string('addmorechoiceblanks', 'question');
    }

    protected function data_preprocessing($question) {

        return parent::data_preprocessing($question);
    }

    public function qtype() {

        return 'writeregex';
    }
}
