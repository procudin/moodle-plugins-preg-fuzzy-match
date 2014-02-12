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
 * Write Regex question renderer class.
 *
 * @package    qtype
 * @subpackage writeregex
 * @copyright 2014 M. Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/shortanswer/renderer.php');

/**
 * Generates the output for writeregex questions.
 * 
 * @package    qtype
 * @subpackage writeregex
 * @copyright 2014 M. Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class qtype_writeregex_renderer extends qtype_shortanswer_renderer {

    /**
     * @param question_attempt $qa
     * @param question_display_options $options
     * @return string
     */
    public function formulation_and_controls (question_attempt $qa, question_display_options $options) {

        $result = parent::formulation_and_controls($qa, $options);

        return $result;
    }

    public function correct_response (question_attempt $qa) {

        $question = $qa->get_question();

        $answer = $question->get_correct_response();

        if (!$answer) {
            return '';
        }

        return get_string('correctansweris', 'qtype_shortanswer', $answer);
    }
}
