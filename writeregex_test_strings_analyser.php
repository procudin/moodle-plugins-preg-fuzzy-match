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
 * Class analyser fot test strings.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class test_strings_analyser {

    /** @var  object Question object. */
    protected $question;

    /**
     * Init analyzer object.
     * @param $question object Question object.
     */
    public function __construct($question) {
        $this->question = $question;
    }

    /**
     * Get equality for user response.
     * @param $answer string Regex answer.
     * @param $response string User response.
     * @return float Value of compare.
     */
    public function get_equality($answer, $response) {

        $totalfraction = 0;

        $pregquestionstd = new qtype_preg_question();
        $matcherstd = $pregquestionstd->get_matcher($this->question->engine, $answer, false,
            $pregquestionstd->get_modifiers($this->question->usecase), 0, $this->question->notation);

        $pregquestiont = new qtype_preg_question();
        $matchert = $pregquestiont->get_matcher($this->question->engine, $response, false,
            $pregquestiont->get_modifiers($this->question->usecase), 0, $this->question->notation);

        foreach ($this->question->answers as $item) {

            if (!$matcherstd->errors_exist() and !$matchert->errors_exist() and $item->feedbackformat == 0) {
                $resulltstd = $matcherstd->match($item->answer);
                $resulltt = $matchert->match($item->answer);

                if ($resulltstd->indexfirst == $resulltt->indexfirst and
                    $resulltstd->length == $resulltt->length) {
                    $totalfraction += $item->fraction;
                }
            }
        }

        return $totalfraction;
    }

}