<?php
// This file is part of Preg question type - https://code.google.com/p/oasychev-moodle-plugins/
//
// Preg question type is free software: you can redistribute it and/or modify
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
 * Defines FA matcher execution state.
 *
 * @package    qtype_preg
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Valeriy Streltsov <vostreltsov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/preg/fa_matcher/fa_nodes.php');

class qtype_preg_fa_stack_item {

    // The corresponding fa state.
    public $state;

    // Bitwise union of the qtype_preg_fa_exec_state flags.
    public $flags;

    // 2-dimensional array of matches; 1st is subpattern number; 2nd is repetitions of the subpattern.
    // Each subpattern is initialized with (-1,-1) at start.
    public $matches;

    // Array used mostly for disambiguation when there are duplicate subpexpressions numbers.
    // Keys are subexpr numbers, values are qtype_preg_node objects.
    public $subexpr_to_subpatt;

    // The last transition matched.
    public $last_transition;

    // Length of the last match.
    public $last_match_len;

    public function current_match($subpatt) {
        return isset($this->matches[$subpatt]) ? end($this->matches[$subpatt]) : null;
    }

    public function set_current_match($subpatt, $index, $length) {
        if (!array_key_exists($subpatt, $this->matches)) {
            return;
        }
        $count = count($this->matches[$subpatt]);
        $this->matches[$subpatt][$count - 1] = array($index, $length);
    }

    public function last_match($mode, $subpatt) {
        // POSIX mode
        if ($mode == qtype_preg_handling_options::MODE_POSIX) {
            $result = $this->current_match($subpatt);
            if ($result === null) {
                return null;
            }
            return qtype_preg_fa_exec_state::is_being_captured($result[0], $result[1]) ? qtype_preg_fa_exec_state::empty_subpatt_match()
                                                                                       : $result;
        }

        // PCRE mode
        if (!isset($this->matches[$subpatt])) {
            return null;
        }

        $matches = $this->matches[$subpatt];
        $count = count($matches);

        // It's a tricky thing. PCRE uses last successful match for situations like "(a|b\1)*" and string "ababbabbba".
        // So we need to iterate from the last to the first repetitions until a match found.
        for ($i = $count - 1; $i >= 0; $i--) {
            $cur = $matches[$i];
            if (qtype_preg_fa_exec_state::is_completely_captured($cur[0], $cur[1])) {
                return $cur;
            }
        }

        return qtype_preg_fa_exec_state::empty_subpatt_match();
    }

