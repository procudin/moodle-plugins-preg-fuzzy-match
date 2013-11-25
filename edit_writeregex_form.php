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

    protected function definition_inner($mform) {

        error_log("[definition_inner-{start}\n", 3, 'writeregex_log.txt');

        global $CFG;

        // RegEx notations.
        $notation_options = array(
            '0' => get_string('wre_notation_simple', 'qtype_writeregex'),
            '1' => get_string('wre_notation_extended', 'qtype_writeregex'),
            '2' => get_string('wre_notation_moodle', 'qtype_writeregex')
        );

        // Syntax tree options
        $syntax_tree_options = array(
            '0' => get_string('wre_st_none', 'qtype_writeregex'),
            '1' => get_string('wre_st_student', 'qtype_writeregex'),
            '2' => get_string('wre_st_answer', 'qtype_writeregex'),
            '3' => get_string('wre_st_both', 'qtype_writeregex')
        );
        $syntax_tree = array();

        // Explaining graph options
        $expl_graph_options = array(
            '0' => get_string('wre_eg_none', 'qtype_writeregex'),
            '1' => get_string('wre_eg_student', 'qtype_writeregex'),
            '2' => get_string('wre_eg_answer', 'qtype_writeregex'),
            '3' => get_string('wre_eg_both', 'qtype_writeregex')
        );
        $expl_graph = array();

        // Description options
        $description_options = array(
            '0' => get_string('wre_d_none', 'qtype_writeregex'),
            '1' => get_string('wre_d_student', 'qtype_writeregex'),
            '2' => get_string('wre_d_answer', 'qtype_writeregex'),
            '3' => get_string('wre_d_both', 'qtype_writeregex')
        );
        $description = array();

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

        $syntax_tree[] =& $mform->createElement('select', 'wre_st', get_string('wre_st', 'qtype_writeregex'),
            $syntax_tree_options);
        $syntax_tree[] =& $mform->createElement('text', 'wre_st_penalty',
            get_string('wre_st_penalty', 'qtype_writeregex'));
        $mform->setType('wre_st_penalty', PARAM_FLOAT);
        $mform->addGroup($syntax_tree, 'wre_st_group', '', array(' '), false);

        $expl_graph[] =& $mform->createElement('select', 'wre_eg', get_string('wre_eg', 'qtype_writeregex'),
            $expl_graph_options);
        $expl_graph[] =& $mform->createElement('text', 'wre_eg_penalty',
            get_string('wre_eg_penalty', 'qtype_writeregex'));
        $mform->setType('wre_eg_penalty', PARAM_FLOAT);
        $mform->addGroup($expl_graph, 'wre_eg_group', '', array(' '), false);

        $description[] =& $mform->createElement('select', 'wre_d', get_string('wre_d', 'qtype_writeregex'),
            $description_options);
        $description[] =& $mform->createElement('text', 'wre_d_penalty',
            get_string('wre_d_penalty', 'qtype_writeregex'));
        $mform->setType('wre_d_penalty', PARAM_FLOAT);
        $mform->addGroup($description, 'wre_d_group', '', array(' '), false);

        $test_string[] =& $mform->createElement('select', 'wre_td', get_string('wre_td', 'qtype_writeregex'),
            $test_string_options);
        $test_string[] =& $mform->createElement('text', 'wre_td_penalty',
            get_string('wre_td_penalty', 'qtype_writeregex'));
        $mform->setType('wre_td_penalty', PARAM_FLOAT);
        $mform->addGroup($test_string, 'wre_td_group', '', array(' '), false);

        $comp_regex[] =& $mform->createElement('select', 'wre_cre', get_string('wre_cre', 'qtype_writeregex'),
            $comp_regex_options);
        $comp_regex[] =& $mform->createElement('text', 'wre_cre_percentage',
            get_string('wre_cre_percentage', 'qtype_writeregex'));
        $mform->setType('wre_cre_percentage', PARAM_FLOAT);
        $mform->addGroup($comp_regex, 'wre_cre_group', '', array(' '), false);

        $comp_aregex[] =& $mform->createElement('selectyesno', 'wre_acre', get_string('wre_acre', 'qtype_writeregex'));
        $comp_aregex[] =& $mform->createElement('text', 'wre_acre_percentage',
            get_string('wre_acre_percentage', 'qtype_writeregex'));
        $mform->setType('wre_acre_percentage', PARAM_FLOAT);
        $mform->addGroup($comp_aregex, 'wre_acre_group', '', array(' '), false);


        // $mform->addElement('header', 'wre_regexp_answers', get_string('wre_regexp_answers', 'qtype_writeregex'));
       
        // $mform->closeHeaderBefore('nameforyourheaderelement');

        $this->add_per_answer_fields($mform, 'wre_regexp_answers',
               question_bank::fraction_options());

        $this->add_per_answer_fields($mform, 'wre_regexp_ts',
               question_bank::fraction_options());


        $this->add_interactive_settings();

        error_log("[definition_inner-{end}]\n", 3, 'writeregex_log.txt');
    }

    private function add_test_strings(&$mform, $label, $gradeoptions,
                                     $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        echo '<pre>';
        print_r($label);
        echo '</pre>';
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
//        print_r($repeated[0]->_elements[0]->_attributes['name']);
//        echo '</pre>';

        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), true);
    }

    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
                                             $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
//        global $CFG;
//        echo '<pre>';
//        print_r($CFG->libdir.'/formslib.php');
//        echo '</pre>';

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

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption) {
        $repeated = array();

        $repeated [] =& $mform->createElement('textarea', $label . '_answer',
            get_string($label, 'qtype_writeregex'), 'wrap="virtual" rows="2" cols="60"', $this->editoroptions);

        $repeated[] =& $mform->createElement('select', 'fraction', get_string('grade'), $gradeoptions);
        $repeated[] =& $mform->createElement('editor', 'feedback', get_string('feedback', 'question'),
            array('rows' => 5), $this->editoroptions);

        $repeatedoptions[$label . '_answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = $label;

        return $repeated;
    }

    protected function data_preprocessing($question) {

        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);

        if (isset($question->id)) {
            $local_qtype = new qtype_writeregex();
            $q = $local_qtype->get_question_options($question);

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
        }

        return $question;
//        error_log('[data_preprocessing]', 3, 'writeregex_log.txt');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $answers = $data['wre_regexp_answers_answer'];
        $answercount = 0;
        $maxgrade = false;
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if ($trimmedanswer !== '') {
                $answercount++;
                if ($data['fraction'][$key] == 1) {
                    $maxgrade = true;
                }
            } else if ($data['fraction'][$key] != 0 ||
                    !html_is_blank($data['feedback'][$key]['text'])) {
                $errors["answeroptions[$key]"] = get_string('answermustbegiven', 'qtype_shortanswer');
                $answercount++;
            }
        }
        if ($answercount==0) {
            $errors['answeroptions[0]'] = get_string('notenoughanswers', 'qtype_shortanswer', 1);
        }
        if ($maxgrade == false) {
            $errors['answeroptions[0]'] = get_string('fractionsnomax', 'question');
        }
        return $errors;
//        error_log('[validation]', 3, 'writeregex_log.txt');
    }

    public function qtype() {
        error_log('[qtype]', 3, 'writeregex_log.txt');
        return 'writeregex';
    }
}
