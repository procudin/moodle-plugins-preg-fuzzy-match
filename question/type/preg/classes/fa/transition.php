<?php
// This file is part of Preg question type - https://bitbucket.org/oasychev/moodle-plugins/overview
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
 * Defines finite automata states and transitions classes for regular expression matching.
 * The class is used by FA-based matching engines, provides standartisation to them and enchances testability.
 *
 * @package    qtype_preg
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Oleg Sychev <oasychev@gmail.com>, Valeriy Streltsov, Elena Lepilkina
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_preg\fa;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/poasquestion/stringstream/stringstream.php');
require_once($CFG->dirroot . '/question/type/preg/preg_lexer.lex.php');
require_once($CFG->dirroot . '/question/type/preg/preg_dot_lexer.lex.php');
require_once($CFG->dirroot . '/question/type/preg/preg_dot_parser.php');

/**
 * Represents a finite automaton transition.
 */
class transition {

    //const GREED_ZERO = 1;
    const GREED_LAZY = 2;
    const GREED_GREEDY = 4;
    const GREED_POSSESSIVE = 8;

    /** Empty transition. */
    const TYPE_TRANSITION_EPS = 'eps_transition';
    /** Transition with unmerged simple assert. */
    const TYPE_TRANSITION_ASSERT = 'assert';
    /** Empty transition or transition with unmerged simple assert. */
    const TYPE_TRANSITION_BOTH = 'both';
    /** Capturing transition. */
    const TYPE_TRANSITION_CAPTURE = 'capturing';

    /** Transition from first automata. */
    const ORIGIN_TRANSITION_FIRST = 0x01;
    /** Transition from second automata. */
    const ORIGIN_TRANSITION_SECOND = 0x02;
    /** Transition from intersection part. */
    const ORIGIN_TRANSITION_INTER = 0x04;

    /** @var int - state which transition starts from. */
    public $from;
    /** @var object of \qtype_preg_leaf class - condition for this transition. */
    public $pregleaf;
    /** @var int - state which transition leads to. */
    public $to;
    /** @var greediness of this transition. */
    public $greediness;
    /** @var array of \qtype_preg_node objects - subpatterns opened by this transition */
    public $opentags;
    /** @var array of \qtype_preg_node objects - subpatterns closed by this transition */
    public $closetags;
    public $minopentag;
    /** @var type of the transition - should be equal to a constant defined in this class. */
    public $type;
    /** @var origin of the transition - should be equal to a constant defined in this class. */
    public $origin;
    /** @var bool - TODO. */
    public $consumeschars;
    /** @var bool - does this transition start a backreferenced subexpression(s)? */
    public $startsbackrefedsubexprs;
    /** @var bool - does this transition start a quantifier? */
    public $startsquantifier;
    /** @var bool - does this transition end a quantifier? */
    public $endsquantifier;
    /** @var bool - does this transition make a infinite quantifier loop? */
    public $loopsback;

    /** Array of transition objects merged to this transition and matched before it. Note that:
      a) Merged transitions are expected to be zero-length (simple assertions, epsilons)
      b) Max 'nestedness' level is 2, i.e. you are not expected to merge transitions into merged transitions
      c) You should guarantee that merged transitins are placed in the same order as they occurred originally */
    public $mergedbefore;

    /** Array of transition objects merged to this transition and matched after it. */
    public $mergedafter;

    public $isforintersection;


    public function __toString() {
        return $this->from . ' -> ' . $this->pregleaf->leaf_tohr() . ' -> ' . $this->to;
    }

    public function __construct($from, $pregleaf, $to, $origin = self::ORIGIN_TRANSITION_FIRST, $consumeschars = true) {
        $this->from = $from;
        $this->pregleaf = clone $pregleaf;
        $this->to = $to;
        $this->greediness = self::GREED_GREEDY;
        $this->opentags = array();
        $this->closetags = array();
        $this->minopentag = null;
        $this->type = null; // TODO
        $this->origin = $origin;
        $this->consumeschars = $consumeschars;
        $this->startsbackrefedsubexprs = false;
        $this->startsquantifier = false;
        $this->endsquantifier = false;
        $this->loopsback = false;
        $this->mergedbefore = array();
        $this->mergedafter = array();
        $this->isforintersection = false;
    }

    public function __clone() {
        $this->pregleaf = clone $this->pregleaf;
        if ($this->minopentag !== null) {
            $this->minopentag = clone $this->minopentag;
        }
        foreach ($this->mergedbefore as $key => $merged) {
            $this->mergedbefore[$key]->mergedafter = array();
            $this->mergedbefore[$key] = clone $merged;
        }

        foreach ($this->mergedafter as $key => $merged) {
            $this->mergedafter[$key]->mergedafter = array();
            $this->mergedafter[$key] = clone $merged;
        }
    }

    public function equal($other) {
        return $this->from == $other->from && $this->to == $other->to && $this->pregleaf == $other->pregleaf && count($this->mergedbefore) == count($other->mergedbefore) && count($this->mergedafter) == count($other->mergedafter);
    }

