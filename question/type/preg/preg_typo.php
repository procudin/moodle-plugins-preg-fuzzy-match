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
use \qtype_poasquestion\utf8_string;

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

    public function __toString() {
        $str = "type = \'{$this->typo_description($this->type)}\' pos = {$this->position}";
        if ($this->type === self::INSERTION || $this->type === self::SUBSTITUTION) {
            $str .= ", char = \'{$this->char}\'";
        }
        return $str;
    }

    /**
     * Returns description for given type
     */
    public static function typo_description($type) {
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
                $string = utf8_string::substr($string, 0, $this->position) . utf8_string::substr($string, $this->position + 1);
                break;
            case self::SUBSTITUTION:
                $string = utf8_string::substr($string, 0, $this->position) . $this->char . utf8_string::substr($string, $this->position + 1);
                //$string[$this->position] = $this->char;
                break;
            case self::TRANSPOSITION:
                $tmp1 = utf8_string::substr($string,$this->position,1);
                $tmp2 = utf8_string::substr($string,$this->position + 1,1);
                $string = utf8_string::substr($string, 0, $this->position) . $tmp2 . $tmp1 . utf8_string::substr($string, $this->position + 2);
                break;
            case self::INSERTION:
                $string = utf8_string::substr($string, 0, $this->position) . $this->char . utf8_string::substr($string, $this->position);
                break;
        }
        return $string;
    }
}

class qtype_preg_typo_container {
    /** @var int $count errors count */
    protected $count;

    /** @var array $errors array of typotype => array(qtype_preg_typo1, qtype_preg_typo2) */
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

    public function contains($type, $pos, $char = null) {
        $comparebychar = $type !== qtype_preg_typo::TRANSPOSITION && $type !== qtype_preg_typo::DELETION && $char !== null && strlen($char) > 0;

        foreach ($this->errors[$type] as $typo) {
            if ($typo->position === $pos
                    && (!$comparebychar || strcmp($typo->char, $char) === 0)) {
                return true;
            }
        }
        return false;
    }

    public function __toString() {
        $result = "";
        foreach ($this->errors as $type => $errors) {
            if (count($errors)) {
                $result.= "\t" . qtype_preg_typo::typo_description($type) . "s:\n";
            }
            foreach($errors as $err) {
                $result.= "\t\tpos = {$err->position}, char = {$err->char}" . "\n";
            }
        }
        return $result;
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

    public function get_errors() {
        return $this->errors;
    }

    public static function substitution_as_deletion_and_insertion($container) {
        $substitutions = $container->errors[qtype_preg_typo::SUBSTITUTION];
        $subcount = count($substitutions);

        if ($subcount === 0) {
            return;
        }

        $container->errors[qtype_preg_typo::SUBSTITUTION] = [];
        $container->count -= $subcount;

        foreach ($substitutions as $sub) {
            $container->add(new qtype_preg_typo(qtype_preg_typo::DELETION, $sub->position));
            $container->add(new qtype_preg_typo(qtype_preg_typo::INSERTION, $sub->position + 1, $sub->char));
        }
    }

    protected function apply_inner($string, $modifycurrent = false , $removedeletions = true) {
        $container = $this;
        if (!$modifycurrent) {
            $container = clone $container;
        }

        $deletions = $container->errors[qtype_preg_typo::DELETION];
        $insertions = $container->errors[qtype_preg_typo::INSERTION];
        $transpositions =  $container->errors[qtype_preg_typo::TRANSPOSITION];
        $substitutions =  $container->errors[qtype_preg_typo::SUBSTITUTION];

        // Apply transposition.
        foreach ($transpositions as $typo) {
            $string = $typo->apply($string);
        }

        // Apply substitutions.
        foreach ($substitutions as $typo) {
            $string = $typo->apply($string);
        }

        for ($i = 0, $count = count($insertions); $i < $count; $i++) {
            $string = $insertions[$i]->apply($string);

            for ($j = $i + 1; $j < $count; $j++) {
                $insertions[$j]->position++;
            }

            foreach ($deletions as $del) {
                if ($del->position >= $insertions[$i]->position) {
                    $del->position++;
                }
            }
            foreach ($transpositions as $tr) {
                if ($tr->position >= $insertions[$i]->position) {
                    $tr->position++;
                }
            }
            foreach ($substitutions as $sub) {
                if ($sub->position >= $insertions[$i]->position) {
                    $sub->position++;
                }
            }
        }

        // Apply deletions.
        for ($i = 0, $count = count($deletions); $i < $count && $removedeletions; $i++) {
            $string = $deletions[$i]->apply($string);

            for ($j = $i + 1; $j < $count; $j++) {
                $deletions[$j]->position--;
            }

            foreach ($insertions as $ins) {
                if ($ins->position > $deletions[$i]->position) {
                    $ins->position--;
                }
            }
            foreach ($transpositions as $tr) {
                if ($tr->position > $deletions[$i]->position) {
                    $tr->position--;
                }
            }
            foreach ($substitutions as $sub) {
                if ($sub->position > $deletions[$i]->position) {
                    $sub->position--;
                }
            }
        }

        return array($string, $container);
    }

    /** Apply all typos to given string
     * @param $string
     * @return string after applying
     */
    public function apply($string, $removedeletions = true) {
        list($newstring, $newcontainer) = $this->apply_inner($string, false, $removedeletions);
        return $newstring;
    }


    public function apply_with_ops($string) {
        $container = clone $this;

        self::substitution_as_deletion_and_insertion($container);

        list($string, $container) = $this->apply_inner($string, true,false);

        $deletions = $container->errors[qtype_preg_typo::DELETION];
        $insertions = $container->errors[qtype_preg_typo::INSERTION];
        $transpositions =  $container->errors[qtype_preg_typo::TRANSPOSITION];
        $operations = [];

        foreach($deletions as $del) {
            $operations[$del->position] = 'strikethrough';
        }
        foreach($insertions as $ins) {
            $operations[$ins->position] = 'insert';
        }
        foreach($transpositions as $tr) {
            $operations[$tr->position] = $operations[$tr->position + 1] = 'transpose';
        }

        for ($i = 0; $i < utf8_string::strlen($string); $i++) {
            if (!isset($operations[$i])) {
                $operations[$i] = 'normal';
            }
        }

        return array($string, $operations);
    }

}



