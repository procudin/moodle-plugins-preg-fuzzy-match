<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/preg/preg_hints.php');
require_once($CFG->dirroot . '/question/type/writeregex/writeregex_hints.php');


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

    /** @var number only answers with fraction >= hintgradeborder would be used for hinting. */
    public $hintgradeborder;

    /** @var  int Value of grader analyzer type. */
    public $graderanalyzertype;

    /** @var  float Value of penalty for grader analyzer type. */
    public $graderanalyzerpenalty;

    public function get_expected_data() {
        return array('answer' => PARAM_RAW);
    }

    public function get_correct_response() {
        $response = array('answer' => '');
        $correctanswer = '';

        if (trim($correctanswer) == '') {
            $bestfit = $this->get_best_fit_answer($response, 1);
            $matchresult = $bestfit['match'];

            if ($matchresult == 1) {
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

    public function is_complete_response (array $response) {

        return array_key_exists('answer', $response) &&
            ($response['answer'] || $response['answer'] === '0');
    }

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

    public function get_validation_error (array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }

        return get_string('pleaseenterananswer', 'qtype_shortanswer');
    }

    public function grade_response (array $response) {
        $bestfitanswer = $this->get_best_fit_answer($response);
        $grade = 0;
        $state = question_sate::$gradedwrong;
        if ($bestfitanswer['match'] == 1) {
            $grade = $bestfitanswer['answer']->fraction;
            $state = question_state::graded_state_for_fraction($bestfitanswer['answer']->fraction);
        }

        return array($grade, $state);
    }

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

    public function get_feedback_for_response ($response, $qa) {
        $besftfit = $this->get_best_fit_answer($response);
        $feedback = '';
        $boardermatch = 0.7;
        $state = $qa->get_state();

        if (isset($besftfit['answer']) &&
            ($besftfit['match'] == 1 || $besftfit['match'] > $boardermatch && $state->is_finished())) {
            $answer = $besftfit['answer'];

            if ($answer->feedback) {
                $feedback = 'get_feedback_for_response';
            }
        }

        return $feedback;
    }

    public function get_best_fit_answer (array $response, $gradeborder = null) {
        $graderanalyzer = new graderaanalyser($this->graderanalyzerpenalty);

        $equality_answers_arr = array();
        foreach( $this->answers as $answer){
            if($this->graderanalyzerpenalty < $answer->fraction){
                $equality = $graderanalyzer->get_equality($answer->answer, $response['answer']);
                $equality_answers_arr[$equality] = $answer;
            }
        }

        $bestfit = array();
        if(count($equality_answers_arr) > 0){
            krsort($equality_answers_arr);
            foreach($equality_answers_arr as $key => $val){
                $bestfit['answer'] = $val;
                $bestfit['match'] = $key/10;
                break;
            }
        }
        else{
            $bestfit['answer'] = array();
            $bestfit['match'] = 0;
        }
        return $bestfit;
    }

    /**
     * Hint object factory.
     *
     * Returns a hint object for given type.
     */
    /**
     * Hint object factory.
     * @param $hintkey Hint key.
     * @param null $response Response
     * @return qtype_poasquestion_hintmoodle A Hint object for given type.
     */
    public function hint_object($hintkey, $response = null) {
        // Moodle-specific hints
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

}

