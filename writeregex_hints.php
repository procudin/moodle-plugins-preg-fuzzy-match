<?php


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/poasquestion/hints.php');

/**
 * Class qtype_writeregex_syntaxtreehint Class of syntax tree hint.
 */
class qtype_writeregex_syntaxtreehint extends qtype_specific_hint {}

/**
 * Class qtype_writeregex_explgraphhint Class of explanation graph hint.
 */
class qtype_writeregex_explgraphhint extends qtype_specific_hint {}

/**
 * Class qtype_writeregex_descriptionhint Class of text description hint.
 */
class qtype_writeregex_descriptionhint extends qtype_specific_hint {}

/**
 * Class qtype_writeregex_teststringshint Class of test strings hint.
 */
class qtype_writeregex_teststringshint extends qtype_specific_hint {}