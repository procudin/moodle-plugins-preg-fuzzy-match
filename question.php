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

    public function get_expected_data() {
        return array('answer' => PARAM_RAW);
    }

}