    public function has_duplicate_subexpression() {
        foreach ($this->subexpr_to_subpatt as $node) {
            if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR && $node->isduplicate) {
                return true;
            }
        }
        return false;
    }

    // TODO
    public function is_subexpr_match_started($subexpr) {
        if (!isset($this->subexpr_to_subpatt[$subexpr])) {
            return false;
        }
        $current = $this->current_match($this->subexpr_to_subpatt[$subexpr]->subpattern);
        return ($current !== null && $current[0] != qtype_preg_matching_results::NO_MATCH_FOUND);
    }

    // TODO
    public function is_subexpr_match_finished($subexpr) {
        if (!isset($this->subexpr_to_subpatt[$subexpr])) {
            return false;
        }
        $current = $this->current_match($this->subexpr_to_subpatt[$subexpr]->subpattern);
        return ($current !== null && qtype_preg_fa_exec_state::is_completely_captured($current[0], $current[1]));
    }

    /**
     * Resets the given subpattern to no match.
     */
    protected function begin_subpatt_iteration($node, $matcher) {
        if (!$matcher->get_options()->capturesubexpressions && $node->subpattern != $this->root_subpatt_number()) {
            return;
        }

        $nodes = array($node);
        if ($matcher->get_options()->capturesubexpressions) {
            $nodes = array_merge($nodes, $matcher->get_nested_nodes($node->subpattern));
        }

        foreach ($nodes as $node) {
            if ($node->subpattern == -1) {
                continue;
            }

            $cur = $this->current_match($node->subpattern);

            if ($cur === null) {
                $this->matches[$node->subpattern] = array(); // Very first iteration.
            }

            if ($cur[0] == qtype_preg_matching_results::NO_MATCH_FOUND && $cur[1] == qtype_preg_matching_results::NO_MATCH_FOUND) {
                continue;   // The new iteration is already started.
            }

            $this->matches[$node->subpattern][] = qtype_preg_fa_exec_state::empty_subpatt_match();
        }
    }

    public function write_tag_values($transition, $strpos, $matchlen, $matcher) {
        // Begin a new iteration of a subpattern. All "bigger" (inner) subpatterns will start a new iteration recursively.
        if ($transition->minopentag !== null) {
            $this->begin_subpatt_iteration($transition->minopentag, $matcher);
        }

        $options = $matcher->get_options();

        // Set matches to ($strpos, -1) for the new iteration.
        foreach ($transition->opentags as $tag) {
            if (!$options->capturesubexpressions && $tag->subpattern != $this->root_subpatt_number()) {
                continue;
            }
            // Starting indexes are always the same, equal $strpos
            $index = $strpos;
            $this->set_current_match($tag->subpattern, $index, qtype_preg_matching_results::NO_MATCH_FOUND);
        }

        // Set matches to ($strpos, length) for the ending iterations.
        foreach ($transition->closetags as $tag) {
            if (!$options->capturesubexpressions && $tag->subpattern != $this->root_subpatt_number()) {
                continue;
            }
            $current_match = $this->current_match($tag->subpattern);
            $index = $current_match[0];
            $length = $strpos - $index + $matchlen;
            if ($index != qtype_preg_matching_results::NO_MATCH_FOUND) {
                $this->set_current_match($tag->subpattern, $index, $length);
            }
        }

        // Some stuff for subexpressions.
        foreach ($transition->opentags as $tag) {
            if ($tag->subtype != qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
                continue;
            }
            if (!$options->capturesubexpressions && $tag->subpattern != $this->root_subpatt_number()) {
                continue;
            }
            $this->subexpr_to_subpatt[$tag->number] = $tag;
        }
    }

    /**
     * Checks if this state contains null iterations, for example \b*. Such states should be skipped during matching.
     */
    public function has_null_iterations() {
        foreach ($this->matches as $subpatt => $repetitions) {
            $count = count($repetitions);
            if ($count < 2) {
                continue;
            }
            for ($i = $count - 1; $i > 0; $i--) {
                $penult = $repetitions[$i - 1];
                $last = $repetitions[$i];
                if (qtype_preg_fa_exec_state::is_completely_captured($last[0], $last[1]) && $penult == $last) {
                    return true;
                }
            }
        }
        return false;
    }
}

/**
 * Represents an execution state of an fa.
 */
class qtype_preg_fa_exec_state implements qtype_preg_matcher_state {

    // Indicates that this state is a full match state.
    const FLAG_FULL = 0x01;
    // Indicates that this state had \A or ^ transition.
    const FLAG_VISITED_START_ANCHOR = 0x02;
    // Indicates that this state had \Z \z or $ transition.
    const FLAG_VISITED_END_ANCHOR   = 0x04;

    // FA being executed.
    public $matcher;

    // Level of recursion
    public $recursionlevel;

    // Starting position of the match.
    public $startpos;

    // Length of the match.
    public $length;

    // How many characters left for full match?
    public $left;

    // Match extension in case of partial match. An object of this same class.
    public $extendedmatch;

    // String being captured and/or generated.
    public $str;

    // Array of qtype_preg_fa_stack_item objects
    public $stack;

    // States to backtrack to when generating extensions of partial matches.
    public $backtrack_states;

    public function __clone() {
        $this->str = clone $this->str;  // Needs to be cloned for correct string generation.
        foreach ($this->stack as $key => $item) {
            $this->stack[$key] = clone $item;
        }
    }

    public static function empty_subpatt_match() {
        return array(qtype_preg_matching_results::NO_MATCH_FOUND, qtype_preg_matching_results::NO_MATCH_FOUND);
    }

    public static function is_being_captured($index, $length) {
        return $index != qtype_preg_matching_results::NO_MATCH_FOUND && $length == qtype_preg_matching_results::NO_MATCH_FOUND;
    }

    public static function is_completely_captured($index, $length) {
        return $index != qtype_preg_matching_results::NO_MATCH_FOUND && $length != qtype_preg_matching_results::NO_MATCH_FOUND;
    }

/// wrapper functions

    public function state() {
        $end = end($this->stack);
        return $end->state;
    }

    public function set_state($value) {
        $end = end($this->stack);
        $end->state = $value;
    }

    public function last_transition() {
        $end = end($this->stack);
        return $end->last_transition;
    }

    public function set_last_transition($value) {
        $end = end($this->stack);
        $end->last_transition = $value;
    }

    public function last_match_len() {
        $end = end($this->stack);
        return $end->last_match_len;
    }

    public function set_last_match_len($value) {
        $end = end($this->stack);
        $end->last_match_len = $value;
    }

///

