<?php
// This file is part of WriteRegex question type - https://bitbucket.org/oasychev/moodle-plugins
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

$string['answer'] = 'Show the correct answer';
$string['automataanalyzersheader'] = 'Finite automata analyzer';
$string['automataanalyzersoverflowforstudent'] = 'Can\'t check correctness of your answer. Call for teacher to solve this problem.';
$string['automataanalyzersoverflowforteacher'] = 'Checking equivalence of current answer with student\'s one by automata analyzer may cause long duration - limit of maximal count of groups pairs at single step of finite automata equivalence check algorithm overflowed. Ask your site administrator to change this limit.';
$string['automataequivalencecheckgroupspairlimit'] = 'Maximum number of groups pair for one wave while automata equivalence check';
$string['automataequivalencecheckgroupspairlimitdescription'] = 'Automata equivalence check function generates groups pairs for each iteration. When checking equivalence with subpatterns, pairs count may be big and rise exponential, so equivalence check will take a long time. This value limits count of simultaneously processed groups pairs.';
$string['automataequivalencecheckmismatcheslimit'] = 'Maximum number of mismatches from automata equivalence check';
$string['automataequivalencecheckmismatcheslimitdescription'] = 'Count of mismatches, founded by automata equivalence check may be big. This value limit its count, after which equivalence check stops.';
$string['both'] = 'Show the student\'s answer and the correct answer (both)';
$string['compareautomataanalyzername'] = 'Automata analyzer';
$string['compareautomatapercentage'] = 'Rating coincidentally automata regular expressions';
$string['compareautomatapercentage_help'] = "<p>Value (in%) of the share estimates coincidentally automata regular expressions.</p>";
$string['compareinvalidvalue'] = 'The value must be in the range from 0 to 100';
$string['comparestringsanalyzername'] = 'Test strings analyzer';
$string['comparestringspercentage'] = 'Based on testing on the test lines of regular expressions';
$string['comparestringspercentage_help'] = "<p>Value (in%) of the share valuation verification test on the lines of regular expressions.</p>";
$string['comparesubpatterns'] = 'Yes, compare with subpatterns';
$string['comparetreepercentage'] = 'Based on regular expression match';
$string['comparetreepercentage_help'] = "<p>The value (in%) of the share estimates for regular expression matching.</p>";
$string['comparewithsubpatterns'] = 'Subpatterns support';
$string['comparewithsubpatterns_help'] = 'Necessity of subpatterns support in automata analyzer';
$string['comparewithoutsubpatterns'] = 'No, compare without subpatterns';
$string['descriptionhintpenalty'] = 'Explanation of the expression: penalty';
$string['descriptionhintpenalty_help'] = "<p>The amount of penalty for using hints as explanations of expression.</p>";
$string['descriptionhinttype'] = 'Explanation of the expression';
$string['descriptionhinttype_help'] = "<p>Display value in the form of tips explaining expression.</p>";
$string['doterror'] = 'Can\'t draw {$a->name} for regex #{$a->index}';
$string['explgraphhintpenalty'] = 'Explanation graph: penalty';
$string['explgraphhintpenalty_help'] = "<p>The amount of penalty for the use of tips as a graph explanation.</p>";
$string['explgraphhinttype'] = 'Explanation graph';
$string['explgraphhinttype_help'] = "<p>Value to display a tooltip as a graph explanation.</p>";
$string['filloutoneanswer'] = 'You must provide at least one possible answer. Answers left blank will not be used. \'*\' can be used as a wildcard to match any characters. The first matching answer will be used to determine the score and feedback.';
$string['hintdescriptionstudentsanswer'] = "Your answer";
$string['hintdescriptionteachersanswer'] = "Correct answer";
$string['hintexplanation'] = '{$a->type} {$a->mode}:';
$string['hintsheader'] = 'Hints';
$string['hinttitleaddition'] = '({$a})';
$string['hinttitleadditionformode_1'] = 'for your answer';
$string['hinttitleadditionformode_2'] = 'for correct answer';
$string['hinttitleadditionformode_3'] = 'for your and correct answers';
$string['invalidcomparets'] = 'Check value for the test string is set to 0, remove the test strings';
$string['invalidmatchingtypessumvalue'] = 'Sum of all matching types is not equal 100%';
$string['invalidmismatchshowncount'] = 'Mismatches shown count can\'t be negative';
$string['invalidmismatchpenalty'] = 'Mismatch penalty can\'t be negative or higher, than maximal mark for question';
$string['invalidtssumvalue'] = 'Sum fractions of lines must be set to 100';
$string['mismatchedteststrings'] = 'Mismatched test strings:';
$string['mismatchesshowncount'] = 'Count of mismatches to show';
$string['mismatchesshowncount_help'] = 'Max count of mismatches from automata compare algorithm to show for student';
$string['moremismatches'] = 'And also mismatches: {$a}';
$string['none'] = 'None';
$string['notenoughanswers'] = 'This type of question requires at least {$a} answers';
$string['noteststringsforhint'] = 'There are no test strings for hint';
$string['penalty'] = 'Penalty';
$string['pleaseenterananswer'] = 'Please enter an answer.';
$string['pluginname'] = 'Write RegEx';
$string['pluginname_help'] = 'In response to a question (that may include a image) the respondent types a word or short phrase. There may be several possible correct answers, each with a different grade. If the "Case sensitive" option is selected, then you can have different scores for "Word" or "word".';
$string['pluginname_link'] = 'question/type/writeregex';
$string['pluginnameadding'] = 'Adding a Write RegEx question';
$string['pluginnameediting'] = 'Editing a Write RegEx question';
$string['pluginnamesummary'] = 'Question to monitor student\'s knowledge of compiling regular expressions (regexp).';
$string['regexp_answers'] = 'Regular expression {no}';
$string['regexp_ts'] = 'Test string {no}';
$string['regexp_ts_header'] = 'Test strings';
$string['showmismatchedteststrings'] = 'Show mismatched test strings';
$string['showmismatchedteststrings_help'] = 'Will test strings, which haven\'t match the correct answer, be shown to student as results of checking, or not';
$string['showmismatchedteststringsfalse'] = 'No, don\'t show';
$string['showmismatchedteststringstrue'] = 'Yes, show';
$string['stringmismatchpenalty'] = 'Single string mismatch penalty';
$string['stringmismatchpenalty_help'] = 'Penalty, which will be given for answer for each character, final state or assertion mismatch from automata analyzer';
$string['student'] = 'Show the student\'s answer';
$string['subpatternmismatchpenalty'] = 'Single subpattern mismatch penalty';
$string['subpatternmismatchpenalty_help'] = 'Penalty, which will be given for answer for each subpattern mismatch from automata analyzer';
$string['syntaxtreehintpenalty'] = 'Syntax tree: penalty';
$string['syntaxtreehintpenalty_help'] = "<p>Meaning usage penalty hints as syntax tree</p>";
$string['syntaxtreehinttype'] = 'Syntax tree';
$string['syntaxtreehinttype_help'] = "<p>Value display hints as syntax tree.</p>";
$string['teststringshintexplanation'] = 'Test strings match results {$a}:';
$string['teststringshintpenalty'] = 'Test string: penalty';
$string['teststringshintpenalty_help'] = "<p>The amount of penalty for the use of clues in the form of test strings.</p>";
$string['teststringshinttype'] = 'Test string';
$string['teststringshinttype_help'] = "<p>Value display clues in the form of test strings.</p>";
$string['teststringsmatchedcount'] = 'Matched test strings count: {$a->matchedcount}/{$a->count}.';
$string['unavailableautomataanalyzer'] = 'You can\'t use automata analyzer with this engine';

