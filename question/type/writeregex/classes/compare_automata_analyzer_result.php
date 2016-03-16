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

/**
 * Result of comparing regexes by automata.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Kamo Spertsian <spertsiankamo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compare_automata_analyzer_result extends analyzer_result {

    /** @var array of qtype_preg\fa\equivalence\mismatched_pair Differences of compared automata */
    public $differences;

    /**
     * Get feedback for analyzing results.
     * @param qtype_writeregex_renderer renderer Renderer
     * @return string Feedback about mismatches to show to student.
     */
    public function get_feedback($renderer) {
        $feedback = "";
        foreach ($this->differences as $difference) {
            switch ($difference->type) {
                case \qtype_preg\fa\equivalence\mismatched_pair::CHARACTER:
                    $feedback .= $this->get_character_mismatch_feedback($difference, $renderer);
                    break;
                case \qtype_preg\fa\equivalence\mismatched_pair::FINAL_STATE:
                    $feedback .= $this->get_final_state_mismatch_feedback($difference, $renderer);
                    break;
            }
        }

        return $feedback;
    }

    /**
     * Get feedback for character mismatch.
     * @param qtype_writeregex_renderer renderer Renderer
     * @return string Feedback about character mismatch to show to student.
     */
    public function get_character_mismatch_feedback($difference, $renderer) {
        $a = new \stdClass;
        // Substing without last character of matched string in difference is the matched string of both automata.
        $a->matchedstring = substr($difference->matchedstring, 0, strlen($difference->matchedstring) - 1);
        // Last character of matched string in difference is mismatch character.
        $a->character = $difference->matchedstring[strlen($difference->matchedstring) - 1];
        // Get titles for string description of mismatch
        $studentstitle = get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ': ';
        $teachersstitle = get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ': ';
        // If students answer accepts extra character.
        if ($difference->matchedautomaton == 1) {
            if (empty($a->matchedstring)) {
                $feedback = get_string('extracharactermismatchfrombeginning', 'qtype_writeregex', $a);
            } else {
                $feedback = get_string('extracharactermismatch', 'qtype_writeregex', $a);
            }
            $feedback .= $this->get_matching_string_explanation($renderer, $studentstitle, $difference->matchedstring);
            $feedback .= $this->get_matching_string_explanation($renderer, $teachersstitle, $a->matchedstring, $a->character);
        }
        // If students answer doesn't accept character.
        else {
            if (empty($a->matchedstring)) {
                $feedback = get_string('missingcharactermismatchfrombeginning', 'qtype_writeregex', $a);
            } else {
                $feedback = get_string('missingcharactermismatch', 'qtype_writeregex', $a);
            }
            $feedback .= $this->get_matching_string_explanation($renderer, $studentstitle, $a->matchedstring, $a->character);
            $feedback .= $this->get_matching_string_explanation($renderer, $teachersstitle, $difference->matchedstring);
        }


        return $feedback;
    }

    /**
     * Get feedback for final state mismatch.
     * @param qtype_writeregex_renderer renderer Renderer
     * @return string Feedback about final state mismatch to show to student.
     */
    public function get_final_state_mismatch_feedback($difference, $renderer) {
        $a = new \stdClass;
        $a->matchedstring = $difference->matchedstring;
        // Substing without last character of matched string in difference is the matched string of both automata.
        $a->bothmatchedstring = substr($difference->matchedstring, 0, strlen($difference->matchedstring) - 1);
        // Last character of matched string in difference which transport automaton to final state.
        $a->character = $difference->matchedstring[strlen($difference->matchedstring) - 1];
        // Get titles for string description of mismatch
        $studentstitle = get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ': ';
        $teachersstitle = get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ': ';
        // If students answer accepts extra string.
        if ($difference->matchedautomaton == 1) {
            $feedback = get_string('extrafinalstatemismatch', 'qtype_writeregex', $difference->matchedstring);
            $feedback .= $this->get_matching_string_explanation($renderer, $studentstitle, $difference->matchedstring);
            $feedback .= $this->get_matching_string_explanation($renderer, $teachersstitle, $difference->matchedstring, '...');
        } // If students answer doesn't accept string.
        else {
            $feedback = get_string('missingfinalstatemismatch', 'qtype_writeregex', $difference->matchedstring);
            $feedback .= $this->get_matching_string_explanation($renderer, $studentstitle, $difference->matchedstring, '...');
            $feedback .= $this->get_matching_string_explanation($renderer, $teachersstitle, $difference->matchedstring);
        }

        return $feedback;
    }

    /**
     * Get line description about matching string to authors automaton
     * @param qtype_writeregex_renderer renderer Renderer
     * @param string author Author of answer
     * @param string matched Matched part of string
     * @param string mismatched Mismatched part of string
     */
    public function get_matching_string_explanation($renderer, $author, $matched, $mismatched = '') {
        $result = $renderer->render_automaton_matched_string($matched, true);
        if (!empty($mismatched))
            $result .= $renderer->render_automaton_matched_string($mismatched, false);
        $result = $renderer->add_span($result);
        return $renderer->render_automaton_matched_string_with_author($author, $result);
    }
}