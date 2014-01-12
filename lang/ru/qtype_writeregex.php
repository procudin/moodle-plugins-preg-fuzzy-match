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
$string['pluginnameadding'] = 'Добавить вопрос Write RegEx';
$string['pluginnameediting'] = 'Изменение вопроса Write RegEx';
$string['pluginnamesummary'] = 'Allows a response of one or a few words that is graded by comparing against various model answers, which may contain wildcards.';
$string['wre_notation'] = 'Нотация';

/* Notation of regexp. */
$string['wre_notation_simple'] = 'Простая';
$string['wre_notation_extended'] = 'Расширенная';
$string['wre_notation_moodle'] = 'Moodle Short Answer';

/* Syntax tree hint. */
$string['wre_st'] = 'Синтаксическое дерево';
$string['wre_st_penalty'] = 'Штраф';
$string['wre_st_none'] = 'Не показывать';
$string['wre_st_student'] = 'Демонстрация для ответа студента';
$string['wre_st_answer'] = 'Демонстрация для правильного ответа';
$string['wre_st_both'] = 'Демонстрация для ответа студента и правильного ответа (оба)';

/* Explaining graph hint. */
$string['wre_eg'] = 'Граф объяснения';
$string['wre_eg_penalty'] = 'Штраф';
$string['wre_eg_none'] = 'Не показывать';
$string['wre_eg_student'] = 'Демонстрация для ответа студента';
$string['wre_eg_answer'] = 'Демонстрация для правильного ответа';
$string['wre_eg_both'] = 'Демонстрация для ответа студента и правильного ответа (оба)';

/* Description hint. */
$string['wre_d'] = 'Объяснение выражения';
$string['wre_d_penalty'] = 'Штраф';
$string['wre_d_none'] = 'Не показывать';
$string['wre_d_student'] = 'Демонстрация для ответа студента';
$string['wre_d_answer'] = 'Демонстрация для правильного ответа';
$string['wre_d_both'] = 'Демонстрация для ответа студента и правильного ответа (оба)';

/* Test string hint. */
$string['teststrings'] = 'Тестовые строки';
$string['penalty'] = 'Штраф';
$string['none'] = 'Не показывать';
$string['student'] = 'Демонстрация для ответа студента';
$string['answer'] = 'Демонстрация для правильного ответа';
$string['both'] = 'Демонстрация для ответа студента и правильного ответа (оба)';

/* Compare regex. */
$string['wre_cre'] = 'Проверка совпадения регулярных выражений';
$string['wre_cre_no'] = 'Да';
$string['wre_cre_yes'] = 'Нет';
$string['wre_cre_percentage'] = 'Проверка совпадения регулярных выражений (в %) по строкам';

/* Compare regexp's automats */
$string['wre_acre'] = 'Проверка совпадения конечных автоматов регулярных выражений';
$string['wre_acre_percentage'] = 'Проверка совпадения конечных автоматов регулярных выражений в %';
$string['compareautomatapercentage'] = 'Проверка совпадения регулярных выражений (в %) по автоматам';

/* Compare regexps by test strings */
$string['compareregexpteststrings'] = 'Проверка совпадения регулярных выражений (в %) по тестовым строкам';

$string['wre_regexp_answers'] = 'Регулярное выражение';
$string['wre_regexp_ts'] = 'Тестовая строка';

/* Ошибка суммы типов проверок. */
$string['wre_error_matching'] = 'Сумма всех типов проверок не равна 100%';

/* Ошибки ответов regexp. */
$string['wre_regexp_answers_count'] = 'Должен быть хотя бы один ответ';
$string['wre_regexp_fractions_count'] = 'Хотя бы один из ответов должен иметь оценку 100%';

/* Ошибки ответов тестовых строк. */
$string['wre_ts_answers_count'] = 'Должен быть хотя бы один ответ';
$string['wre_ts_fractions_count'] = 'Хотя бы один из ответов должен иметь оценку 100%';

/* Справочные кнопки. */
$string['compare'] = 'Вы можете указать вес проверки по строкам и по автоматам';
$string['compare_title'] = 'Вы можете указать вес проверки по строкам и по автоматам';
$string['compare_help'] = '<p>Сравнение осуществляется непосредственно по строкам и автоматам. Если вы задали значение для
 строк 1, то значение для автоматов получится 0 (общее значение = сравнение по строкам  + сравнение по автоматам).</p>';