$string['extracharactermismatchfrombeginning'] = 'Your answer accepts character \'{$a->character}\' at the beginning while the correct one doesn\'t';
$string['missingcharactermismatchfrombeginning'] = 'Your answer doesn\'t accept character \'{$a->character}\'  at the beginning while the correct one does';
$string['extracharactermismatch'] = 'Your answer accepts character \'{$a->character}\' after matching the string \'{$a->matchedstring}\' while the correct one doesn\'t';
$string['missingcharactermismatch'] = 'Your answer doesn\'t accept character \'{$a->character}\' after matching the string \'{$a->matchedstring}\' while the correct one does';
$string['extrafinalstatemismatch'] = 'Your answer accepts the string \'{$a}\' while the correct one doesn\'t';
$string['missingfinalstatemismatch'] = 'Your answer doesn\'t accept the string \'{$a}\' while the correct one does';
$string['extraassertionmismatchfrombeginning'] = 'Your answer contains assertion \'{$a->assert}\' at the beginning while the correct one doesn\'t';
$string['missingassertionmismatchfrombeginning'] = 'Your answer doesn\'t contain assertion \'{$a->assert}\'  at the beginning while the correct one does';
$string['extraassertionmismatch'] = 'Your answer contains assertion \'{$a->assert}\' after matching the string \'{$a->matchedstring}\' while the correct one doesn\'t';
$string['missingassertionmismatch'] = 'Your answer doesn\'t contain assertion \'{$a->assert}\' after matching the string \'{$a->matchedstring}\' while the correct one does';
$string['singlesubpatternmismatch'] = 'Subpattern #{$a->subpatterns} {$a->behavior} in {$a->matchedanswer} after matching character \'{$a->character}\' {$a->place} while in {$a->mismatchedanswer} it doesn\'t';
$string['multiplesubpatternsmismatch'] = 'Subpatterns #{$a->subpatterns} {$a->behavior} in {$a->matchedanswer} after matching character \'{$a->character}\' {$a->place} while in {$a->mismatchedanswer} they don\'t';
$string['nosubpatternmismatch'] = '{$a->matchedanswer} accepts character \'{$a->character}\' {$a->place} without any subpattern while {$a->mismatchedanswer} doesn\'t';
$string['starts'] = 'starts';
$string['ends'] = 'ends';
$string['subpattern'] = 'subpattern';
$string['subpatterns'] = 'subpatterns';
$string['youranswer'] = 'your answer';
$string['correctanswer'] = 'the correct answer';
$string['diffplacesubpatternmismatch'] = 'subpattern №{$a->subpattern} {$a->behavior} in your answer after matching substring {$a->studentmatchedstring}, while in the correct one it {$a->behavior} after matching substring {$a->correctmatchedstring}';
$string['singleuniquesubpatternmismatch'] = 'subpattern №{$a->subpatterns} matches in {$a->matchedanswer}, while in {$a->mismatchedanswer} it doesn\'t';
$string['multipleuniquesubpatternmismatch'] = 'subpatterns №{$a->subpatterns} match in {$a->matchedanswer}, while in {$a->mismatchedanswer} they don\'t';
$string['singlesubpatternmismatchcommonpartwithouttags'] = 'Your answer accepts string {$a->matchedstring} without common subpatterns, when ';
$string['multiplesubpatternmismatchcommonpartwithouttags'] = 'Your answer accepts string {$a->matchedstring} without common subpatterns, when:';
$string['singlesubpatternmismatchcommonpart'] = 'Your answer accepts string {$a->matchedstring} with matching {$a->subpatternword} №{$a->matchedsubpatterns}, when ';
$string['multiplesubpatternmismatchcommonpart'] = 'Your answer accepts string {$a->matchedstring} with matching {$a->subpatternword} №{$a->matchedsubpatterns}, when:';