    /**
     * Divides pair of groups to noncrossed with tags
     * @param oldpair groups_pair Pair of groups of states to divide transitions from
     * @param mismatches array of mismatches
     * @param withtags bool flag of necessity to divide transitions with tags or not
     * @return result Array of pairs of group after dividing
     */
    public static function divide_intervals($oldpair, $withtags = false) {
        $result = array();
        $assertclasses = array('qtype_preg_leaf_assert_esc_a',
                               'qtype_preg_leaf_assert_small_esc_z',
                               'qtype_preg_leaf_assert_capital_esc_z',
                               'qtype_preg_leaf_assert_esc_g',
                               'qtype_preg_leaf_assert_circumflex',
                               'qtype_preg_leaf_assert_dollar');
        $assertdescriptions = array('\\A', '\\z', '\\Z', '\\G', '^', '$');

        // Divide charsets
        $firstgroupcharsets = array();
        $secondgroupcharsets = array();
        $firstgrouptransitions = $oldpair->first->get_outgoing_transitions();
        $secondgrouptransitions = $oldpair->second->get_outgoing_transitions();

        $assertions = array(array(array(), array()),
                            array(array(), array()),
                            array(array(), array()),
                            array(array(), array()),
                            array(array(), array()),
                            array(array(), array()));
        $epsilon = array(array(), array());
        foreach ($firstgrouptransitions as $index => $curtransition) {
            if (is_a($curtransition->pregleaf, 'qtype_preg_leaf_charset')) {
                $firstgroupcharsets[] = $curtransition->pregleaf;
            }
            else {
                for($i = 0; $i < count($assertclasses); $i++) {
                    if (is_a($curtransition->pregleaf, $assertclasses[$i])) {
                        $assertions[$i][0][] = $index;
                    }
                }
                if (is_a($curtransition->pregleaf, 'qtype_preg_leaf') && $curtransition->pregleaf->subtype == 'empty_leaf_meta') {
                    $epsilon[0][] = $index;
                }
            }
        }
        foreach ($secondgrouptransitions as $curtransition) {
            if (is_a($curtransition->pregleaf, 'qtype_preg_leaf_charset')) {
                $secondgroupcharsets[] = $curtransition->pregleaf;
            }
            else {
                for($i = 0; $i < count($assertclasses); $i++) {
                    if (is_a($curtransition->pregleaf, $assertclasses[$i])) {
                        $assertions[$i][1][] = $index;
                    }
                }
                if (is_a($curtransition->pregleaf, 'qtype_preg_leaf') && $curtransition->pregleaf->subtype == 'empty_leaf_meta') {
                    $epsilon[1][] = $index;
                }
            }
        }
        $charsetranges = \qtype_preg_leaf_charset::divide_intervals($firstgroupcharsets, $secondgroupcharsets, $charsetindexes);
        for ($i = 0; $i < count($charsetranges) + count($assertions) + 1; ++$i) {
            if ($i < count($charsetranges)) {
                $indexes = $charsetindexes[$i];
            }
            elseif ($i < count($charsetranges) + count($assertions)) {
                $indexes = $assertions[$i - count($charsetranges)];
            }
            else {
                $indexes = $epsilon;
            }

            if (count($indexes[0]) + count($indexes[1]) == 0) {
                continue;
            }

            // Counting string, matched to this pair of groups
            $newmatchedstring = $oldpair->matchedstring;
            if ($i < count($charsetranges)) {
                $matchedcondition = $charsetranges[$i][1];
                $newmatchedstring .= $matchedcondition;
            }
            else if ($i < count($charsetranges) + count($assertions)) {
                $matchedcondition = $assertdescriptions[$i - count($charsetranges)];
            }
            else {
                $matchedcondition = 'epsilon';
            }

            // Add new array of pairs to results matching current character
            $result[$matchedcondition] = array();

            // Divide by different tagsets in one automaton
            if ($withtags && !empty($indexes[0]) && !empty($indexes[1])) {
                $tags = array(array(), array());  // All tags: [group ind][transition ind][open or close][tag ind]
                // Combine first group tags
                for ($j = 0; $j < count($indexes[0]); ++$j) {
                    $transitionind = $indexes[0][$j];
                    $tags[0][] = array(array(), array());
                    foreach ($firstgrouptransitions[$transitionind]->opentags as $tag) {
                        if (is_a($tag, 'qtype_preg_node_subexpr'))
                            $tags[0][$j][0][] = $tag->number;
                    }
                    foreach ($firstgrouptransitions[$transitionind]->closetags as $tag) {
                        if (is_a($tag, 'qtype_preg_node_subexpr'))
                            $tags[0][$j][1][] = $tag->number;
                    }
                }
                // Combine second group tags
                for ($j = 0; $j < count($indexes[1]); ++$j) {
                    $transitionind = $indexes[1][$j];
                    $tags[1][] = array(array(), array());
                    foreach ($secondgrouptransitions[$transitionind]->opentags as $tag) {
                        if (is_a($tag, 'qtype_preg_node_subexpr'))
                            $tags[1][$j][0][] = $tag->number;
                    }
                    foreach ($secondgrouptransitions[$transitionind]->closetags as $tag) {
                        if (is_a($tag, 'qtype_preg_node_subexpr'))
                            $tags[1][$j][1][] = $tag->number;
                    }
                }

                // Divide tagsets to noncrossed

                // key - id from $noncrossedtagsetvalues, value - equivalence\states_group.
                $noncrossedtagsets = array('first' => array(), 'second' => array());
                // key - id, value - two arrays of open and close tags.
                $noncrossedtagsetvalues = array('first' => array(), 'second' => array());
                foreach (array_keys($noncrossedtagsets) as $ind => $key) {
                    $transitionsarrayname = $key . 'grouptransitions';
                    $transitionsarray = $$transitionsarrayname;
                    foreach ($tags[$ind] as $transitionind => $tagset) {
                        // If states group for current tagset already exists - add state to that group
                        $isnewtagset = true;
                        foreach ($noncrossedtagsetvalues[$key] as $tagsetid => $existingtagset) {
                            if ($existingtagset[0] == $tagset[0] && $existingtagset[1] == $tagset[1]) {
                                $noncrossedtagsets[$key][$tagsetid]->add_state($transitionsarray[$indexes[$ind][$transitionind]]->to);
                                $isnewtagset = false;
                                break;
                            }
                        }
                        // If there is no states group for current tagset - create new one and add current state to it
                        if ($isnewtagset) {
                            $id = count($noncrossedtagsetvalues[$key]);
                            $noncrossedtagsetvalues[$key][$id] = $tagset;
                            $noncrossedtagsets[$key][$id] = new equivalence\states_group($oldpair->$key->fa, array($transitionsarray[$indexes[$ind][$transitionind]]->to));
                            $noncrossedtagsets[$key][$id]->opentags = $tagset[0];
                            $noncrossedtagsets[$key][$id]->closetags = $tagset[1];
                        }
                    }
                }

                // Generate new groups for each unique combination of first and second groups tagsets
                foreach ($noncrossedtagsets['first'] as $firstgroupstatesgroup) {
                    foreach ($noncrossedtagsets['second'] as $secondgroupstatesgroup) {
                        $result[$matchedcondition][] = equivalence\groups_pair::generate_pair($firstgroupstatesgroup,
                            $secondgroupstatesgroup, $newmatchedstring);
                    }
                }
            }
            else {
                // Generating first state group
                $firststatesgroup = new equivalence\states_group($oldpair->first->fa);
                foreach ($indexes[0] as $transitionind) {
                    $firststatesgroup->add_state($firstgrouptransitions[$transitionind]->to);
                }
                // Generating second state group
                $secondstatesgroup = new equivalence\states_group($oldpair->second->fa);
                foreach ($indexes[1] as $transitionind) {
                    $secondstatesgroup->add_state($secondgrouptransitions[$transitionind]->to);
                }
                $result[$matchedcondition][] = equivalence\groups_pair::generate_pair($firststatesgroup, $secondstatesgroup, $newmatchedstring);
            }
            // Check for character mismatch
            /*if ($newpair->first->is_empty() != $newpair->second->is_empty()) {
                if ($i < count($charsetranges)) {
                    $mismatches[] = new equivalence\mismatched_pair(equivalence\mismatched_pair::CHARACTER,
                        $newpair->first->is_empty() ? 1 : 0, $newpair);
                }
                elseif ($i < count($charsetranges) + count($assertions)) {
                    $mismatches[] = new equivalence\mismatched_pair(equivalence\mismatched_pair::ASSERT,
                        $newpair->first->is_empty() ? 1 : 0, $newpair);
                    $mismatches[count($mismatches) - 1]->assert = $assertdescriptions[$i - count($charsetranges)];
                }
                else {
                    $mismatches[] = new equivalence\mismatched_pair(equivalence\mismatched_pair::FINAL_STATE,
                        $newpair->first->has_end_states() ? 0 : 1, $newpair);
                }
                continue;
            }

            // Check for final state mismatch
            if ($newpair->first->has_end_states() != $newpair->second->has_end_states()) {
                $mismatches[] = new equivalence\mismatched_pair(equivalence\mismatched_pair::FINAL_STATE,
                    $newpair->first->has_end_states() ? 0 : 1, $newpair);
                continue;
            }

            // Check for merged assertions mismatch


            if ($withtags && $newpair->tagmismatch == false)
            {
                // Compare tagsets
                $tags = array(array(), array());  // All tags: [group ind][transition ind][open or close][tag ind]
                // Combine first group tags
                for ($j = 0; $j < count($indexes[0]); ++$j) {
                    $transitionind = $indexes[0][$j];
                    $tags[0][] = array(array(), array());
                    foreach ($firstgrouptransitions[$transitionind]->opentags as $tag) {
                        if (is_a($tag, 'qtype_preg_node_subexpr'))
                            $tags[0][$j][0][] = $tag->number;
                    }
                    foreach ($firstgrouptransitions[$transitionind]->closetags as $tag) {
                        if (is_a($tag, 'qtype_preg_node_subexpr'))
                            $tags[0][$j][1][] = $tag->number;
                    }
                }
                // Combine second group tags
                for ($j = 0; $j < count($indexes[1]); ++$j) {
                    $transitionind = $indexes[1][$j];
                    $tags[1][] = array(array(), array());
                    foreach ($secondgrouptransitions[$transitionind]->opentags as $tag) {
                        if (is_a($tag, 'qtype_preg_node_subexpr'))
                            $tags[1][$j][0][] = $tag->number;
                    }
                    foreach ($secondgrouptransitions[$transitionind]->closetags as $tag) {
                        if (is_a($tag, 'qtype_preg_node_subexpr'))
                            $tags[1][$j][1][] = $tag->number;
                    }
                }

                // Check for mismatches
                // For each group (first and second)
                for ($groupind = 0; $groupind <= 1; ++$groupind) {
                    // For each pair of tagsets of different groups
                    for ($onegrouptransitionind = 0; $onegrouptransitionind < count($tags[$groupind]); ++$onegrouptransitionind) {
                        $matchfound = false;
                        for ($othergrouptransitionind = 0; $othergrouptransitionind < count($tags[($groupind + 1) % 2]) && !$matchfound; ++$othergrouptransitionind) {
                            // Check for situation with empty tagsets
                            if (count($tags[$groupind][$onegrouptransitionind][0]) + count($tags[$groupind][$onegrouptransitionind][1]) == 0) {
                                if (count($tags[($groupind + 1) % 2][$othergrouptransitionind][0]) + count($tags[($groupind + 1) % 2][$othergrouptransitionind][1]) == 0) {
                                    $matchfound = true;
                                }
                                continue;
                            }
                            $mismatchfound = false;
                            // For both open and close tags
                            for ($tagtype = 0; $tagtype <= 1 && !$mismatchfound; ++$tagtype) {
                                // Check, if tag from one group has no equivalent in other group
                                foreach ($tags[$groupind][$onegrouptransitionind][$tagtype] as $onegrouptag) {
                                    if (!in_array($onegrouptag, $tags[($groupind + 1) % 2][$othergrouptransitionind][$tagtype])) {
                                        $mismatchfound = true;
                                        break;
                                    }
                                }
                            }

                            // If all tags from transition of one group found in one of transitions of other group, then match found
                            if (!$mismatchfound) {
                                $matchfound = true;
                            }
                        }
                        if (!$matchfound) {
                            $newpair->tagmismatch = true;
                            $mismatch  = new equivalence\mismatched_pair(equivalence\mismatched_pair::SUBPATTERN, $groupind, $newpair);
                            $mismatch->opentags = $tags[$groupind][$onegrouptransitionind][0];
                            $mismatch->closetags = $tags[$groupind][$onegrouptransitionind][1];
                            $mismatches[] = $mismatch;
                        }
                    }
                }
            }*/
        }

        return $result;
    }

