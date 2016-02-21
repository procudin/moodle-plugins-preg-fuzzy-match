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

namespace qtype_writeregex;

global $CFG;
require_once($CFG->dirroot . '/question/type/preg/fa_matcher/fa_matcher.php');

/**
 * Class analyser fot compare regex by automata.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compare_automata_analyzer {

    const MISMATCH_PENALTY = 0.2;

    /** @var  object Question object. */
    protected $question;

    /**
     * Init analyzer object.
     * @param $question object Question object.
     */
    public function __construct($question) {

    }

    /**
     * Get equality for user response.
     * @param $answer string Regex answer.
     * @param $respose string User response.
     * @return float Value of compare.
     */
    public function get_fitness ($answer, $response)
    {
        $answermatcher = new \qtype_preg_fa_matcher($answer);
        $answerautomaton = $answermatcher->automaton;
        $responsematcher = new \qtype_preg_fa_matcher($response);
        $responseautomaton = $responsematcher->automaton;

        $differences = array();
        $fitness = 1;

        if (!$answerautomaton->equal($responseautomaton, $differences)) {
            $fitness = max(0, 1 - count($differences) * compare_automata_analyzer::MISMATCH_PENALTY);
        }

        return $fitness;
    }
}