    public function set_flag($flag) {
        $end = end($this->stack);
        $end->flags = ($end->flags | $flag);
    }

    public function unset_flag($flag) {
        $flag = ~$flag;
        $end = end($this->stack);
        $end->flags = ($end->flags & $flag);
    }

    public function is_flag_set($flag) {
        $end = end($this->stack);
        return ($end->flags & $flag) != 0;
    }

    public function set_full($value) {
        if ($value) {
            $this->set_flag(self::FLAG_FULL);
        } else {
            $this->unset_flag(self::FLAG_FULL);
        }
    }

    public function is_full() {
        return $this->is_flag_set(self::FLAG_FULL);
    }

    protected function root_subpatt_number() {
        return $this->matcher->get_ast_root()->subpattern;
    }

    /**
     * Returns the current match for the given subpattern number. If there was no attemt to match, returns null.
     * @param subpatt - subpattern number.
     * @param wholestack - should we scan the whole stack, or just the top item.
     */
    protected function current_match($subpatt, $wholestack = false) {
        $array = $wholestack
               ? array_reverse($this->stack)
               : array(end($this->stack));
        foreach ($array as $item) {
            $tmp = $item->current_match($subpatt);
            if ($tmp !== null) {
                return $tmp;
            }
        }
        return null;
    }

    /**
     * Sets the current match for the given subpattern number. Always works with the top stack item.
     */
    protected function set_current_match($subpatt, $index, $length) {
        $end = end($this->stack);
        $end->set_current_match($subpatt, $index, $length);
    }

    /**
     * Returns the last successfull match for the given subpattern.
     * Behaves differently in PCRE and POSIX modes.
     * If there was no attemt to match, returns null.
     * @param subpatt - subpattern number.
     * @param wholestack - should we scan the whole stack, or just the top item.
     */
    protected function last_match($subpatt, $wholestack = false) {
        if ($this->matcher->get_options()->mode == qtype_preg_handling_options::MODE_POSIX) {
            $result = $this->current_match($subpatt, $wholestack);
            if ($result === null) {
                return null;
            }
            return self::is_being_captured($result[0], $result[1]) ? self::empty_subpatt_match()
                                                                   : $result;
        }

        $array = $wholestack
               ? array_reverse($this->stack)
               : array(end($this->stack));

        $hasattempts = false;

        foreach ($array as $item) {
            $cur = $item->last_match($this->matcher->get_options()->mode, $subpatt);
            $hasattempts = $hasattempts || ($cur !== null);
            if (self::is_completely_captured($cur[0], $cur[1])) {
                return $cur;
            }
        }

        return $hasattempts ? self::empty_subpatt_match() : null;
    }

    protected function last_subexpr_match($subexpr) {
        if ($subexpr == 0) {
            return array($this->startpos, $this->length);
        }

        $array = array_reverse($this->stack);

        $hasattempts = false;

        foreach ($array as $item) {
            if (!isset($item->subexpr_to_subpatt[$subexpr])) {
                continue;   // Can get here when {0} occurs in the regex.
            }
            $hasattempts = true;
            $subpatt = $item->subexpr_to_subpatt[$subexpr]->subpattern;
            $last = $item->last_match($this->matcher->get_options()->mode, $subpatt);
            if (self::is_completely_captured($last[0], $last[1])) {
                return $last;
            }
        }

        return $hasattempts ? self::empty_subpatt_match() : null;
    }

    public function has_duplicate_subexpression() {
        $end = end($this->stack);
        return $end->has_duplicate_subexpression();
    }

    public function is_subexpr_match_started($subexpr) {
        $end = end($this->stack);
        return $end->is_subexpr_match_started($subexpr);
    }

    public function is_subexpr_match_finished($subexpr) {
        $end = end($this->stack);
        return $end->is_subexpr_match_finished($subexpr);
    }

    public function index_first($subexpr = 0) {
        $last = $this->last_subexpr_match($subexpr);
        return $last === null ? qtype_preg_matching_results::NO_MATCH_FOUND
                              : $last[0];
    }

    public function length($subexpr = 0) {
        $last = $this->last_subexpr_match($subexpr);
        return $last === null ? qtype_preg_matching_results::NO_MATCH_FOUND
                              : $last[1];
    }

    public function is_subexpr_captured($subexpr) {
        $last = $this->last_subexpr_match($subexpr);
        return $last !== null && self::is_completely_captured($last[0], $last[1]);
    }

    public function match_from_pos_internal($str, $startpos, $subexpr = 0, $recursionlevel = 0) {
        return $this->matcher->match_from_pos_internal($str, $startpos, $subexpr, $recursionlevel);
    }