    /**
     * Returns true, if given tag match to subpattern, and false otherwise
     */
    public function is_subpattern_tag($tag) {
        return true;
    }

    /**
     * Generates a character considering merged transitions that affect the resulting char (^ \A $ \Z \z)
     */
    public function next_character($originalstr, $newstr, $pos, $length = 0, $matcherstateobj = null) {

        if ($this->pregleaf->type != \qtype_preg_node::TYPE_LEAF_CHARSET) {
            return $this->pregleaf->next_character($originalstr, $newstr, $pos, $length, $matcherstateobj);
        }

        // Get ranges from charset
        $ranges = $this->pregleaf->ranges();

        if (empty($ranges)) {
            return array(\qtype_preg_leaf::NEXT_CHAR_CANNOT_GENERATE, null);
        }

        // Determine which assertions we have

        $circumflex = array('before' => false, 'after' => false);
        $dollar = array('before' => false, 'after' => false);
        $capz = array('before' => false, 'after' => false);

        $key = 'before';
        foreach (array($this->mergedbefore, $this->mergedafter) as $assertions) {
            foreach ($assertions as $assertion) {
                switch ($assertion->pregleaf->subtype) {
                case \qtype_preg_leaf_assert::SUBTYPE_SMALL_ESC_Z:
                    return array(\qtype_preg_leaf::NEXT_CHAR_CANNOT_GENERATE, null);
                case \qtype_preg_leaf_assert::SUBTYPE_ESC_A:
                    return array(\qtype_preg_leaf::NEXT_CHAR_CANNOT_GENERATE, null);
                case \qtype_preg_leaf_assert::SUBTYPE_CIRCUMFLEX:
                    $circumflex[$key] = true;
                    break;
                case \qtype_preg_leaf_assert::SUBTYPE_DOLLAR:
                    $dollar[$key] = true;
                    break;
                case \qtype_preg_leaf_assert::SUBTYPE_CAPITAL_ESC_Z:
                    $capz[$key] = true;
                    break;
                default:
                    break;
                }
            }
            $key = 'after';
        }

        // If there are assertions we can only return \n
        if ($dollar['before'] || $capz['before']) {
            // There are end string assertions.
            if (\qtype_preg_unicode::is_in_range("\n", $ranges)) {
                return $capz['before']
                    ? array(\qtype_preg_leaf::NEXT_CHAR_END_HERE, new \qtype_poasquestion\utf8_string("\n"))
                    : array(\qtype_preg_leaf::NEXT_CHAR_OK, new \qtype_poasquestion\utf8_string("\n"));
            } else {
                return array(\qtype_preg_leaf::NEXT_CHAR_CANNOT_GENERATE, null);
            }
        } else if ($circumflex['after']) {
            // There are start string assertions.
            if (\qtype_preg_unicode::is_in_range("\n", $ranges)) {
                return array(\qtype_preg_leaf::NEXT_CHAR_OK, new \qtype_poasquestion\utf8_string("\n"));
            } else {
                return array(\qtype_preg_leaf::NEXT_CHAR_CANNOT_GENERATE, null);
            }
        }


        // Now we don't have assertions affecting characters. Form the resulting ranges. trying desired ranges first

        $originalchar = $originalstr[$pos];
        $originalcode = \core_text::utf8ord($originalchar);

        $desired_ranges = array();
        if ($pos < $originalstr->length()) {
            $desired_ranges[] = array(array($originalcode, $originalcode)); // original character - highest priority
        }
        $desired_ranges[] = array(array(0x21, 0x7F));   // regular ASCII characters - middle priority
        $desired_ranges[] = array(array(0x20, 0x20));   // space for \s - lowest priority

        $result_ranges = $ranges;   // By default original leaf's ranges.
        foreach ($desired_ranges as $desired) {
            $tmp = \qtype_preg_unicode::intersect_ranges($ranges, $desired);
            //$tmp = \qtype_preg_unicode::kinda_operator($ranges, $desired, true, false, false, false);
            if (!empty($tmp)) {
                $result_ranges = $tmp;
                break;
            }
        }

        return array(\qtype_preg_leaf::NEXT_CHAR_OK, new \qtype_poasquestion\utf8_string(core_text::code2utf8($result_ranges[0][0])));
    }

