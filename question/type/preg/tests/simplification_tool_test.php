<?php

/**
 * Unit tests for question/type/preg/authoring_tools/preg_simplification_tool.php.
 *
 * @package    qtype_preg
 * @copyright  2015 Oleg Sychev, Volgograd State Technical University
 * @author     Terechov Grigory, Volgograd State Technical University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_simplification_tool.php');

class qtype_preg_simplification_tool_test extends PHPUnit_Framework_TestCase {

    public function test_cse_trivial() {
        $tests = $this->get_test_cse_trivial();
        for($i = 0; $i < count($tests); $i++) {
            $test_result = $tests[$i][1];
            $test_regex = $tests[$i][0];

            $stooloptions = new qtype_preg_simplification_tool_options();
            $stooloptions->engine = 'fa_matcher';
            $stooloptions->notation = 'native';
            $stooloptions->exactmatch = false;
            $stooloptions->problem_ids = array();
            $stooloptions->problem_ids[0] = '';
            $stooloptions->problem_type = -2;
            $stooloptions->indfirst = -2;
            $stooloptions->indlast = -2;

            $stooloptions->selection = new qtype_preg_position(-2, -2);
            $stooloptions->preserveallnodes = true;

            $result_regex = '';
            $st = new qtype_preg_simplification_tool($test_regex, $stooloptions);
            $eq = $st->cse();

            if (count($eq) != 0) {
                if (count($eq['problem_ids']) > 0 && $eq['problem_type'] != -2) {
                    $stooloptions->problem_ids = $eq['problem_ids'];
                    $stooloptions->problem_type = $eq['problem_type'];
                    $stooloptions->indfirst = $eq['problem_indfirst'];
                    $stooloptions->indlast = $eq['problem_indlast'];

                    $simplified_regex = new qtype_preg_simplification_tool($test_regex, $stooloptions);
                    $result_regex = $simplified_regex->optimization();
                }
            } else {
                $result_regex = $test_regex;
            }
            $this->assertTrue($result_regex === $test_result);
        }
    }

    public function test_grouping_node_trivial() {
        $tests = $this->get_test_grouping_node_trivial();
        for($i = 0; $i < count($tests); $i++) {
            $test_result = $tests[$i][1];
            $test_regex = $tests[$i][0];

            $stooloptions = new qtype_preg_simplification_tool_options();
            $stooloptions->engine = 'fa_matcher';
            $stooloptions->notation = 'native';
            $stooloptions->exactmatch = false;
            $stooloptions->problem_ids = array();
            $stooloptions->problem_ids[0] = '';
            $stooloptions->problem_type = -2;
            $stooloptions->indfirst = -2;
            $stooloptions->indlast = -2;

            $stooloptions->selection = new qtype_preg_position(-2, -2);
            $stooloptions->preserveallnodes = true;

            $result_regex = '';
            $st = new qtype_preg_simplification_tool($test_regex, $stooloptions);
            $eq = $st->grouping_node();

            if (count($eq) != 0) {
                if (count($eq['problem_ids']) > 0 && $eq['problem_type'] != -2) {
                    $stooloptions->problem_ids = $eq['problem_ids'];
                    $stooloptions->problem_type = $eq['problem_type'];
                    $stooloptions->indfirst = $eq['problem_indfirst'];
                    $stooloptions->indlast = $eq['problem_indlast'];

                    $simplified_regex = new qtype_preg_simplification_tool($test_regex, $stooloptions);
                    $result_regex = $simplified_regex->optimization();
                }
            } else {
                $result_regex = $test_regex;
            }
            $this->assertTrue($result_regex === $test_result);
        }
    }

    public function test_subpattern_node_trivial() {
        $tests = $this->get_test_subpattern_node_trivial();
        for($i = 0; $i < count($tests); $i++) {
            $test_result = $tests[$i][1];
            $test_regex = $tests[$i][0];

            $stooloptions = new qtype_preg_simplification_tool_options();
            $stooloptions->engine = 'fa_matcher';
            $stooloptions->notation = 'native';
            $stooloptions->exactmatch = false;
            $stooloptions->problem_ids = array();
            $stooloptions->problem_ids[0] = '';
            $stooloptions->problem_type = -2;
            $stooloptions->indfirst = -2;
            $stooloptions->indlast = -2;

            $stooloptions->selection = new qtype_preg_position(-2, -2);
            $stooloptions->preserveallnodes = true;

            $result_regex = '';
            $st = new qtype_preg_simplification_tool($test_regex, $stooloptions);
            $eq = $st->subpattern_node();

            if (count($eq) != 0) {
                if (count($eq['problem_ids']) > 0 && $eq['problem_type'] != -2) {
                    $stooloptions->problem_ids = $eq['problem_ids'];
                    $stooloptions->problem_type = $eq['problem_type'];
                    $stooloptions->indfirst = $eq['problem_indfirst'];
                    $stooloptions->indlast = $eq['problem_indlast'];

                    $simplified_regex = new qtype_preg_simplification_tool($test_regex, $stooloptions);
                    $result_regex = $simplified_regex->optimization();
                }
            } else {
                $result_regex = $test_regex;
            }
            $this->assertTrue($result_regex === $test_result);
        }
    }

    public function test_single_charset_trivial() {
        $tests = $this->get_test_single_charset_trivial();
        for($i = 0; $i < count($tests); $i++) {
            $test_result = $tests[$i][1];
            $test_regex = $tests[$i][0];

            $stooloptions = new qtype_preg_simplification_tool_options();
            $stooloptions->engine = 'fa_matcher';
            $stooloptions->notation = 'native';
            $stooloptions->exactmatch = false;
            $stooloptions->problem_ids = array();
            $stooloptions->problem_ids[0] = '';
            $stooloptions->problem_type = -2;
            $stooloptions->indfirst = -2;
            $stooloptions->indlast = -2;

            $stooloptions->selection = new qtype_preg_position(-2, -2);
            $stooloptions->preserveallnodes = true;

            $result_regex = '';
            $st = new qtype_preg_simplification_tool($test_regex, $stooloptions);
            $eq = $st->single_charset_node();

            if (count($eq) != 0) {
                if (count($eq['problem_ids']) > 0 && $eq['problem_type'] != -2) {
                    $stooloptions->problem_ids = $eq['problem_ids'];
                    $stooloptions->problem_type = $eq['problem_type'];
                    $stooloptions->indfirst = $eq['problem_indfirst'];
                    $stooloptions->indlast = $eq['problem_indlast'];

                    $simplified_regex = new qtype_preg_simplification_tool($test_regex, $stooloptions);
                    $result_regex = $simplified_regex->optimization();
                }
            } else {
                $result_regex = $test_regex;
            }
            $this->assertTrue($result_regex === $test_result);
        }
    }

    protected function get_test_cse_trivial() {
        return array (
            array('aaa', 'a{2}a', true),
            array('aaab', 'a{2}ab', true),
            array('baaa', 'ba{2}a', true),
            array('abab', '(?:ab){2}', true),
            array('ababa', '(?:ab){2}a', true), //или a(?:ba){2}
            array('cbaba', 'c(?:ba){2}', true),
            array('cbabc', 'cbabc', true),
            array('cbabb', 'cbab{2}', true),
            array('bbaba', 'b{2}aba', true), // или b(?:ba){2}
            array('bbabb', 'b{2}abb', true),
            array('b{2}abb', 'b{2}ab{2}', true),
            array('babac', '(?:ba){2}c', true),
            array('cbbababa', 'cb{2}ababa', true),
            array('cb{2}ababa', 'cb{2}(?:ab){2}a', true), //или cb(?:ba){3}
            array('aababab', 'a{2}babab', true),
            array('a{2}babab', 'a{2}(?:ba){2}b', true), //или a(?:ab){3}
            array('(aaa)', '(a{2}a)', true),
            array('(?:aaa)', '(?:a{2}a)', true),
            array('(abab)', '((?:ab){2})', true),
            array('(?:abab)', '(?:(?:ab){2})', true),
            array('aa(?:a)', 'a{2}(?:a)', true),
            array('(?:a)aa', '(?:a)a{2}', true),
            array('a(?:a)a', 'a(?:a)a', true),
            array('(?:aa)aa', '(?:a{2})aa', true),
            array('(?:a{2})aa', '(?:a{2})a{2}', true),
            array('aa(?:aa)aa', 'a{2}(?:aa)aa', true),
            array('a{2}(?:aa)aa', 'a{2}(?:a{2})aa', true),
            array('a{2}(?:a{2})aa', 'a{2}(?:a{2})a{2}', true),
            array('aa(?:aa)', 'a{2}(?:aa)', true),
            array('a{2}(?:aa)', 'a{2}(?:a{2})', true),
            array('(?:ab)aaaa', '(?:ab)a{2}aa', true),
            array('(?:ab)a{2}aa', '(?:ab)a{2}a{2}', true),
            array('aa(?:ab)aa', 'a{2}(?:ab)aa', true),
            array('a{2}(?:ab)aa', 'a{2}(?:ab)a{2}', true),
            array('aaaa(?:ab)', 'a{2}aa(?:ab)', true),
            array('a{2}aa(?:ab)', 'a{2}a{2}(?:ab)', true),
            array('(?:ab)aa', '(?:ab)a{2}', true),
            array('aa(?:ab)', 'a{2}(?:ab)', true),
            array('(?:ba)aa', '(?:ba)a{2}', true),
            array('aa(?:ba)aa', 'a{2}(?:ba)aa', true),
            array('a{2}(?:ba)aa', 'a{2}(?:ba)a{2}', true),
            array('aa(?:ba)', 'a{2}(?:ba)', true),
            array('(?:ab)ab', '(?:ab){2}', true),
            array('ab(?:ab)ab', '(?:ab){3}', true),
            array('ab(?:ab)', '(?:ab){2}', true),
            array('(?:abab)ab', '(?:ab){3}', true),
            array('ab(?:abab)ab', '(?:ab){4}', true),
            array('ab(?:abab)', '(?:ab){3}', true),
            array('(?:a)bab', '(?:ab){2}', true),
            array('a(?:b)ab', '(?:ab){2}', true),
            array('ab(?:a)b', '(?:ab){2}', true),
            array('aba(?:b)', '(?:ab){2}', true),
            array('[a]aa', 'a{3}', true),
            array('a[a]a', 'a{3}', true),
            array('aa[a]', 'a{3}', true),
            array('[a]bab', '(?:ab){2}', true),
            array('a[b]ab', '(?:ab){2}', true),
            array('ab[a]b', '(?:ab){2}', true),
            array('aba[b]', '(?:ab){2}', true),
        );
    }

    protected function get_test_single_charset_trivial() {
        return array(
            array('[a]', 'a'),
            array('a[a]', 'aa'),
            array('[a]a', 'aa'),
            array('a[a]a', 'aaa'),
            array('b[a]c', 'bac'),
            array('[aa]', '[aa]'),
            array('a[aa]', 'a[aa]'),
            array('[aa]a', '[aa]a'),
            array('a[aa]a', 'a[aa]a'),
            array('[^a]', '[^a]'),
            array('a[^a]', 'a[^a]'),
            array('[^a]a', '[^a]a'),
            array('a[^a]a', 'a[^a]a'),
            array('b[^a]c', 'b[^a]c'),
            array('[^aa]', '[^aa]'),
            array('a[^aa]', 'a[^aa]'),
            array('[^aa]a', '[^aa]a'),
            array('a[^aa]a', 'a[^aa]a'),
        );
    }

    protected function get_test_grouping_node_trivial() {
        return array(
            array('(?:)', ''),
            array('a(?:)', 'a'),
            array('(?:)a', 'a'),
            array('a(?:)a', 'a'),
            array('ab(?:)ab', 'abab'),
            array('a|(?:)', 'a|'),
            array('(?:)(?:aa)', '(?:aa)'),
            array('a(?:){3}', 'a'),
            array('(?:(?:a))', '(?:a)'),
        );
    }

    protected function get_test_subpattern_node_trivial() {
        return array(
            array('()', ''),
            array('a()', 'a'),
            array('()a', 'a'),
            array('a()a', 'a'),
            array('ab()ab', 'abab'),
            array('a|()', 'a|'),
            array('()(?:aa)', '(?:aa)'),
            array('a(){3}', 'a'),
            array('((a))', '(a)'),
            array('()\1', '()\1'),
            array('a()\1', 'a()\1'),
            array('()a\1', '()a\1'),
            array('a()a\1', 'a()a\1'),
            array('ab()ab\1', 'ab()ab\1'),
            array('a|()\1', 'a|()\1'),
            array('()(?:aa)\1', '()(?:aa)\1'),
            array('a(){3}\1', 'a(){3}\1'),
            array('((a))\1', '((a))\1'),
            array('\1()', '\1()'),
            array('\1a()', '\1a()'),
            array('\1()a', '\1()a'),
            array('\1a()a', '\1a()a'),
            array('\1ab()ab', '\1ab()ab'),
            array('\1a|()', '\1a|()'),
            array('\1()(?:aa)', '\1()(?:aa)'),
            array('\1a(){3}', '\1a(){3}'),
            array('\1((a))', '\1((a))'),
            array('()\2', '\2'),
            array('a()\2', 'a\2'),
            array('()a\2', 'a\2'),
            array('a()a\2', 'aa\2'),
            array('ab()ab\2', 'abab'),
            array('a|()\2', 'a|\2'), //?????
            array('()(?:aa)\2', '(?:aa)\2'),
            array('a(){3}\2', 'a\2'),
            array('((a))\2', '(a)\2'),
            array('\2()', '\2()'),
            array('\2a()', '\2a'),
            array('\2()a', '\2a'),
            array('\2a()a', '\2aa'),
            array('\2ab()ab', '\2abab'),
            array('\2a|()', '\2a|'),
            array('\2()(?:aa)', '\2(?:aa)'),
            array('\2a(){3}', '\2a'),
            array('\2((a))', '\2(a)'),
        );
    }
}

