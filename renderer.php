<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/shortanswer/renderer.php');

class qtype_writeregex_renderer extends qtype_shortanswer_renderer {
    public function formulation_and_controls(question_attempt $qa,
                                             question_display_options $options) {

        $result = parent::formulation_and_controls($qa, $options);

        return $result;
    }

    public function correct_response(question_attempt $qa) {

        return parent::correct_response($qa);
    }

    public function feedback(question_attempt $qa, question_display_options $options) {

        return parent::feedback($qa, $options);
    }
}