    public function is_start_anchor() {
        return ($this->pregleaf->type == \qtype_preg_node::TYPE_LEAF_ASSERT && $this->pregleaf->is_start_anchor()) /*&& empty($this->mergedbefore))*/;
    }

    public function is_end_anchor() {
        return ($this->pregleaf->type == \qtype_preg_node::TYPE_LEAF_ASSERT && $this->pregleaf->is_end_anchor()) /*&& empty($this->mergedafter))*/;
    }

    public function is_artificial_assert() {
        return ($this->pregleaf->type == \qtype_preg_node::TYPE_LEAF_ASSERT &&  ($this->pregleaf->is_artificial_assert() && (!empty($this->mergedafter) || !empty($this->mergedbefore))));
    }

    /**
     * Find intersection of asserts.
     *
     * @param other - the second assert for intersection.
     * @return assert, which is intersection of ginen.
     */
    public function intersect_asserts($other) {

        // Adding assert to array.
        if ($this->is_start_anchor()) {
            array_unshift($this->mergedafter, clone $this);
        } else if ($this->is_end_anchor()) {
            $this->mergedbefore[] = clone $this;    // TODO: maybe prepend?
        }

        if ($other->is_start_anchor()) {
            array_unshift($other->mergedafter,clone $other);
        } else if ($other->is_end_anchor()){
            $other->mergedbefore[] = clone $other;  // TODO: same
        }

        $resultbefore = array_merge($this->mergedbefore, $other->mergedbefore);
        $resultafter = array_merge($this->mergedafter, $other->mergedafter);
        // Removing same asserts.
        for ($i = 0; $i < count($resultbefore); $i++) {
            for ($j = ($i+1); $j < count($resultbefore); $j++) {
                if ($resultbefore[$i] == $resultbefore[$j]) {
                    unset($resultbefore[$j]);
                    $resultbefore = array_values($resultbefore);
                    $j--;
                }
            }
        }

        for ($i = 0; $i < count($resultafter); $i++) {
            for ($j = ($i+1); $j < count($resultafter); $j++) {
                if ($resultafter[$i] == $resultafter[$j]) {
                    unset($resultafter[$j]);
                    $resultafter = array_values($resultafter);
                    $j--;
                }
            }
        }

        $resultbefore = array_values($resultbefore);
        $resultafter = array_values($resultafter);
        foreach ($resultbefore as $tran) {
            $before[] = $tran->pregleaf;
        }
        foreach ($resultafter as $tran) {
            $after[] = $tran->pregleaf;
        }
        foreach ($resultafter as $assert) {
            $key = array_search($assert, $resultafter);
            if ($assert->pregleaf->subtype == \qtype_preg_leaf_assert::SUBTYPE_CIRCUMFLEX) {
                // Searching compatible asserts.
                if (\qtype_preg_leaf::contains_node_of_subtype(\qtype_preg_leaf_assert::SUBTYPE_ESC_A, $after)) {
                    unset($resultafter[$key]);
                    $resultafter = array_values($resultafter);
                }
            }
        }

        foreach ($resultbefore as $assert) {
            $key = array_search($assert, $resultbefore);
            if ($assert->pregleaf->subtype == \qtype_preg_leaf_assert::SUBTYPE_DOLLAR) {
                // Searching compatible asserts.
                if (\qtype_preg_leaf::contains_node_of_subtype(\qtype_preg_leaf_assert::SUBTYPE_SMALL_ESC_Z, $before) || \qtype_preg_leaf::contains_node_of_subtype(\qtype_preg_leaf_assert::SUBTYPE_CAPITAL_ESC_Z, $before)) {
                    unset($resultbefore[$key]);
                    $resultbefore = array_values($resultbefore);
                }

            }
            if ($assert->pregleaf->subtype == \qtype_preg_leaf_assert::SUBTYPE_CAPITAL_ESC_Z) {
                // Searching compatible asserts.
                if (\qtype_preg_leaf::contains_node_of_subtype(\qtype_preg_leaf_assert::SUBTYPE_SMALL_ESC_Z, $before)) {
                    unset($resultbefore[$key]);
                    $resultbefore = array_values($resultbefore);
                }

            }
        }

        // Getting result leaf.
        if ($this->pregleaf->type == \qtype_preg_node::TYPE_LEAF_CHARSET || $this->pregleaf->type == \qtype_preg_node::TYPE_LEAF_BACKREF) {
            $assert = clone $this;
        } else if ($other->pregleaf->type == \qtype_preg_node::TYPE_LEAF_CHARSET || $other->pregleaf->type == \qtype_preg_node::TYPE_LEAF_BACKREF) {
            $assert = clone $other;
        } else {
            if (!empty($resultbefore)) {
                $assert = clone $resultbefore[count($resultbefore) - 1];
                //unset($resultbefore[count($resultbefore) - 1]);
            } else if (!empty($resultafter)) {
                $assert = $resultafter[0];
                //unset($resultafter[0]);
            } else {
                $pregleaf = new \qtype_preg_leaf_meta(\qtype_preg_leaf_meta::SUBTYPE_EMPTY);
                $assert = new transition(0, $pregleaf, 1);
            }
        }
        $assert->mergedbefore = $resultbefore;
        $assert->mergedafter = $resultafter;
        if ($this->pregleaf->type == \qtype_preg_node::TYPE_LEAF_ASSERT) {
            if ($this->is_start_anchor()) {
                unset($this->mergedafter[0]);
            } else {
                unset($this->mergedbefore[count($this->mergedbefore) - 1]);
            }
        }
        if ($other->pregleaf->type == \qtype_preg_node::TYPE_LEAF_ASSERT) {
            if ($other->is_start_anchor()) {
                unset($other->mergedafter[0]);
            } else {
                unset($other->mergedbefore[count($other->mergedbefore) - 1]);
            }
        }
        return $assert;
    }

