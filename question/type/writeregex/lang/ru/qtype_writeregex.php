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
 * Strings for component 'qtype_writeregex', language 'ru'
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
$string['doterror'] = 'Невозможно отрисовать {$a->name} для регулярного выражения №{$a->index}';
$string['filloutoneanswer'] = 'You must provide at least one possible answer. Answers left blank will not be used. \'*\' can be used as a wildcard to match any characters. The first matching answer will be used to determine the score and feedback.';
$string['notenoughanswers'] = 'This type of question requires at least {$a} answers';
$string['pleaseenterananswer'] = 'Please enter an answer.';
$string['pluginname'] = 'Write RegEx';
$string['pluginname_help'] = 'In response to a question (that may include a image) the respondent types a word or short phrase. There may be several possible correct answers, each with a different grade. If the "Case sensitive" option is selected, then you can have different scores for "Word" or "word".';
$string['pluginname_link'] = 'question/type/writeregex';
$string['pluginnameadding'] = 'Добавить вопрос Write RegEx';
$string['pluginnameediting'] = 'Изменение вопроса Write RegEx';
$string['pluginnamesummary'] = 'Вопрос для контроля знаний студентов по составлению регулярных выражений (regexp).';
$string['wre_notation'] = 'Нотация';

$string['wre_notation_simple'] = 'Простая';
$string['wre_notation_extended'] = 'Расширенная';
$string['wre_notation_moodle'] = 'Moodle Short Answer';

$string['wre_hintsheader'] = 'Подсказки';

$string['wre_st'] = 'Синтаксическое дерево';
$string['wre_st_penalty'] = 'Штраф';
$string['wre_st_none'] = 'Не показывать';
$string['wre_st_student'] = 'Демонстрация для ответа студента';
$string['wre_st_answer'] = 'Демонстрация для правильного ответа';
$string['wre_st_both'] = 'Демонстрация для ответа студента и правильного ответа (оба)';

$string['wre_eg'] = 'Граф объяснения';
$string['wre_eg_penalty'] = 'Штраф';
$string['wre_eg_none'] = 'Не показывать';
$string['wre_eg_student'] = 'Демонстрация для ответа студента';
$string['wre_eg_answer'] = 'Демонстрация для правильного ответа';
$string['wre_eg_both'] = 'Демонстрация для ответа студента и правильного ответа (оба)';

$string['wre_d'] = 'Объяснение выражения';
$string['wre_d_penalty'] = 'Штраф';
$string['wre_d_none'] = 'Не показывать';
$string['wre_d_student'] = 'Демонстрация для ответа студента';
$string['wre_d_answer'] = 'Демонстрация для правильного ответа';
$string['wre_d_both'] = 'Демонстрация для ответа студента и правильного ответа (оба)';

$string['teststrings'] = 'Тестовые строки';
$string['penalty'] = 'Штраф';
$string['none'] = 'Не показывать';
$string['student'] = 'Демонстрация для ответа студента';
$string['answer'] = 'Демонстрация для правильного ответа';
$string['both'] = 'Демонстрация для ответа студента и правильного ответа (оба)';

$string['wre_cre'] = 'Проверка совпадения регулярных выражений';
$string['wre_cre_no'] = 'Да';
$string['wre_cre_yes'] = 'Нет';
$string['wre_cre_percentage'] = 'Проверка совпадения регулярных выражений (в %) по дереву';

$string['wre_acre'] = 'Проверка совпадения конечных автоматов регулярных выражений';
$string['wre_acre_percentage'] = 'Проверка совпадения конечных автоматов регулярных выражений в %';
$string['compareautomatapercentage'] = 'Проверка совпадения регулярных выражений (в %) по автоматам';

$string['compareregexpteststrings'] = 'Проверка совпадения регулярных выражений (в %) по тестовым строкам';

$string['wre_regexp_answers'] = "Регулярное\nвыражение {no}";
$string['wre_regexp_ts'] = 'Тестовая строка {no}';
$string['wre_regexp_ts_header'] = 'Тестовые строки';

$string['wre_error_matching'] = 'Сумма всех типов проверок не равна 100%';

$string['wre_regexp_answers_count'] = 'Должен быть хотя бы один ответ';
$string['wre_regexp_fractions_count'] = 'Хотя бы один из ответов должен иметь оценку 100%';

$string['wre_ts_answers_count'] = 'Должен быть хотя бы один ответ';
$string['wre_ts_fractions_count'] = 'Хотя бы один из ответов должен иметь оценку 100%';

