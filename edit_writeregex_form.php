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
 * Defines the editing form for the shortanswer question type.
 *
 * @package    qtype
 * @subpackage shortanswer
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Short answer question editing form definition.
 *
 * @copyright  2007 Jamie Pratt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex_edit_form extends question_edit_form {

    protected $jsmodule = array(
        'name' => 'writeregex_module',
        'fullpath' => '/question/type/writeregex/writeregex_module.js'
    );

    /**
     * Метод получения массива значений нотации RegExp.
     * @return array Массив возможных нотаций.
     */
    protected function  get_notation_options_array() {

        $notation_options = array(
            '0' => get_string('wre_notation_simple', 'qtype_writeregex'),
            '1' => get_string('wre_notation_extended', 'qtype_writeregex'),
            '2' => get_string('wre_notation_moodle', 'qtype_writeregex')
        );

        return $notation_options;
    }

    /**
     * Метод получения массива вариантов подсказки в виде синтаксического дерева.
     * @return array Массив возможных вариантов подсказок в виде синтаксического дерева.
     */
    protected function  get_syntax_tree_options_array() {

        $syntax_tree_options = array(
            '0' => get_string('wre_st_none', 'qtype_writeregex'),
            '1' => get_string('wre_st_student', 'qtype_writeregex'),
            '2' => get_string('wre_st_answer', 'qtype_writeregex'),
            '3' => get_string('wre_st_both', 'qtype_writeregex')
        );

        return $syntax_tree_options;
    }

    /**
     * Метод получения массива вариантов подсказки в виде объясняющего графа.
     * @return array Массив возможных вариантов подсказок в виде объясняющего графа.
     */
    protected function get_explanation_graph_options_array() {

        $expl_graph_options = array(
            '0' => get_string('wre_eg_none', 'qtype_writeregex'),
            '1' => get_string('wre_eg_student', 'qtype_writeregex'),
            '2' => get_string('wre_eg_answer', 'qtype_writeregex'),
            '3' => get_string('wre_eg_both', 'qtype_writeregex')
        );

        return $expl_graph_options;
    }

    /**
     * Метод получения массива вариантов подсказки в виде объяснения.
     * @return array Массив возможных вариантов подсказок в виде объяснения.
     */
    protected function get_description_options_array() {

        $description_options = array(
            '0' => get_string('wre_d_none', 'qtype_writeregex'),
            '1' => get_string('wre_d_student', 'qtype_writeregex'),
            '2' => get_string('wre_d_answer', 'qtype_writeregex'),
            '3' => get_string('wre_d_both', 'qtype_writeregex')
        );

        return $description_options;
    }

    protected function definition_inner($mform) {

        // RegEx notations.
        $notation_options = $this->get_notation_options_array();

        // Syntax tree options
        $syntax_tree_options = $this->get_syntax_tree_options_array();

        // Explaining graph options
        $expl_graph_options = $this->get_explanation_graph_options_array();

        // Description options
        $description_options =$this->get_description_options_array();

        // Test string options
        $test_string_options = array(
            '0' => get_string('wre_td_none', 'qtype_writeregex'),
            '1' => get_string('wre_td_student', 'qtype_writeregex'),
            '2' => get_string('wre_td_answer', 'qtype_writeregex'),
            '3' => get_string('wre_td_both', 'qtype_writeregex')
        );
        $test_string = array();

        // Compare regex options
        $comp_regex_options = array(
            '0' => get_string('wre_cre_yes', 'qtype_writeregex'),
            '1' => get_string('wre_cre_no', 'qtype_writeregex')
        );
        $comp_regex = array();

        // Compare regexp's automat
        $comp_aregex = array();

        // $mform = $this->_form;

        $mform->addElement('hidden', 'wre_id', 'qwe');
        $mform->setType('wre_id', PARAM_RAW);

        $mform->addElement('select', 'wre_notation',get_string('wre_notation', 'qtype_writeregex'), $notation_options);

        $mform->addElement('select', 'wre_st', get_string('wre_st', 'qtype_writeregex'),
            $syntax_tree_options);
        $mform->addElement('text', 'wre_st_penalty',
            get_string('wre_st_penalty', 'qtype_writeregex'));
        $mform->setType('wre_st_penalty', PARAM_FLOAT);
//        $mform->addGroup($syntax_tree, 'wre_st_group', '', array(' '), false);

        $mform->addElement('select', 'wre_eg', get_string('wre_eg', 'qtype_writeregex'),
            $expl_graph_options);
        $mform->addElement('text', 'wre_eg_penalty',
            get_string('wre_eg_penalty', 'qtype_writeregex'));
        $mform->setType('wre_eg_penalty', PARAM_FLOAT);
//        $mform->addGroup($expl_graph, 'wre_eg_group', '', array(' '), false);

        $mform->addElement('select', 'wre_d', get_string('wre_d', 'qtype_writeregex'),
            $description_options);
        $mform->addElement('text', 'wre_d_penalty',
            get_string('wre_d_penalty', 'qtype_writeregex'));
        $mform->setType('wre_d_penalty', PARAM_FLOAT);
//        $mform->addGroup($description, 'wre_d_group', '', array(' '), false);

        $mform->addElement('select', 'wre_td', get_string('wre_td', 'qtype_writeregex'),
            $test_string_options);
        $mform->addElement('text', 'wre_td_penalty',
            get_string('wre_td_penalty', 'qtype_writeregex'));
        $mform->setType('wre_td_penalty', PARAM_FLOAT);
//        $mform->addGroup($test_string, 'wre_td_group', '', array(' '), false);

        $mform->addElement('select', 'wre_cre', get_string('wre_cre', 'qtype_writeregex'),
            $comp_regex_options);
        $mform->addElement('text', 'wre_cre_percentage',
            get_string('wre_cre_percentage', 'qtype_writeregex'));
        $mform->setType('wre_cre_percentage', PARAM_FLOAT);
//        $mform->addGroup($comp_regex, 'wre_cre_group', '', array(' '), false);

        $mform->addElement('selectyesno', 'wre_acre', get_string('wre_acre', 'qtype_writeregex'));
        $mform->addElement('text', 'wre_acre_percentage',
            get_string('wre_acre_percentage', 'qtype_writeregex'));
        $mform->setType('wre_acre_percentage', PARAM_FLOAT);
//        $mform->addGroup($comp_aregex, 'wre_acre_group', '', array(' '), false);

        // $mform->addElement('header', 'wre_regexp_answers', get_string('wre_regexp_answers', 'qtype_writeregex'));
       
        // $mform->closeHeaderBefore('nameforyourheaderelement');

        $this->set_default_values($mform);
        $this->add_rules($mform);

        $this->add_per_answer_fields($mform, 'wre_regexp_answers',
               question_bank::fraction_options());

        $this->add_per_answer_fields($mform, 'wre_regexp_ts',
               question_bank::fraction_options());


        $this->add_interactive_settings();

//        global $PAGE;
//        $PAGE->requires->js_init_call('M.writeregex_module.init', null, true, $this->jsmodule);
    }

    /**
     * Метод установки правил для формы.
     * @param $mform Форма ввода вопроса.
     */
    private function add_rules ($mform) {

        $mform->disabledIf('wre_cre_percentage', 'wre_cre', 'eq', 0);
        $mform->disabledIf('wre_acre_percentage', 'wre_acre', 'eq', 0);
    }

	/**
	 * Метод установки значений по умолчанию для формы.
	 * @param $mform Форма ввода вопроса.
	 */
    private function set_default_values ($mform) {

        $mform->setDefault('wre_st_penalty', '0.0000000');
        $mform->setDefault('wre_eg_penalty', '0.0000000');
        $mform->setDefault('wre_d_penalty', '0.0000000');
        $mform->setDefault('wre_td_penalty', '0.0000000');

        $mform->setDefault('wre_cre', 1);
        $mform->setDefault('wre_cre_percentage', 100);

        $mform->setDefault('wre_acre_percentage', 0);
    }

    private function add_test_strings(&$mform, $label, $gradeoptions,
                                     $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $mform->addElement('header', 'teststrhdr', get_string($label, 'qtype_writeregex'), '');
        $mform->setExpanded('teststrhdr', 1);

        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);

        if (isset($this->question->options)) {
            $repeatsatstart = count($this->question->options->$answersoption);
        } else {
            $repeatsatstart = $minoptions;
        }

        $repeatsatstart = 5;
        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), true);
    }

    private function add_regexp_strings(&$mform, $label, $gradeoptions,
                                        $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $mform->addElement('header', 'answerhdr', get_string($label, 'qtype_writeregex'), '');
        $mform->setExpanded('answerhdr', 1);

        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);

        if (isset($this->question->options)) {
            $repeatsatstart = count($this->question->options->$answersoption);
        } else {
            $repeatsatstart = $minoptions;
        }