    /**
     * Return the laziest greedines of two
     */
    public static function min_greediness($g1, $g2) {
        return min($g1, $g2);   // This actually works
    }

    public function all_open_tags() {
        $allopentags = array();
        foreach ($this->mergedbefore as $merged) {
            foreach ($merged->opentags as $tag) {
                $allopentags[] = $tag;
            }
        }
        foreach ($this->opentags as $tag) {
            $allopentags[] = $tag;
        }
        foreach ($this->mergedafter as $merged) {
            foreach ($merged->opentags as $tag) {
                $allopentags[] = $tag;
            }
        }
        return $allopentags;
    }

    public function all_close_tags() {
        $allclosetags = array();
        foreach ($this->mergedbefore as $merged) {
            foreach ($merged->closetags as $tag) {
                $allclosetags[] = $tag;
            }
        }
        foreach ($this->closetags as $tag) {
            $allclosetags[] = $tag;
        }
        foreach ($this->mergedafter as $merged) {
            foreach ($merged->closetags as $tag) {
                $allclosetags[] = $tag;
            }
        }
        return $allclosetags;
    }

    public function get_label_for_dot($index1, $index2) {
        $addedcharacters = '/(), ';
        if (strpbrk($index1, $addedcharacters) !== false) {
            $index1 = '"' . $index1 . '"';
        }
        if (strpbrk($index2, $addedcharacters) !== false) {
            $index2 = '"' . $index2 . '"';
        }
        if ($this->origin == self::ORIGIN_TRANSITION_FIRST) {
            $color = 'violet';
        } else if ($this->origin == self::ORIGIN_TRANSITION_SECOND) {
            $color = 'blue';
        } else if ($this->origin == self::ORIGIN_TRANSITION_INTER) {
            $color = 'red';
        }
        $lab = '';
        foreach ($this->mergedbefore as $before) {
            $open = $before->tags_before_transition();
            $close = $before->tags_after_transition();
            $label = $before->pregleaf->leaf_tohr();
            $lab .= $open . ' ' . $label . ' ' . $close;
            $lab .= '(' . $before->from . ',' . $before->to . ')';
            $lab .= '<BR/>';
        }
        $open = $this->tags_before_transition();
        $close = $this->tags_after_transition();
        $label = $this->pregleaf->leaf_tohr();
        $lab .= '<B>' . $open . ' ' . $label . ' ' . $close . '</B>';

        foreach ($this->mergedafter as $after) {
            $lab .= '<BR/>';
            $open = $after->tags_before_transition();
            $close = $after->tags_after_transition();
            $label = $after->pregleaf->leaf_tohr();
            $lab .= $open . ' ' . $label . ' ' . $close;
            $lab .= '(' . $after->from . ',' . $after->to . ')';
        }

        $lab = str_replace('\\', '\\\\', $lab);
        $lab = str_replace('"', '\"', $lab);
        $lab = '<' . $lab . '>';

        $thickness = 2;
        if ($this->greediness == self::GREED_LAZY) {
            $thickness = 1;
        } else if ($this->greediness == self::GREED_POSSESSIVE) {
            $thickness = 3;
        }

        // Dummy transitions are displayed dotted.
        if ($this->consumeschars) {
            return "$index1->$index2" . "[label = $lab, color = $color, penwidth = $thickness];";
        } else {
            return "$index1->$index2" . "[label = $lab, color = $color, penwidth = $thickness, style = dotted];";
        }
    }

