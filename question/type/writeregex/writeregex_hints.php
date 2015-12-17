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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_description_tool.php');
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_regex_testing_tool.php');
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_syntax_tree_tool.php');
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_explaining_graph_tool.php');
require_once($CFG->dirroot . '/question/type/preg/preg_matcher.php');

/**
 * Class for writeregex specific hints.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex_syntaxtreehint extends qtype_poasquestion\hint {

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
        return qtype_poasquestion\hint::SINGLE_INSTANCE_HINT;
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
                $regexoptions = new qtype_preg_authoring_tools_options();
                $regexoptions->engine = $this->question->engine;
                $regexoptions->usecase = $this->question->usecase;
                $regexoptions->notation = $this->question->notation;

                $tree = new qtype_preg_syntax_tree_tool($response['answer'], $regexoptions);
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
    public function render_hint ($renderer, question_attempt $qa = null,
                                 question_display_options $options = null, $response = null) {
        $regexoptions = new qtype_preg_authoring_tools_options();
        $regexoptions->engine = $this->question->engine;
        $regexoptions->usecase = $this->question->usecase;
        $regexoptions->notation = $this->question->notation;
        $hinttitlestring = $renderer->render_hint_title(get_string('syntaxtreehintexplanation', 'qtype_writeregex',
            get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex')));

        switch($this->mode){
            case 1:
                $tree = new qtype_preg_syntax_tree_tool($response['answer'], $regexoptions);
                $json = $tree->generate_json();
                return $hinttitlestring . $json['tree']['img'];
            case 2:
                $answer = $this->question->get_best_fit_answer($response);
                $tree = new qtype_preg_syntax_tree_tool($answer['answer']->answer, $regexoptions);
                $json = $tree->generate_json();
                return $hinttitlestring . $json['tree']['img'];
            case 3:
                $tree = new qtype_preg_syntax_tree_tool($response['answer'], $regexoptions);
                $json = $tree->generate_json();
                $answer = $this->question->get_best_fit_answer($response);
                $tree2 = new qtype_preg_syntax_tree_tool($answer['answer']->answer, $regexoptions);
                $json2 = $tree2->generate_json();
                return $hinttitlestring .
                    get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ':<br>' . $json['tree']['img'] . "<br>" .
                    get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ':<br>' . $json2['tree']['img'];
            default:
                return '';
        }
    }
}

/**
 * Class qtype_writeregex_explgraphhint Class of explanation graph hint.
 */
class qtype_writeregex_explgraphhint extends qtype_poasquestion\hint {

