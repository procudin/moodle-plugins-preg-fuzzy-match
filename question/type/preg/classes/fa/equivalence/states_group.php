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
 * Defines finite automata states and transitions classes for regular expression matching.
 * The class is used by FA-based matching engines, provides standartisation to them and enchances testability.
 *
 * @package    qtype_preg
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Oleg Sychev <oasychev@gmail.com>, Valeriy Streltsov, Elena Lepilkina
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace qtype_preg\fa\equivalence;

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a finite automaton states group.
 */
class states_group {
    /** @var array of states indexes in the group */
    public $states;
    /** @var array of open tags */
    public $opentags;
    /** @var array of close tags */
    public $closetags;
    /** @var finite automaton, the states of which are included in the group */
    public $fa;

    public function __construct($fa, $states = null) {
        $this->fa = $fa;
        $this->states = array();
        if ($states != null)
            $this->states = $states;
    }


    /**
     * Sets states to group.
     *
     * @param states - array of state, which include in this group.
     */
    public function set_states($states) {
        $this->states = $states;
    }

    /**
     * Adds new state to group
     * @param $state int state number
     */
    public function add_state($state) {
        if (!$this->contains($state)) {
            $this->states[] = $state;
        }
    }

    /**
     * Adds new states to group
     * @param $states array of states to add
     */
    public function add_states($states) {
        foreach ($states as $state) {
            $this->add_state($state);
        }
    }

    /**
     * Checks if given state already exists in current group
     * @param $state int state number
     * @return boolean whether given state exists in current group or not
     */
    public function contains($state) {
        return in_array($state, $this->states);
    }

    /**
     * Returns states of this group
     */
    public function get_states() {
        return $this->states;
    }

    /**
     * Returns fa, to which belong this group
     */
    public function get_fa() {
        return $this->fa;
    }

    /**
     * Returns outgoing transitions from states in current group
     */
    public function get_outgoing_transitions() {
        $transitions = array();
        foreach ($this->states as $curstate) {
            $transitions = array_merge($transitions, $this->fa->get_adjacent_transitions($curstate));
        }
        return $transitions;
    }

    /**
     * Compares two groups
     */
    public function equal($other) {
        // Check if all states of this group are included in the given one
        foreach ($this->states as $state) {
            if (!in_array($state, $other->states))
                return false;
        }

        // Check if all states of other group are included in this (for the case of repeated state indexes in one of groups)
        foreach ($other->states as $state) {
            if (!in_array($state, $this->states))
                return false;
        }

        return true;
    }

    /**
     * Checks if there are end states in group
     */
    public function has_end_states() {
        foreach ($this->states as $state)
            if ($this->fa->has_endstate($state))
                return true;

        return false;
    }

    /**
     * Checks if group is empty
     */
    public function is_empty() {
        return count($this->states) == 0;
    }
}