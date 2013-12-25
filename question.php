<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/question/type/questionbase.php');


class qtype_writeregex_question extends question_graded_automatically
    implements question_automatically_gradable, question_with_qtype_specific_hints {


    public function __construct() {
        parent::__construct();
    }

    public function is_complete_response(array $response) {
        return array_key_exists('answer', $response) &&
        ($response['answer'] || $response['answer'] === '0');
    }

    public function hint_object($hintkey, $response = null) {

        return null;
    }

    public function available_specific_hints($response = null) {
        $hinttypes = array();

        return $hinttypes;
    }

    public function get_expected_data() {
        /* Note: not using PARAM_RAW_TRIMMED because it'll interfere with next character hinting in most ungraceful way:
         disabling it by eating trailing spaces just when you try to get a first letter of the next word. */
        return array('answer' => PARAM_RAW);
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_shortanswer');
    }

    public function summarise_response(array $response) {
        if (isset($response['answer'])) {
            $resp = $response['answer'];
        } else {
            $resp = null;
        }
        return $resp;
    }

    public function grade_response(array $response) {

        return array();
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        return question_utils::arrays_have_same_keys_and_values($prevresponse, $newresponse);
    }

    public function get_correct_response() {
        return array();
    }

}

