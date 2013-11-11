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
//        $menu = array(
//            get_string('caseno', 'qtype_shortanswer'),
//            get_string('caseyes', 'qtype_shortanswer')
//        );
//        $mform->addElement('select', 'usecase',
//                get_string('casesensitive', 'qtype_shortanswer'), $menu);
//
//        $mform->addElement('static', 'answersinstruct',
//                get_string('correctanswers', 'qtype_shortanswer'),
//                get_string('filloutoneanswer', 'qtype_shortanswer'));
//        $mform->closeHeaderBefore('answersinstruct');
//
//        $this->add_per_answer_fields($mform, get_string('answerno', 'qtype_shortanswer', '{no}'),
//                question_bank::fraction_options());
//
//        $this->add_interactive_settings();
        error_log('[definition_inner]', 3, 'writeregex_log.txt');

        global $CFG;

        // RegEx notations.
        $notation_options = array(
            'val_0' => get_string('wre_notation_simple', 'qtype_writeregex'),
            'val_1' => get_string('wre_notation_extended', 'qtype_writeregex'),
            'val_2' => get_string('wre_notation_moodle', 'qtype_writeregex')
        );

        $mform = $this->_form;

        $mform->addElement('select', 'wre_notation',get_string('wre_notation', 'qtype_writeregex'), $notation_options);
        $mform->setType('email', PARAM_NOTAGS);
    }

    protected function get_more_choices_string() {
//        return get_string('addmoreanswerblanks', 'qtype_shortanswer');
        error_log('[get_more_choices_string]', 3, 'writeregex_log.txt');
    }

    protected function data_preprocessing($question) {
//        $question = parent::data_preprocessing($question);
//        $question = $this->data_preprocessing_answers($question);
//        $question = $this->data_preprocessing_hints($question);
//
//        return $question;
        error_log('[data_preprocessing]', 3, 'writeregex_log.txt');
    }

    public function validation($data, $files) {
//        $errors = parent::validation($data, $files);
//        $answers = $data['answer'];
//        $answercount = 0;
//        $maxgrade = false;
//        foreach ($answers as $key => $answer) {
//            $trimmedanswer = trim($answer);
//            if ($trimmedanswer !== '') {
//                $answercount++;
//                if ($data['fraction'][$key] == 1) {
//                    $maxgrade = true;
//                }
//            } else if ($data['fraction'][$key] != 0 ||
//                    !html_is_blank($data['feedback'][$key]['text'])) {
//                $errors["answeroptions[$key]"] = get_string('answermustbegiven', 'qtype_shortanswer');
//                $answercount++;
//            }
//        }
//        if ($answercount==0) {
//            $errors['answeroptions[0]'] = get_string('notenoughanswers', 'qtype_shortanswer', 1);
//        }
//        if ($maxgrade == false) {
//            $errors['answeroptions[0]'] = get_string('fractionsnomax', 'question');
//        }
//        return $errors;
        error_log('[validation]', 3, 'writeregex_log.txt');
    }

    public function qtype() {
        error_log('[qtype]', 3, 'writeregex_log.txt');
        return 'writeregex';
    }
}