    /** @var  int Mode of hint. */
    protected $mode;
    /** @var  object Object on question. */
    protected $question;
    /** @var  string Hint key. */
    protected $hintkey;
    /** @var array Options of explanation graph hint. */
    protected $explgraphhintoptions = array();

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
        $this->explgraphhintoptions = array(
            '0' => get_string('none', 'qtype_writeregex'),
            '1' => get_string('student', 'qtype_writeregex'),
            '2' => get_string('answer', 'qtype_writeregex'),
            '3' => get_string('both', 'qtype_writeregex')
        );
    }

    /**
     * Get options of explanation graph.
     * @return array Options of explanation graph.
     */
    public function explgraphhintoptions() {
        return $this->explgraphhintoptions;
    }

    /**
     * Get hint type.
     * @return int Hint type.
     */
    public function hint_type() {
        return qtype_poasquestion\hint::SINGLE_INSTANCE_HINT;
    }

    /**
     * Get hint description.
     * @return string Hint description.
     */
    public function hint_description() {
        // Description for hint using mode (e.g. '(for correct answer)')
        $additionformode = get_string('hinttitleaddition', 'qtype_writeregex', get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex'));
        return \qtype_poasquestion\string::strtolower(get_string('wre_eg', 'qtype_writeregex')) . ' ' . $additionformode;
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
        return $this->question->explgraphhintpenalty;
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

        switch ($this->question->explgraphhinttype) {
            case 0:
                return false;
            case 2:
                return true;
            default:
                if ($response === null)
                    return true;

                // Check for possibility of using hint for current student response.
                $regexoptions = new qtype_preg_authoring_tools_options();
                $regexoptions->engine = $this->question->engine;
                $regexoptions->usecase = $this->question->usecase;
                $regexoptions->notation = $this->question->notation;

                $tree = new qtype_preg_explaining_graph_tool($response['answer'], $regexoptions);
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
    public function render_hint($renderer, question_attempt $qa = null, question_display_options $options = null, $response = null) {

        $regexoptions = new qtype_preg_authoring_tools_options();
        $regexoptions->engine = $this->question->engine;
        $regexoptions->usecase = $this->question->usecase;
        $regexoptions->notation = $this->question->notation;
        $hinttitlestring = $renderer->render_hint_title(get_string('explgraphhintexplanation', 'qtype_writeregex',
            get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex')));

        switch($this->mode){
            case 1:
                $tree = new qtype_preg_explaining_graph_tool($response['answer'], $regexoptions);
                $json = $tree->generate_json();
                return $hinttitlestring . $json['graph']['img'];
            case 2:
                $answer = $this->question->get_best_fit_answer($response);
                $tree = new qtype_preg_explaining_graph_tool($answer['answer']->answer, $regexoptions);
                $json = $tree->generate_json();
                return $hinttitlestring . $json['graph']['img'];
            case 3:
                $tree = new qtype_preg_explaining_graph_tool($response['answer'], $regexoptions);
                $json = $tree->generate_json();
                $answer = $this->question->get_best_fit_answer($response);
                $tree2 = new qtype_preg_explaining_graph_tool($answer['answer']->answer, $regexoptions);
                $json2 = $tree2->generate_json();
                return $hinttitlestring .
                    get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ':<br>' . $json['graph']['img'] . "<br>" .
                    get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ':<br>' . $json2['graph']['img'];
            default:
                return '';
        }
    }
}

/**
 * Class qtype_writeregex_descriptionhint Class of text description hint.
 */
class qtype_writeregex_descriptionhint extends qtype_poasquestion\hint {

    /** @var  int Mode of hint. */
    protected $mode;
    /** @var  object Object on question. */
    protected $question;
    /** @var  string Hint key. */
    protected $hintkey;
    /** @var array Options of text description hint. */
    protected $descriptionhintoptions = array();

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
        $this->descriptionhintoptions = array(
            '0' => get_string('none', 'qtype_writeregex'),
            '1' => get_string('student', 'qtype_writeregex'),
            '2' => get_string('answer', 'qtype_writeregex'),
            '3' => get_string('both', 'qtype_writeregex')
        );
    }

    /**
     * Get options of text description hint.
     * @return array Options of text description hint.
     */
    public function descriptionhintoptions() {
        return $this->descriptionhintoptions;
    }

    /**
     * Get hint type.
     * @return int hint type.
     */
    public function hint_type() {
        return qtype_poasquestion\hint::SINGLE_INSTANCE_HINT;
    }

    /**
     * Get hint description.
     * @return string hint description.
     */
    public function hint_description() {
        // Description for hint using mode (e.g. '(for correct answer)')
        $additionformode = get_string('hinttitleaddition', 'qtype_writeregex', get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex'));
        return \qtype_poasquestion\string::strtolower(get_string('wre_d', 'qtype_writeregex')) . ' ' . $additionformode;
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
        return $this->question->descriptionhintpenalty;
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

        switch ($this->question->descriptionhinttype) {
            case 0:
                return false;
            case 2:
                return true;
            default:
                if ($response === null)
                    return true;

                // Check for possibility of using hint for current student response.
                $regexoptions = new qtype_preg_authoring_tools_options();
                $regexoptions->engine = $this->question->engine;
                $regexoptions->usecase = $this->question->usecase;
                $regexoptions->notation = $this->question->notation;

                $tree = new qtype_preg_description_tool($response['answer'], $regexoptions);
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
    public function render_hint($renderer, question_attempt $qa = null, question_display_options $options = null, $response = null) {

        $regexoptions = new qtype_preg_authoring_tools_options();
        $regexoptions->engine = $this->question->engine;
        $regexoptions->usecase = $this->question->usecase;
        $regexoptions->notation = $this->question->notation;
        $hinttitlestring = $renderer->render_hint_title(get_string('descriptionhintexplanation', 'qtype_writeregex',
            get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex')));

        switch($this->mode){
            case 1:
                $description = new qtype_preg_description_tool($response['answer'], $regexoptions);
                $html = $description->generate_html();
                return $hinttitlestring . $html;
            case 2:
                $answer = $this->question->get_best_fit_answer($response);
                $description = new qtype_preg_description_tool($answer['answer']->answer, $regexoptions);
                $html = $description->generate_html();
                return $hinttitlestring . $html;
            case 3:
                $description = new qtype_preg_description_tool($response['answer'], $regexoptions);
                $answer = $this->question->get_best_fit_answer($response);
                $description2 = new qtype_preg_description_tool($answer['answer']->answer, $regexoptions);
                return $hinttitlestring .
                    get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ': ' . $description->generate_html() . "<br>" .
                    get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ': ' . $description2->generate_html();
            default:
                return '';
        }
    }
}

/**
 * Class qtype_writeregex_teststringshint Class of test strings hint.
 */
class qtype_writeregex_teststringshint extends qtype_poasquestion\hint {

    /** @var  int Mode of hint. */
    protected $mode;
    /** @var  object Object on question. */
    protected $question;
    /** @var  string Hint key. */
    protected $hintkey;
    /** @var array Options of syntax tree hint. */
    protected $teststringshintoptions = array();

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
        $this->teststringshintoptions = array(
            '0' => get_string('none', 'qtype_writeregex'),
            '1' => get_string('student', 'qtype_writeregex'),
            '2' => get_string('answer', 'qtype_writeregex'),
            '3' => get_string('both', 'qtype_writeregex')
        );
    }

    /**
     * Get options of test strings hint.
     * @return array Options of test strings hint.
     */
    public function teststringshintoptions() {
        return $this->teststringshintoptions;
    }

    /**
     * Get hint type.
     * @return int hint type.
     */
    public function hint_type() {
        return qtype_poasquestion\hint::SINGLE_INSTANCE_HINT;
    }

    /**
     * Get hint description.
     * @return string hint description.
     */
    public function hint_description() {
        // Description for hint using mode (e.g. '(for correct answer)')
        $additionformode = get_string('hinttitleaddition', 'qtype_writeregex', get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex'));
        return \qtype_poasquestion\string::strtolower(get_string('teststrings', 'qtype_writeregex')) . ' ' . $additionformode;
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
        return $this->question->teststringshintpenalty;
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

        switch ($this->question->teststringshinttype) {
            case 0:
                return false;
            case 2:
                return true;
            default:
                if ($response === null)
                    return true;

                // Check for possibility of using hint for current student response.
                $strings = '';
                $key = 0;
                foreach ($this->question->teststrings as $item) {

                    if ($key == count($this->question->teststrings) - 1) {
                        $strings .= $item->teststring;
                    } else {
                        $strings .= $item->teststring . "\n";
                        $key++;
                    }
                }
                $usecase = $this->question->usecase;
                $exactmatch = false;
                $engine = $this->question->engine;
                $notation = $this->question->notation;
                $regex = $response['answer'];

                $tool = new qtype_preg_regex_testing_tool($regex, $strings, $usecase, $exactmatch, $engine,
                        $notation, new qtype_preg_position());
                if ($tool->errors_exist())
                    return false;

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
    public function render_hint($renderer, question_attempt $qa = null, question_display_options $options = null, $response = null) {

        $strings = '';
        $key = 0;
        foreach ($this->question->teststrings as $item) {

            if ($key == count($this->question->teststrings) - 1) {
                $strings .= $item->teststring;
            } else {
                $strings .= $item->teststring . "\n";
                $key++;
            }
        }

        $usecase = $this->question->usecase;
        $exactmatch = false;
        $engine = $this->question->engine;
        $notation = $this->question->notation;
        $hinttitlestring = $renderer->render_hint_title(get_string('teststringshintexplanation', 'qtype_writeregex',
            get_string('hinttitleadditionformode_' . $this->mode, 'qtype_writeregex')));

        switch($this->mode){
            case 1:
                $regex = $response['answer'];
                $tool = new qtype_preg_regex_testing_tool($regex, $strings, $usecase, $exactmatch, $engine,
                    $notation, new qtype_preg_position());
                $json = $tool->generate_json();
                return $hinttitlestring . $json['regex_test'];
            case 2:
                $answer = $this->question->get_best_fit_answer($response);
                $regex = $answer['answer']->answer;
                $tool = new qtype_preg_regex_testing_tool($regex, $strings, $usecase, $exactmatch, $engine,
                    $notation, new qtype_preg_position());
                $json = $tool->generate_json();
                return $hinttitlestring . $json['regex_test'];
            case 3:
                $studentsregex = $response['answer'];
                $tool = new qtype_preg_regex_testing_tool($studentsregex, $strings, $usecase, $exactmatch, $engine,
                    $notation, new qtype_preg_position());
                $studentsjson = $tool->generate_json();
                $answer = $this->question->get_best_fit_answer($response);
                $correctregex = $answer['answer']->answer;
                $tool = new qtype_preg_regex_testing_tool($correctregex, $strings, $usecase, $exactmatch, $engine,
                    $notation, new qtype_preg_position());
                $correctjson = $tool->generate_json();
                return $hinttitlestring . $renderer->generate_teststring_hint_result_table($studentsjson['regex_test'], $correctjson['regex_test']);
            default:
                return '';
        }
    }
}
