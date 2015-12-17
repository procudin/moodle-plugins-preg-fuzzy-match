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
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_syntax_tree_tool.php');

/**
 * Class for writeregex syntax tree hint.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class syntax_tree_hint extends \qtype_poasquestion\hint {

    /** @var  int Mode of hint. */
    protected $mode;
    /** @var  object Object on question. */
    protected $question;
    /** @var  string Hint key. */
    protected $hintkey;
    /** @var array Options of syntax tree hint. */
    protected $syntaxtreeoptions = array();

    public function  set_mode ($value) {
        $this->mode = $value;
    }

    /**
     * Init all fields.
     * @param $question object Object of question's class.
     * @param $hintkey string Hint key.
     * @param $mode int Mode of current hint.
     */
    public function __construct ($question, $hintkey, $mode) {

        $this->question = $question;
        $this->hintkey = $hintkey;
        $this->mode = $mode;
        $this->syntaxtreeoptions = array(
            '0' => get_string('none', 'qtype_writeregex'),
            '1' => get_string('student', 'qtype_writeregex'),
            '2' => get_string('answer', 'qtype_writeregex'),
            '3' => get_string('both', 'qtype_writeregex')
        );
    }

    /**
     * Get options of syntax tree hint.
     * @return array Options of syntax tree hint.
     */
    public function syntaxtreeoptions() {
        return $this->syntaxtreeoptions;
    }

    /**
     * Get hint type.
     * @return int hint type.
     */
    public function hint_type() {
        return \qtype_poasquestion\hint::SINGLE_INSTANCE_HINT;
    }

    /**
     * Get hint description.
     * @return string hint description.
     */
    public function hint_description() {
        // Description for hint using mode (e.g. '(for correct answer)')
        $additionformode = get_string('hinttitleaddition', 'qtype_writeregex', get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex'));
        return \qtype_poasquestion\string::strtolower(get_string('wre_st', 'qtype_writeregex')) . ' ' . $additionformode;
    }

    /**
     * Get value of hint response based or not.
     * @return bool hint response based.
     */
    public function hint_response_based() {
        return $this->mode != 2;
    }

    /**
     * Get penalty value.
     * @param null $response object Response.
     * @return float Value of current hint penalty.
     */
    public function penalty_for_specific_hint ($response = null) {
        return $this->question->syntaxtreehintpenalty;
    }

    /**
     * Return true if can available hint for answer.
     * @param $answer User answer.
     * @return bool Can available hint for answer?
     */
    public function can_available_hint_for_answer ($answer) {

        if ($this->mode == 3) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get hint available.
     * @param null $response Response.
     * @return bool Hint available.
     */
    public function hint_available ($response = null) {

        switch ($this->question->syntaxtreehinttype) {
            case 0:
                return false;
            case 2:
                return true;
            default:
                if ($response === null)
                    return true;

                // Check for possibility of using hint for current student response.
                $regexoptions = new \qtype_preg_authoring_tools_options();
                $regexoptions->engine = $this->question->engine;
                $regexoptions->usecase = $this->question->usecase;
                $regexoptions->notation = $this->question->notation;

                $tree = new \qtype_preg_syntax_tree_tool($response['answer'], $regexoptions);
                if (count($tree->get_errors()) > 0) {
                    return false;
                }
                return true;
        }
        return true;
    }

    /**
     * Render hint function.
     * @param question $renderer
     * @param question_attempt $qa
     * @param question_display_options $options
     * @param null $response
     * @return string Template code value.
     */
    public function render_hint ($renderer, \question_attempt $qa = null,
                                 \question_display_options $options = null, $response = null) {
        $regexoptions = new \qtype_preg_authoring_tools_options();
        $regexoptions->engine = $this->question->engine;
        $regexoptions->usecase = $this->question->usecase;
        $regexoptions->notation = $this->question->notation;
        $hinttitlestring = $renderer->render_hint_title(get_string('syntaxtreehintexplanation', 'qtype_writeregex',
            get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex')));

        switch($this->mode){
            case 1:
                $tree = new \qtype_preg_syntax_tree_tool($response['answer'], $regexoptions);
                $json = $tree->generate_json();
                return $hinttitlestring . $json['tree']['img'];
            case 2:
                $answer = $this->question->get_best_fit_answer($response);
                $tree = new \qtype_preg_syntax_tree_tool($answer['answer']->answer, $regexoptions);
                $json = $tree->generate_json();
                return $hinttitlestring . $json['tree']['img'];
            case 3:
                $tree = new \qtype_preg_syntax_tree_tool($response['answer'], $regexoptions);
                $json = $tree->generate_json();
                $answer = $this->question->get_best_fit_answer($response);
                $tree2 = new \qtype_preg_syntax_tree_tool($answer['answer']->answer, $regexoptions);
                $json2 = $tree2->generate_json();
                return $hinttitlestring .
                    get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ':<br>' . $json['tree']['img'] . "<br>" .
                    get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ':<br>' . $json2['tree']['img'];
            default:
                return '';
        }
    }
}
