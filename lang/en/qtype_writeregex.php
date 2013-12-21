<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'qtype_shortanswer', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    qtype
 * @subpackage shortanswer
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addmoreanswerblanks'] = 'Blanks for {no} More Answers';
$string['answer'] = 'Answer: {$a}';
$string['answermustbegiven'] = 'You must enter an answer if there is a grade or feedback.';
$string['answerno'] = 'Answer {$a}';
$string['caseno'] = 'No, case is unimportant';
$string['casesensitive'] = 'Case sensitivity';
$string['caseyes'] = 'Yes, case must match';
$string['correctansweris'] = 'The correct answer is: {$a}';
$string['correctanswers'] = 'Correct answers';
$string['filloutoneanswer'] = 'You must provide at least one possible answer. Answers left blank will not be used. \'*\' can be used as a wildcard to match any characters. The first matching answer will be used to determine the score and feedback.';
$string['notenoughanswers'] = 'This type of question requires at least {$a} answers';
$string['pleaseenterananswer'] = 'Please enter an answer.';
$string['pluginname'] = 'Write RegEx';
$string['pluginname_help'] = 'In response to a question (that may include a image) the respondent types a word or short phrase. There may be several possible correct answers, each with a different grade. If the "Case sensitive" option is selected, then you can have different scores for "Word" or "word".';
$string['pluginname_link'] = 'question/type/writeregex';
$string['pluginnameadding'] = 'Adding a Write RegEx question';
$string['pluginnameediting'] = 'Editing a Write RegEx question';
$string['pluginnamesummary'] = 'Write a regular expression answer.';
$string['wre_notation'] = 'Notation';

/* Notation of regexp. */
$string['wre_notation_simple'] = 'Simple';
$string['wre_notation_extended'] = 'Extended';
$string['wre_notation_moodle'] = 'Moodle Short Answer';

/* Syntax tree hint. */
$string['wre_st'] = 'Syntax tree hint';
$string['wre_st_penalty'] = 'Penalty';
$string['wre_st_none'] = 'None';
$string['wre_st_student'] = 'Show the student\'s answer';
$string['wre_st_answer'] = 'Show the correct answer';
$string['wre_st_both'] = 'Show the student\'s answer and the correct answer (both)';

/* Explaining graph hint. */
$string['wre_eg'] = 'Explaining graph hint';
$string['wre_eg_penalty'] = 'Penalty';
$string['wre_eg_none'] = 'None';
$string['wre_eg_student'] = 'Show the student\'s answer';
$string['wre_eg_answer'] = 'Show the correct answer';
$string['wre_eg_both'] = 'Show the student\'s answer and the correct answer (both)';

/* Description hint. */
$string['wre_d'] = 'Description hint';
$string['wre_d_penalty'] = 'Penalty';
$string['wre_d_none'] = 'None';
$string['wre_d_student'] = 'Show the student\'s answer';
$string['wre_d_answer'] = 'Show the correct answer';
$string['wre_d_both'] = 'Show the student\'s answer and the correct answer (both)';

/* Test string hint. */
$string['teststrings'] = 'Test string hint';
$string['penalty'] = 'Penalty';
$string['none'] = 'None';
$string['student'] = 'Show the student\'s answer';
$string['answer'] = 'Show the correct answer';
$string['both'] = 'Show the student\'s answer and the correct answer (both)';

/* Compare regex. */
$string['wre_cre'] = 'Compare regexp';
$string['wre_cre_no'] = 'No';
$string['wre_cre_yes'] = 'Yes';
$string['wre_cre_percentage'] = 'Percentage';

/* Compare regexp's automats */
$string['wre_acre'] = 'Compare regexp\'s automats';
$string['wre_acre_percentage'] = 'Percentage';

$string['wre_regexp_answers'] = 'Regular expression';
$string['wre_regexp_ts'] = 'Test string';

/* Ошибка суммы типов проверок. */
$string['wre_error_matching'] = 'Sum of all matching type is not equal 100%';

/* Ошибки ответов regexp. */
$string['wre_regexp_answers_count'] = 'Must be at least one answer';
$string['wre_regexp_fractions_count'] = 'At least one of the answers must have a fraction 100%';

/* Ошибки ответов тестовых строк. */
$string['wre_ts_answers_count'] = 'Must be at least one answer';
$string['wre_ts_fractions_count'] = 'At least one of the answers must have a fraction 100%';