    public function start_pos() {
        return $this->startpos;
    }

    public function recursion_level() {
        return $this->recursionlevel;
    }

    public function to_matching_results() {
        $index = array();
        $length = array();
        $subexprs = array(-2);
        for ($subexpr = 0; $subexpr <= $this->matcher->get_max_subexpr(); $subexpr++) {
            $subexprs[] = $subexpr;
        }
        foreach ($subexprs as $subexpr) {
            $match = $this->last_subexpr_match($subexpr);
            if ($match !== null && self::is_completely_captured($match[0], $match[1])) {
                $index[$subexpr] = $match[0];
                $length[$subexpr] = $match[1];
            } else {
                $index[$subexpr] = qtype_preg_matching_results::NO_MATCH_FOUND;
                $length[$subexpr] = qtype_preg_matching_results::NO_MATCH_FOUND;
            }
        }
        if ($length[-2] == qtype_preg_matching_results::NO_MATCH_FOUND) {
            $cur = $this->current_match(-2);
            if ($cur !== null && $cur[0] != qtype_preg_matching_results::NO_MATCH_FOUND) {
                $index[-2] = $cur[0];
                $length[-2] = $this->length - $cur[0];
            }
        }
        $index[0] = $this->startpos;
        $length[0] = $this->length;
        $result = new qtype_preg_matching_results($this->is_full(), $index, $length, $this->left, $this->extendedmatch);
        $result->set_source_info($this->str, $this->matcher->get_max_subexpr(), $this->matcher->get_subexpr_map());
        return $result;
    }

    /**
     * Checks if this state contains null iterations, for example \b*. Such states should be skipped during matching.
     */
    public function has_null_iterations() {
        $end = end($this->stack);
        return $end->has_null_iterations();
    }

