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
 * Write Regex question renderer class.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/shortanswer/renderer.php');

/**
 * Generates the output for writeregex questions.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class qtype_writeregex_renderer extends qtype_shortanswer_renderer {

    /**
     * Render correct response.
     * @param question_attempt $qa Question attempt.
     * @return string Correct response value.
     */
    public function correct_response (question_attempt $qa) {

        $question = $qa->get_question();

        $answer = $question->get_correct_response();

        if (!$answer) {
            return '';
        }

        return get_string('correctansweris', 'qtype_shortanswer', $answer['answer']);
    }

    /**
     * Get specific feedback.
     * @param question_attempt $qa Question attempt.
     * @return string Specific feedback.
     */
    public function specific_feedback(question_attempt $qa) {

        $question = $qa->get_question();
        $currentanswer = $qa->get_last_qt_var('answer');

        if (!$currentanswer) {
            return '';
        }

        // use hint
        return $question->get_feedback_for_response(array('answer' => $currentanswer), $qa);
    }

    /**
     * Get feedback value.
     * @param question_attempt $qa Question attempt.
     * @param question_display_options $options Question display options.
     * @return string Feedback value.
     */
    public function feedback(question_attempt $qa, question_display_options $options){

        $feedback = '';

        $question = $qa->get_question();
        $behaviour = $qa->get_behaviour();
        $currentanswer = $qa->get_last_qt_var('answer');

        if (!$currentanswer) {
            $currentanswer = '';
        }

        $br = html_writer::empty_tag('br');

        if (is_a($behaviour, 'behaviour_with_hints')) {
            $hints = $question->available_specific_hints();
            $hints = $behaviour->adjust_hints($hints);

            foreach ($hints as $hintkey) {
                if ($qa->get_last_step()->has_behaviour_var('_render_' . $hintkey)) {
                    $hintobj = $question->hint_object($hintkey);
                    $feedback .= $hintobj->render_hint($this, $qa, $options, array('answer' => $currentanswer)) . $br;
                }
            }
        }

        if(get_class($behaviour) == 'qbehaviour_interactivehints') {
            $hints = $question->available_specific_hints();
            $hints = $behaviour->adjust_hints($hints);
            $hintoptions = explode('\n', $qa->get_applicable_hint()->options);

            foreach ($hints as $index => $hintkey) {
                $hintobj = $question->hint_object($hintkey);
                $hintobj->set_mode($hintoptions[$index]);
                $feedback .= $hintobj->render_hint($this, $qa, $options, array('answer' => $currentanswer)) . $br;
            }
        }

        $output = parent::feedback($qa, $options);

        return $feedback . $output;
    }
}
