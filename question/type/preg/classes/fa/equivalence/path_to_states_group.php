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

/**
 * Represents path to states group from initial state of finite automaton.
 */
class path_to_states_group {
    const CHARACTER = 0x0002;
    const ASSERT = 0x0004;
    const EPSILON = 0x0006;
    /**
     * @var type of current path step. Set to one of constants of current class
     */
    public $type;
    /**
     * @var char character, matched in automaton to get this group.
     */
    public $character;
    /**
     * @var string assert, matched in automaton to get this group.
     */
    public $assert;
    /**
     * @var array of integer opentags, matched before character.
     */
    public $beforeopentags = array();
    /**
     * @var array of integer closetags, matched before character. Necessary for merged transitions.
     */
    public $beforeclosetags = array();
    /**
     * @var array of integer opentags, matched after character. Necessary for merged transitions.
     */
    public $afteropentags = array();
    /**
     * @var array of integer closetags, matched after character.
     */
    public $afterclosetags = array();
    /**
     * @var path_to_states_group path to previous group.
     */
    public $prev;

    public function __construct($type = path_to_states_group::CHARACTER, $value = '') {
        $this->type = $type;
        switch ($type) {
            case path_to_states_group::CHARACTER:
                $this->character = $value;
                break;
            case path_to_states_group::ASSERT:
                $this->assert = $value;
                break;
        }
    }

    /**
     * Returns string description of condition symbol
     */
    public function get_condition_symbol_description() {
        switch ($this->type) {
            case path_to_states_group::CHARACTER:
                return $this->character;
            case path_to_states_group::ASSERT:
                return $this->assert;
            default:
                return 'epsilon';
        }
    }

    /**
     * Get subexpression tags from given and set to named array
     */
    public function filter_subexpression_tags($arrayoftags, $nameofarraytofill) {
        foreach ($arrayoftags as $tag) {
            if (is_a($tag, 'qtype_preg_node_subexpr')) {
                array_push($this->$nameofarraytofill, $tag->number);
            }
        }
    }

    /**
     * Compares two paths without history
     * @param $other path_to_states_group whis which to compare
     * @return bool result of comparison
     */
    public function equal_step($other) {
        // Compare type and symbol
        $res = $this->type == $other->type;
        switch ($this->type) {
            case path_to_states_group::CHARACTER:
                $res &= $this->character == $other->character;
                break;
            case path_to_states_group::ASSERT:
                $res &= $this->assert == $other->assert;
                break;
        }

        // Compare tagsets
        $this->normalize_tagsets();
        $other->normalize_tagsets();

        $res &= $this->beforeopentags == $other->beforeopentags
            && $this->afterclosetags == $other->afterclosetags
            && $this->beforeclosetags == $other->beforeclosetags
            && $this->afteropentags == $other->afteropentags;

        return $res;
    }

    /**
     * Removes duplicate tags from each array and sorts values
     */
    public function normalize_tagsets() {
        // Remove duplicate values
        $this->beforeopentags = array_unique($this->beforeopentags, SORT_NUMERIC);
        $this->afterclosetags = array_unique($this->afterclosetags, SORT_NUMERIC);
        $this->beforeclosetags = array_unique($this->beforeclosetags, SORT_NUMERIC);
        $this->afteropentags = array_unique($this->afteropentags, SORT_NUMERIC);
        // Sort tagsets
        sort($this->beforeopentags);
        sort($this->afterclosetags);
        sort($this->beforeclosetags);
        sort($this->afteropentags);
    }

    /**
     * Returns full character path.
     * @return string full character path.
     */
    public function matched_string() {
        if ($this->prev == null) {
            if ($this->type == path_to_states_group::CHARACTER) {
                return $this->character;
            }
            return '';
        }
        if ($this->type == path_to_states_group::CHARACTER) {
            return $this->prev->matched_string() . $this->character;
        }
        return $this->prev->matched_string();
    }

    /**
     * Returns clone of current path
     * @param $prev path_to_states_group previouse step of path, if is necessary to set it not by clone value
     * @return path_to_states_group clone of current path
     */
    public function clone_path($prev = null) {
        $path = new path_to_states_group();

        $path->type = $this->type;
        $path->character = $this->character;
        $path->assert = $this->assert;
        $path->beforeopentags = $this->beforeopentags;
        $path->beforeclosetags = $this->beforeclosetags;
        $path->afteropentags = $this->afteropentags;
        $path->afterclosetags = $this->afterclosetags;
        if ($prev !== null) {
            $path->prev = $prev;
        }
        else {
            $path->prev = $this->prev;
        }

        return $path;
    }
}