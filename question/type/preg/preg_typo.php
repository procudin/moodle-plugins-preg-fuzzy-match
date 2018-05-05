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
 * Defines Preg typo.
 *
 * @package    qtype_preg
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Oleg Sychev <oasychev@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_preg_typo {
    const INSERTION         = 0x0001;   // Insertion typo type.
    const DELETION          = 0x0002;   // Deletion typo type.
    const SUBSTITUTION      = 0x0004;   // Substitution typo type.
    const TRANSPOSITION     = 0x0008;   // Transposition typo type.

    /** @var int typo type */
    public $type;

    /** @var int typo position */
    public $position;

    /** @var string typo character */
    public $char;

    /**
     * qtype_preg_typo constructor.
     * @param int $type typo type
     * @param int $pos typo position
     * @param string $char typo character
     */
    public function __construct($type, $pos, $char = '') {
        $this->type = $type;
        $this->position = $pos;
        $this->char = $char;
    }

    /**
     * Returns description for given type
     */
    public function typo_description($type) {
        switch ($type) {
            case self::INSERTION:
                return 'insertion';
            case self::DELETION:
                return 'deletion';
            case self::SUBSTITUTION:
                return 'substitution';
            case self::TRANSPOSITION:
                return 'transposition';
        }
        return 'undefined';
    }

    /**
     * Apply typo to string
     * @param string $string input string
     * @return string string after typo applying
     */
    public function apply($string) {
        switch ($this->type) {
            case self::DELETION:
                $string = substr_replace($string, '', $this->position, 1);
                break;
            case self::SUBSTITUTION:
                $string[$this->position] = $this->char;
                break;
            case self::TRANSPOSITION:
                $tmp = $string[$this->position];
                $string[$this->position] = $string[$this->position + 1];
                $string[$this->position + 1] = $tmp;
                break;
            case self::INSERTION:
                $string = substr_replace($string, $this->char, $this->position, 0);
                break;
        }
        return $string;
    }
}

class qtype_preg_typo_container {
    /** @var int $count errors count */
    protected $count;

    /** @var array $errors array of typotype => array(qtype_preg_typo1,qtype_preg_typo2) */
    protected $errors;

    public function __construct() {
        $this->errors = [
                qtype_preg_typo::SUBSTITUTION => [],
                qtype_preg_typo::INSERTION => [],
                qtype_preg_typo::DELETION => [],
                qtype_preg_typo::TRANSPOSITION => [],
        ];
        $this->count = 0;
    }

    public function contains($type,$pos,$char = '') {
        $comparebychar = $char !== null && strlen($char) > 0;

        foreach ($this->errors[$type] as $typo) {
            if ($typo->position === $pos
                    && (!$comparebychar || strcmp($typo->char,$char) === 0)){
                return true;
            }
        }
        return false;
    }

    public function remove($type, $pos) {
        for ($count = count($this->errors[$type]), $i = $count - 1; $i >= 0; $i--) {
            if ($this->errors[$type][$i]->position === $pos) {
                array_splice($this->errors[$type], $i, 1);
                $this->count--;
                return true;
            }
        }
        return false;
    }

    /** Returns count of chosen errors.
     * @param int $type
     * @return int
     */
    public function count($type = -1) {
        if ($type === -1) {
            return $this->count;
        }

        // If only 1 type.
        if ($type & ($type - 1) == 0) {
            return count($this->errors[$type]);
        }

        $result = 0;
        foreach ($this->errors as $key => $value) {
            if ($type & $key) {
                $result += count($value);
            }
        }
        return $result;
    }

    /**
     * Add typo to container
     */
    public function add($typo) {
        if (!is_a($typo, '\qtype_preg_typo')) {
            return false;
        }
        $this->errors[$typo->type] [] = $typo;
        $this->count++;
    }


    public function invalidate() {
        $this->count = 99999999;
    }

    public function worse_than($other, $orequal = false) {
        if ($this->count > $other->count) {
            return true;
        } else if ($this->count < $other->count) {
            return false;
        }

        if ($this->count(qtype_preg_typo::TRANSPOSITION) < $other->count(qtype_preg_typo::TRANSPOSITION)) {
            return true;
        } else if ($this->count(qtype_preg_typo::TRANSPOSITION) > $other->count(qtype_preg_typo::TRANSPOSITION)) {
            return false;
        }

        if ($this->count(qtype_preg_typo::SUBSTITUTION) < $other->count(qtype_preg_typo::SUBSTITUTION)) {
            return true;
        } else if ($this->count(qtype_preg_typo::SUBSTITUTION) > $other->count(qtype_preg_typo::SUBSTITUTION)) {
            return false;
        }

        if ($this->count(qtype_preg_typo::DELETION) < $other->count(qtype_preg_typo::DELETION)) {
            return true;
        } else if ($this->count(qtype_preg_typo::DELETION) > $other->count(qtype_preg_typo::DELETION)) {
            return false;
        }

        if ($this->count(qtype_preg_typo::INSERTION) < $other->count(qtype_preg_typo::INSERTION)) {
            return true;
        } else if ($this->count(qtype_preg_typo::INSERTION) > $other->count(qtype_preg_typo::INSERTION)) {
            return false;
        }

        return $orequal;
    }

    /** Apply all typos to given string
     * @param $string
     * @return string after applying
     */
    public function apply($string) {
        // Apply transposition.
        foreach($this->errors[qtype_preg_typo::TRANSPOSITION] as $typo) {
            $string = $typo->apply($string);
        }

        // Apply substitutions.
        foreach($this->errors[qtype_preg_typo::SUBSTITUTION] as $typo) {
            $string = $typo->apply($string);
        }

        // Copy mutable typos
        $deletions = $this->errors[qtype_preg_typo::DELETION];
        $insertions = $this->errors[qtype_preg_typo::INSERTION];

        for($i = 0, $count = count($insertions); $i < $count; $i++) {
            $string = $insertions[$i]->apply($string);

            for ($j = $i; $j < $count; $j++) {
                $insertions[$j]->position++;
            }

            foreach($deletions as $del) {
                if ($del->position >= $insertions[$i]->position){
                    $del->position++;
                }
            }
        }

        // Apply deletions.
        foreach($deletions as $del) {
            $string = $del->apply($string);
        }

        return $string;
    }
}