    protected static function compare_tags($node1, $node2) {
        $result = $node1->type == $node2->type &&
                  $node1->pos == $node2->pos &&
                  $node1->pregnode->subpattern == $node2->pregnode->subpattern;
      return $result ? 0 : 1;
    }

    /**
     * Copies tags from other transition in this transition.
     */
    public function unite_tags($other, $result) {
        $result->opentags = array_merge($this->opentags, $other->opentags);
        $result->closetags = array_merge($this->closetags, $other->closetags);
        foreach ($result->opentags as $key => $tag) {
            $result->opentags[$key] = clone $tag;
        }
        foreach ($result->closetags as $key => $tag) {
            $result->closetags[$key] = clone $tag;
        }
    }

    /**
     * Returns intersection of transitions.
     *
     * @param other another transition for intersection.
     */
    public function intersect($other) {

        $thishastags = $this->has_tags();
        $otherhastags = $other->has_tags();
        $resulttran = null;
        $flag = new \qtype_preg_charset_flag();
        $flag->set_data(\qtype_preg_charset_flag::TYPE_SET, new \qtype_poasquestion\utf8_string("\n"));
        $charset = new \qtype_preg_leaf_charset();
        $charset->flags = array(array($flag));
        $charset->userinscription = array(new \qtype_preg_userinscription("\n"));
        $righttran = new transition(0, $charset, 1);
        if ($this->pregleaf->type === \qtype_preg_node::TYPE_LEAF_BACKREF) {
            throw new \qtype_preg_backref_intersection_exception('', $this->pregleaf->position);
        }
        if ($other->pregleaf->type === \qtype_preg_node::TYPE_LEAF_BACKREF) {
            throw new \qtype_preg_backref_intersection_exception('', $other->pregleaf->position);
        }
        // Consider that eps and transition which doesn't consume characters always intersect
        if ($this->is_eps() && $other->consumeschars == false) {
            $resulttran = new transition(0, $other->pregleaf, 1, self::ORIGIN_TRANSITION_INTER, $other->consumeschars);
            $assert = $this->intersect_asserts($other);
            $resulttran->mergedbefore = $assert->mergedbefore;
            if ($thishastags) {
                $resulttran->mergedafter = array_merge($assert->mergedafter, array(clone $this));
            } else {
                $resulttran->mergedafter = $assert->mergedafter;
            }
            $resulttran->loopsback = $this->loopsback && $other->loopsback;
            //$this->unite_tags($other, $resulttran);
            return $resulttran;
        }
        if ($other->is_eps() && $this->consumeschars == false) {
            $resulttran = new transition(0, $this->pregleaf, 1, self::ORIGIN_TRANSITION_INTER, $this->consumeschars);

            $assert = $this->intersect_asserts($other);

            $resulttran->mergedafter = $assert->mergedafter;

            if ($otherhastags) {
                $resulttran->mergedbefore = array_merge( $assert->mergedbefore, array(clone $other));

            } else {
                $resulttran->mergedbefore = $assert->mergedbefore;
            }
            $resulttran->loopsback = $this->loopsback && $other->loopsback;
            $resulttran->count_min_open_tag();
            return $resulttran;
        }
        if ($this->is_unmerged_assert()  && (!$other->is_eps() && !$other->is_unmerged_assert())
            || $other->is_unmerged_assert() && (!$this->is_eps() && !$this->is_unmerged_assert())) {
            // We can intersect asserts only with \n.
            $intersection = null;

            if ($this->is_unmerged_assert()) {
                $intersection = $other->intersect($righttran);
                $resulttran = clone $other;
            } else if ($other->is_unmerged_assert()) {
                $intersection = $this->intersect($righttran);
                $resulttran = clone $this;
            }
            if ($intersection != null) {
                $resulttran->pregleaf = $intersection->pregleaf;
                $resulttran->count_min_open_tag();
                $assert = $this->intersect_asserts($other);
                $resulttran->mergedbefore = $assert->mergedbefore;
                $resulttran->mergedafter = $assert->mergedafter;
                if ($this->is_unmerged_assert()) {
                    $resulttran->consumeschars = false;
                }
                $resulttran->loopsback = $this->loopsback && $other->loopsback;
                return $resulttran;
            }
            return null;
        }
        $resultleaf = $this->pregleaf->intersect_leafs($other->pregleaf, $thishastags, $otherhastags);
        if ($resultleaf != null) {
            if (($this->is_eps() || $this->is_unmerged_assert()) && (!$other->is_eps() && !$other->is_unmerged_assert())) {
                $resulttran = null;
            } else if (($other->is_eps() || $other->is_unmerged_assert()) && (!$this->is_eps() && !$this->is_unmerged_assert())) {
                $resulttran = null;
            } else {
                $resulttran = new transition(0, $resultleaf, 1, self::ORIGIN_TRANSITION_INTER);
            }
        }
        if ($resulttran !== null ) {

            $assert = $this->intersect_asserts($other);
            $resulttran->mergedbefore = $assert->mergedbefore;
            $resulttran->mergedafter = $assert->mergedafter;
            if (!$this->is_eps()) {
                $this->unite_tags($other, $resulttran);
            }
            if ($this->consumeschars && $other->consumeschars) {
                $resulttran->loopsback = $this->loopsback && $other->loopsback;
            } else {
                $resulttran->loopsback = $this->loopsback || $other->loopsback;
            }

            $resulttran->count_min_open_tag();
        }
        return $resulttran;
    }


