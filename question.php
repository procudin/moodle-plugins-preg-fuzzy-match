<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/preg/preg_hints.php');


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

    // types of compare
    /** @var  float Value of compare regexps in %. */
    public $compareregexpercentage;

    /** @var  float Value of compare by automates in %. */
    public  $compareautomatapercentage;

    /** @var  float Value of compare by test strings in %. */
    public $compareregexpteststrings;

    /*private $graderanalyzer;
    private $stackhintanalyzer;
    private $algebrahintanalyzer;*/

    /*public function __construct() {
        parent::__construct();
        /*$this-raderanalyzer = new graderaanalyser($this->graderanalyzertype);
        $this->stackhintanalyzer = new qtype_cppexpr_stackanalyzerhint($this, 'stackanalyzerhint', $this->stackhintanalyzertype);
        $this->algebrahintanalyzer = new qtype_cppexpr_algebraanalyzerhint($this, 'stackanalyzerhint', $this->algebrahintanalyzertype);
    }*/

    public function get_expected_data() {
        /* Note: not using PARAM_RAW_TRIMMED because it'll interfere with next character hinting in most ungraceful way:
         disabling it by eating trailing spaces just when you try to get a first letter of the next word. */
        return array('answer' => PARAM_RAW);
    }

    public function get_correct_response() {

        $response = array('answer' => '');
        $correctanswer = '';
        if (trim($correctanswer) == '') {
            // No correct answer set be the teacher, so try to generate correct response.
            // TODO - should we default to generate even if teacher entered the correct answer?
            $bestfit = $this->get_best_fit_answer($response, 1);
            $matchresult = $bestfit['match'];
            if ($matchresult == 1) {
                // Engine generated a full match.
                $correctanswer = $bestfit['answer'];
            }
        }
        return array('answer' => $correctanswer);
    }

    public function get_matching_answer(array $response) {
        $bestfit = $this->get_best_fit_answer($response);
        if ($bestfit['match'] == 1) {
            return $bestfit['answer'];
        }
        return array();
    }

    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) &&
        ($response['answer'] || $response['answer'] === '0');
    }

    public function is_gradable_response(array $response) {
        return $this->is_complete_response($response);
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_have_same_keys_and_values($prevresponse, $newresponse);
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            $resp = $response['answer'];
        } else {
            $resp = null;
        }
        return $resp;
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_shortanswer');
    }

    public function grade_response(array $response) {

        $bestfitanswer = $this->get_best_fit_answer($response);
        $grade = 0;
        $state = question_state::$gradedwrong;
        if ($bestfitanswer['match']==1) {
            $grade = $bestfitanswer['answer']->fraction;
            $state = question_state::graded_state_for_fraction($bestfitanswer['answer']->fraction);
        }

        return array($grade, $state);
    }

    /**
     * Get available specific hints array.
     * @param null $response
     * @return array of available specific hints
     */
    public function available_specific_hints($response = null) {

        $hinttypes = array();

        if (count($this->hints) > 0) {
            $hinttypes[] = 'hintmoodle#';
        }

        if ($this->syntaxtreehinttype > 0) {
            $hinttypes[] = 'syntaxtreehinttype';
        }

        if ($this->explgraphhinttype > 0) {
            $hinttypes[] = 'explgraphhinttype';
        }

        if ($this->descriptionhinttype > 0) {
            $hinttypes[] = 'descriptionhinttype';
        }

        if ($this->teststringshinttype > 0) {
            $hinttypes[] = '$teststringshinttype';
        }

        return $hinttypes;
    }

    /**
     * Hint object factory.
     *
     * Returns a hint object for given type.
     */
    public function hint_object($hintkey, $response = null) {
        // Moodle-specific hints
        if (substr($hintkey, 0, 11) == 'hintmoodle#') {
            return new qtype_poasquestion_hintmoodle($this, $hintkey);
        }

        $hintclass = 'qtype_cppexpr_'.$hintkey;

        $analysermode = 0;
        if($hintkey == 'stackanalyzerhint'){
            $analysermode = $this->stackhintanalyzertype;
        }
        if($hintkey == 'algebraanalyzerhint'){
            $analysermode = $this->algebrahintanalyzertype;
        }
        return new $hintclass($this, $hintkey, $analysermode);
    }

    public function get_best_fit_answer(array $response, $gradeborder = null) {

//        $graderanalyzer = new graderaanalyser($this->graderanalyzerpenalty);

        /*echo '<pre>';
        print_r($this->answers);
        print_r('==================================');
        echo '<pre>';*/

        $equality_answers_arr = array();
//        foreach( $this->answers as $answer){
//            if($this->graderanalyzerpenalty < $answer->fraction){
////                $equality = $graderanalyzer->get_equality($answer->answer, $response['answer']);
////                $equality_answers_arr[$equality] = $answer;
//            }
//        }

        $bestfit = array();
        if(count($equality_answers_arr) > 0){
            krsort($equality_answers_arr);
            foreach($equality_answers_arr as $key => $val){
                $bestfit['answer'] = $val;
                $bestfit['match'] = $key/10;
                break;
            }
        }
        else{// will never be
            $bestfit['answer'] = array();
            $bestfit['match'] = 0;
        }
        return $bestfit;
    }

    /**
     * Returns formatted feedback text to show to the user, or null if no feedback should be shown.
     */
    public function get_feedback_for_response($response, $qa) {


        $bestfit = $this->get_best_fit_answer($response);
        $feedback = '';
        $boardermatch = 0.7;
        // If best fit answer is found and there is a full match.
        // We should not show feedback for partial matches while question still active since student still don't get his answer correct.
        // But if the question is finished there is no harm in showing feedback for partial matching.
        $state = $qa->get_state();
        if (isset($bestfit['answer']) && ($bestfit['match']==1  || $bestfit['match'] > $boardermatch && $state->is_finished()) ) {
            $answer = $bestfit['answer'];
            if ($answer->feedback) {
                //$feedbacktext = $this->insert_subexpressions($answer->feedback, $response, $bestfit['match']);
                //$feedback = $this->format_text($feedbacktext, $answer->feedbackformat, $qa, 'question', 'answerfeedback', $answer->id);
                $feedback = 'get_feedback_for_response';
            }
        }

        return $feedback;
    }

    // We need adaptive or interactive behaviour to use hints.
    public function make_behaviour(question_attempt $qa, $preferredbehaviour) {
        global $CFG;

        if ($preferredbehaviour == 'adaptive' && file_exists($CFG->dirroot.'/question/behaviour/adaptivehints/')) {
            question_engine::load_behaviour_class('adaptivehints');
            return new qbehaviour_adaptivehints($qa, $preferredbehaviour);
        }

        if ($preferredbehaviour == 'adaptivenopenalty' && file_exists($CFG->dirroot.'/question/behaviour/adaptivehintsnopenalties/')) {
            question_engine::load_behaviour_class('adaptivehintsnopenalties');
            return new qbehaviour_adaptivehintsnopenalties($qa, $preferredbehaviour);
        }

        if ($preferredbehaviour == 'interactive' && file_exists($CFG->dirroot.'/question/behaviour/interactivehints/')) {
            question_engine::load_behaviour_class('interactivehints');
            return new qbehaviour_interactivehints($qa, $preferredbehaviour);
        }

        return parent::make_behaviour($qa, $preferredbehaviour);
    }

}

