<?php
// This file is part of WriteRegex question type - https://bitbucket.org/oasychev/moodle-pluginss
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
 * Result of comparing regexes by test strings.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Kamo Spertsian <spertsiankamo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class compare_strings_analyzer_result extends analyzer_result {
    /** @var int Count of test strings */
    public $stringscount;
    /** @var int Count of matched test strings */
    public $matchedstringscount;
    /** @var array of mismatched test strings */
    public $mismatchedstrings;
    /** @var boolean flag of necessity to show mismatched test strings */
    public $showmismatchedstrings;

    /**
     * Get feedback for analyzing results.
     * @param qtype_writeregex_renderer renderer Renderer
     * @return string Feedback about mismatches to show to student.
     */
    public function get_feedback($renderer) {
        $a = new \stdClass;
        $a->count = $this->stringscount;
        $a->matchedcount = $this->matchedstringscount;
        $feedback = $renderer->add_break(get_string('teststringsmatchedcount', 'qtype_writeregex', $a));

        // Show if necessary mismatched test strings.
        if ($this->showmismatchedstrings == 1) {
            $feedback .= $renderer->add_break(get_string('mismatchedteststrings', 'qtype_writeregex'));
            foreach ($this->mismatchedstrings as $mismatchedstring) {
                $feedback .= $renderer->add_break($mismatchedstring->teststring);
            }
        }

        return $feedback;
    }
}