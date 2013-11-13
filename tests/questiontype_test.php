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

require_once(dirname(__FILE__) . '/../../../../config.php');

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
        $this->qtype = new qtype_writeregex();
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

    public function run_sql_script_for_test ($sql_script) {
        error_log("[run_sql_script]\n", 3, "writeregex_log.txt");

        global $CFG;

        $vals = array(
            'db_user' => 'root',
            'db_pass' => '',
            'db_host' => 'localhost',
            'db_name' => 'moodle'
        );

        $script_path = $CFG->dirroot . '/question/type/writeregex/tests/';

        $command = "mysql -u{$vals['db_user']} -p{$vals['db_pass']} "
            . "-h {$vals['db_host']} -D {$vals['db_name']} < {$script_path}";

//        $output = shell_exec($command . '/test_generate_new_id__append_1.sql');
        $output = shell_exec($command . $sql_script);

        $this->assertEquals(1, 1);
    }

    private function form_options($id, $questionid, $notation, $syntaxtreetype, $syntaxtreepenalty,
     $explgraphtype, $explgraphpenalty, $desctype, $descpenalty, $teststringtype, $teststringpenalty,
     $compareregex, $compareautomat) {

        $result = new stdClass();

        $result->id = $id;
        $result->questionid = $questionid;
        $result->notation = $notation;
        $result->syntaxtreehinttype = $syntaxtreetype;
        $result->syntaxtreehintpenalty = $syntaxtreepenalty;
        $result->explgraphhinttype = $explgraphtype;
        $result->explgraphhintpenalty = $explgraphpenalty;
        $result->descriptionhinttype = $desctype;
        $result->descriptionhintpenalty = $descpenalty;
        $result->teststringshinttype = $teststringtype;
        $result->teststringshintpenalty = $teststringpenalty;
        $result->compareregexpercentage = $compareregex;
        $result->compareautomatercentage = $compareautomat;

        return $result;
    }

    function test_delete_question() {
        error_log('[test_delete_question]', 3, 'writeregex_log.txt');


        $this->assertEquals(1, 1);
    }

    function test_generate_new_id() {
        error_log("[test_generate_new_id]\n", 3, 'writeregex_log.txt');

        // prepare test data
        global $DB;

        $record1 = $this->form_options(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
        $new_id = $DB->insert_record('qtype_writeregex_options', $record1);
        $this->assertEquals($new_id, 1);

        $calc_id = $this->qtype->generate_new_id();
        $this->assertEquals($calc_id, 2);

        $record2 = $this->form_options(2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);
    }
}

?>
