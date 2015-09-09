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

global $CFG;

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/preg/preg_hints.php');
require_once($CFG->dirroot . '/question/type/preg/preg_matcher.php');
require_once($CFG->dirroot . '/question/type/writeregex/writeregex_hints.php');
require_once($CFG->dirroot . '/question/type/writeregex/writeregex_compare_regex_automata_analyzer.php');
require_once($CFG->dirroot . '/question/type/writeregex/writeregex_test_strings_analyser.php');
require_once($CFG->dirroot . '/question/type/writeregex/writeregex_compare_regex_analyzer.php');

/**
 * Represents a write regex question.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex_question extends question_graded_automatically
    implements question_automatically_gradable, question_with_qtype_specific_hints {


    /** @var array Answers. */
    public $answers = array();

    /** @var string Value of using case. */
    public $usecase;

    /** @var string Value of matcher engine. */
    public $engine;

    /** @var string Notation of regex. */
    public $notation;

    /** @var int Value of syntax tree type. */
    public $syntaxtreehinttype;

    /** @var  float Value of penalty for using syntax tree adaptive hint. */
    public $syntaxtreehintpenalty;

    /** @var  int Value of explanation graph type. */
    public $explgraphhinttype;

    /** @var  float Value of penalty for using explanation graph adaptive hint. */
    public $explgraphhintpenalty;

    /** @var  int Value of description text hint type. */
    public $descriptionhinttype;

    /** @var  float Value of penalty for using description text adaptive hint. */
    public $descriptionhintpenalty;

    /** @var  int Value of test string hint type. */
    public $teststringshinttype;

    /** @var  float Value of penalty for using test string adaptive hint. */
    public $teststringshintpenalty;

    // Types of compare.
    /** @var  float Value of compare regexps in %. */
    public $compareregexpercentage;

    /** @var  float Value of compare by automates in %. */
    public $compareautomatapercentage;

    /** @var  float Value of compare by test strings in %. */
    public $compareregexpteststrings;

    /** @var number Only answers with fraction >= hintgradeborder would be used for hinting. */
    public $hintgradeborder = 0.1;

    /** @var  int Value of grader analyzer type. */
    public $graderanalyzertype;

    /** @var  float Value of penalty for grader analyzer type. */
    public $graderanalyzerpenalty;

    /** @var  array Best fit answer value. */
    public $bestfitanswer;

    /** @var  string Value of best fit answers response. */
    public $responseforbestfit;

    /**
     * Get type of expected data.
     * @return array|string Type of expected data.
     */
    public function get_expected_data() {
        return array('answer' => PARAM_RAW);
    }

    /**
     * Get correct response value.
     * @return array|null
     */
    public function get_correct_response() {
        $response = array('answer' => '');
        $correctanswer = '';

        foreach ($this->answers as $item) {
            if ($item->feedbackformat == 1 and $item->fraction == 1.0000000) {
                $correctanswer = $item->answer;
                break;
            }
        }

        return array('answer' => $correctanswer);
    }

    /**
     * Get matching answer value.
     * @param array $response Users response.
     * @return array answer value.
     */
    public function get_matching_answer(array $response) {
        $bestfit = $this->get_best_fit_answer($response);

        if ($bestfit['fitness'] >= $this->hintgradeborder) {
            return $bestfit['answer']->answer;
        }

        return array();
    }

    /**
     * Return true if response is complete.
     * @param array $response Users response.
     * @return bool Is response complete?
     */
    public function is_complete_response (array $response) {

        return array_key_exists('answer', $response) &&
            ($response['answer'] || $response['answer'] === '0');
    }

    /**
     * Return true if response is gradable.
     * @param array $response Users response.
     * @return bool Is response gradable?
     */
    public function is_gradable_response (array $response) {
        return $this->is_complete_response($response);
    }

    public function is_same_response (array $prevresponse, array $newresponse) {
        return question_utils::arrays_have_same_keys_and_values($prevresponse, $newresponse);
    }

    public function summarise_response (array $response) {
        if (isset($response['answer'])) {
            $resp = $response['answer'];
        } else {
            $resp = null;
        }

        return $resp;
    }

    /**
     * Get validation error.
     * @param array $response Users response.
     * @return string Validation error.
     */
    public function get_validation_error (array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }

        return get_string('pleaseenterananswer', 'qtype_shortanswer');
    }

    /**
     * Doing grade response.
     * @param array $response Users response.
     * @return array Grade and state of users response.
     */
    public function grade_response (array $response) {

        $bestfitanswer = $this->get_best_fit_answer($response);
        $grade = $bestfitanswer['fitness'];
        question_state::graded_state_for_fraction($bestfitanswer['fitness']);
        $state = question_state::$gradedwrong;

        return array($grade, $state);
    }

    /**
     * Make behaviour.
     * @param question_attempt $qa Question attempt.
     * @param string $preferredbehaviour Preferred behaviour.
     * @return object Behaviour object.
     */
    public function make_behaviour (question_attempt $qa, $preferredbehaviour) {
        global $CFG;

        if ($preferredbehaviour == 'adaptive' &&
            file_exists($CFG->dirroot.'/question/behaviour/adaptivehints/')) {
            question_engine::load_behaviour_class('adaptivehints');
            return new qbehaviour_adaptivehints($qa, $preferredbehaviour);
        }

        if ($preferredbehaviour == 'adaptivenopenalty' &&
            file_exists($CFG->dirroot.'/question/behaviour/adaptivehintsnopenalties/')) {
            question_engine::load_behaviour_class('adaptivehintsnopenalties');
            return new qbehaviour_adaptivehintsnopenalties($qa, $preferredbehaviour);
        }

        if ($preferredbehaviour == 'interactive' &&
            file_exists($CFG->dirroot.'/question/behaviour/interactivehints/')) {
            question_engine::load_behaviour_class('interactivehints');
            return new qbehaviour_interactivehints($qa, $preferredbehaviour);
        }

        return parent::make_behaviour($qa, $preferredbehaviour);
    }

    /**
     * Get feedback for response.
     * @param $response array User response.
     * @param $qa question_attempt Question attempt.
     * @return string Feedback value.
     */
    public function get_feedback_for_response ($response, $qa) {
        $besftfit = $this->get_best_fit_answer($response);
        $feedback = '';
        $boardermatch = 0.7;
        $state = $qa->get_state();

        if (isset($besftfit['answer']) &&
            ($besftfit['fitness'] == 1 || $besftfit['fitness'] > $boardermatch && $state->is_finished())) {
            $answer = $besftfit['answer'];
            $feedback = $answer->feedback;
        }

        return $feedback;
    }

    /**
     * Get best fit answer.
     * @param array $response Users response.
     * @param null $gradeborder Gradeborder value.
     * @return array Answer array.
     */
    public function get_best_fit_answer (array $response, $gradeborder = null) {

        // Check cache for valid results.
        if ($response['answer'] == $this->responseforbestfit && $this->bestfitanswer !== array()) {
            return $this->bestfitanswer;
        }

        $graderanalyzer = new test_strings_analyser($this);
        $compregex = new compare_regex_analyzer($this);
        $compregexa = new compare_regex_automata_analyzer($this);

        $bestfitanswer = null;
        $bestfitness = 0.0;

        foreach ($this->answers as $answer) {
            if ($answer->feedbackformat == 1) {
                $fitness1 = $graderanalyzer->get_equality($answer->answer, $response['answer']);
                $fitness2 = $compregex->get_equality($answer->answer, $response['answer']);
                $fitness3 = $compregexa->get_equality($answer->answer, $response['answer']);

                $teststringfiness = $fitness1 * $this->compareregexpteststrings / 100;
                $compregexfitness = $fitness2 * $this->compareregexpercentage / 100;
                $compregexafitness = $fitness3 * $this->compareautomatapercentage / 100;

                $fraction = $teststringfiness + $compregexfitness + $compregexafitness;

                if ( $fraction > $bestfitness and $fraction > $this->hintgradeborder ) {
                    $bestfitness = $teststringfiness + $compregexfitness + $compregexafitness;
                    $bestfitanswer = $answer;
                }
            }
        }

        $bestfit = array('answer' => $bestfitanswer, 'fitness' => $bestfitness);

        $this->bestfitanswer = $bestfit;
        $this->responseforbestfit = $response['answer'];

        return $bestfit;
    }

    /**
     * Hint object factory.
     * @param $hintkey Hint key.
     * @param null $response Response
     * @return qtype_poasquestion_hintmoodle A Hint object for given type.
     */
    public function hint_object($hintkey, $response = null) {

        // Moodle-specific hints.
        if (substr($hintkey, 0, 11) == 'hintmoodle#') {
            return new qtype_poasquestion_hintmoodle($this, $hintkey);
        }

        $hintclass = 'qtype_writeregex_'.$hintkey;

        $analysermode = 0;
        if ($hintkey == 'syntaxtreehint') {
            $analysermode = $this->syntaxtreehinttype;
        }

        if ($hintkey == 'explgraphhint') {
            $analysermode = $this->explgraphhinttype;
        }

        if ($hintkey == 'descriptionhint') {
            $analysermode = $this->descriptionhinttype;
        }

        if ($hintkey == 'teststringshint') {
            $analysermode = $this->teststringshinttype;
        }

        return new $hintclass($this, $hintkey, $analysermode);
    }

    /**
     * Get specific hints.
     * @param null $response User response.
     * @return array Array of hints types.
     */
    public function available_specific_hints ($response = null) {
        $hinttypes = array();

        if (count($this->hints) < 0) {
            $hinttypes[] = 'hintmoodle#';
        }

        if ($this->syntaxtreehinttype > 0) {
            $hinttypes[] = 'syntaxtreehint';
        }

        if ($this->explgraphhinttype > 0) {
            $hinttypes[] = 'explgraphhint';
        }

        if ($this->descriptionhinttype > 0) {
            $hinttypes[] = 'descriptionhint';
        }

        if ($this->teststringshinttype > 0) {
            $hinttypes[] = 'teststringshint';
        }

        return $hinttypes;
    }

    public function hints_available_for_student($response = null) {
        $result = $this->available_specific_hints($response);

        return $result;
    }

}