$string['compare'] = 'Вы можете указать вес проверки по строкам и по автоматам';
$string['compare_title'] = 'Вы можете указать вес проверки по строкам и по автоматам';
$string['compare_help'] = '<p>Сравнение осуществляется непосредственно по строкам и автоматам. Если вы задали значение для строк 1, то значение для автоматов получится 0 (общее значение = сравнение по строкам  + сравнение по автоматам).</p>';

$string['compareinvalidvalue'] = 'Значение должно быть в диапазоне от 0 до 100';
$string['invalidtssumvalue'] = 'Сумма оценок строк должна иметь значение 100, т. к. высталена проверка по ним';
$string['invalidcomparets'] = 'Значение проверки по тестовым строкам выставлена в 0, удалите тестовые строки';

$string['hintdescriptionstudentsanswer'] = "Ваш ответ";
$string['hintdescriptionteachersanswer'] = "Правильный ответ";
$string['hinttitleaddition'] = '(для {$a})';
$string['hinttitleadditionformode_1'] = 'Вашего ответа';
$string['hinttitleadditionformode_2'] = 'правильного ответа';
$string['hinttitleadditionformode_3'] = 'Вашего и правильного ответов';

$string['syntaxtreehinttype_title'] = 'Синтаксическое дерево';
$string['syntaxtreehinttype'] = 'Синтаксическое дерево';
$string['syntaxtreehinttype_help'] = "<p>Значение отображения подсказки в виде синтаксического дерева</p>";

$string['syntaxtreehintpenalty_title'] = 'Синтаксическое дерево: штраф';
$string['syntaxtreehintpenalty'] = 'Синтаксическое дерево: штраф';
$string['syntaxtreehintpenalty_help'] = "<p>Значение штрафа за использование подсказки в виде синтаксического дерева</p>";
$string['syntaxtreehintexplanation'] = 'Синтаксическое дерево {$a}:';

$string['explgraphhinttype_title'] = 'Граф объяснения';
$string['explgraphhinttype'] = 'Граф объяснения';
$string['explgraphhinttype_help'] = "<p>Значение отображения подсказки в виде графа объяснения.</p>";

$string['explgraphhintpenalty_title'] = 'Граф объяснения: штраф';
$string['explgraphhintpenalty'] = 'Граф объяснения: штраф';
$string['explgraphhintpenalty_help'] = "<p>Величина штрафа за использование подсказки в виде графа объяснения.</p>";
$string['explgraphhintexplanation'] = 'Граф объяснения {$a}:';

$string['descriptionhinttype_title'] = 'Объяснение выражения';
$string['descriptionhinttype'] = 'Объяснение выражения';
$string['descriptionhinttype_help'] = "<p>Значение отображения подсказки в виде объяснения выражения.</p>";

$string['descriptionhintpenalty_title'] = 'Объяснение выражения: штраф';
$string['descriptionhintpenalty'] = 'Объяснение выражения: штраф';
$string['descriptionhintpenalty_help'] = "<p>Величина штрафа за использование подсказки в виде объяснения выражения.</p>";
$string['descriptionhintexplanation'] = 'Описание {$a}:';

$string['teststringshinttype_title'] = 'Тестовые строки';
$string['teststringshinttype'] = 'Тестовые строки';
$string['teststringshinttype_help'] = "<p>Значение отображения подсказки в виде тестовых строк.</p>";
$string['teststringshintexplanation'] = 'Результаты совпадения {$a} с тестовыми строками:';

$string['teststringshintpenalty_title'] = 'Тестовые строки: штраф';
$string['teststringshintpenalty'] = 'Тестовые строки: штраф';
$string['teststringshintpenalty_help'] = "<p>Величина штрафа за использование подсказки в виде тестовых строк.</p>";

$string['comparetreepercentage_title'] = 'Оценка по совпадению регулярных выражений';
$string['comparetreepercentage'] = 'Оценка по свопадению регулярных выражений';
$string['comparetreepercentage_help'] = "<p>Значение (в %) доли оценки по совпадению регулярных выражений .</p>";

$string['compareautomatapercentage_title'] = 'Оценка по совпадению автоматов регулярных выражений';
$string['compareautomatapercentage'] = 'Оценка по свопадению автоматов регулярных выражений';
$string['compareautomatapercentage_help'] = "<p>Значение (в %) доли оценки по совпадению автоматов регулярных выражений .</p>";

$string['comparestringspercentage_title'] = 'Оценка по проверке на тестовых строках регулярных выражений';
$string['comparestringspercentage'] = 'Оценка по проверке на тестовых строках регулярных выражений';
$string['comparestringspercentage_help'] = "<p>Значение (в %) доли оценки по проверке на тестовых строках регулярных выражений .</p>";