    private function count_min_open_tag() {
        $minopentag;
        if (!empty($this->opentags)) {
            $minopentag = $this->opentags[0];
            foreach ($this->opentags as $tag) {
                if ($tag->subpattern < $minopentag->subpattern) {
                    $minopentag = $tag;
                }
            }
            $this->minopentag = $minopentag;
        }
    }

    /**
     * Returns true if transition has any tag.
     */
    public function has_tags() {
        foreach (array_merge($this->mergedbefore, array($this), $this->mergedafter) as $transition) {
            if (!empty($transition->opentags) || !empty($transition->closetags)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if transition is eps.
     */
    public function is_eps() {
        return $this->pregleaf->subtype == \qtype_preg_leaf_meta::SUBTYPE_EMPTY;
    }

    /**
     * Returns true if transition is with unmerged assert.
     */
    public function is_unmerged_assert() {
        return ($this->pregleaf->type == \qtype_preg_node::TYPE_LEAF_ASSERT && $this->pregleaf->subtype != \qtype_preg_leaf_assert::SUBTYPE_ESC_B  && $this->pregleaf->subtype != \qtype_preg_leaf_assert::SUBTYPE_ESC_G);
    }

    public function is_wordbreak() {
        return $this->pregleaf->type == \qtype_preg_node::TYPE_LEAF_ASSERT && $this->pregleaf->subtype == \qtype_preg_leaf_assert::SUBTYPE_ESC_B;
    }

    /**
     * Set this transition right type.
     */
    public function set_transition_type() {
        if ($this->is_eps()) {
            $this->type = self::TYPE_TRANSITION_EPS;
        } else if ($this->is_unmerged_assert()) {
            $this->type = self::TYPE_TRANSITION_ASSERT;
        } else {
            $this->type = self::TYPE_TRANSITION_CAPTURE;
        }
    }

    public function redirect_merged_transitions() {
        foreach ($this->mergedbefore as &$merged) {
            $merged->from = $this->from;
            $merged->to = $this->to;
        }
        unset($merged);
        foreach ($this->mergedafter as &$merged) {
            $merged->from = $this->from;
            $merged->to = $this->to;
        }
        unset($merged);
    }

    private function this_tags_tohr($open, $close) {
        //return '';  // uncomment when needed

        $result = '';
        if ($open) {
            $result .= 'o:';
            foreach ($this->opentags as $tag) {
                $result .= $tag->subpattern . ',';
            }
        }
        if ($close) {
            $result .= 'c:';
            foreach ($this->closetags as $tag) {
                $result .= $tag->subpattern . ',';
            }
        }
        return $result;
    }

    public function tags_before_transition() {
        return $this->this_tags_tohr(true, false);
    }

    public function tags_after_transition() {
        return $this->this_tags_tohr(false, true);
    }
}