    /**
     * Returns true if this beats other, false if other beats this; for equal states returns false.
     */
    public function leftmost_longest($other, $matchinginprogress = true) {
        //echo "\n";
        //echo $this->subpatts_to_string();
        //echo "vs\n";
        //echo $other->subpatts_to_string();
        // Check for full match.
        if ($this->is_full() && !$other->is_full()) {
            //echo "wins 1\n";
            return true;
        } else if (!$this->is_full() && $other->is_full()) {
            //echo "wins 2\n";
            return false;
        }

        // Choose the longest match
        if ($this->length > $other->length) {
            //echo "wins 1\n";
            return true;
        } else if ($other->length > $this->length) {
            //echo "wins 2\n";
            return false;
        }

        // If both states have partial match, choose one with minimal left
        if (!$matchinginprogress && !$this->is_full() && !$other->is_full()) {
            if ($this->left < $other->left) {
                //echo "wins 1\n";
                return true;
            } else if ($other->left < $this->left) {
                //echo "wins 2\n";
                return false;
            }
        }

        $subpattmap = $this->matcher->get_subpatt_map();
        $refsmap = $this->matcher->get_subexpr_refs_map();

        // PCRE/POSIX selection goes on below. Iterate over all subpatterns skipping the first which is the whole expression.
        $modepcre = $this->matcher->get_options()->mode == qtype_preg_handling_options::MODE_PCRE;

        // We will compare corresponding stack objects.
        $tocompare = array();
        for ($i = 0; $i < min(count($this->stack), count($other->stack)); ++$i) {
            $tocompare[] = array($this->stack[$i], $other->stack[$i]);
        }

        foreach ($tocompare as $stackitems) {
            for ($i = $this->root_subpatt_number() + 1; $i <= $this->matcher->get_max_subpatt(); $i++) {
                $this_match = isset($stackitems[0]->matches[$i]) ? $stackitems[0]->matches[$i] : array(self::empty_subpatt_match());
                $other_match = isset($stackitems[1]->matches[$i]) ? $stackitems[1]->matches[$i] : array(self::empty_subpatt_match());

                $this_repetitions_count = count($this_match);
                $other_repetitions_count = count($other_match);

                $repetitions_count_difference = $this_repetitions_count - $other_repetitions_count;
                if ($modepcre && abs($repetitions_count_difference) == 1) {
                    // PCRE mode selection: if states have N and N + 1 subpattern repetitions, respectively,
                    // and the (N + 1)th repetition is empty, then select the second state. And vice versa.
                    $this_last = $stackitems[0]->last_match($this->matcher->get_options()->mode, $i);
                    $other_last = $stackitems[1]->last_match($this->matcher->get_options()->mode, $i);
                    if ($repetitions_count_difference == 1 && $this_last[1] == 0 && $this_last[0] > $other_last[0]) {
                        //echo "wins 1\n";
                        return true;
                    } else if ($repetitions_count_difference == -1 && $other_last[1] == 0 && $other_last[0] > $this_last[0]) {
                        //echo "wins 2\n";
                        return false;
                    }
                }

                // Iterate over all repetitions.
                for ($j = 0; $j < min($this_repetitions_count, $other_repetitions_count); $j++) {
                    $this_index = $this_match[$j][0];
                    $this_length = $this_match[$j][1];
                    $other_index = $other_match[$j][0];
                    $other_length = $other_match[$j][1];
                    $this_being_captured = self::is_being_captured($this_index, $this_length);
                    $other_being_captured = self::is_being_captured($other_index, $other_length);

                    if ($matchinginprogress && $this_being_captured) {
                        $this_length = $this->startpos + $this->length - $this_index;
                    }
                    if ($matchinginprogress && $other_being_captured) {
                        $other_length = $this->startpos + $other->length - $other_index;
                    }

                    // Continue if both iterations have no match.
                    if ($this_index == qtype_preg_matching_results::NO_MATCH_FOUND && $other_index == qtype_preg_matching_results::NO_MATCH_FOUND) {
                        continue;
                    }

                    // Match existance.
                    if ($other_index == qtype_preg_matching_results::NO_MATCH_FOUND) {
                        //echo "wins 1\n";
                        return true;
                    } else if ($this_index == qtype_preg_matching_results::NO_MATCH_FOUND) {
                        //echo "wins 2\n";
                        return false;
                    }

                    // Longest of all possible matches.
                    if ($this_length > $other_length) {
                        //echo "wins 1\n";
                        return true;
                    } else if ($other_length > $this_length) {
                        //echo "wins 2!!\n";
                        return false;
                    }
                }

                // Now let's see if this is a backreferenced subexpression. Not sure, but looks like the following code implies that
                // the referenced subexpression has zero-length match. It does the trick for the situations like:
                // :RE#49:B    \(a*\)*b\1*     ab  (0,2)(1,1)
                // Yes, the match is NOT (0,2)(0,1) because \1 should me zero-length-matched, not skipped.
                if ($subpattmap[$i]->type == qtype_preg_node::TYPE_NODE_SUBEXPR && array_key_exists($subpattmap[$i]->number, $refsmap)) {
                    $refs = $refsmap[$subpattmap[$i]->number];
                    foreach ($refs as $ref) {
                        $this_ref_last = $this->last_match($ref->subpattern);
                        $other_ref_last = $other->last_match($ref->subpattern);
                        $this_ref_captured = self::is_completely_captured($this_ref_last[0], $this_ref_last[1]);
                        $other_ref_captured = self::is_completely_captured($other_ref_last[0], $other_ref_last[1]);
                        if ($this_ref_captured && !$other_ref_captured) {
                            return true;
                        } else if ($other_ref_captured && !$this_ref_captured) {
                            return false;
                        }
                    }
                }

                // Finally, select the one with minimal repetitions count.
                if ($this_repetitions_count < $other_repetitions_count) {
                    return true;
                } else if ($other_repetitions_count < $this_repetitions_count) {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Returns true if this beats other, false if other beats this; for equal states returns false.
     */
    public function leftmost_shortest($other) {
        // Check for full match.
        if ($this->is_full() && !$other->is_full()) {
            return true;
        } else if (!$this->is_full() && $other->is_full()) {
            return false;
        }

        if ($this->length < $other->length) {
            return true;
        } else if ($other->length < $this->length) {
            return false;
        }

        return false;
    }

    public function write_tag_values($transition, $strpos, $matchlen) {
        $end = end($this->stack);
        $end->write_tag_values($transition, $strpos, $matchlen, $this->matcher);
    }

    public function subpatts_to_string() {
        $res = '';
        foreach ($this->matches as $subpatt => $repetitions) {
            $res .= $subpatt . ': ';
            foreach ($repetitions as $repetition) {
                $ind = $repetition[0];
                $len = $repetition[1];
                $res .= "($ind, $len) ";
            }
            $res .= "\n";
        }
        return $res;
    }

    public function subexprs_to_string() {
        $res = '';
        foreach ($this->subexpr_to_subpatt as $subexpr => $node) {
            $lastmatch = $this->last_match($node->subpattern);
            $ind = $lastmatch[0];
            $len = $lastmatch[1];
            $res .= $subexpr . ": ($ind, $len) ";
        }
        $res .= "\n";
        return $res;
    }
}
