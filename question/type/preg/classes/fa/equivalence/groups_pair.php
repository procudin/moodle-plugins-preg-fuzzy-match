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
 * Represents pair of states_group for two automatons with the same path
 */
class groups_pair {
    /** @var states_group for first automaton */
    public $first;
    /** @var states_group for second automaton */
    public $second;
    /** @var path to current groups */
    public $matchedstring;
    /** @var string explanation about matched assert */
    public $assert;

    public function __construct($other = null) {
        if ($other != null) {
            $this->first = $other->first;
            $this->second = $other->second;
            $this->matchedstring = $other->matchedstring;
        }
    }

    public static function generate_pair($firstgroup, $secondgroup, $matchedstring, $assert = null) {
        $pair = new groups_pair();
        $pair->first = $firstgroup;
        $pair->second = $secondgroup;
        $pair->matchedstring = $matchedstring;
        $pair->assert = $assert;

        return $pair;
    }

    /**
     * Compare equality of two pairs of groups
     * @param $other groups_pair with which to compare
     * @param bool $withmatchedstring the whether to compare with matchedstring or not
     * @return bool the whether two groups pairs are equal
     */
    public function equal($other, $withmatchedstring = false) {
        $res = $this->first->equal($other->first) && $this->second->equal($other->second);
        if ($withmatchedstring) {
            $res &= $this->matchedstring == $other->matchedstring;
        }

        return $res;
    }
}