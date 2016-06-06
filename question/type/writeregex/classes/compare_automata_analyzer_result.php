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
                case \qtype_preg\fa\equivalence\mismatched_pair::ASSERT:
                    $feedback .= $this->get_assertion_mismatch_feedback($difference, $renderer);
                    break;
                case \qtype_preg\fa\equivalence\mismatched_pair::FINAL_STATE:
                    $feedback .= $this->get_final_state_mismatch_feedback($difference, $renderer);
                    break;
                case \qtype_preg\fa\equivalence\mismatched_pair::SUBPATTERN:
                    $feedback .= $this->get_subpattern_mismatch_feedback($difference, $renderer);
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
        $matchedstring = $difference->matched_string();
        // Substring without last character of matched string in difference is the matched string of both automata.
        $a->matchedstring = substr($matchedstring, 0, strlen($matchedstring) - 1);
        // Last character of matched string in difference is mismatch character.
        $a->character = $matchedstring[strlen($matchedstring) - 1];
        // Get titles for string description of mismatch
        $studentstitle = get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ': ';
        $teachersstitle = get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ': ';
        // If students answer accepts extra character.
        if ($difference->matchedautomaton == 1) {
            if (strlen($a->matchedstring) == 0) {
                $feedback = get_string('extracharactermismatchfrombeginning', 'qtype_writeregex', $a);
            } else {
                $feedback = get_string('extracharactermismatch', 'qtype_writeregex', $a);
            }
            $feedback .= $this->get_matching_string_explanation($renderer, $studentstitle, $matchedstring);
            $feedback .= $this->get_matching_string_explanation($renderer, $teachersstitle, $a->matchedstring, $a->character);
        }
        // If students answer doesn't accept character.
        else {
            if (strlen($a->matchedstring) == 0) {
                $feedback = get_string('missingcharactermismatchfrombeginning', 'qtype_writeregex', $a);
            } else {
                $feedback = get_string('missingcharactermismatch', 'qtype_writeregex', $a);
            }
            $feedback .= $this->get_matching_string_explanation($renderer, $studentstitle, $a->matchedstring, $a->character);
            $feedback .= $this->get_matching_string_explanation($renderer, $teachersstitle, $matchedstring);
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
        $matchedstring = $difference->matched_string();
        $a->matchedstring = $matchedstring;
        // Substring without last character of matched string in difference is the matched string of both automata.
        $a->bothmatchedstring = substr($matchedstring, 0, strlen($matchedstring) - 1);
        // Last character of matched string in difference which transport automaton to final state.
        $a->character = $matchedstring[strlen($matchedstring) - 1];
        // Get titles for string description of mismatch
        $studentstitle = get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ': ';
        $teachersstitle = get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ': ';
        // If students answer accepts extra string.
        if ($difference->matchedautomaton == 1) {
            $feedback = get_string('extrafinalstatemismatch', 'qtype_writeregex', $matchedstring);
            $feedback .= $this->get_matching_string_explanation($renderer, $studentstitle, $matchedstring);
            $feedback .= $this->get_matching_string_explanation($renderer, $teachersstitle, $matchedstring, '...');
        } // If students answer doesn't accept string.
        else {
            $feedback = get_string('missingfinalstatemismatch', 'qtype_writeregex', $matchedstring);
            $feedback .= $this->get_matching_string_explanation($renderer, $studentstitle, $matchedstring, '...');
            $feedback .= $this->get_matching_string_explanation($renderer, $teachersstitle, $matchedstring);
        }

        return $feedback;
    }

    /**
     * Get feedback for assertion mismatch.
     * @param qtype_writeregex_renderer renderer Renderer
     * @return string Feedback about assertion mismatch to show to student.
     */
    public function get_assertion_mismatch_feedback($difference, $renderer) {
        $assertions = array('qtype_preg_leaf_assert_esc_a' => '\A',
            'qtype_preg_leaf_assert_small_esc_z' => '\z',
            'qtype_preg_leaf_assert_capital_esc_z' => '\Z',
            'qtype_preg_leaf_assert_esc_g' => '\G',
            'qtype_preg_leaf_assert_circumflex' => '^',
            'qtype_preg_leaf_assert_dollar' => '$');
        $a = new \stdClass;
        $a->matchedstring = $difference->matched_string();
        // Mismatched assertion
        $a->assert = $assertions[$difference->mismatched_assertion()];
        // Get titles for string description of mismatch
        $studentstitle = get_string('hintdescriptionstudentsanswer', 'qtype_writeregex') . ': ';
        $teachersstitle = get_string('hintdescriptionteachersanswer', 'qtype_writeregex') . ': ';
        // If students answer accepts extra assertion.
        if ($difference->matchedautomaton == 1) {
            if (strlen($a->matchedstring) == 0) {
                $feedback = get_string('extraassertionmismatchfrombeginning', 'qtype_writeregex', $a);
            } else {
                $feedback = get_string('extraassertionmismatch', 'qtype_writeregex', $a);
            }
        }
        // If students answer doesn't accept assertion.
        else {
            if (strlen($a->matchedstring) == 0) {
                $feedback = get_string('missingassertionmismatchfrombeginning', 'qtype_writeregex', $a);
            } else {
                $feedback = get_string('missingassertionmismatch', 'qtype_writeregex', $a);
            }
        }

        return $feedback;
    }

    /**
     * Get feedback for subpattern mismatch.
     * @param qtype_writeregex_renderer renderer Renderer
     * @return string Feedback about subpattern mismatch to show to student.
     */
    public function get_subpattern_mismatch_feedback($difference, $renderer) {
        $a = new \stdClass();
        $singlemismatch = count($difference->diffpositionsubpatterns) + count($difference->uniquesubpatterns[0])
            + count($difference->uniquesubpatterns[1]) == 1;
        // Matched string
        $a->matchedstring = $difference->matched_string();
        // Subpattern word
        if (count($difference->matchedsubpatterns) == 1) {
            $a->subpatternword = get_string('subpattern', 'qtype_writeregex');
        }
        else {
            $a->subpatternword = get_string('subpatterns', 'qtype_writeregex');
        }
        // Matched subpatterns
        $a->matchedsubpatterns = $this->enumeration_from_array($difference->matchedsubpatterns);

        // Title for matched part
        if (count($difference->matchedsubpatterns) == 0) {
            if ($singlemismatch) {
                $feedback = get_string('singlesubpatternmismatchcommonpartwithouttags', 'qtype_writeregex', $a);
            }
            else {
                $feedback = get_string('multiplesubpatternmismatchcommonpartwithouttags', 'qtype_writeregex', $a);
            }
        }
        else {
            if ($singlemismatch) {
                $feedback = get_string('singlesubpatternmismatchcommonpart', 'qtype_writeregex', $a);
            } else {
                $feedback = get_string('multiplesubpatternmismatchcommonpart', 'qtype_writeregex', $a);
            }
        }

        // Information about different place subpattern mismatches
        foreach ($difference->diffpositionsubpatterns as $mismatch) {
            if (!$singlemismatch) {
                $feedback = $renderer->add_break($feedback) . '  - ';
            }
            $a = new \stdClass();
            $a->subpattern = $mismatch['subexpression'];
            if ($mismatch['isopen']) {
                $a->behavior = get_string('starts', 'qtype_writeregex');
            }
            else {
                $a->behavior = get_string('ends', 'qtype_writeregex');
            }
            $a->studentmatchedstring = $mismatch['secondmatchedstring'];
            $a->correctmatchedstring = $mismatch['firstmatchedstring'];

            $feedback .= get_string('diffplacesubpatternmismatch', 'qtype_writeregex', $a);
        }

        // Information about unique subpatterns mimsatches
        if (count($difference->uniquesubpatterns[0]) > 0) {
            if (!$singlemismatch) {
                $feedback = $renderer->add_break($feedback) . '  - ';
            }
            $a = new \stdClass();
            $a->mismatchedanswer = get_string('youranswer', 'qtype_writeregex');
            $a->matchedanswer = get_string('correctanswer', 'qtype_writeregex');
            $a->subpatterns = $this->enumeration_from_array($difference->uniquesubpatterns[0]);
            if (count($difference->uniquesubpatterns[0]) == 1) {
                $feedback .= get_string('singleuniquesubpatternmismatch', 'qtype_writeregex', $a);
            }
            else {
                $feedback .= get_string('multipleuniquesubpatternmismatch', 'qtype_writeregex', $a);
            }
        }
        if (count($difference->uniquesubpatterns[1]) > 0) {
            if (!$singlemismatch) {
                $feedback = $renderer->add_break($feedback) . '  - ';
            }
            $a = new \stdClass();
            $a->matchedanswer = get_string('youranswer', 'qtype_writeregex');
            $a->mismatchedanswer = get_string('correctanswer', 'qtype_writeregex');
            $a->subpatterns = $this->enumeration_from_array($difference->uniquesubpatterns[1]);
            if (count($difference->uniquesubpatterns[1]) == 1) {
                $feedback .= get_string('singleuniquesubpatternmismatch', 'qtype_writeregex', $a);
            }
            else {
                $feedback .= get_string('multipleuniquesubpatternmismatch', 'qtype_writeregex', $a);
            }
        }
        return $feedback;
    }

    /**
     * Generates enumeration of array of integers
     * @param $arr array of integers
     * @return string enumeration
     */
    private function enumeration_from_array($arr) {
        $res = "";
        for ($i = 0; $i < count($arr); $i++) {
            $res .= $arr[$i];
            if ($i != count($arr) - 1) {
                $res .= ',';
            }
        }
        return $res;
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
        if (strlen($mismatched) > 0)
            $result .= $renderer->render_automaton_matched_string($mismatched, false);
        $result = $renderer->add_span($result);
        return $renderer->render_automaton_matched_string_with_author($author, $result);
    }
}