//        echo '<pre>';
//        print_r('Count ' . print_r($repeatsatstart, true));
//        echo '</pre>';
        $addoptions = 1;
        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), true);
    }

    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
                                             $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        // Some code here...
        if ($label == 'wre_regexp_ts') {
            $this->add_test_strings($mform, $label, $gradeoptions, $minoptions, $addoptions);
        } else {
            $this->add_regexp_strings($mform, $label, $gradeoptions, $minoptions, $addoptions);
        }
    }

    protected function get_more_choices_string() {
        return get_string('addmorechoiceblanks', 'question');
    }

    private function  get_per_answer_fields_regexp($mform, $label, $gradeoptions,
                                                   &$repeatedoptions, &$answersoption) {
        $repeated = array();

        $repeated [] =& $mform->createElement('hidden', 'regexp_id', 'qwe');

        $repeated [] =& $mform->createElement('textarea', $label . '_answer',
            get_string($label, 'qtype_writeregex'), 'wrap="virtual" rows="2" cols="60"', $this->editoroptions);

        $repeated[] =& $mform->createElement('select', $label . '_fraction', get_string('grade'), $gradeoptions);
        $repeated[] =& $mform->createElement('editor', $label . '_feedback', get_string('feedback', 'question'),
            array('rows' => 5), $this->editoroptions);

        $repeatedoptions[$label . '_answer']['type'] = PARAM_RAW;
        $repeatedoptions['regexp_id']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = $label;

        return $repeated;
    }

    private function  get_per_answer_fields_strings($mform, $label, $gradeoptions,
                                                   &$repeatedoptions, &$answersoption) {
        $repeated = array();

        $repeated [] =& $mform->createElement('hidden', 'test_string_id', 'qwe');
        $repeated [] =& $mform->createElement('textarea', $label . '_answer',
            get_string($label, 'qtype_writeregex'), 'wrap="virtual" rows="2" cols="60"', $this->editoroptions);

        $repeated[] =& $mform->createElement('select', $label . '_fraction', get_string('grade'), $gradeoptions);

        $repeatedoptions[$label . '_answer']['type'] = PARAM_RAW;
        $repeatedoptions['test_string_id']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = $label;

        return $repeated;
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption) {
        $repeated = array();

        if ($label != 'wre_regexp_ts') {
            $repeated = $this->get_per_answer_fields_regexp($mform, $label,
                $gradeoptions, $repeatedoptions, $answersoption);
        } else {
            $repeated = $this->get_per_answer_fields_strings($mform, $label,
                $gradeoptions, $repeatedoptions, $answersoption);
        }

        return $repeated;
    }

    protected function data_preprocessing($question) {

        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        if (isset($question->id)) {
            $local_qtype = new qtype_writeregex();
            $q = $local_qtype->get_question_options($question);
            $answer_regexp = array();
            $answer_string = array();

            $question->wre_notation        = $q->options->notation;
            $question->wre_st              = $q->options->syntaxtreehinttype;
            $question->wre_st_penalty      = $q->options->syntaxtreehintpenalty;
            $question->wre_eg              = $q->options->explgraphhinttype;
            $question->wre_eg_penalty      = $q->options->explgraphhintpenalty;
            $question->wre_d               = $q->options->descriptionhinttype;
            $question->wre_d_penalty       = $q->options->descriptionhintpenalty;
            $question->wre_td              = $q->options->teststringshinttype;
            $question->wre_td_penalty      = $q->options->teststringshintpenalty;
            $question->wre_cre_percentage  = $q->options->compareregexpercentage;
            $question->wre_acre_percentage = $q->options->compareautomatercentage;
            $question->wre_id              = $q->options->id;

            foreach($q->answers as $answer) {
                if ($answer->answerformat == 1) {
                    $answer_regexp[] = $answer;
                } else if ($answer->answerformat == 2) {
                    $answer_string[] = $answer;
                }
            }

            $question->wre_regexp_answers_answer = $this->forming_regexp_answers_value($answer_regexp);
            $question->wre_regexp_answers_fraction = $this->forming_regexp_answers_fraction($answer_regexp);
            $question->wre_regexp_answers_feedback = $this->forming_regexp_answers_feedback($answer_regexp);
            $question->regexp_id = $this->forming_regexp_answers_id($answer_regexp);

            $question->wre_regexp_ts_answer = $this->forming_regexp_answers_value($answer_string);
            $question->wre_regexp_ts_fraction = $this->forming_regexp_answers_fraction($answer_string);
            $question->test_string_id = $this->forming_regexp_answers_id($answer_string);
        }

//        echo '<pre>';
//        print_r($question);
//        echo '</pre>';

        return $question;
    }

    protected function forming_regexp_answers_id ($answers) {
        $result = array();

        foreach ($answers as $item) {

            $result[] = $item->id;
        }

        return $result;
    }

    protected function  forming_regexp_answers_fraction ($answers) {
        $result = array();

        foreach ($answers as $item) {
            $result[] = $item->fraction;
        }

        return $result;
    }

    protected function forming_regexp_answers_feedback ($answer) {
        $result = array();

        foreach ($answer as $item) {
            $feedback = array();
            $feedback['text'] = $item->feedback;
            $feedback['format'] = $item->feedbackformat;
            $result[] = $feedback;
        }

        return $result;
    }

    protected function forming_regexp_answers_value ($answers) {
        $result = array();

        foreach ($answers as $item) {
            $result[] = $item->answer;
        }

        return $result;
    }

    protected function data_preprocessing_answers($question, $withanswerfiles = false) {

        $question = parent::data_preprocessing_answers($question);

        if (isset($question->id)) {
//            $answer = ;
        }

        return $question;
    }

    /**
     * Метод проверки того, что сумма типов проверки соответствует 100.
     * @param $data Данные с формы.
     * @return bool false, если ошибка есть
     */
    public function is_validate_match_type ($data) {
        $result = true;

        $val1 = $data['wre_cre_percentage'];
        $val2 = $data['wre_acre_percentage'];

        if ($val1 + $val2 != 100) {
            $result = false;
        }

        return $result;
    }

    /**
     * Метод выбора поля ввода типа сравнения для отображения ошибки над ним.
     * @param $data Данные с формы.
     * @param $errors Массив ошибок.
     */
    protected function select_error_input($data, &$errors) {

        $val1 = $data['wre_cre_percentage'];
        $val2 = $data['wre_acre_percentage'];

        if ($val2 < $val1) {
            $errors['wre_acre_percentage'] = get_string('wre_error_matching', 'qtype_writeregex');
        } else {
            $errors['wre_cre_percentage'] = get_string('wre_error_matching', 'qtype_writeregex');
        }
    }

    /**
     * Метод проверки regexp ответов.
     * @param $data Данные с формы.
     * @param $errors Список ошибок.
     */
    public function validate_regexp_answers($data, &$errors) {

        $answers_value = $data['wre_regexp_answers_answer'];
        $fractions_value = $data['wre_regexp_answers_fraction'];

        $count = 0;
        $fraction = 0;
        foreach ($answers_value as $item) {
            if ($item != '') {
                if ($fractions_value[$count] == 1.0) {
                    $fraction++;
                }
                $count++;
            }
        }

        if ($count < 1) {
            $errors['wre_regexp_answers_answer[0]'] = get_string('wre_regexp_answers_count', 'qtype_writeregex');
        }

        if ($fraction < 1) {
            $errors['wre_regexp_answers_fraction[0]'] = get_string('wre_regexp_fractions_count', 'qtype_writeregex');
        }
    }

    /**
     * Метод проверки ответов тестовых строк.
     * @param $data Данные с формы.
     * @param $errors Список ошибок.
     */
    public function validate_test_string_answers ($data, &$errors) {

        $answers_value = $data['wre_regexp_ts_answer'];
        $fractions_value = $data['wre_regexp_ts_fraction'];

        $count = 0;
        $fraction = 0;
        foreach ($answers_value as $item) {
            if ($item != '') {
                if ($fractions_value[$count] == 1.0) {
                    $fraction++;
                }
                $count++;
            }
        }

        if ($count < 1) {
            $errors['wre_regexp_ts_answer[0]'] = get_string('wre_ts_answers_count', 'qtype_writeregex');
        }

        if ($fraction < 1) {
            $errors['wre_regexp_ts_fraction[0]'] = get_string('wre_ts_fractions_count', 'qtype_writeregex');
        }
    }

    public function validation($data, $files) {

        $errors = parent::validation($data, $files);

        if (!$this->is_validate_match_type($data)) {
            $this->select_error_input($data, $errors);
        }

        $this->validate_regexp_answers($data, $errors);

        $this->validate_test_string_answers($data, $errors);

        return $errors;
    }

    public function qtype() {

        return 'writeregex';
    }
}
