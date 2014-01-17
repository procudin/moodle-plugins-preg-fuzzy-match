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
require_once($CFG->dirroot . '/question/type/preg/questiontype.php');
require_once($CFG->dirroot . '/question/type/writeregex/questiontype.php');

/**
 * Опреление формы изменения вопроса типа Write Regex.
 *
 * @package    qtype
 * @subpackage writeregex
 * @copyright  2013 M. Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex_edit_form extends qtype_shortanswer_edit_form {

    /* Поля класса. */
    private $hintsoptions = array();

    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function definition_inner($mform) {

        global $CFG;

        $menu = array(
            get_string('caseno', 'qtype_shortanswer'),
            get_string('caseyes', 'qtype_shortanswer')
        );
        $mform->addElement('select', 'usecase',
            get_string('casesensitive', 'qtype_shortanswer'), $menu);

        // init hints options
        $this->hintsoptions = array(
            '0' => get_string('none', 'qtype_writeregex'),
            '1' => get_string('student', 'qtype_writeregex'),
            '2' => get_string('answer', 'qtype_writeregex'),
            '3' => get_string('both', 'qtype_writeregex')
        );

        // include preg
        $pregclass = 'qtype_preg';
        $preg = new $pregclass;

        // add engines
        $engines = $preg->available_engines();
        $mform->addElement('select', 'engine', get_string('engine', 'qtype_preg'), $engines);
        $mform->setDefault('engine', $CFG->qtype_preg_defaultengine);
        $mform->addHelpButton('engine', 'engine', 'qtype_preg');

        // add notations
        $notations = $preg->available_notations();
        $mform->addElement('select', 'notation', get_string('notation', 'qtype_preg'), $notations);
        $mform->setDefault('notation', $CFG->qtype_preg_defaultnotation);
        $mform->addHelpButton('notation', 'notation', 'qtype_preg');

        // add syntax tree options
        $mform->addElement('select', 'syntaxtreehinttype', get_string('wre_st', 'qtype_writeregex'),
            $this->hintsoptions);
        $mform->addElement('text', 'syntaxtreehintpenalty',
            get_string('penalty', 'qtype_writeregex'));
        $mform->setType('syntaxtreehintpenalty', PARAM_FLOAT);
        $mform->setDefault('syntaxtreehintpenalty', '0.0000000');

        // add explaining graph options
        $mform->addElement('select', 'explgraphhinttype', get_string('wre_eg', 'qtype_writeregex'),
            $this->hintsoptions);
        $mform->addElement('text', 'explgraphhintpenalty',
            get_string('penalty', 'qtype_writeregex'));
        $mform->setType('explgraphhintpenalty', PARAM_FLOAT);
        $mform->setDefault('explgraphhintpenalty', '0.0000000');

        // add description options
        $mform->addElement('select', 'descriptionhinttype', get_string('wre_d', 'qtype_writeregex'),
            $this->hintsoptions);
        $mform->addElement('text', 'descriptionhintpenalty',
            get_string('penalty', 'qtype_writeregex'));
        $mform->setType('descriptionhintpenalty', PARAM_FLOAT);
        $mform->setDefault('descriptionhintpenalty', '0.0000000');

        // add test string option
        $mform->addElement('select', 'teststringshinttype', get_string('teststrings', 'qtype_writeregex'),
            $this->hintsoptions);
        $mform->addElement('text', 'teststringshintpenalty',
            get_string('penalty', 'qtype_writeregex'));
        $mform->setType('teststringshintpenalty', PARAM_FLOAT);
        $mform->setDefault('teststringshintpenalty', '0.0000000');

        // add compare regex percentage
        $mform->addElement('text', 'compareregexpercentage',
            get_string('wre_cre_percentage', 'qtype_writeregex'));
        $mform->setType('compareregexpercentage', PARAM_FLOAT);
        $mform->setDefault('compareregexpercentage', '34');

        // add compare regexps automata percentage
        $mform->addElement('text', 'compareautomatapercentage',
            get_string('compareautomatapercentage', 'qtype_writeregex'));
        $mform->setType('compareautomatapercentage', PARAM_FLOAT);
        $mform->setDefault('compareautomatapercentage', '33');

        // add compare regexp by test strings
        $mform->addElement('text', 'compareregexpteststrings',
            get_string('compareregexpteststrings', 'qtype_writeregex'));
        $mform->setType('compareregexpteststrings', PARAM_FLOAT);
        $mform->setDefault('compareregexpteststrings', '33');

        // add asnwers fields.
        $this->add_per_answer_fields($mform, 'wre_regexp_answers',
            question_bank::fraction_options());

        $this->add_per_answer_fields($mform, 'wre_regexp_ts',
            question_bank::fraction_options());


        $this->add_interactive_settings();
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
        return parent::get_per_answer_fields($mform, $label, $gradeoptions, $repeatedoptions, $answersoption);
    }

    /**
     * Подготовка ответов.
     * @param object $question Вопрос.
     * @param bool $withanswerfiles Наличие файлов.
     * @return object Объект вопроса.
     */
    protected function data_preprocessing_answers($question, $withanswerfiles = false) {

        if (empty($question->options->answers)) {
            return $question;
        }

        $questiontype = 'qtype_writeregex';
        $questiontypeclass = new $questiontype;

        $key = 0;
        $index = 0;
        foreach ($question->options->answers as $answer) {

            if ($answer->answerformat != $questiontypeclass->test_string_answer_format_value()) {
                $question->answer[$key] = $answer->answer;
                unset($this->_form->_defaultValues["fraction[$key]"]);
                $question->fraction[$key] = $answer->fraction;
                $question->feedback[$key] = array();
                $question->feedback[$key]['text'] = $answer->feedback;
                $question->feedback[$key]['format'] = $answer->feedbackformat;
                $key++;
            } else if ($answer->answerformat == $questiontypeclass->test_string_answer_format_value()) {

                $question->wre_regexp_ts_answer[$index] = $answer->answer;
                $question->wre_regexp_ts_fraction[$index] = $answer->fraction;
                $index++;
            }

        }

        return $question;

    }

    /**
     * Проверка полей ввода.
     * @param array $data Данные с формы.
     * @param array $files Файлы пользователя.
     * @return array Массив ошибок.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $strings = $data['wre_regexp_ts_answer'];
        $answercount = 0;
        $maxgrade = false;

        foreach ($strings as $key => $item) {
            $trimmeditem = trim($item);
            if ($trimmeditem != '') {
                $answercount++;
                if ($data['wre_regexp_ts_fraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['wre_regexp_ts_fraction'][$key] != 0) {
                $errors["wre_regexp_ts_answer[$key]"] = get_string('answermustbegiven', 'qtype_shortanswer');
                $answercount++;
            }
        }

        if ($answercount==0) {
            $errors['wre_regexp_ts_answer[0]'] = get_string('notenoughanswers', 'qtype_shortanswer', 1);
        }
        if ($maxgrade == false) {
            $errors['wre_regexp_ts_answer[0]'] = get_string('fractionsnomax', 'question');
        }

        $this->is_valid_compare_values($data, $errors);

        return $errors;
    }

    /**
     * Validation values of compare values.
     * @param $data Data from form.
     * @param $errors Errors array.
     */
    private function is_valid_compare_values ($data, &$errors) {

        $regex = $data['compareregexpercentage'];
        $automata = $data['compareautomatapercentage'];
        $test = $data['compareregexpteststrings'];
        $flag = false;

        if ($regex < 0 || $regex > 100) {
            $errors['compareregexpercentage'] = get_string('compareinvalidvalue', 'qtype_writeregex');
            $flag = true;
        }

        if ($automata < 0 || $automata > 100) {
            $errors['compareautomatapercentage'] = get_string('compareinvalidvalue', 'qtype_writeregex');
        }

        if ($test < 0 || $test > 100) {
            $errors['compareregexpteststrings'] = get_string('compareinvalidvalue', 'qtype_writeregex');
        }

        if ($regex + $automata + $test != 100 and !$flag) {
            $errors['compareregexpercentage'] = get_string('wre_error_matching', 'qtype_writeregex');
        }
    }

    /**
     * Метод вставки полей - regexp.
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param $repeatedoptions
     * @param $answersoption
     * @return array
     */
    private function  get_per_answer_fields_regexp($mform, $label, $gradeoptions,
                                                   &$repeatedoptions, &$answersoption) {

        $repeated = array();
        $answeroptions = array();
        $answeroptions[] = $mform->CreateElement('hidden', 'freply', 'yes'); // fake element
        $repeated[] = $mform->createElement('group', 'answeroptions',
            get_string($label, 'qtype_writeregex'), $answeroptions, null, false);
        $repeated[] = $mform->createElement('textarea', 'answer',
            '', 'wrap="virtual" rows="4" cols="80"');
        $repeated[] = $mform->createElement('select', 'fraction',
            get_string('grade'), $gradeoptions);
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 8, 'cols' => 80), $this->editoroptions);
        $repeated[] = $mform->addElement('html', '<hr />');
        $repeatedoptions['freply']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;

    }

    /**
     * Метод вставки полей - тестовых строк.
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param $repeatedoptions
     * @param $answersoption
     * @return array
     */
    private function  get_per_answer_fields_strings($mform, $label, $gradeoptions,
                                                    &$repeatedoptions, &$answersoption) {
        $repeated = array();

        $repeated [] =& $mform->createElement('textarea', $label . '_answer',
            get_string($label, 'qtype_writeregex'), 'wrap="virtual" rows="2" cols="80"', $this->editoroptions);

        $repeated[] =& $mform->createElement('select', $label . '_fraction', get_string('grade'), $gradeoptions);

        $repeated[] = $mform->addElement('html', '<hr />');

        $repeatedoptions[$label . '_answer']['type'] = PARAM_RAW;
        $repeatedoptions['test_string_id']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = $label;

        return $repeated;
    }

    /**
     * Метод добавления ответов - тестовых строк
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param int $minoptions
     * @param int $addoptions
     */
    private function add_test_strings(&$mform, $label, $gradeoptions,
                                      $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $mform->addElement('header', 'teststrhdr', get_string($label, 'qtype_writeregex'), '');
        $mform->setExpanded('teststrhdr', 1);

        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields_strings($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);

        $repeatsatstart = 5;
        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), true);
    }

    /**
     * Метод добавления ответов regexp
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param int $minoptions
     * @param int $addoptions
     */
    private function add_regexp_strings(&$mform, $label, $gradeoptions,
                                        $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $mform->addElement('header', 'answerhdr', get_string($label, 'qtype_writeregex'), '');
        $mform->setExpanded('answerhdr', 1);

        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields_regexp($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);

        if (isset($this->question->options)) {
            $repeatsatstart = count($this->question->options->$answersoption);
        } else {
            $repeatsatstart = $minoptions;
        }

        $addoptions = 1;
        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), true);
    }

    /**
     * Функция добавления разделов с ответами.
     * @param object $mform Форма.
     * @param the $label Подпись раздела.
     * @param the $gradeoptions
     * @param int|the $minoptions
     * @param int|the $addoptions
     */
    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
                                             $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        // Select type of answers fields
        if ($label == 'wre_regexp_ts') {
            $this->add_test_strings($mform, $label, $gradeoptions, $minoptions, $addoptions);
        } else {
            $this->add_regexp_strings($mform, $label, $gradeoptions, $minoptions, $addoptions);
        }
    }

    protected function get_more_choices_string() {
        return get_string('addmorechoiceblanks', 'question');
    }

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false) {
        $repeated = array();

        $parentresult = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);

        // add our inputs
        $mform = $this->_form;
        $count = count($parentresult[0]);

        // add syntax tree options
        $repeated[$count++] = $mform->createElement('select', 'syntaxtreehint', get_string('wre_st', 'qtype_writeregex'),
            $this->hintsoptions);

        // add explaining graph options
        $repeated[$count++] = $mform->createElement('select', 'explgraphhint', get_string('wre_eg', 'qtype_writeregex'),
            $this->hintsoptions);


        // add description options
        $repeated[$count++] = $mform->createElement('select', 'descriptionhint', get_string('wre_d', 'qtype_writeregex'),
            $this->hintsoptions);


        // add test string option
        $repeated[$count] = $mform->createElement('select', 'teststringshint', get_string('teststrings', 'qtype_writeregex'),
            $this->hintsoptions);

        $parentresult[0] = array_merge($parentresult[0], $repeated);

        return $parentresult;
    }

    /**
     * Метод подготовки данных для отображения.
     * @param object $question Вопрос.
     * @return object Вопрос с данными.
     */
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        return $question;
    }

    public function qtype() {

        return 'writeregex';
    }
}
