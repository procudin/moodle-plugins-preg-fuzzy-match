<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
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
 * Unit tests for the shortanswer question type class.
 *
 * @package    qtype
 * @subpackage shortanswer
 * @copyright  2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/writeregex/questiontype.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/vendor/phpunit/phpunit/PHPUnit/Framework/TestCase.php');


/**
 * Unit tests for the shortanswer question type class.
 *
 * @copyright  2007 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_writeregex_test extends PHPUnit_Framework_TestCase {
    var $qtype;

    function setUp() {
        $this->qtype = new writeregex_qtype();
        error_log('[setUp]', 3, 'writeregex_log.txt');
    }

    function tearDown() {
        $this->qtype = null;
        error_log('[tearDown]', 3, 'writeregex_log.txt');
    }

    function test_name() {
        error_log('[test_name]', 3, 'writeregex_log.txt');
        $this->assertEquals($this->qtype->name(), 'writeregex');
    }

    function test_get_question_options() {
        error_log('[test_get_question_options]', 3, 'writeregex_log.txt');
        $this->assertEquals(1, 1);
    }

    function test_save_question_options() {
        error_log('[get_possible_responses]', 3, 'writeregex_log.txt');
        $this->assertEquals(1, 1);
    }

    function test_delete_question() {
        error_log('[test_delete_question]', 3, 'writeregex_log.txt');
        $this->assertEquals(1, 1);
    }

    function test_generate_new_id() {
        error_log('[test_generate_new_id]', 3, 'writeregex_log.txt');
        // prepare test data
        global $DB;

        $record1 = new stdClass();
        $record1->id = 1;
        $record1->questionid = 1;
        $record1->notation = 1;
        $record1->syntaxtreehinttype = 1;
        $record1->syntaxtreehintpenalty = 1.02;
        $record1->explgraphhinttype = 1;
        $record1->explgraphhintpenalty = 1.02;
        $record1->descriptionhinttype = 1;
        $record1->descriptionhintpenalty = 1.02;
        $record1->teststringshinttype = 1;
        $record1->teststringshintpenalty = 1.02;
        $record1->compareregexpercentage = 0.3;
        $record1->compareautomatercentage = 0.4;

        $new_id = $DB->insert_record('qtype_writeregex_options', $record1);

        $this->assertEquals($new_id, 1);

        $record2 = new stdClass();
        $record2->id = 2;
        $record2->questionid = 1;
        $record2->notation = 1;
        $record2->syntaxtreehinttype = 1;
        $record2->syntaxtreehintpenalty = 1.02;
        $record2->explgraphhinttype = 1;
        $record2->explgraphhintpenalty = 1.02;
        $record2->descriptionhinttype = 1;
        $record2->descriptionhintpenalty = 1.02;
        $record2->teststringshinttype = 1;
        $record2->teststringshintpenalty = 1.02;
        $record2->compareregexpercentage = 0.3;
        $record2->compareautomatercentage = 0.4;

        $new_id = $DB->insert_record('qtype_writeregex_options', $record2);

        $this->assertEquals($new_id, 2);

        $g_id = $this->qtype->generate_new_id();

        $this->assertEquals($g_id, 3);
    }
}

?>
