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
require_once($CFG->dirroot . '/question/type/preg/question.php');

/**
 * Class analyser fot compare regex by automata.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compare_automata_analyzer extends analyzer {

    /**
     * Get equality for user response.
     * @param $answer string Regex answer.
     * @param $response string User response.
     * @return compare_automata_analyzer_result Result of compare.
     */
    public function analyze ($answer, $response)
    {
        global $CFG;
        $CFG->qtype_preg_assertfailmode = true;

        $pregquestionstd = new \qtype_preg_question();
        $matchingoptions = $pregquestionstd->get_matching_options(false, $pregquestionstd->get_modifiers($this->question->usecase), null, $this->question->notation);
        $matchingoptions->extensionneeded = false;
        $matchingoptions->capturesubexpressions = true;

        $answermatcher = $pregquestionstd->get_matcher($this->question->engine, $answer, $matchingoptions);
        $answerautomaton = $answermatcher->automaton;

        $responsematcher = $pregquestionstd->get_matcher($this->question->engine, $response, $matchingoptions);
        $responseautomaton = $responsematcher->automaton;

        $differences = array();
        $fitness = 1;

        // If the was syntax error in response automaton.
        if ($responseautomaton == null) {
            $result = new compare_automata_analyzer_result();
            $result->fitness = 0;
            $result->differences = $differences;
            return $result;
        }
        try {
            if (!$answerautomaton->equal($responseautomaton, $differences, ($this->question->comparewithsubpatterns == 1))) {
                foreach ($differences as $difference) {
                    if ($difference->type == \qtype_preg\fa\equivalence\mismatched_pair::SUBPATTERN) {
                        $fitness = max(0, $fitness - $this->question->subpatternmismatchpenalty);
                    } else {
                        $fitness = max(0, $fitness - $this->question->stringmismatchpenalty);
                    }
                }
            }
        } catch (\moodle_exception $e) {
            throw $e;
        }

        // Generate result
        $result = new compare_automata_analyzer_result();
        $result->fitness = $fitness;
        $result->differences = $differences;
        $result->maxshowncount = $this->question->mismatchesshowncount;
        return $result;
    }

    /**
     * Get analyzer name
     * @return analyzer name, understandable for user
     */
    public function name()
    {
        return get_string('compareautomataanalyzername', 'qtype_writeregex');
    }
}