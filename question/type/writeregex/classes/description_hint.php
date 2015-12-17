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

namespace qtype_writeregex;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_description_tool.php');

/**
 * Class for writeregex description hint.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class description_hint extends hint {

    /**
     * Get hint title.
     * @return string hint title.
     */
    public function hint_title() {
        return get_string('wre_d', 'qtype_writeregex');
    }

    /**
     * @return string key for lang strings and field names
     */
    function short_key() {
        return 'description';
    }

    /**
     * @return qtype_preg_authoring_tool tool used for hint
     */
    function tool($regex) {
        $regexoptions = new \qtype_preg_authoring_tools_options();
        $regexoptions->engine = $this->question->engine;
        $regexoptions->usecase = $this->question->usecase;
        $regexoptions->notation = $this->question->notation;
        return new \qtype_preg_description_tool($regex, $regexoptions);
    }

    /**
     * Render hint for concrete regex.
     * @param string $regex regex for which hint is to be shown
     * @return string hint display result for given regex.
     */
    public function render_hint_for_answer($answer) {
        $description = $this->tool($answer);
        $html = $description->generate_html();
        return $html;
    }

    /**
     * Render hint for both students and teachers answers.
     * @param string $studentsanswer students answer
     * @param string $teachersanswer teachers answer
     * @param question $renderer
     * @return string hint display result for given answers.
     */
    public function render_hint_for_both_students_and_teachers_answers($studentsanswer, $teachersanswer, $renderer) {
        $hintforstudent = $this->render_hint_for_answer($studentsanswer);
        $hintforteacher = $this->render_hint_for_answer($teachersanswer);
        return get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ': ' . $hintforstudent . "<br>" .
               get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ': ' . $hintforteacher;
    }
}