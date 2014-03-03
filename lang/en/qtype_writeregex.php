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

/**
 * Strings for component 'qtype_writeregex', language 'en'
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
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
$string['pluginnamesummary'] = 'Question to monitor student\'s knowledge of compiling regular expressions (regexp).';
$string['wre_notation'] = 'Notation';

$string['wre_notation_simple'] = 'Simple';
$string['wre_notation_extended'] = 'Extended';
$string['wre_notation_moodle'] = 'Moodle Short Answer';

$string['wre_st'] = 'Syntax tree hint';
$string['wre_st_penalty'] = 'Penalty';
$string['wre_st_none'] = 'None';
$string['wre_st_student'] = 'Show the student\'s answer';
$string['wre_st_answer'] = 'Show the correct answer';
$string['wre_st_both'] = 'Show the student\'s answer and the correct answer (both)';

$string['wre_eg'] = 'Explaining graph hint';
$string['wre_eg_penalty'] = 'Penalty';
$string['wre_eg_none'] = 'None';
$string['wre_eg_student'] = 'Show the student\'s answer';
$string['wre_eg_answer'] = 'Show the correct answer';
$string['wre_eg_both'] = 'Show the student\'s answer and the correct answer (both)';

$string['wre_d'] = 'Description hint';
$string['wre_d_penalty'] = 'Penalty';
$string['wre_d_none'] = 'None';
$string['wre_d_student'] = 'Show the student\'s answer';
$string['wre_d_answer'] = 'Show the correct answer';
$string['wre_d_both'] = 'Show the student\'s answer and the correct answer (both)';

$string['teststrings'] = 'Test string hint';
$string['penalty'] = 'Penalty';
$string['none'] = 'None';
$string['student'] = 'Show the student\'s answer';
$string['answer'] = 'Show the correct answer';
$string['both'] = 'Show the student\'s answer and the correct answer (both)';

$string['wre_cre'] = 'Compare regexp';
$string['wre_cre_no'] = 'No';
$string['wre_cre_yes'] = 'Yes';
$string['wre_cre_percentage'] = 'Percentage';

$string['wre_acre'] = 'Compare regexp\'s automats';
$string['wre_acre_percentage'] = 'Percentage';
$string['compareautomatapercentage'] = 'Checking regular expression matching (in %) for automata';

$string['compareregexpteststrings'] = 'Checking regular expression matching (in %) in test strings';

$string['wre_regexp_answers'] = 'Regular expression';
$string['wre_regexp_ts'] = 'Test string';

$string['wre_error_matching'] = 'Sum of all matching type is not equal 100%';

$string['wre_regexp_answers_count'] = 'Must be at least one answer';
$string['wre_regexp_fractions_count'] = 'At least one of the answers must have a fraction 100%';

$string['wre_ts_answers_count'] = 'Must be at least one answer';
$string['wre_ts_fractions_count'] = 'At least one of the answers must have a fraction 100%';

$string['compare'] = 'You can specify a weight check in regexps and automata';
$string['compare_title'] = 'You can specify a weight check in regexps and automata';
$string['compare_help'] = 'Comparison is carried out directly in regexps and automata. If you specify a value for
regexps 1, the value for automatic turn 0 (total value = comparison of regexp + comparison of engines).';

$string['compareinvalidvalue'] = 'The value must be in the range from 0 to 100';
$string['invalidtssumvalue'] = 'Sum fractions of lines must be set to 100';
$string['invalidcomparets'] = 'Check value for the test string is set to 0, remove the test strings';

$string['syntaxtreehinttype_title'] = 'Syntax tree';
$string['syntaxtreehinttype'] = 'Syntax tree';
$string['syntaxtreehinttype_help'] = "<p>Value display hints as syntax tree.</p>";

$string['syntaxtreehintpenalty_title'] = 'Syntax tree: penalty';
$string['syntaxtreehintpenalty'] = 'Syntax tree: prnalty';
$string['syntaxtreehintpenalty_help'] = "<p>Meaning usage penalty hints as syntax tree</p>";

$string['explgraphhinttype_title'] = 'Count explanation';
$string['explgraphhinttype'] = 'Count explanation';
$string['explgraphhinttype_help'] = "<p>Value to display a tooltip as a graph explanation.</p>";

$string['explgraphhintpenalty_title'] = 'Count explanation: fine';
$string['explgraphhintpenalty'] = 'Count explanation: fine';
$string['explgraphhintpenalty_help'] = "<p>The amount of penalty for the use of tips as a graph explanation.</p>";

$string['descriptionhinttype_title'] = 'Explanation of the expression';
$string['descriptionhinttype'] = 'Explanation of the expression';
$string['descriptionhinttype_help'] = "<p>Display value in the form of tips explaining expression.</p>";

$string['descriptionhintpenalty_title'] = 'Explanation of the expression: fine';
$string['descriptionhintpenalty'] = 'Explanation of the expression: fine';
$string['descriptionhintpenalty_help'] = "<p>The amount of penalty for using hints as explanations of expression.</p>";

$string['teststringshinttype_title'] = 'Test string';
$string['teststringshinttype'] = 'Test string';
$string['teststringshinttype_help'] = "<p>Value display clues in the form of test strings.</p>";

$string['teststringshintpenalty_title'] = 'Test string: fine';
$string['teststringshintpenalty'] = 'Test string: fine';
$string['teststringshintpenalty_help'] = "<p>The amount of penalty for the use of clues in the form of test strings.</p>";

$string['compareregexpercentage_title'] = 'Based on regular expression match';
$string['compareregexpercentage'] = 'Based on regular expression match';
$string['compareregexpercentage_help'] = "<p>The value (in%) of the share estimates for regular expression matching.</p>";

$string['compareautomatapercentage_title'] = 'Rating coincidentally automata regular expressions';
$string['compareautomatapercentage'] = 'Rating coincidentally automata regular expressions';
$string['compareautomatapercentage_help'] = "<p>Value (in%) of the share estimates coincidentally automata regular expressions.</p>";

$string['compareregexpteststrings_title'] = 'Based on testing on the test lines of regular expressions';
$string['compareregexpteststrings'] = 'Based on testing on the test lines of regular expressions';
$string['compareregexpteststrings_help'] = "<p>Value (in%) of the share valuation verification test on the lines of regular expressions.</p>";