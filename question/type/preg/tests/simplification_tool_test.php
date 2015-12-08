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

    protected function abstract_testing($function_name_for_test, $function_name_with_tests) {
        $tests = $this->$function_name_with_tests();
        for($i = 0; $i < count($tests); $i++) {
            $test_result = $tests[$i][1];
            $test_regex = $tests[$i][0];

            //var_dump($test_regex);

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

            //try {
                $result_regex = '';
                $st = new qtype_preg_simplification_tool($test_regex, $stooloptions);
                $eq = $st->$function_name_for_test();

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

                if ($result_regex !== $test_result) {
                    var_dump($test_regex);
                    var_dump($result_regex);
                    var_dump($test_result);
                    var_dump($result_regex === $test_result);
                }
            /*} catch(Exception $e) {
                var_dump('Exception!');
                var_dump($test_regex);
            }*/

            $this->assertTrue($result_regex === $test_result);
        }
    }

    public function test_common_subexpressions_trivial() {
        $this->abstract_testing('common_subexpressions', 'get_test_common_subexpressions_trivial');
    }

    public function test_grouping_node_trivial() {
        $this->abstract_testing('grouping_node', 'get_test_grouping_node_trivial');
    }

    public function test_subpattern_node_trivial() {
        $this->abstract_testing('subpattern_node', 'get_test_subpattern_node_trivial');
    }

    public function test_single_charset_trivial() {
        $this->abstract_testing('single_charset_node', 'get_test_single_charset_trivial');
    }

    public function test_alt_without_question_quant_trivial() {
        $this->abstract_testing('alt_without_question_quant', 'get_test_alt_without_question_quant_trivial');
    }

    public function test_alt_with_question_quant_trivial() {
        $this->abstract_testing('alt_with_question_quant', 'get_test_alt_with_question_quant_trivial');
    }

    public function test_quant_node_trivial() {
        $this->abstract_testing('quant_node', 'get_test_quant_node_trivial');
    }

    public function test_question_quant_for_alternative_node_trivial() {
        $this->abstract_testing('question_quant_for_alternative_node', 'get_test_question_quant_for_alternative_node_trivial');
    }

    public function test_nullable_alternative_node_trivial() {
        $this->abstract_testing('nullable_alternative_node', 'get_test_nullable_alternative_node_trivial');
    }

    public function test_quant_node_1_to_1_trivial() {
        $this->abstract_testing('quant_node_1_to_1', 'get_test_quant_node_1_to_1_trivial');
    }

    public function test_single_alternative_node_trivial() {
        $this->abstract_testing('single_alternative_node', 'get_test_single_alternative_node_trivial');
    }

    public function test_space_charset_trivial() {
        $this->abstract_testing('space_charset', 'get_test_space_charset_trivial');
    }

    public function test_space_charset_without_quant_trivial() {
        $this->abstract_testing('space_charset_without_quant', 'get_test_space_charset_without_quant_trivial');
    }

    public function test_subpattern_without_backref_trivial() {
        $this->abstract_testing('subpattern_without_backref', 'get_test_subpattern_without_backref_trivial');
    }

    public function test_space_charset_with_finit_quant_trivial() {
        $this->abstract_testing('space_charset_with_finit_quant', 'get_test_space_charset_with_finit_quant_trivial');
    }

    public function test_consecutive_quant_nodes_trivial() {
        $this->abstract_testing('consecutive_quant_nodes', 'get_test_consecutive_quant_nodes_trivial');
    }

    protected function get_test_common_subexpressions_trivial() {
        return array (
            array('aaa', 'a{3}', true),
            array('aaab', 'a{3}b', true),
            array('baaa', 'ba{3}', true),
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
            array('(aaa)', '(a{3})', true),
            array('(?:aaa)', '(?:a{3})', true),
            array('(abab)', '((?:ab){2})', true),
            array('(?:abab)', '(?:(?:ab){2})', true),
            array('aa(?:a)', 'a{3}', true),
            array('(?:a)aa', 'a{3}', true),
            array('a(?:a)a', 'a{3}', true),
            array('(?:aa)aa', 'a{4}', true),
            array('(?:a{2})aa', '(?:a{2})a{2}', true),
            array('aa(?:aa)aa', 'a{6}', true),
            array('a{2}(?:aa)aa', 'a{2}a{4}', true),
            array('a{2}(?:a{2})aa', '(?:a{2}){2}aa', true),
            array('aa(?:aa)', 'a{4}', true),
            array('a{2}(?:aa)', 'a{2}(?:a{2})', true),
            array('(?:ab)aaaa', '(?:ab)a{4}', true),
            array('aa(?:ab)aa', 'a{3}baa', true),
            array('a(?:ba)aa', 'aba{3}', true),
            array('a{2}(?:ab)aa', 'a{2}(?:ab)a{2}', true),
            array('aaaa(?:ab)', 'a{5}b', true),
            array('(?:ab)aa', '(?:ab)a{2}', true),
            array('aa(?:ab)', 'a{3}b', true),
            array('(?:ba)aa', 'ba{3}', true),
            array('aa(?:ba)aa', 'a{2}(?:ba)aa', true),
            array('a(?:(?:ba)aa)', 'a(?:ba{3})', true),
            array('a(?:(?:ba)aab)', 'a(?:ba{3}b)', true),
            array('(?:aa(?:ab))a', '(?:a{3}b)a', true),
            array('(?:baa(?:ab))a', '(?:ba{3}b)a', true),
            array('a{2}(?:ba)aa', 'a{2}ba{3}', true),
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

            array('a(?:a)', 'a{2}', true),
            array('(?:a)a', 'a{2}', true),
            array('a(?:a)a', 'a{3}', true),
            array('aa(?:aa)aa', 'a{6}', true),
            array('a(?:ab)', 'a{2}b', true),
            array('(?:ab)a', '(?:ab)a', true),
            array('a(?:ab)a', 'a{2}ba', true),
            array('a(?:ba)', 'a(?:ba)', true),
            array('(?:ba)a', 'ba{2}', true),
            array('a(?:ba)a', 'aba{2}', true),

            array('ab(?:ab)', '(?:ab){2}', true),
            array('(?:ab)ab', '(?:ab){2}', true),
            array('ab(?:ab)ab', '(?:ab){3}', true),
            array('abab(?:abab)abab', '(?:ab){6}', true),
            array('ab(?:abc)', '(?:ab){2}c', true),
            array('(?:abc)ab', '(?:abc)ab', true),
            array('ab(?:abc)ab', '(?:ab){2}cab', true),
            array('ab(?:cab)', 'ab(?:cab)', true),
            array('(?:cab)ab', 'c(?:ab){2}', true),
            array('ab(?:cab)ab', 'abc(?:ab){2}', true),

            array('(?:a)(?:a)', 'a{2}', true),
            array('a(?:a)(?:a)', 'a{3}', true),
            array('(?:a)(?:a)(?:a)', 'a{3}', true),
            array('(?:a)(?:a)a', 'a{3}', true),
            array('(?:a)a(?:a)', 'a{3}', true),
            array('(?:a)a(?:aa)aa', 'a{6}', true),
            array('(?:a)a(?:aa)a(?:a)', 'a{6}', true),
            array('aa(?:aa)a(?:a)', 'a{6}', true),
            array('a(?:a)(?:aa)(?:a)a', 'a{6}', true),
            array('a(?:a)(?:aa)aa', 'a{6}', true),
            array('aa(?:aa)(?:a)a', 'a{6}', true),
            array('(?:a)(?:ab)', 'a{2}b', true),
            array('(?:ab)(?:a)', '(?:ab)(?:a)', true),
            array('(?:a)(?:ab)a', 'a{2}ba', true),
            array('(?:a)(?:ab)(?:a)', 'a{2}b(?:a)', true),
            array('a(?:ab)(?:a)', 'a{2}b(?:a)', true),
            array('(?:a)(?:ba)', '(?:a)(?:ba)', true),
            array('(?:ba)(?:a)', 'ba{2}', true),
            array('(?:a)(?:ba)(?:a)', '(?:a)ba{2}', true),
            array('(?:a)(?:ba)a', '(?:a)ba{2}', true),
            array('a(?:ba)(?:a)', 'aba{2}', true),

            array('(?:ab)(?:ab)', '(?:ab){2}', true),
            array('(?:ab)(?:a)b', '(?:ab){2}', true),
            array('(?:ab)a(?:b)', '(?:ab){2}', true),
            array('(?:a)b(?:ab)', '(?:ab){2}', true),
            array('a(?:b)(?:ab)', '(?:ab){2}', true),
            array('(?:ab)(?:ab)ab', '(?:ab){3}', true),
            array('ab(?:ab)(?:ab)', '(?:ab){3}', true),
            array('(?:ab)(?:ab)(?:ab)', '(?:ab){3}', true),
            array('(?:a)bab(?:abab)abab', '(?:ab){6}', true),
            array('a(?:b)ab(?:abab)abab', '(?:ab){6}', true),
            array('ab(?:a)b(?:abab)abab', '(?:ab){6}', true),
            array('aba(?:b)(?:abab)abab', '(?:ab){6}', true),
            array('abab(?:abab)(?:a)bab', '(?:ab){6}', true),
            array('abab(?:abab)a(?:b)ab', '(?:ab){6}', true),
            array('abab(?:abab)ab(?:a)b', '(?:ab){6}', true),
            array('abab(?:abab)aba(?:b)', '(?:ab){6}', true),
            array('(?:ab)(?:abc)', '(?:ab){2}c', true),
            array('(?:abc)(?:ab)', '(?:abc)(?:ab)', true),
            array('(?:ab)(?:abc)ab', '(?:ab){2}cab', true),
            array('ab(?:abc)(?:ab)', '(?:ab){2}c(?:ab)', true),
            array('(?:ab)(?:cab)', '(?:ab)(?:cab)', true),
            array('(?:cab)(?:ab)', 'c(?:ab){2}', true),
            array('(?:ab)(?:cab)ab', '(?:ab)c(?:ab){2}', true),
            array('ab(?:cab)(?:ab)', 'abc(?:ab){2}', true),

            array('[a]aa', 'a{3}', true),
            array('a[a]a', 'a{3}', true),
            array('aa[a]', 'a{3}', true),
            array('[a]bab', '(?:ab){2}', true),
            array('a[b]ab', '(?:ab){2}', true),
            array('ab[a]b', '(?:ab){2}', true),
            array('aba[b]', '(?:ab){2}', true),
            array('(?:)aa', '(?:)a{2}', true),
            array('a(?:)a', 'a{2}', true),
            array('aa(?:)', 'a{2}(?:)', true),
            array('(?:a|c)(?:c|a)', '(?:a|c){2}', true),
            array('(?:ab|c)(?:c|ab)', '(?:ab|c){2}', true),
            array('a(?:ab|c)(?:c|ab)', 'a(?:ab|c){2}', true),
            array('(?:ab|c)(?:c|ab)a', '(?:ab|c){2}a', true),
//            array('[ab](?:a|b)', '[ab]{2}', true),
//            array('a|aa', 'a{1,2}', true),
//            array('(a|aa)', '(a{1,2})', true),
//            array('(?:a|aa)', 'a{1,2}', true),
//            array('aa?', 'a{1,2}', true),
//            array('aa+', 'a{2,}', true),
//            array('aa*', 'a+', true),
//            // Cast to quantifiers: complex tests
//            // Quantifier? , A lot of single characters
//            array('aaa?', 'a{2,3}', true),
//            array('aa?a', 'a{2,3}', true),
//            array('a?aa', 'a{2,3}', true),
//            array('a?a?a', 'a{1,3}', true),
//            array('aa?a?', 'a{1,3}', true),
//            array('a?aa?', 'a{1,3}', true),
//            array('a?a?a?', 'a{0,3}', true),
//            array('aa(?:a)?', 'a{2,3}', true),
//            array('aa?(?:a)?', 'a{1,3}', true),
//            array('a?a(?:a)?', 'a{1,3}', true),
//            array('a?a?(?:a)?', 'a{0,3}', true),
//            array('aa(?:a?)', 'a{2,3}', true),
//            array('aa?(?:a?)', 'a{1,3}', true),
//            array('a?a(?:a?)', 'a{1,3}', true),
//            array('a?a?(?:a?)', 'a{0,3}', true),
//            array('aa(?:a)?(?:a)?', 'a{2,4}', true),
//            array('aa?(?:a)?(?:a)?', 'a{1,4}', true),
//            array('a?a(?:a)?(?:a)?', 'a{1,4}', true),
//            array('a?a?(?:a)?(?:a)?', 'a{0,4}', true),
//            array('aa(?:a?)(?:a?)', 'a{2,4}', true),
//            array('aa?(?:a?)(?:a?)', 'a{1,4}', true),
//            array('a?a(?:a?)(?:a?)', 'a{1,4}', true),
//            array('a?a?(?:a?)(?:a?)', 'a{0,4}', true),
//            array('aa(?:a?)(?:a)?', 'a{2,4}', true),
//            array('aa?(?:a?)(?:a)?', 'a{1,4}', true),
//            array('a?a(?:a?)(?:a)?', 'a{1,4}', true),
//            array('a?a?(?:a?)(?:a)?', 'a{0,4}', true),
//            array('aa(?:a)?(?:a?)', 'a{2,4}', true),
//            array('aa?(?:a)?(?:a?)', 'a{1,4}', true),
//            array('a?a(?:a)?(?:a?)', 'a{1,4}', true),
//            array('a?a?(?:a)?(?:a?)', 'a{0,4}', true),
//            array('aa(a)?', 'a{2}(a)?', true),
//            array('aa?(a)?', 'a{1,2}(a)?', true),
//            array('a?a(a)?', 'a{1,2}(a)?', true),
//            array('a?a?(a)?', 'a{0,2}(a)?', true),
//            array('aa(a?)', 'a{2}(a?)', true),
//            array('aa?(a?)', 'a{1,2}(a?)', true),
//            array('a?a(a?)', 'a{1,2}(a?)', true),
//            array('a?a?(a?)', 'a{0,2}(a?)', true),
//            array('aa(a)?(a)?', 'a{2}(a)?(a)?', true),
//            array('aa?(a)?(a)?', 'a{1,2}(a)?(a)?', true),
//            array('a?a(a)?(a)?', 'a{1,2}(a)?(a)?', true),
//            array('a?a?(a)?(a)?', 'a{0,2}(a)?(a)?', true),
//            array('aa(a?)(a?)', 'a{2}(a?)(a?)', true),
//            array('aa?(a?)(a?)', 'a{1,2}(a?)(a?)', true),
//            array('a?a(a?)(a?)', 'a{1,2}(a?)(a?)', true),
//            array('a?a?(a?)(a?)', 'a{0,2}(a?)(a?)', true),
//            array('aa(a?)(a)?', 'a{2}(a?)(a)?', true),
//            array('aa?(a?)(a)?', 'a{1,2}(a?)(a)?', true),
//            array('a?a(a?)(a)?', 'a{1,2}(a?)(a)?', true),
//            array('a?a?(a?)(a)?', 'a{0,2}(a?)(a)?', true),
//            array('aa(a)?(a?)', 'a{2}(a)?(a?)', true),
//            array('aa?(a)?(a?)', 'a{1,2}(a)?(a?)', true),
//            array('a?a(a)?(a?)', 'a{1,2}(a)?(a?)', true),
//            array('a?a?(a)?(a?)', 'a{0,2}(a)?(a?)', true),
//            array('(aa)(a)?', '(a{2})(a)?', true),
//            array('(aa)?(a)?', '(a{2})?(a)?', true),
//            array('(aa?)(a)?', '(a{1,2})(a)?', true),
//            array('(a?a)(a)?', '(a{1,2})(a)?', true),
//            array('(a?a?)(a)?', '(a{0,2})(a)?', true),
//            array('(a?a)?(a)?', '(a{1,2})?(a)?', true),
//            array('(aa?)?(a)?', '(a{1,2})?(a)?', true),
//            array('(a?a?)?(a)?', '(a{0,2})?(a)?', true),
//            array('(aa)(a?)', '(a{2})(a?)', true),
//            array('(aa)?(a?)', '(a{2})?(a?)', true),
//            array('(aa?)(a?)', '(a{1,2})(a?)', true),
//            array('(a?a)(a?)', '(a{1,2})(a?)', true),
//            array('(a?a?)(a?)', '(a{0,2})(a?)', true),
//            array('(a?a)?(a?)', '(a{1,2})?(a?)', true),
//            array('(aa?)?(a?)', '(a{1,2})?(a?)', true),
//            array('(a?a?)?(a?)', '(a{0,2})?(a?)', true),
//            array('(aa)(?:a)?', '(a{2})a?', true),
//            array('(aa)?(?:a)?', '(a{2})?a?', true),
//            array('(aa?)(?:a)?', '(a{1,2})a?', true),
//            array('(a?a)(?:a)?', '(a{1,2})a?', true),
//            array('(a?a?)(?:a)?', '(a{0,2})a?', true),
//            array('(a?a)?(?:a)?', '(a{1,2})?a?', true),
//            array('(aa?)?(?:a)?', '(a{1,2})?a?', true),
//            array('(a?a?)?(?:a)?', '(a{0,2})?a?', true),
//            array('(aa)(?:a?)', '(a{2})a?', true),
//            array('(aa)?(?:a?)', '(a{2})?a?', true),
//            array('(aa?)(?:a?)', '(a{1,2})a?', true),
//            array('(a?a)(?:a?)', '(a{1,2})a?', true),
//            array('(a?a?)(?:a?)', '(a{0,2})a?', true),
//            array('(a?a)?(?:a?)', '(a{1,2})?a?', true),
//            array('(aa?)?(?:a?)', '(a{1,2})?a?', true),
//            array('(a?a?)?(?:a?)', '(a{0,2})?a?', true),
//            array('(?:aa)(?:a)?', 'a{2,3}', true),
//            array('(?:aa)?(?:a)?', 'a{0,3}', true),
//            array('(?:aa?)(?:a)?', 'a{1,3}', true),
//            array('(?:a?a)(?:a)?', 'a{1,3}', true),
//            array('(?:a?a?)(?:a)?', 'a{0,3}', true),
//            array('(?:a?a)?(?:a)?', 'a{0,3}', true),
//            array('(?:aa?)?(?:a)?', 'a{0,3}', true),
//            array('(?:a?a?)?(?:a)?', 'a{0,3}', true),
//            array('(?:aa)(?:a?)', 'a{2,3}', true),
//            array('(?:aa)?(?:a?)', 'a{0,3}', true),
//            array('(?:aa?)(?:a?)', 'a{1,3}', true),
//            array('(?:a?a)(?:a?)', 'a{1,3}', true),
//            array('(?:a?a?)(?:a?)', 'a{0,3}', true),
//            array('(?:a?a)?(?:a?)', 'a{0,3}', true),
//            array('(?:aa?)?(?:a?)', 'a{0,3}', true),
//            array('(?:a?a?)?(?:a?)', 'a{0,3}', true),
//            // Quantifier? a plurality of pairs of characters
//            array('abab?', 'abab?', true),
//            array('aba?b', 'aba?b', true),
//            array('ab?ab', 'ab?ab', true),
//            array('a?bab', 'a?bab', true),
//            array('ababab?', '(?:ab){2}ab?', true),
//            array('ababa?b', '(?:ab){1,2}a?b', true),
//            array('abab(?:ab)?', '(?:ab){2,3}', true),
//            array('abab(?:ab?)', '(?:ab){2}ab?', true),
//            array('abab(?:a?b)', '(?:ab){2}a?b', true),
//            array('abab(?:ab)?(?:ab)?', '(?:ab){2,4}', true),
//            array('abab(?:ab?)(?:ab?)', '(?:ab){2}(?:ab?){2}', true),
//            array('abab(?:a?b)(?:ab?)', '(?:ab){2}(?:a?b)(?:ab?)', true),
//            array('abab(?:ab?)(?:a?b)', '(?:ab){2}(?:ab?)(?:a?b)', true),
//            array('abab(?:ab?)(?:ab)?', '(?:ab){2}(?:ab?)(?:ab)?', true),
//            array('abab(?:a?b)(?:ab)?', '(?:ab){2}(?:a?b)(?:ab)?', true),
//            array('abab(?:ab)?(?:ab?)', '(?:ab){2,3}(?:ab?)', true),
//            array('abab(?:ab)?(?:a?b)', '(?:ab){2,3}(?:a?b)', true),
//            array('abab(ab)?', '(?:ab){2}(ab)?', true),
//            array('abab(ab)?(ab)?', '(?:ab){2}(ab)?(ab)?', true),
//            array('abab(ab?)(ab?)', '(?:ab){2}(ab?)(ab?)', true),
//            array('abab(a?b)(ab?)', '(?:ab){2}(a?b)(ab?)', true),
//            array('abab(ab?)(a?b)', '(?:ab){2}(ab?)(a?b)', true),
//            array('abab(ab?)(ab)?', '(?:ab){2}(ab?)(ab)?', true),
//            array('abab(a?b)(ab)?', '(?:ab){2}(a?b)(ab)?', true),
//            array('abab(ab)?(ab?)', '(?:ab){2}(ab)?(a?b)', true),
//            array('abab(ab)?(a?b)', '(?:ab){2}(ab)?(a?b)', true),
//            array('(abab)(ab)?', '((?:ab){2})(ab)?', true),
//            array('(abab)(ab?)', '((?:ab){2})(ab?)', true),
//            array('(abab)(a?b)', '((?:ab){2})(a?b)', true),
//            array('(abab)(?:ab)?', '((?:ab){2})(?:ab)?', true),
//            array('(abab)(?ab?)', '((?:ab){2})(?:ab?)', true),
//            array('(abab)(?a?b)', '((?:ab){2})(?:a?b)', true),
//            array('(?:abab)(?:ab)?', '(?:ab){2,3}', true),
//            array('(?:abab)(?:ab?)', '(?:ab){2}(?:ab?)', true),
//            array('(?:abab)(?:a?b)', '(?:ab){2}(?:a?b)', true),
//            array('aaa+', 'a{3,}', true),
//            array('aa+a', 'a{3,}', true),
//            array('a+aa', 'a{3,}', true),
//            // Quantifier + set of single characters
//            array('a+a+a', 'a{3,}', true),
//            array('aa+a+', 'a{3,}', true),
//            array('a+aa+', 'a{3,}', true),
//            array('a+a+a+', 'a{3,}', true),
//            array('aa(?:a)+', 'a{3,}', true),
//            array('aa+(?:a)+', 'a{3,}', true),
//            array('a+a(?:a)+', 'a{3,}', true),
//            array('a+a+(?:a)+', 'a{3,}', true),
//            array('aa(?:a+)', 'a{3,}', true),
//            array('aa+(?:a+)', 'a{3,}', true),
//            array('a+a(?:a+)', 'a{3,}', true),
//            array('a+a+(?:a+)', 'a{3,}', true),
//            array('aa(?:a)+(?:a)+', 'a{4,}', true),
//            array('aa+(?:a)+(?:a)+', 'a{4,}', true),
//            array('a+a(?:a)+(?:a)+', 'a{4,}', true),
//            array('a+a+(?:a)+(?:a)+', 'a{4,}', true),
//            array('aa(?:a+)(?:a+)', 'a{4,}', true),
//            array('aa+(?:a+)(?:a+)', 'a{4,}', true),
//            array('a+a(?:a+)(?:a+)', 'a{4,}', true),
//            array('a+a+(?:a+)(?:a+)', 'a{4,}', true),
//            array('aa(?:a+)(?:a)+', 'a{4,}', true),
//            array('aa+(?:a+)(?:a)+', 'a{4,}', true),
//            array('a+a(?:a+)(?:a)+', 'a{4,}', true),
//            array('a+a+(?:a+)(?:a)+', 'a{4,}', true),
//            array('aa(?:a)+(?:a+)', 'a{4,}', true),
//            array('aa+(?:a)+(?:a+)', 'a{4,}', true),
//            array('a+a(?:a)+(?:a+)', 'a{4,}', true),
//            array('a+a+(?:a)+(?:a+)', 'a{4,}', true),
//            array('aa(a)+', 'a{2}(a)+', true),
//            array('aa+(a)+', 'a{2,}(a)+', true),
//            array('a+a(a)+', 'a{2,}(a)+', true),
//            array('a+a+(a)+', 'a{2,}(a)+', true),
//            array('aa(a+)', 'a{2}(a+)', true),
//            array('aa+(a+)', 'a{2,}(a+)', true),
//            array('a+a(a+)', 'a{2,}(a+)', true),
//            array('a+a+(a+)', 'a{2,}(a+)', true),
//            array('aa(a)+(a)+', 'a{2}(?:(a)(a))+', true),
//            array('aa+(a)+(a)+', 'a{2,}(?:(a)(a))+', true),
//            array('a+a(a)+(a)+', 'a{2,}(?:(a)(a))+', true),
//            array('a+a+(a)+(a)+', 'a{2,}(?:(a)(a))+', true),
//            array('aa(a+)(a+)', 'a{2}(a+)(a+)', true),
//            array('aa+(a+)(a+)', 'a{2,}(a+)(a+)', true),
//            array('a+a(a+)(a+)', 'a{2,}(a+)(a+)', true),
//            array('a+a+(a+)(a+)', 'a{2,}(a+)(a+)', true),
//            array('aa(a+)(a)+', 'a{2}(a+)(a)+', true),
//            array('aa+(a+)(a)+', 'a{2,}(a+)(a)+', true),
//            array('a+a(a+)(a)+', 'a{2,}(a+)(a)+', true),
//            array('a+a+(a+)(a)+', 'a{2,}(a+)(a)+', true),
//            array('aa(a)+(a+)', 'a{2}(a)+(a+)', true),
//            array('aa+(a)+(a+)', 'a{2,}(a)+(a+)', true),
//            array('a+a(a)+(a+)', 'a{2,}(a)+(a+)', true),
//            array('a+a+(a)+(a+)', 'a{2,}(a)+(a+)', true),
//            array('(aa)(a)+', '(a{2})(a)+', true),
//            array('(aa)+(a)+', '(a{2})+(a)+', true),
//            array('(aa+)(a)+', '(a{2,})(a)+', true),
//            array('(a+a)(a)+', '(a{2,})(a)+', true),
//            array('(a+a+)(a)+', '(a{2,})(a)+', true),
//            array('(a+a)+(a)+', '(a{2,})+(a)+', true),
//            array('(aa+)+(a)+', '(a{2,})+(a)+', true),
//            array('(a+a+)+(a)+', '(a{2,})+(a)+', true),
//            array('(aa)(a+)', '(a{2})(a+)', true),
//            array('(aa)+(a+)', '(a{2})+(a+)', true),
//            array('(aa+)(a+)', '(a{2,})(a+)', true),
//            array('(a+a)(a+)', '(a{2,})(a+)', true),
//            array('(a+a+)(a+)', '(a{2,})(a+)', true),
//            array('(a+a)+(a+)', '(a{2,})+(a+)', true),
//            array('(aa+)+(a+)', '(a{2,})+(a+)', true),
//            array('(a+a+)+(a+)', '(a{2,})+(a+)', true),
//            array('(aa)(?:a)+', '(a{2})a+', true),
//            array('(aa)+(?:a)+', '(a{2})+a+', true),
//            array('(aa+)(?:a)+', '(a{2,})a+', true),
//            array('(a+a)(?:a)+', '(a{2,})a+', true),
//            array('(a+a+)(?:a)+', '(a{2,})a+', true),
//            array('(a+a)+(?:a)+', '(a{2,})+a+', true),
//            array('(aa+)+(?:a)+', '(a{2,})+a+', true),
//            array('(a+a+)+(?:a)+', '(a{2,})+a+', true),
//            array('(aa)(?:a+)', '(a{2})a+', true),
//            array('(aa)+(?:a+)', '(a{2})+a+', true),
//            array('(aa+)(?:a+)', '(a{2,})a+', true),
//            array('(a+a)(?:a+)', '(a{2,})a+', true),
//            array('(a+a+)(?:a+)', '(a{2,})a+', true),
//            array('(a+a)+(?:a+)', '(a{2,})+a+', true),
//            array('(aa+)+(?:a+)', '(a{2,})+a+', true),
//            array('(a+a+)+(?:a+)', '(a{2,})+a+', true),
//            array('(?:aa)(?:a)+', 'a{3,}', true),
//            array('(?:aa)+(?:a)+', 'a{3,}', true),
//            array('(?:aa+)(?:a)+', 'a{3,}', true),
//            array('(?:a+a)(?:a)+', 'a{3,}', true),
//            array('(?:a+a+)(?:a)+', 'a{3,}', true),
//            array('(?:a+a)+(?:a)+', 'a{3,}', true),
//            array('(?:aa+)+(?:a)+', 'a{3,}', true),
//            array('(?:a+a+)+(?:a)+', 'a{3,}', true),
//            array('(?:aa)(?:a+)', 'a{3,}', true),
//            array('(?:aa)+(?:a+)', 'a{3,}', true),
//            array('(?:aa+)(?:a+)', 'a{3,}', true),
//            array('(?:a+a)(?:a+)', 'a{3,}', true),
//            array('(?:a+a+)(?:a+)', 'a{3,}', true),
//            array('(?:a+a)+(?:a+)', 'a{3,}', true),
//            array('(?:aa+)+(?:a+)', 'a{3,}', true),
//            array('(?:a+a+)+(?:a+)', 'a{3,}', true),
//            // Quantifier + set of delimiters
//            array('abab+', 'abab+', true),
//            array('aba+b', 'aba+b', true),
//            array('ab+ab', 'ab+ab', true),
//            array('a+bab', 'a+bab', true),
//            array('ababab+', '(?:ab){2}ab+', true),
//            array('ababa+b', '(?:ab){1,2}a+b', true),
//            array('abab(?:ab)+', '(?:ab){3,}', true),
//            array('abab(?:ab+)', '(?:ab){2}ab+', true),
//            array('abab(?:a+b)', '(?:ab){2}a+b', true),
//            array('abab(?:ab)+(?:ab)+', '(?:ab){4,}', true),
//            array('abab(?:ab+)(?:ab+)', '(?:ab){2}(?:ab+){2}', true),
//            array('abab(?:a+b)(?:ab+)', '(?:ab){2}(?:a+b)(?:ab+)', true),
//            array('abab(?:ab+)(?:a+b)', '(?:ab){2}(?:ab+)(?:a+b)', true),
//            array('abab(?:ab+)(?:ab)+', '(?:ab){2}(?:ab+)(?:ab)+', true),
//            array('abab(?:a+b)(?:ab)+', '(?:ab){2}(?:a+b)(?:ab)+', true),
//            array('abab(?:ab)+(?:ab+)', '(?:ab){3,}(?:ab+)', true),
//            array('abab(?:ab)+(?:a+b)', '(?:ab){3,}(?:a+b)', true),
//            array('abab(ab)+', '(?:ab){2}(ab)+', true),
//            array('abab(ab)+(ab)+', '(?:ab){2}(ab)+(ab)+', true),
//            array('abab(ab+)(ab+)', '(?:ab){2}(ab+)(ab+)', true),
//            array('abab(a+b)(ab+)', '(?:ab){2}(a+b)(ab+)', true),
//            array('abab(ab+)(a+b)', '(?:ab){2}(ab+)(a+b)', true),
//            array('abab(ab+)(ab)+', '(?:ab){2}(ab+)(ab)+', true),
//            array('abab(a+b)(ab)+', '(?:ab){2}(a+b)(ab)+', true),
//            array('abab(ab)+(ab+)', '(?:ab){2}(ab)+(a+b)', true),
//            array('abab(ab)+(a+b)', '(?:ab){2}(ab)+(a+b)', true),
//            array('(abab)(ab)+', '((?:ab){2})(ab)+', true),
//            array('(abab)(ab+)', '((?:ab){2})(ab+)', true),
//            array('(abab)(a+b)', '((?:ab){2})(a+b)', true),
//            array('(abab)(?:ab)+', '((?:ab){2})(?:ab)+', true),
//            array('(abab)(?ab+)', '((?:ab){2})(?:ab+)', true),
//            array('(abab)(?a+b)', '((?:ab){2})(?:a+b)', true),
//            array('(?:abab)(?:ab)+', '(?:ab){3,}', true),
//            array('(?:abab)(?:ab+)', '(?:ab){2}(?:ab+)', true),
//            array('(?:abab)(?:a+b)', '(?:ab){2}(?:a+b)', true),
//            // Quantifier * set of single characters
//            array('aaa*', 'a{2,}', true),
//            array('aa*a', 'a{2,}', true),
//            array('a*aa', 'a{2,}', true),
//            array('a*a*a', 'a+', true),
//            array('aa*a*', 'a+', true),
//            array('a*aa*', 'a+', true),
//            array('a*a*a*', 'a*', true),
//            array('aa(?:a)*', 'a{2,}', true),
//            array('aa*(?:a)*', 'a+', true),
//            array('a*a(?:a)*', 'a+', true),
//            array('a*a*(?:a)*', 'a*', true),
//            array('aa(?:a*)', 'a{2,}', true),
//            array('aa*(?:a*)', 'a+', true),
//            array('a*a(?:a*)', 'a+', true),
//            array('a*a*(?:a*)', 'a*', true),
//            array('aa(?:a)*(?:a)*', 'a{2,}', true),
//            array('aa*(?:a)*(?:a)*', 'a+', true),
//            array('a*a(?:a)*(?:a)*', 'a+', true),
//            array('a*a*(?:a)*(?:a)*', 'a*', true),
//            array('aa(?:a*)(?:a*)', 'a{2,}', true),
//            array('aa*(?:a*)(?:a*)', 'a+', true),
//            array('a*a(?:a*)(?:a*)', 'a+', true),
//            array('a*a*(?:a*)(?:a*)', 'a*', true),
//            array('aa(?:a*)(?:a)*', 'a{2,}', true),
//            array('aa*(?:a*)(?:a)*', 'a+', true),
//            array('a*a(?:a*)(?:a)*', 'a+', true),
//            array('a*a*(?:a*)(?:a)*', 'a*', true),
//            array('aa(?:a)*(?:a*)', 'a{2,}', true),
//            array('aa*(?:a)*(?:a*)', 'a+', true),
//            array('a*a(?:a)*(?:a*)', 'a+', true),
//            array('a*a*(?:a)*(?:a*)', 'a*', true),
//            array('aa(a)*', 'a{2}(a)*', true),
//            array('aa*(a)*', 'a+(a)*', true),
//            array('a*a(a)*', 'a+(a)*', true),
//            array('a*a*(a)*', 'a*(a)*', true),
//            array('aa(a*)', 'a{2}(a*)', true),
//            array('aa*(a*)', 'a+(a*)', true),
//            array('a*a(a*)', 'a+(a*)', true),
//            array('a*a*(a*)', 'a*(a*)', true),
//            array('aa(a)*(a)*', 'a{2}(?:(a)(a))*', true),
//            array('aa*(a)*(a)*', 'a+(?:(a)(a))*', true),
//            array('a*a(a)*(a)*', 'a+(?:(a)(a))*', true),
//            array('a*a*(a)*(a)*', 'a*(?:(a)(a))*', true),
//            array('aa(a*)(a*)', 'a{2}(a*)(a*)', true),
//            array('aa*(a*)(a*)', 'a+(a*)(a*)', true),
//            array('a*a(a*)(a*)', 'a+(a*)(a*)', true),
//            array('a*a*(a*)(a*)', 'a*(a*)(a*)', true),
//            array('aa(a*)(a)*', 'a{2}(a*)(a)*', true),
//            array('aa*(a*)(a)*', 'a+(a*)(a)*', true),
//            array('a*a(a*)(a)*', 'a+(a*)(a)*', true),
//            array('a*a*(a*)(a)*', 'a*(a*)(a)*', true),
//            array('aa(a)*(a*)', 'a{2}(a)*(a*)', true),
//            array('aa*(a)*(a*)', 'a+(a)*(a*)', true),
//            array('a*a(a)*(a*)', 'a+(a)*(a*)', true),
//            array('a*a*(a)*(a*)', 'a*(a)*(a*)', true),
//            array('(aa)(a)*', '(a{2})(a)*', true),
//            array('(aa)*(a)*', '(a{2})*(a)*', true),
//            array('(aa*)(a)*', '(a+)(a)*', true),
//            array('(a*a)(a)*', '(a+)(a)*', true),
//            array('(a*a*)(a)*', '(a*)(a)*', true),
//            array('(a*a)*(a)*', '(a+)*(a)*', true),
//            array('(aa*)*(a)*', '(a+)*(a)*', true),
//            array('(a*a*)*(a)*', '(a*)*(a)*', true),
//            array('(aa)(a*)', '(a{2})(a*)', true),
//            array('(aa)*(a*)', '(a{2})*(a*)', true),
//            array('(aa*)(a*)', '(a+)(a*)', true),
//            array('(a*a)(a*)', '(a+)(a*)', true),
//            array('(a*a*)(a*)', '(a*)(a*)', true),
//            array('(a*a)*(a*)', '(a+)*(a*)', true),
//            array('(aa*)*(a*)', '(a+)*(a*)', true),
//            array('(a*a*)*(a*)', '(a*)*(a*)', true),
//            array('(aa)(?:a)*', '(a{2})a*', true),
//            array('(aa)*(?:a)*', '(a{2})*a*', true),
//            array('(aa*)(?:a)*', '(a+)a*', true),
//            array('(a*a)(?:a)*', '(a+)a*', true),
//            array('(a*a*)(?:a)*', '(a*)a*', true),
//            array('(a*a)*(?:a)*', '(a+)*a*', true),
//            array('(aa*)*(?:a)*', '(a+)*a*', true),
//            array('(a*a*)*(?:a)*', '(a*)*a*', true),
//            array('(aa)(?:a*)', '(a{2})a*', true),
//            array('(aa)*(?:a*)', '(a{2})*a*', true),
//            array('(aa*)(?:a*)', '(a+)a*', true),
//            array('(a*a)(?:a*)', '(a+)a*', true),
//            array('(a*a*)(?:a*)', '(a*)a*', true),
//            array('(a*a)*(?:a*)', '(a+)*a*', true),
//            array('(aa*)*(?:a*)', '(a+)*a*', true),
//            array('(a*a*)*(?:a*)', '(a*)*a*', true),
//            array('(?:aa)(?:a)*', 'a{2,}', true),
//            array('(?:aa)*(?:a)*', 'a*', true),
//            array('(?:aa*)(?:a)*', 'a+', true),
//            array('(?:a*a)(?:a)*', 'a+', true),
//            array('(?:a*a*)(?:a)*', 'a*', true),
//            array('(?:a*a)*(?:a)*', 'a*', true),
//            array('(?:aa*)*(?:a)*', 'a*', true),
//            array('(?:a*a*)*(?:a)*', 'a*', true),
//            array('(?:aa)(?:a*)', 'a{2,}', true),
//            array('(?:aa)*(?:a*)', 'a*', true),
//            array('(?:aa*)(?:a*)', 'a+', true),
//            array('(?:a*a)(?:a*)', 'a+', true),
//            array('(?:a*a*)(?:a*)', 'a*', true),
//            array('(?:a*a)*(?:a*)', 'a*', true),
//            array('(?:aa*)*(?:a*)', 'a*', true),
//            array('(?:a*a*)*(?:a*)', 'a*', true),
//            // Quantifier *, the set of paired characters
//            array('abab*', 'abab*', true),
//            array('aba*b', 'aba*b', true),
//            array('ab*ab', 'ab*ab', true),
//            array('a*bab', 'a*bab', true),
//            array('ababab*', '(?:ab){2}ab*', true),
//            array('ababa*b', '(?:ab){1,2}a*b', true),
//            array('abab(?:ab)*', '(?:ab){2,}', true),
//            array('abab(?:ab*)', '(?:ab){2}ab*', true),
//            array('abab(?:a*b)', '(?:ab){2}a*b', true),
//            array('abab(?:ab)*(?:ab)*', '(?:ab){2,}', true),
//            array('abab(?:ab*)(?:ab*)', '(?:ab){2}(?:ab*){2}', true),
//            array('abab(?:a*b)(?:ab*)', '(?:ab){2}(?:a*b)(?:ab*)', true),
//            array('abab(?:ab*)(?:a*b)', '(?:ab){2}(?:ab*)(?:a*b)', true),
//            array('abab(?:ab*)(?:ab)*', '(?:ab){2}(?:ab*)(?:ab)*', true),
//            array('abab(?:a*b)(?:ab)*', '(?:ab){2}(?:a*b)(?:ab)*', true),
//            array('abab(?:ab)*(?:ab*)', '(?:ab){2,}(?:ab*)', true),
//            array('abab(?:ab)*(?:a*b)', '(?:ab){2,}(?:a*b)', true),
//            array('abab(ab)*', '(?:ab){2}(ab)*', true),
//            array('abab(ab)*(ab)*', '(?:ab){2}(ab)*(ab)*', true),
//            array('abab(ab*)(ab*)', '(?:ab){2}(ab*)(ab*)', true),
//            array('abab(a*b)(ab*)', '(?:ab){2}(a*b)(ab*)', true),
//            array('abab(ab*)(a*b)', '(?:ab){2}(ab*)(a*b)', true),
//            array('abab(ab*)(ab)*', '(?:ab){2}(ab*)(ab)*', true),
//            array('abab(a*b)(ab)*', '(?:ab){2}(a*b)(ab)*', true),
//            array('abab(ab)*(ab*)', '(?:ab){2}(ab)*(a*b)', true),
//            array('abab(ab)*(a*b)', '(?:ab){2}(ab)*(a*b)', true),
//            array('(abab)(ab)*', '((?:ab){2})(ab)*', true),
//            array('(abab)(ab*)', '((?:ab){2})(ab*)', true),
//            array('(abab)(a*b)', '((?:ab){2})(a*b)', true),
//            array('(abab)(?:ab)*', '((?:ab){2})(?:ab)*', true),
//            array('(abab)(?ab*)', '((?:ab){2})(?:ab*)', true),
//            array('(abab)(?a*b)', '((?:ab){2})(?:a*b)', true),
//            array('(?:abab)(?:ab)*', '(?:ab){2,}', true),
//            array('(?:abab)(?:ab*)', '(?:ab){2}(?:ab*)', true),
//            array('(?:abab)(?:a*b)', '(?:ab){2}(?:a*b)', true),
//            // Simple tests on combinations of quantifiers
//            array('(?:a?)?', 'a?', true),
//            array('(?:a?)+', 'a*', true),
//            array('(?:a?)*', 'a*', true),
//            array('(?:a+)?', 'a*', true),
//            array('(?:a+)+', 'a+', true),
//            array('(?:a+)*', 'a*', true),
//            array('(?:a*)?', 'a*', true),
//            array('(?:a*)+', 'a*', true),
//            array('(?:a*)*', 'a*', true),
//            array('(a?)?', '(a?)?', true),
//            array('(a?)+', '(a?)+', true),
//            array('(a?)*', '(a?)*', true),
//            array('(a+)?', '(a*)', true),
//            array('(a+)+', '(a+)+', true),
//            array('(a+)*', '(a+)*', true),
//            array('(a*)?', '(a*)', true),
//            array('(a*)+', '(a*)', true),
//            array('(a*)*', '(a*)', true),
//            array('(?:a?){1,2}', 'a{0,2}', true),
//            array('(?:a+){1,2}', 'a+', true),
//            array('(?:a*){1,2}', 'a*', true),
//            array('(a?){1,2}', '(a){0,2}', true),
//            array('(a+){1,2}', '(a)+', true),
//            array('(a*){1,2}', '(a*)', true),
//            // Integration tests on a combination of quantifiers:
//            // Match a single character within the group with a single character to the left
//            array('a(?:a?)?', 'aa?', true), // или a{1,2}
//            array('a(?:a?)+', 'a+', true),
//            array('a(?:a?)*', 'a+', true),
//            array('a(?:a+)?', 'a*', true),
//            array('a(?:a+)+', 'aa+', true),
//            array('a(?:a+)*', 'a+', true),
//            array('a(?:a*)?', 'a+', true),
//            array('a(?:a*)+', 'a+', true),
//            array('a(?:a*)*', 'a+', true),
//            // Integration tests on a combination of quantifiers:
//            // Match a single character inside a subpattern with a single character to the left
//            array('a(a?)?', 'a(a?)?', true),
//            array('a(a?)+', 'a(a?)+', true),
//            array('a(a?)*', 'a(a?)*', true),
//            array('a(a+)?', 'a(a*)', true),
//            array('a(a+)+', 'a(a+)+', true),
//            array('a(a+)*', 'a(a+)*', true),
//            array('a(a*)?', 'a(a*)', true),
//            array('a(a*)+', 'a(a*)', true),
//            array('a(a*)*', 'a(a*)', true),
//            // Integration tests on a combination of quantifiers:
//            // single match character within the group with a single character to the right
//            array('(?:a?)?a', 'a{1,2}', true),
//            array('(?:a?)+a', 'a+', true),
//            array('(?:a?)*a', 'a+', true),
//            array('(?:a+)?a', 'a+', true),
//            array('(?:a+)+a', 'a{2,}', true),
//            array('(?:a+)*a', 'a+', true),
//            array('(?:a*)?a', 'a+', true),
//            array('(?:a*)+a', 'a+', true),
//            array('(?:a*)*a', 'a+', true),
//            // Integration tests on a combination of quantifiers:
//            // single match character inside a subpattern with a single character to the right
//            array('(a?)?a', '(a?)?a', true),
//            array('(a?)+a', '(a?)+a', true),
//            array('(a?)*a', '(a?)*a', true),
//            array('(a+)?a', '(a*)a', true),
//            array('(a+)+a', '(a+)+a', true),
//            array('(a+)*a', '(a+)*a', true),
//            array('(a*)?a', '(a*)a', true),
//            array('(a*)+a', '(a*)a', true),
//            array('(a*)*a', '(a*)a', true),
//            // Integration tests on a combination of quantifiers:
//            // single match character within the group with a single character to the left
//            array('a(?:a?){1,2}', 'a{1,3}', true),
//            array('a(?:a+){1,2}', 'a{2,}', true),
//            array('a(?:a*){1,2}', 'a+', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with a single character to the left
//            array('a(a?){1,2}', 'a(a?){1,2}', true),
//            array('a(a+){1,2}', 'a(a+)', true),
//            array('a(a*){1,2}', 'a(a*)', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with a single character to the right
//            array('(?:a?){1,2}a', 'a{1,3}', true),
//            array('(?:a+){1,2}a', 'a{2,}', true),
//            array('(?:a*){1,2}a', 'a+', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with a single character to the right
//            array('(a?){1,2}a', '(a?){1,2}a', true),
//            array('(a+){1,2}a', '(a+)a', true),
//            array('(a*){1,2}a', '(a*)a', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ab(?:a?)?', 'aba?', true),
//            array('ab(?:a?)+', 'aba*', true),
//            array('ab(?:a?)*', 'aba*', true),
//            array('ab(?:a+)?', 'aba*', true),
//            array('ab(?:a+)+', 'aba+', true),
//            array('ab(?:a+)*', 'aba*', true),
//            array('ab(?:a*)?', 'aba*', true),
//            array('ab(?:a*)+', 'aba*', true),
//            array('ab(?:a*)*', 'aba*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the leftmost Character in the sequence
//            // Characters left
//            array('ab(a?)?', 'ab(a?)?', true),
//            array('ab(a?)+', 'ab(a?)+', true),
//            array('ab(a?)*', 'ab(a?)*', true),
//            array('ab(a+)?', 'ab(a*)', true),
//            array('ab(a+)+', 'ab(a+)+', true),
//            array('ab(a+)*', 'ab(a+)*', true),
//            array('ab(a*)?', 'ab(a*)', true),
//            array('ab(a*)+', 'ab(a*)', true),
//            array('ab(a*)*', 'ab(a*)', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters from the right
//            array('(?:a?)?ab', 'a{0,2}b', true),
//            array('(?:a?)+ab', 'a+b', true),
//            array('(?:a?)*ab', 'a+b', true),
//            array('(?:a+)?ab', 'a*b', true),
//            array('(?:a+)+ab', 'a{2,}b', true),
//            array('(?:a+)*ab', 'a+b', true),
//            array('(?:a*)?ab', 'a+b', true),
//            array('(?:a*)+ab', 'a+b', true),
//            array('(?:a*)*ab', 'a+b', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the leftmost character in the sequence
//            // Characters to the right
//            array('(a?)?ab', '(a?)?ab', true),
//            array('(a?)+ab', '(a?)+ab', true),
//            array('(a?)*ab', '(a?)*ab', true),
//            array('(a+)?ab', '(a*)ab', true),
//            array('(a+)+ab', '(a+)+ab', true),
//            array('(a+)*ab', '(a+)*ab', true),
//            array('(a*)?ab', '(a*)ab', true),
//            array('(a*)+ab', '(a*)ab', true),
//            array('(a*)*ab', '(a*)ab', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ab(?:a?){1,2}', 'aba{0,2}', true),
//            array('ab(?:a+){1,2}', 'aba+', true),
//            array('ab(?:a*){1,2}', 'aba*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the leftmost character in the sequence
//            // Characters left
//            array('ab(a?){1,2}', 'ab(a?){1,2}', true),
//            array('ab(a+){1,2}', 'ab(a+){1,2}', true),
//            array('ab(a*){1,2}', 'ab(a*)', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters from the right
//            array('(?:a?){1,2}ab', 'a{1,3}b', true),
//            array('(?:a+){1,2}ab', 'a+b', true),
//            array('(?:a*){1,2}ab', 'a+b', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the leftmost character in the sequence
//            // Characters to the right
//            array('(a?){1,2}ab', '(a?){1,2}ab', true),
//            array('(a+){1,2}ab', '(a+){1,2}ab', true),
//            array('(a*){1,2}ab', '(a*)ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Character on the left
//            array('a(?:ab?)?', 'a(?:ab?)?', true),
//            array('a(?:ab?)+', 'a(?:ab?)+', true),
//            array('a(?:ab?)*', 'a(?:ab?)*', true),
//            array('a(?:ab+)?', 'a(?:ab+)?', true),
//            array('a(?:ab+)+', 'a(?:ab+)+', true),
//            array('a(?:ab+)*', 'a(?:ab+)*', true),
//            array('a(?:ab*)?', 'a(?:ab*)?', true),
//            array('a(?:ab*)+', 'a(?:ab*)+', true),
//            array('a(?:ab*)*', 'a(?:ab*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with
//            // Character on the left
//            array('a(ab?)?', 'a(ab?)?', true),
//            array('a(ab?)+', 'a(ab?)+', true),
//            array('a(ab?)*', 'a(ab?)*', true),
//            array('a(ab+)?', 'a(ab+)?', true),
//            array('a(ab+)+', 'a(ab+)+', true),
//            array('a(ab+)*', 'a(ab+)*', true),
//            array('a(ab*)?', 'a(ab*)?', true),
//            array('a(ab*)+', 'a(ab*)+', true),
//            array('a(ab*)*', 'a(ab*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Character to the right
//            array('(?:ab?)?a', '(?:ab?)?a', true),
//            array('(?:ab?)+a', '(?:ab?)+a', true),
//            array('(?:ab?)*a', '(?:ab?)*a', true),
//            array('(?:ab+)?a', '(?:ab+)?a', true),
//            array('(?:ab+)+a', '(?:ab+)+a', true),
//            array('(?:ab+)*a', '(?:ab+)*a', true),
//            array('(?:ab*)?a', '(?:ab*)?a', true),
//            array('(?:ab*)+a', '(?:ab*)+a', true),
//            array('(?:ab*)*a', '(?:ab*)*a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with
//            // Character to the right
//            array('(ab?)?a', '(ab?)?a', true),
//            array('(ab?)+a', '(ab?)+a', true),
//            array('(ab?)*a', '(ab?)*a', true),
//            array('(ab+)?a', '(ab+)?a', true),
//            array('(ab+)+a', '(ab+)+a', true),
//            array('(ab+)*a', '(ab+)*a', true),
//            array('(ab*)?a', '(ab*)?a', true),
//            array('(ab*)+a', '(ab*)+a', true),
//            array('(ab*)*a', '(ab*)*a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Character on the left
//            array('a(?:ab?){1,2}', 'a(?:ab?){1,2}', true),
//            array('a(?:ab+){1,2}', 'a(?:ab+){1,2}', true),
//            array('a(?:ab*){1,2}', 'a(?:ab*)', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with
//            // Character on the left
//            array('a(ab?){1,2}', 'a(ab?){1,2}', true),
//            array('a(ab+){1,2}', 'a(ab+){1,2}', true),
//            array('a(ab*){1,2}', 'a(ab*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Character to the right
//            array('(?:ab?){1,2}a', '(?:ab?){1,2}a', true),
//            array('(?:ab+){1,2}a', '(?:ab+){1,2}a', true),
//            array('(?:ab*){1,2}a', '(?:ab*){1,2}a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with
//            // Character to the right
//            array('(ab?){1,2}a', '(ab?){1,2}a', true),
//            array('(ab+){1,2}a', '(ab+){1,2}a', true),
//            array('(ab*){1,2}a', '(ab*)a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(?:ab?)?', 'ab(?:ab?)?', true),
//            array('ab(?:ab?)+', 'ab(?:ab?)+', true),
//            array('ab(?:ab?)*', 'ab(?:ab?)*', true),
//            array('ab(?:ab+)?', 'ab(?:ab+)?', true),
//            array('ab(?:ab+)+', 'ab(?:ab+)+', true),
//            array('ab(?:ab+)*', 'ab(?:ab+)*', true),
//            array('ab(?:ab*)?', 'ab(?:ab*)?', true),
//            array('ab(?:ab*)+', 'ab(?:ab*)+', true),
//            array('ab(?:ab*)*', 'ab(?:ab*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Leftmost character in the sequence of characters left
//            array('ab(ab?)?', 'ab(ab?)?', true),
//            array('ab(ab?)+', 'ab(ab?)+', true),
//            array('ab(ab?)*', 'ab(ab?)*', true),
//            array('ab(ab+)?', 'ab(ab+)?', true),
//            array('ab(ab+)+', 'ab(ab+)+', true),
//            array('ab(ab+)*', 'ab(ab+)*', true),
//            array('ab(ab*)?', 'ab(ab*)?', true),
//            array('ab(ab*)+', 'ab(ab*)+', true),
//            array('ab(ab*)*', 'ab(ab*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Leftmost character of a sequence of characters to the right
//            array('(?:ab?)?ab', '(?:ab?)?ab', true),
//            array('(?:ab?)+ab', '(?:ab?)+ab', true),
//            array('(?:ab?)*ab', '(?:ab?)*ab', true),
//            array('(?:ab+)?ab', '(?:ab+)?ab', true),
//            array('(?:ab+)+ab', '(?:ab+)+ab', true),
//            array('(?:ab+)*ab', '(?:ab+)*ab', true),
//            array('(?:ab*)?ab', '(?:ab*)?ab', true),
//            array('(?:ab*)+ab', '(?:ab*)+ab', true),
//            array('(?:ab*)*ab', '(?:ab*)*ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Leftmost character of a sequence of characters from the right
//            array('(ab?)?ab', '(ab?)?ab', true),
//            array('(ab?)+ab', '(ab?)+ab', true),
//            array('(ab?)*ab', '(ab?)*ab', true),
//            array('(ab+)?ab', '(ab+)?ab', true),
//            array('(ab+)+ab', '(ab+)+ab', true),
//            array('(ab+)*ab', '(ab+)*ab', true),
//            array('(ab*)?ab', '(ab*)?ab', true),
//            array('(ab*)+ab', '(ab*)+ab', true),
//            array('(ab*)*ab', '(ab*)*ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(?:ab?){1,2}', 'ab(?:ab?){1,2}', true),
//            array('ab(?:ab+){1,2}', 'ab(?:ab+){1,2}', true),
//            array('ab(?:ab*){1,2}', 'ab(?:ab*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Leftmost character in the sequence of characters left
//            array('ab(ab?){1,2}', 'ab(ab?){1,2}', true),
//            array('ab(ab+){1,2}', 'ab(ab+){1,2}', true),
//            array('ab(ab*){1,2}', 'ab(ab*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Leftmost character of a sequence of characters to the right
//            array('(?:ab?){1,2}ab', '(?:ab?){1,2}ab', true),
//            array('(?:ab+){1,2}ab', '(?:ab+){1,2}ab', true),
//            array('(?:ab*){1,2}ab', '(?:ab*){1,2}ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Leftmost character of a sequence of characters from the right
//            array('(ab?){1,2}ab', '(ab?){1,2}ab', true),
//            array('(ab+){1,2}ab', '(ab+){1,2}ab', true),
//            array('(ab*){1,2}ab', '(ab*){1,2}ab', true),
//            // Integration tests on a combination of quantifiersв: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ab(?:a?)?', 'aba?', true),
//            array('ab(?:a?)+', 'aba*', true),
//            array('ab(?:a?)*', 'aba*', true),
//            array('ab(?:a+)?', 'aba*', true),
//            array('ab(?:a+)+', 'aba+', true),
//            array('ab(?:a+)*', 'aba*', true),
//            array('ab(?:a*)?', 'aba*', true),
//            array('ab(?:a*)+', 'aba*', true),
//            array('ab(?:a*)*', 'aba*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ab(a?)?', 'ab(a?)?', true),
//            array('ab(a?)+', 'ab(a?)+', true),
//            array('ab(a?)*', 'ab(a?)*', true),
//            array('ab(a+)?', 'ab(a+)?', true),
//            array('ab(a+)+', 'ab(a+)+', true),
//            array('ab(a+)*', 'ab(a+)*', true),
//            array('ab(a*)?', 'ab(a*)?', true),
//            array('ab(a*)+', 'ab(a*)+', true),
//            array('ab(a*)*', 'ab(a*)*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the rightmost characters of the
//            // A sequence of characters on the left
//            array('ba(?:a?)?', 'ba{1,2}', true),
//            array('ba(?:a?)+', 'ba+', true),
//            array('ba(?:a?)*', 'ba+', true),
//            array('ba(?:a+)?', 'ba+', true),
//            array('ba(?:a+)+', 'ba{2,}', true),
//            array('ba(?:a+)*', 'ba+', true),
//            array('ba(?:a*)?', 'ba+', true),
//            array('ba(?:a*)+', 'ba+', true),
//            array('ba(?:a*)*', 'ba+', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the rightmost characters of the
//            // A sequence of characters on the left
//            array('ba(a?)?', 'ba(a?)?', true),
//            array('ba(a?)+', 'ba(a?)+', true),
//            array('ba(a?)*', 'ba(a?)*', true),
//            array('ba(a+)?', 'ba(a+)?', true),
//            array('ba(a+)+', 'ba(a+)+', true),
//            array('ba(a+)*', 'ba(a+)*', true),
//            array('ba(a*)?', 'ba(a*)?', true),
//            array('ba(a*)+', 'ba(a*)+', true),
//            array('ba(a*)*', 'ba(a*)*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the rightmost characters of
//            // A sequence of characters from the right
//            array('(?:a?)?ba', 'a?ba', true),
//            array('(?:a?)+ba', 'a*ba', true),
//            array('(?:a?)*ba', 'a*ba', true),
//            array('(?:a+)?ba', 'a*ba', true),
//            array('(?:a+)+ba', 'a+ba', true),
//            array('(?:a+)*ba', 'a*ba', true),
//            array('(?:a*)?ba', 'a*ba', true),
//            array('(?:a*)+ba', 'a*ba', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the rightmost characters of
//            // A sequence of characters from the right
//            array('(a?)?ba', '(a?)?ba', true),
//            array('(a?)+ba', '(a?)+ba', true),
//            array('(a?)*ba', '(a?)*ba', true),
//            array('(a+)?ba', '(a+)?ba', true),
//            array('(a+)+ba', '(a+)+ba', true),
//            array('(a+)*ba', '(a+)*ba', true),
//            array('(a*)?ba', '(a*)?ba', true),
//            array('(a*)+ba', '(a*)+ba', true),
//            array('(a*)*ba', '(a*)*ba', true),
//            array('(a*)*ba', '(a*)*ba', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ba(?:a?){1,2}', 'ba{1,3}', true),
//            array('ba(?:a+){1,2}', 'ba{2,}', true),
//            array('ba(?:a*){1,2}', 'ba+', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the rightmost characters of the
//            // A sequence of characters on the left
//            array('ba(a?){1,2}', 'ba(a?){1,2}', true),
//            array('ba(a+){1,2}', 'ba(a+){1,2}', true),
//            array('ba(a*){1,2}', 'ba(a*){1,2}', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the rightmost characters of
//            // A sequence of characters from the right
//            array('(?:a?){1,2}ba', 'a{0,2}ba', true),
//            array('(?:a+){1,2}ba', 'a+ba', true),
//            array('(?:a*){1,2}ba', 'a*ba', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the rightmost characters of the
//            // A sequence of characters from the right
//            array('(a?){1,2}ba', '(a?){1,2}ba', true),
//            array('(a+){1,2}ba', '(a+){1,2}ba', true),
//            array('(a*){1,2}ba', '(a*){1,2}ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Character on the left
//            array('a(?:ba?)?', 'a(?:ba?)?', true),
//            array('a(?:ba?)+', 'a(?:ba?)+', true),
//            array('a(?:ba?)*', 'a(?:ba?)*', true),
//            array('a(?:ba+)?', 'a(?:ba+)?', true),
//            array('a(?:ba+)+', 'a(?:ba+)+', true),
//            array('a(?:ba+)*', 'a(?:ba+)*', true),
//            array('a(?:ba*)?', 'a(?:ba*)?', true),
//            array('a(?:ba*)+', 'a(?:ba*)+', true),
//            array('a(?:ba*)*', 'a(?:ba*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Character on the left
//            array('a(ba?)?', 'a(ba?)?', true),
//            array('a(ba?)+', 'a(ba?)+', true),
//            array('a(ba?)*', 'a(ba?)*', true),
//            array('a(ba+)?', 'a(ba+)?', true),
//            array('a(ba+)+', 'a(ba+)+', true),
//            array('a(ba+)*', 'a(ba+)*', true),
//            array('a(ba*)?', 'a(ba*)?', true),
//            array('a(ba*)+', 'a(ba*)+', true),
//            array('a(ba*)*', 'a(ba*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Character to the right
//            array('(?:ba?)?a', '(?:ba?)?a', true),
//            array('(?:ba?)+a', '(?:ba?)+a', true),
//            array('(?:ba?)*a', '(?:ba?)*a', true),
//            array('(?:ba+)?a', '(?:ba+)?a', true),
//            array('(?:ba+)+a', '(?:ba+)+a', true),
//            array('(?:ba+)*a', '(?:ba+)*a', true),
//            array('(?:ba*)?a', '(?:ba*)?a', true),
//            array('(?:ba*)+a', '(?:ba*)+a', true),
//            array('(?:ba*)*a', '(?:ba*)*a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Character to the right
//            array('(ba?)?a', '(ba?)?a', true),
//            array('(ba?)+a', '(ba?)+a', true),
//            array('(ba?)*a', '(ba?)*a', true),
//            array('(ba+)?a', '(ba+)?a', true),
//            array('(ba+)+a', '(ba+)+a', true),
//            array('(ba+)*a', '(ba+)*a', true),
//            array('(ba*)?a', '(ba*)?a', true),
//            array('(ba*)+a', '(ba*)+a', true),
//            array('(ba*)*a', '(ba*)*a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Character on the left
//            array('a(?:ba?){1,2}', 'a(?:ba?){1,2}', true),
//            array('a(?:ba+){1,2}', 'a(?:ba+){1,2}', true),
//            array('a(?:ba*){1,2}', 'a(?:ba*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Character on the left
//            array('a(ba?){1,2}', 'a(ba?){1,2}', true),
//            array('a(ba+){1,2}', 'a(ba+){1,2}', true),
//            array('a(ba*){1,2}', 'a(ba*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Character to the right
//            array('(?:ba?){1,2}a', '(?:ba?){1,2}a', true),
//            array('(?:ba+){1,2}a', '(?:ba+){1,2}a', true),
//            array('(?:ba*){1,2}a', '(?:ba*){1,2}a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Character on the left
//            array('(ba?){1,2}a', '(ba?){1,2}a', true),
//            array('(ba+){1,2}a', '(ba+){1,2}a', true),
//            array('(ba*){1,2}a', '(ba*){1,2}a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // The rightmost characters of a sequence of characters on the left
//            array('ba(?:ab?)?', 'ba(?:ab?)?', true),
//            array('ba(?:ab?)+', 'ba(?:ab?)+', true),
//            array('ba(?:ab?)*', 'ba(?:ab?)*', true),
//            array('ba(?:ab+)?', 'ba(?:ab+)?', true),
//            array('ba(?:ab+)+', 'ba(?:ab+)+', true),
//            array('ba(?:ab+)*', 'ba(?:ab+)*', true),
//            array('ba(?:ab*)?', 'ba(?:ab*)?', true),
//            array('ba(?:ab*)+', 'ba(?:ab*)+', true),
//            array('ba(?:ab*)*', 'ba(?:ab*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(?:ba?)?', 'ab(?:ba?)?', true),
//            array('ab(?:ba?)+', 'ab(?:ba?)+', true),
//            array('ab(?:ba?)*', 'ab(?:ba?)*', true),
//            array('ab(?:ba+)?', 'ab(?:ba+)?', true),
//            array('ab(?:ba+)+', 'ab(?:ba+)+', true),
//            array('ab(?:ba+)*', 'ab(?:ba+)*', true),
//            array('ab(?:ba*)?', 'ab(?:ba*)?', true),
//            array('ab(?:ba*)+', 'ab(?:ba*)+', true),
//            array('ab(?:ba*)*', 'ab(?:ba*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Right character from a sequence of characters on the left
//            array('ba(ab?)?', 'ba(ab?)?', true),
//            array('ba(ab?)+', 'ba(ab?)+', true),
//            array('ba(ab?)*', 'ba(ab?)*', true),
//            array('ba(ab+)?', 'ba(ab+)?', true),
//            array('ba(ab+)+', 'ba(ab+)+', true),
//            array('ba(ab+)*', 'ba(ab+)*', true),
//            array('ba(ab*)?', 'ba(ab*)?', true),
//            array('ba(ab*)+', 'ba(ab*)+', true),
//            array('ba(ab*)*', 'ba(ab*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(ba?)?', 'ab(ba?)?', true),
//            array('ab(ba?)+', 'ab(ba?)+', true),
//            array('ab(ba?)*', 'ab(ba?)*', true),
//            array('ab(ba+)?', 'ab(ba+)?', true),
//            array('ab(ba+)+', 'ab(ba+)+', true),
//            array('ab(ba+)*', 'ab(ba+)*', true),
//            array('ab(ba*)?', 'ab(ba*)?', true),
//            array('ab(ba*)+', 'ab(ba*)+', true),
//            array('ab(ba*)*', 'ab(ba*)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // The rightmost characters of a sequence of characters to the right
//            array('(?:ab?)?ba', '(?:ab?)?ba', true),
//            array('(?:ab?)+ba', '(?:ab?)+ba', true),
//            array('(?:ab?)*ba', '(?:ab?)*ba', true),
//            array('(?:ab+)?ba', '(?:ab+)?ba', true),
//            array('(?:ab+)+ba', '(?:ab+)+ba', true),
//            array('(?:ab+)*ba', '(?:ab+)*ba', true),
//            array('(?:ab*)?ba', '(?:ab*)?ba', true),
//            array('(?:ab*)+ba', '(?:ab*)+ba', true),
//            array('(?:ab*)*ba', '(?:ab*)*ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Leftmost character of a sequence of characters to the right
//            array('(?:ba?)?ab', '(?:ba?)?ab', true),
//            array('(?:ba?)+ab', '(?:ba?)+ab', true),
//            array('(?:ba?)*ab', '(?:ba?)*ab', true),
//            array('(?:ba+)?ab', '(?:ba+)?ab', true),
//            array('(?:ba+)+ab', '(?:ba+)+ab', true),
//            array('(?:ba+)*ab', '(?:ba+)*ab', true),
//            array('(?:ba*)?ab', '(?:ba*)?ab', true),
//            array('(?:ba*)+ab', '(?:ba*)+ab', true),
//            array('(?:ba*)*ab', '(?:ba*)*ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Right character from a sequence of characters from the right
//            array('(ab?)?ba', '(ab?)?ba', true),
//            array('(ab?)+ba', '(ab?)+ba', true),
//            array('(ab?)*ba', '(ab?)*ba', true),
//            array('(ab+)?ba', '(ab+)?ba', true),
//            array('(ab+)+ba', '(ab+)+ba', true),
//            array('(ab+)*ba', '(ab+)*ba', true),
//            array('(ab*)?ba', '(ab*)?ba', true),
//            array('(ab*)+ba', '(ab*)+ba', true),
//            array('(ab*)*ba', '(ab*)*ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Leftmost character of a sequence of characters to the right
//            array('(ba?)?ab', '(ba?)?ab', true),
//            array('(ba?)+ab', '(ba?)+ab', true),
//            array('(ba?)*ab', '(ba?)*ab', true),
//            array('(ba+)?ab', '(ba+)?ab', true),
//            array('(ba+)+ab', '(ba+)+ab', true),
//            array('(ba+)*ab', '(ba+)*ab', true),
//            array('(ba*)?ab', '(ba*)?ab', true),
//            array('(ba*)+ab', '(ba*)+ab', true),
//            array('(ba*)*ab', '(ba*)*ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // The rightmost characters of a sequence of characters on the left
//            array('ba(?:ab?){1,2}', 'ba(?:ab?){1,2}', true),
//            array('ba(?:ab+){1,2}', 'ba(?:ab+){1,2}', true),
//            array('ba(?:ab*){1,2}', 'ba(?:ab*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(?:ba?){1,2}', 'ab(?:ba?){1,2}', true),
//            array('ab(?:ba+){1,2}', 'ab(?:ba+){1,2}', true),
//            array('ab(?:ba*){1,2}', 'ab(?:ba*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Right character from a sequence of characters on the left
//            array('ba(ab?){1,2}', 'ba(ab?){1,2}', true),
//            array('ba(ab+){1,2}', 'ba(ab+){1,2}', true),
//            array('ba(ab*){1,2}', 'ba(ab*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(ba?){1,2}', 'ab(ba?){1,2}', true),
//            array('ab(ba+){1,2}', 'ab(ba+){1,2}', true),
//            array('ab(ba*){1,2}', 'ab(ba*){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // The rightmost characters of a sequence of characters to the right
//            array('(?:ab?){1,2}ba', '(?:ab?){1,2}ba', true),
//            array('(?:ab+){1,2}ba', '(?:ab+){1,2}ba', true),
//            array('(?:ab*){1,2}ba', '(?:ab*){1,2}ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Leftmost character of a sequence of characters to the right
//            array('(?:ba?){1,2}ab', '(?:ba?){1,2}ab', true),
//            array('(?:ba+){1,2}ab', '(?:ba+){1,2}ab', true),
//            array('(?:ba*){1,2}ab', '(?:ba*){1,2}ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Right character from a sequence of characters from the right
//            array('(ab?){1,2}ba', '(ab?){1,2}ba', true),
//            array('(ab+){1,2}ba', '(ab+){1,2}ba', true),
//            array('(ab*){1,2}ba', '(ab*){1,2}ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Leftmost character of a sequence of characters to the right
//            array('(ba?){1,2}ab', '(ba?){1,2}ab', true),
//            array('(ba+){1,2}ab', '(ba+){1,2}ab', true),
//            array('(ba*){1,2}ab', '(ba*){1,2}ab', true),
//            // Integration tests on a combination of quantifiers: It does not help
//            array('a(?:cb?)?', 'a(?:cb?)?', true),
//            array('a(?:cb?)+', 'a(?:cb?)+', true),
//            array('a(?:cb?)*', 'a(?:cb?)*', true),
//            array('a(?:cb+)?', 'a(?:cb+)?', true),
//            array('a(?:cb+)+', 'a(?:cb+)+', true),
//            array('a(?:cb+)*', 'a(?:cb+)*', true),
//            array('a(?:cb*)?', 'a(?:cb*)?', true),
//            array('a(?:cb*)+', 'a(?:cb*)+', true),
//            array('a(?:cb*)*', 'a(?:cb*)*', true),
//            array('a(cb?)?', 'a(cb?)?', true),
//            array('a(cb?)+', 'a(cb?)+', true),
//            array('a(cb?)*', 'a(cb?)*', true),
//            array('a(cb+)?', 'a(cb+)?', true),
//            array('a(cb+)+', 'a(cb+)+', true),
//            array('a(cb+)*', 'a(cb+)*', true),
//            array('a(cb*)?', 'a(cb*)?', true),
//            array('a(cb*)+', 'a(cb*)+', true),
//            array('a(cb*)*', 'a(cb*)*', true),
//            array('(?:cb?)?a', '(?:cb?)?a', true),
//            array('(?:cb?)+a', '(?:cb?)+a', true),
//            array('(?:cb?)*a', '(?:cb?)*a', true),
//            array('(?:cb+)?a', '(?:cb+)?a', true),
//            array('(?:cb+)+a', '(?:cb+)+a', true),
//            array('(?:cb+)*a', '(?:cb+)*a', true),
//            array('(?:cb*)?a', '(?:cb*)?a', true),
//            array('(?:cb*)+a', '(?:cb*)+a', true),
//            array('(?:cb*)*a', '(?:cb*)*a', true),
//            array('(cb?)?a', '(cb?)?a', true),
//            array('(cb?)+a', '(cb?)+a', true),
//            array('(cb?)*a', '(cb?)*a', true),
//            array('(cb+)?a', '(cb+)?a', true),
//            array('(cb+)+a', '(cb+)+a', true),
//            array('(cb+)*a', '(cb+)*a', true),
//            array('(cb*)?a', '(cb*)?a', true),
//            array('(cb*)+a', '(cb*)+a', true),
//            array('(cb*)*a', '(cb*)*a', true),
//            array('a(?:cb?){1,2}', 'a(?:cb?){1,2}', true),
//            array('a(?:cb+){1,2}', 'a(?:cb+){1,2}', true),
//            array('a(?:cb*){1,2}', 'a(?:cb*){1,2}', true),
//            array('a(cb?){1,2}', 'a(cb?){1,2}', true),
//            array('a(cb+){1,2}', 'a(cb+){1,2}', true),
//            array('a(cb*){1,2}', 'a(cb*){1,2}', true),
//            array('(?:cb?){1,2}a', '(?:cb?){1,2}a', true),
//            array('(?:cb+){1,2}a', '(?:cb+){1,2}a', true),
//            array('(?:cb*){1,2}a', '(?:cb*){1,2}a', true),
//            array('(cb?){1,2}a', '(cb?){1,2}a', true),
//            array('(cb+){1,2}a', '(cb+){1,2}a', true),
//            array('(cb*){1,2}a', '(cb*){1,2}a', true),
//            array('ab(?:cb?)?', 'ab(?:cb?)?', true),
//            array('ab(?:cb?)+', 'ab(?:cb?)+', true),
//            array('ab(?:cb?)*', 'ab(?:cb?)*', true),
//            array('ab(?:cb+)?', 'ab(?:cb+)?', true),
//            array('ab(?:cb+)+', 'ab(?:cb+)+', true),
//            array('ab(?:cb+)*', 'ab(?:cb+)*', true),
//            array('ab(?:cb*)?', 'ab(?:cb*)?', true),
//            array('ab(?:cb*)+', 'ab(?:cb*)+', true),
//            array('ab(?:cb*)*', 'ab(?:cb*)*', true),
//            array('ab(cb?)?', 'ab(cb?)?', true),
//            array('ab(cb?)+', 'ab(cb?)+', true),
//            array('ab(cb?)*', 'ab(cb?)*', true),
//            array('ab(cb+)?', 'ab(cb+)?', true),
//            array('ab(cb+)+', 'ab(cb+)+', true),
//            array('ab(cb+)*', 'ab(cb+)*', true),
//            array('ab(cb*)?', 'ab(cb*)?', true),
//            array('ab(cb*)+', 'ab(cb*)+', true),
//            array('ab(cb*)*', 'ab(cb*)*', true),
//            array('(?:cb?)?ab', '(?:cb?)?ab', true),
//            array('(?:cb?)+ab', '(?:cb?)+ab', true),
//            array('(?:cb?)*ab', '(?:cb?)*ab', true),
//            array('(?:cb+)?ab', '(?:cb+)?ab', true),
//            array('(?:cb+)+ab', '(?:cb+)+ab', true),
//            array('(?:cb+)*ab', '(?:cb+)*ab', true),
//            array('(?:cb*)?ab', '(?:cb*)?ab', true),
//            array('(?:cb*)+ab', '(?:cb*)+ab', true),
//            array('(?:cb*)*ab', '(?:cb*)*ab', true),
//            array('(cb?)?ab', '(cb?)?ab', true),
//            array('(cb?)+ab', '(cb?)+ab', true),
//            array('(cb?)*ab', '(cb?)*ab', true),
//            array('(cb+)?ab', '(cb+)?ab', true),
//            array('(cb+)+ab', '(cb+)+ab', true),
//            array('(cb+)*ab', '(cb+)*ab', true),
//            array('(cb*)?ab', '(cb*)?ab', true),
//            array('(cb*)+ab', '(cb*)+ab', true),
//            array('(cb*)*ab', '(cb*)*ab', true),
//            array('ab(?:cb?){1,2}', 'ab(?:cb?){1,2}', true),
//            array('ab(?:cb+){1,2}', 'ab(?:cb+){1,2}', true),
//            array('ab(?:cb*){1,2}', 'ab(?:cb*){1,2}', true),
//            array('ab(cb?){1,2}', 'ab(cb?){1,2}', true),
//            array('ab(cb+){1,2}', 'ab(cb+){1,2}', true),
//            array('ab(cb*){1,2}', 'ab(cb*){1,2}', true),
//            array('(?:cb?){1,2}ab', '(?:cb?){1,2}ab', true),
//            array('(?:cb+){1,2}ab', '(?:cb+){1,2}ab', true),
//            array('(?:cb*){1,2}ab', '(?:cb*){1,2}ab', true),
//            array('(cb?){1,2}ab', '(cb?){1,2}ab', true),
//            array('(cb+){1,2}ab', '(cb+){1,2}ab', true),
//            array('(cb*){1,2}ab', '(cb*){1,2}ab', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with a single character to the left
//            array('a(?:a)?', 'aa?', true), // или a{1,2}
//            array('a(?:a)+', 'a{2,}', true),
//            array('a(?:a)*', 'a+', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with a single character to the left
//            array('a(a)?', 'a(a)?', true),
//            array('a(a)+', 'a(a)+', true),
//            array('a(a)*', 'a(a)*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with a single character to the right
//            array('(?:a)?a', 'a{1,2}', true),
//            array('(?:a)+a', 'a+', true),
//            array('(?:a)*a', 'a+', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with a single character to the right
//            array('(a)?a', '(a)?a', true),
//            array('(a)+a', '(a)+a', true),
//            array('(a)*a', '(a)*a', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with a single character to the left
//            array('a(?:a){1,2}', 'a{1,3}', true),
//            array('a(?:a){3,4}', 'aa{3,4}', true),
//            array('a(?:a)?{3,4}', 'aa?{3,4}', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with a single character to the left
//            array('a(a){1,2}', 'a(a){1,2}', true),
//            array('a(a){3,4}', 'a(a){3,4}', true),
//            array('a(a)?{3,4}', 'a(a)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with a single character to the right
//            array('(?:a){1,2}a', 'a{1,3}', true),
//            array('(?:a){3,4}a', '(?:a){3,4}a', true),
//            array('(?:a)?{3,4}a', '(?:a)?{3,4}a', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with a single character to the right
//            array('(a){1,2}a', '(a){1,2}a', true),
//            array('(a){3,4}a', '(a){3,4}a', true),
//            array('(a)?{3,4}a', '(a)?{3,4}a', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ab(?:a)?', 'aba?', true),
//            array('ab(?:a)+', 'aba+', true),
//            array('ab(?:a)*', 'aba*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the leftmost character in the sequence
//            // Characters left
//            array('ab(a)?', 'ab(a)?', true),
//            array('ab(a)+', 'ab(a)+', true),
//            array('ab(a)*', 'ab(a)*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters from the right
//            array('(?:a)?ab', 'a{0,2}b', true),
//            array('(?:a)+ab', 'a{2,}b', true),
//            array('(?:a)*ab', 'a+b', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the leftmost character in the sequence
//            // Characters to the right
//            array('(a)?ab', '(a)?ab', true),
//            array('(a)+ab', '(a)+ab', true),
//            array('(a)*ab', '(a)*ab', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ab(?:a){1,2}', 'aba{1,2}', true),
//            array('ab(?:a){3,4}', 'aba{3,4}', true),
//            array('ab(?:a)?{3,4}', 'aba?{3,4}', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the leftmost character in the sequence
//            // Characters left
//            array('ab(a){1,2}', 'ab(a){1,2}', true),
//            array('ab(a){3,4}', 'ab(a){3,4}', true),
//            array('ab(a)?{3,4}', 'ab(a)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters from the right
//            array('(?:a){1,2}ab', 'a{1,3}b', true),
//            array('(?:a){3,4}ab', 'a{4,5}b', true),
//            array('(?:a)?{3,4}ab', 'a?{3,4}ab', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the leftmost character in the sequence
//            // Characters to the right
//            array('(a){1,2}ab', '(a){1,2}ab', true),
//            array('(a){3,4}ab', '(a){3,4}ab', true),
//            array('(a)?{3,4}ab', '(a)?{3,4}ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Character on the left
//            array('a(?:ab)?', 'a(?:ab)?', true),
//            array('a(?:ab)+', 'a(?:ab)+', true),
//            array('a(?:ab)*', 'a(?:ab)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with
//            // Character on the left
//            array('a(ab)?', 'a(ab?)?', true),
//            array('a(ab)+', 'a(ab?)+', true),
//            array('a(ab)*', 'a(ab?)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Character to the right
//            array('(?:ab)?a', '(?:ab)?a', true),
//            array('(?:ab)+a', '(?:ab)+a', true),
//            array('(?:ab)*a', '(?:ab)*a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with
//            // Character to the right
//            array('(ab)?a', '(ab)?a', true),
//            array('(ab)+a', '(ab)+a', true),
//            array('(ab)*a', '(ab)*a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Character on the left
//            array('a(?:ab){1,2}', 'a(?:ab){1,2}', true),
//            array('a(?:ab){3,4}', 'a(?:ab){3,4}', true),
//            array('a(?:ab)?{3,4}', 'a(?:ab)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with
//            // Character on the left
//            array('a(ab){1,2}', 'a(ab){1,2}', true),
//            array('a(ab){3,4}', 'a(ab){3,4}', true),
//            array('a(ab)?{3,4}', 'a(ab)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Character to the right
//            array('(?:ab){1,2}a', '(?:ab){1,2}a', true),
//            array('(?:ab){3,4}a', '(?:ab){3,4}a', true),
//            array('(?:ab)?{3,4}a', '(?:ab)?{3,4}a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with the character
//            // on the left
//            array('(ab){1,2}a', '(ab){1,2}a', true),
//            array('(ab){3,4}a', '(ab){3,4}a', true),
//            array('(ab)?{3,4}a', '(ab)?(3,4)a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(?:ab)?', '(?:ab){1,2}', true),
//            array('ab(?:ab)+', '(?:ab){2,}', true),
//            array('ab(?:ab)*', '(?:ab)+', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Leftmost character in the sequence of characters left
//            array('ab(ab)?', 'ab(ab?)?', true),
//            array('ab(ab)+', 'ab(ab?)+', true),
//            array('ab(ab)*', 'ab(ab?)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Leftmost character of a sequence of characters to the right
//            array('(?:ab)?ab', '(?:ab){1,2}', true),
//            array('(?:ab)+ab', '(?:ab?){2,}', true),
//            array('(?:ab)*ab', '(?:ab?)+', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Leftmost character of a sequence of characters from the right
//            array('(ab)?ab', '(ab)?ab', true),
//            array('(ab)+ab', '(ab)+ab', true),
//            array('(ab)*ab', '(ab)*ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(?:ab){1,2}', '(?:ab){2,3}', true),
//            array('ab(?:ab){3,4}', '(?:ab+){4,5}', true),
//            array('ab(?:ab)?{3,4}', 'ab(?:ab)?{1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Leftmost character in the sequence of characters left
//            array('ab(ab){1,2}', 'ab(ab){1,2}', true),
//            array('ab(ab){3,4}', 'ab(ab){3,4}', true),
//            array('ab(ab)?{3,4}', 'ab(ab)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Leftmost character of a sequence of characters to the right
//            array('(?:ab){1,2}ab', '(?:ab){1,2}ab', true),
//            array('(?:ab){3,4}ab', '(?:ab){4,5}', true),
//            array('(?:ab)?{3,4}ab', '(?:ab)?{3,4}ab', true),
//            // Integration tests on a combination of quantifiers: совпадение крайнего
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Leftmost character of a sequence of characters from the right
//            array('(ab){1,2}ab', '(ab){1,2}ab', true),
//            array('(ab){3,4}ab', '(ab){3,4}ab', true),
//            array('(ab)?{3,4}ab', '(ab)?{3,4}ab', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ab(?:a)?', 'aba?', true),
//            array('ab(?:a)+', 'aba+', true),
//            array('ab(?:a)*', 'aba*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ab(a)?', 'ab(a)?', true),
//            array('ab(a)+', 'ab(a)+', true),
//            array('ab(a)*', 'ab(a)*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the rightmost characters of the
//            // A sequence of characters on the left
//            array('ba(?:a)?', 'ba{1,2}', true),
//            array('ba(?:a)+', 'ba{2,}', true),
//            array('ba(?:a)*', 'ba+', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the rightmost characters of the
//            // A sequence of characters on the left
//            array('ba(a)?', 'ba(a)?', true),
//            array('ba(a)+', 'ba(a)+', true),
//            array('ba(a)*', 'ba(a)*', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the rightmost characters of
//            // A sequence of characters from the right
//            array('(?:a)?ba', 'a?ba', true),
//            array('(?:a)+ba', 'a+ba', true),
//            array('(?:a)*ba', 'a*ba', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the rightmost characters of
//            // A sequence of characters from the right
//            array('(a)?ba', '(a)?ba', true),
//            array('(a)+ba', '(a)+ba', true),
//            array('(a)*ba', '(a)*ba', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the leftmost character of the
//            // A sequence of characters on the left
//            array('ba(?:a){1,2}', 'ba{1,3}', true),
//            array('ba(?:a){3,4}', 'baa{3,4}', true),
//            array('ba(?:a)?{3,4}', 'ba(?:a)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the rightmost characters of the
//            // A sequence of characters on the left
//            array('ba(a){1,2}', 'ba(a){1,2}', true),
//            array('ba(a){3,4}', 'ba(a){3,4}', true),
//            array('ba(a)?{3,4}', 'ba(a)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character within the group with the rightmost characters of
//            // A sequence of characters from the right
//            array('(?:a){1,2}ba', 'a{1,2}ba', true),
//            array('(?:a){3,4}ba', 'a{3,4}ba', true),
//            array('(?:a)?{3,4}ba', 'a?{3,4}ba', true),
//            // Integration tests on a combination of quantifiers: single match
//            // Character inside a subpattern with the rightmost characters of the
//            // A sequence of characters from the right
//            array('(a){1,2}ba', '(a){1,2}ba', true),
//            array('(a){3,4}ba', '(a){3,4}ba', true),
//            array('(a)?{3,4}ba', '(a)?{3,4}ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Character on the left
//            array('a(?:ba)?', 'a(?:ba)?', true),
//            array('a(?:ba)+', 'a(?:ba)+', true),
//            array('a(?:ba)*', 'a(?:ba)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the subpattern with
//            // Character on the left
//            array('a(ba)?', 'a(ba)?', true),
//            array('a(ba)+', 'a(ba?)+', true),
//            array('a(ba)*', 'a(ba?)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Character on the left
//            array('a(ba)?', 'a(ba)?', true),
//            array('a(ba)+', 'a(ba)+', true),
//            array('a(ba)*', 'a(ba)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Character to the right
//            array('(?:ba)?a', '(?:ba)?a', true),
//            array('(?:ba)+a', '(?:ba)+a', true),
//            array('(?:ba)*a', '(?:ba)*a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Character to the right
//            array('(ba)?a', '(ba)?a', true),
//            array('(ba)+a', '(ba)+a', true),
//            array('(ba)*a', '(ba)*a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Character on the left
//            array('a(?:ba){1,2}', 'a(?:ba){1,2}', true),
//            array('a(?:ba){3,4}', 'a(?:ba){1,2}', true),
//            array('a(?:ba)?{3,4}', 'a(?:ba){1,2}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Character on the left
//            array('a(ba){1,2}', 'a(ba){1,2}', true),
//            array('a(ba){3,4}', 'a(ba){3,4}', true),
//            array('a(ba)?{3,4}', 'a(ba)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Character to the right
//            array('(?:ba){1,2}a', '(?:ba){1,2}a', true),
//            array('(?:ba){3,4}a', '(?:ba){3,4}a', true),
//            array('(?:ba)?{3,4}a', '(?:ba)?{3,4}a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Character on the left
//            array('(ba){1,2}a', '(ba){1,2}a', true),
//            array('(ba){3,4}a', '(ba){3,4}a', true),
//            array('(ba){3,4}a', '(ba){3,4}a', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Rightmost characters of a sequence of characters on the left
//            array('ba(?:ab)?', 'ba(?:ab)?', true),
//            array('ba(?:ab)+', 'ba(?:ab)+', true),
//            array('ba(?:ab)*', 'ba(?:ab)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(?:ba)?', 'ab(?:ba)?', true),
//            array('ab(?:ba)+', 'ab(?:ba)+', true),
//            array('ab(?:ba)*', 'ab(?:ba)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Right character from a sequence of characters on the left
//            array('ba(ab)?', 'ba(ab)?', true),
//            array('ba(ab)+', 'ba(ab)+', true),
//            array('ba(ab)*', 'ba(ab)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(ba)?', 'ab(ba)?', true),
//            array('ab(ba)+', 'ab(ba)+', true),
//            array('ab(ba)*', 'ab(ba)*', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Rightmost characters of a sequence of characters to the right
//            array('(?:ab)?ba', '(?:ab)?ba', true),
//            array('(?:ab)+ba', '(?:ab)+ba', true),
//            array('(?:ab)*ba', '(?:ab)*ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Leftmost character of a sequence of characters to the right
//            array('(?:ba)?ab', '(?:ba)?ab', true),
//            array('(?:ba)+ab', '(?:ba)+ab', true),
//            array('(?:ba)*ab', '(?:ba)*ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Right character from a sequence of characters from the right
//            array('(ab)?ba', '(ab)?ba', true),
//            array('(ab)+ba', '(ab)+ba', true),
//            array('(ab)*ba', '(ab)*ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Leftmost character of a sequence of characters to the right
//            array('(ba)?ab', '(ba)?ab', true),
//            array('(ba)+ab', '(ba)+ab', true),
//            array('(ba)*ab', '(ba)*ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // Rightmost characters of a sequence of characters on the left
//            array('ba(?:ab){1,2}', 'ba(?:ab){1,2}', true),
//            array('ba(?:ab){3,4}', 'ba(?:ab){3,4}', true),
//            array('ba(?:ab)?{3,4}', 'ba(?:ab)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(?:ba){1,2}', 'ab(?:ba){1,2}', true),
//            array('ab(?:ba){3,4}', 'ab(?:ba){3,4}', true),
//            array('ab(?:ba)?{3,4}', 'ab(?:ba)?{3,4}', true),
//            // Integration tests on a combination of quantifiersв: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Right character from a sequence of characters on the left
//            array('ba(ab){1,2}', 'ba(ab){1,2}', true),
//            array('ba(ab){3,4}', 'ba(ab){3,4}', true),
//            array('ba(ab)?{3,4}', 'ba(ab)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Leftmost character of a sequence of characters on the left
//            array('ab(ba){1,2}', 'ab(ba){1,2}', true),
//            array('ab(ba){3,4}', 'ab(ba){3,4}', true),
//            array('ab(ba)?{3,4}', 'ab(ba)?{3,4}', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters within the group with
//            // The rightmost characters of a sequence of characters to the right
//            array('(?:ab){1,2}ba', '(?:ab?){1,2}ba', true),
//            array('(?:ab){3,4}ba', '(?:ab+){3,4}ba', true),
//            array('(?:ab)?{3,4}ba', '(?:ab*)?{3,4}ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Right character sequence of characters within the group with
//            // Leftmost character of a sequence of characters to the right
//            array('(?:ba){1,2}ab', '(?:ba){1,2}ab', true),
//            array('(?:ba){3,4}ab', '(?:ba){1,2}ab', true),
//            array('(?:ba)?{3,4}ab', '(?:ba){1,2}ab', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Leftmost character of a sequence of characters inside a subpattern with extreme
//            // Right character from a sequence of characters from the right
//            array('(ab){1,2}ba', '(ab){1,2}ba', true),
//            array('(ab){3,4}ba', '(ab){3,4}ba', true),
//            array('(ab)?{3,4}ba', '(ab)?{3,4}ba', true),
//            // Integration tests on a combination of quantifiers: match extreme
//            // Rightmost character of the character sequences inside a subpattern with
//            // Leftmost character of a sequence of characters to the right
//            array('(ba){1,2}ab', '(ba){1,2}ab', true),
//            array('(ba){3,4}ab', '(ba){3,4}ab', true),
//            array('(ba)?{3,4}ab', '(ba)?{3,4}ab', true),
//            // Integration tests on a combination of quantifiers: three or more quantifiers
//            array('(?:(?:a*)?)?', 'a*', true),
//            array('(?:(?:a*)?)+', 'a*', true),
//            array('(?:(?:a*)?)*', 'a*', true),
//            array('(?:(?:a*)+)?', 'a*', true),
//            array('(?:(?:a*)+)+', 'a*', true),
//            array('(?:(?:a*)+)*', 'a*', true),
//            array('(?:(?:a*)*)?', 'a*', true),
//            array('(?:(?:a*)*)+', 'a*', true),
//            array('(?:(?:a*)*)*', 'a*', true),
//            array('(?:(?:a+)?)?', 'a*', true),
//            array('(?:(?:a+)?)+', 'a*', true),
//            array('(?:(?:a+)?)*', 'a*', true),
//            array('(?:(?:a+)+)?', 'a*', true),
//            array('(?:(?:a+)+)+', 'a+', true),
//            array('(?:(?:a+)+)*', 'a*', true),
//            array('(?:(?:a+)*)?', 'a*', true),
//            array('(?:(?:a+)*)+', 'a*', true),
//            array('(?:(?:a+)*)*', 'a*', true),
//            array('(?:(?:a?)?)?', 'a?', true),
//            array('(?:(?:a?)?)+', 'a*', true),
//            array('(?:(?:a?)?)*', 'a*', true),
//            array('(?:(?:a?)+)?', 'a*', true),
//            array('(?:(?:a?)+)+', 'a*', true),
//            array('(?:(?:a?)+)*', 'a*', true),
//            array('(?:(?:a?)*)?', 'a*', true),
//            array('(?:(?:a?)*)+', 'a*', true),
//            array('(?:(?:a?)*)*', 'a*', true),
//            array('(?:(?:aa*)?)?', 'a*', true),
//            array('(?:(?:aa*)?)+', 'a*', true),
//            array('(?:(?:aa*)?)*', 'a*', true),
//            array('(?:(?:aa*)+)?', 'a*', true),
//            array('(?:(?:aa*)+)+', 'a+', true),
//            array('(?:(?:aa*)+)*', 'a*', true),
//            array('(?:(?:aa*)*)?', 'a*', true),
//            array('(?:(?:aa*)*)+', 'a*', true),
//            array('(?:(?:aa*)*)*', 'a*', true),
//            array('(?:(?:aa+)?)?', 'a*', true),
//            array('(?:(?:aa+)?)+', 'a*', true),
//            array('(?:(?:aa+)?)*', 'a*', true),
//            array('(?:(?:aa+)+)?', 'a*', true),
//            array('(?:(?:aa+)+)+', 'a{2,}', true),
//            array('(?:(?:aa+)+)*', 'a*', true),
//            array('(?:(?:aa+)*)?', 'a*', true),
//            array('(?:(?:aa+)*)+', 'a*', true),
//            array('(?:(?:aa+)*)*', 'a*', true),
//            array('(?:(?:aa?)?)?', 'a{1,2}', true),
//            array('(?:(?:aa?)?)+', 'a*', true),
//            array('(?:(?:aa?)?)*', 'a*', true),
//            array('(?:(?:aa?)+)?', 'a*', true),
//            array('(?:(?:aa?)+)+', 'a*', true),
//            array('(?:(?:aa?)+)*', 'a*', true),
//            array('(?:(?:aa?)*)?', 'a*', true),
//            array('(?:(?:aa?)*)+', 'a*', true),
//            array('(?:(?:aa?)*)*', 'a*', true),
            array('a|a', 'a', true),
            array('[a]|a', 'a', true),
            array('a|[a]', 'a', true),
            array('[a]|[a]', 'a', true),
            array('a|a|', 'a|', true),
            array('a|a|()', 'a|()', true),
            array('a|a|(?:)', 'a|(?:)', true),
            array('ab|ab', 'ab', true),
            array('ab|ab|ab', 'ab', true),
            array('ab|ab|a', 'ab|a', true),
            array('ab|[a]b|a', 'ab|a', true),
            array('ab|a[b]|a', 'ab|a', true),
            array('ab|[ab]|a', 'ab|[ab]|a', true),
            array('ab|[^a]b|a', 'ab|[^a]b|a', true),
            array('ab|a[^b]|a', 'ab|a[^b]|a', true),
            array('ab|[^ab]|a', 'ab|[^ab]|a', true),

            array('a{2}ba{2}b', '(?:a{2}b){2}'),
            array('ab{2}ab{2}', '(?:ab{2}){2}'),
            array('ca{2}bca{2}b', '(?:ca{2}b){2}'),
            array('a{2}bca{2}bc', '(?:a{2}bc){2}'),
            array('cab{2}cab{2}', '(?:cab{2}){2}'),
            array('ab{2}cab{2}c', '(?:ab{2}c){2}'),
        );
    }

    protected function get_test_single_charset_trivial() {
        return array(
            array('[a]', 'a'),
            array('a[a]', 'aa'),
            array('[a]a', 'aa'),
            array('a[a]a', 'aaa'),
            array('b[a]c', 'bac'),
            array('[aa]', 'a'),
            array('a[aa]', 'aa'),
            array('[aa]a', 'aa'),
            array('a[aa]a', 'aaa'),
            array('[^a]', '[^a]'),
            array('a[^a]', 'a[^a]'),
            array('[^a]a', '[^a]a'),
            array('a[^a]a', 'a[^a]a'),
            array('b[^a]c', 'b[^a]c'),
            array('[^aa]', '[^aa]'),
            array('a[^aa]', 'a[^aa]'),
            array('[^aa]a', '[^aa]a'),
            array('a[^aa]a', 'a[^aa]a'),

            //array('[\\b]', '\\b'),
            array('[\\]', '[\\]'),
            array('[^]', '[^]'),
            array('[$]', '\\$'),
            array('[.]', '\\.'),
            array('[[]', '\\['),
            array('[]]', '\\]'),
            array('[|]', '\\|'),
            array('[(]', '\\('),
            array('[)]', '\\)'),
            array('[?]', '\\?'),
            array('[+]', '\\+'),
            array('[*]', '\\*'),
            array('[{]', '\\{'),
            array('[}]', '\\}'),

            array('[\z]', 'z'),
            array('[\Z]', 'Z'),
            //array('[\a]', '[\a]'),
            array('[\A]', 'A'),
            //array('[\b]', '[\b]'),
            array('[\B]', 'B'),

            array('[\a]', '[\a]'),
            array('[\b]', '[\b]'),
            array('[\e]', '[\e]'),
            array('[\f]', '[\f]'),
            array('[\r]', '[\r]'),
            array('[\t]', '[\t]'),

            array('[c-e]', '[c-e]'),
            array('[c-ef]', '[c-ef]'),
            array('[fc-ef]', '[fc-ef]'),
            array('[fc-e]', '[fc-e]'),
            array('[aabc-e]', '[aabc-e]'),
            array('[aabc-eaa]', '[aabc-eaa]'),
            array('[bc-eaa]', '[bc-eaa]'),

            array('[[:alnum:]]', '[[:alnum:]]'),
            array('[[:alpha:]]', '[[:alpha:]]'),
            array('[[:ascii:]]', '[[:ascii:]]'),
            array('[[:blank:]]', '[[:blank:]]'),
            array('[[:cntrl:]]', '[[:cntrl:]]'),
            array('[[:digit:]]', '[[:digit:]]'),
            array('[[:graph:]]', '[[:graph:]]'),
            array('[[:lower:]]', '[[:lower:]]'),
            array('[[:print:]]', '[[:print:]]'),
            array('[[:punct:]]', '[[:punct:]]'),
            array('[[:space:]]', '[[:space:]]'),
            array('[[:upper:]]', '[[:upper:]]'),
            array('[[:word:]]', '[[:word:]]'),
            array('[[:xdigit:]]', '[[:xdigit:]]'),
        );
    }

    protected function get_test_grouping_node_trivial() {
        return array(
            array('(?:)', ''),
            array('a(?:)', 'a'),
            array('(?:)a', 'a'),
            array('a(?:)a', 'aa'),
            array('ab(?:)ab', 'abab'),
            array('a|(?:)', 'a|'),
            array('(?:)(?:aa)', '(?:)aa'),
            array('a(?:){3}', 'a'),
            array('(?:(?:a))', '(?:a)'),
            array('(?:(?:))', ''),
            array('a(?:(?:))', 'a'),
            array('(?:(?:))a', 'a'),
            array('a(?:(?:))a', 'aa'),
            array('ab(?:(?:))ab', 'abab'),
            array('a|(?:(?:))', 'a|'),
            array('(?:(?:))(?:aa)', '(?:aa)'),
            array('a(?:(?:)){3}', 'a'),

            array('(?:(?:a))', '(?:a)'),
            array('(?:(?:(?:a)))', '(?:(?:a))'),
            array('(?:a){3}', 'a{3}'),
            array('(?:a){3}|b', 'a{3}|b'),
            array('(?:ab){3}|b', '(?:ab){3}|b'),

            array('(?:a{0,}{1,})?', '(?:a{0,}{1,})?'),

            array('a(?:a|b)', 'a(?:a|b)'),
            array('a(?:a|b)a', 'a(?:a|b)a'),
            array('(?:a|b)a', '(?:a|b)a'),

            array('(?:ab)', 'ab'),
            array('(?:ab)|c', 'ab|c'),
        );
    }

    protected function get_test_subpattern_node_trivial() {
        return array(
            array('()', ''),
            array('a()', 'a'),
            array('()a', 'a'),
            array('a()a', 'aa'),
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
            array('((a))\1', '(a)\1'),
            array('\1()', '\1()'),
            array('\1a()', '\1a()'),
            array('\1()a', '\1()a'),
            array('\1a()a', '\1a()a'),
            array('\1ab()ab', '\1ab()ab'),
            array('\1a|()', '\1a|()'),
            array('\1()(?:aa)', '\1()(?:aa)'),
            array('\1a(){3}', '\1a(){3}'),
            array('\1((a))', '\1(a)'),
            array('()\2', '\2'),
            array('a()\2', 'a\2'),
            array('()a\2', 'a\2'),
            array('a()a\2', 'aa\2'),
            array('ab()ab\2', 'abab\2'),
            array('a|()\2', 'a|\2'), //?????
            array('()(?:aa)\2', '(?:aa)\2'),
            array('a(){3}\2', 'a\2'),
            array('((a))\2', '(a)\1'),
            array('\2()', '\2'),
            array('\2a()', '\2a'),
            array('\2()a', '\2a'),
            array('\2a()a', '\2aa'),
            array('\2ab()ab', '\2abab'),
            array('\2a|()', '\2a|'),
            array('\2()(?:aa)', '\2(?:aa)'),
            array('\2a(){3}', '\2a'),
            array('\2((a))', '\1(a)'),
            array('\2()(ab)', '\1(ab)'),
            array('\2a()(ab)', '\1a(ab)'),
            array('\2()a(ab)', '\1a(ab)'),
            array('\2a()a(ab)', '\1aa(ab)'),
            array('\2ab()ab(ab)', '\1abab(ab)'),
            array('\2a|()|(ab)', '\1a|(ab)'),
            array('\2()(?:aa)(ab)', '\1(?:aa)(ab)'),
            array('\2a(){3}(ab)', '\1a(ab)'),
            array('()\2(ab)', '\1(ab)'),
            array('a()\2(ab)', 'a\1(ab)'),
            array('()\2a(ab)', '\1a(ab)'),
            array('a()\2a(ab)', 'a\1a(ab)'),
            array('ab()\2ab(ab)', 'ab\1ab(ab)'),
            array('a|()|(ab)\2', 'a|(ab)\1'),
            array('()\2(?:aa)(ab)', '\1(?:aa)(ab)'),
            array('a(){3}\2(ab)', 'a\1(ab)'),

            array('\2()(ab)(ab)\3', '\1(ab)(ab)\2'),
            array('\2a()(ab)(ab)\3', '\1a(ab)(ab)\2'),
            array('\2()a(ab)(ab)\3', '\1a(ab)(ab)\2'),
            array('\2a()a(ab)(ab)\3', '\1aa(ab)(ab)\2'),
            array('\2ab()ab(ab)(ab)\3', '\1abab(ab)(ab)\2'),
            array('\2a|()|(ab)(ab)\3', '\1a|(ab)(ab)\2'),
            array('\2()(?:aa)(ab)(ab)\3', '\1(?:aa)(ab)(ab)\2'),
            array('\2a(){3}(ab)(ab)\3', '\1a(ab)(ab)\2'),
            array('()\2(ab)(ab)\3', '\1(ab)(ab)\2'),
            array('a()\2(ab)(ab)\3', 'a\1(ab)(ab)\2'),
            array('()\2a(ab)(ab)\3', '\1a(ab)(ab)\2'),
            array('a()\2a(ab)(ab)\3', 'a\1a(ab)(ab)\2'),
            array('ab()\2ab(ab)(ab)\3', 'ab\1ab(ab)(ab)\2'),
            array('a|()|(ab)\2(ab)\3', 'a|(ab)\1(ab)\2'),
            array('()\2(?:aa)(ab)(ab)\3', '\1(?:aa)(ab)(ab)\2'),
            array('a(){3}\2(ab)(ab)\3', 'a\1(ab)(ab)\2'),

            array('\2()(ab(ab))\3', '\1(ab(ab))\2'),
            array('\2a()(ab(ab))\3', '\1a(ab(ab))\2'),
            array('\2()a(ab(ab))\3', '\1a(ab(ab))\2'),
            array('\2a()a(ab(ab))\3', '\1aa(ab(ab))\2'),
            array('\2ab()ab(ab(ab))\3', '\1abab(ab(ab))\2'),
            array('\2a|()|(ab(ab))\3', '\1a|(ab(ab))\2'),
            array('\2()(?:aa)(ab(ab))\3', '\1(?:aa)(ab(ab))\2'),
            array('\2a(){3}(ab(ab))\3', '\1a(ab(ab))\2'),
            array('()\2(ab(ab))\3', '\1(ab(ab))\2'),
            array('a()\2(ab(ab))\3', 'a\1(ab(ab))\2'),
            array('()\2a(ab(ab))\3', '\1a(ab(ab))\2'),
            array('a()\2a(ab(ab))\3', 'a\1a(ab(ab))\2'),
            array('ab()\2ab(ab(ab))\3', 'ab\1ab(ab(ab))\2'),
            array('a|()|(ab(ab))\2\3', 'a|(ab(ab))\1\2'),
            array('()\2(?:aa)(ab(ab))\3', '\1(?:aa)(ab(ab))\2'),
            array('a(){3}\2(ab(ab))\3', 'a\1(ab(ab))\2'),

            array('\1()(ab)(ab)\3', '\1()ab(ab)\2'),
            array('\1a()(ab)(ab)\3', '\1a()ab(ab)\2'),
            array('\1()a(ab)(ab)\3', '\1()aab(ab)\2'),
            array('\1a()a(ab)(ab)\3', '\1a()aab(ab)\2'),
            array('\1ab()ab(ab)(ab)\3', '\1ab()abab(ab)\2'),
            array('\1a|()|(ab)(ab)\3', '\1a|()|ab(ab)\2'),
            array('\1()(?:aa)(ab)(ab)\3', '\1()(?:aa)ab(ab)\2'),
            array('\1a(){3}(ab)(ab)\3', '\1a(){3}ab(ab)\2'),
            array('()\1(ab)(ab)\3', '()\1ab(ab)\2'),
            array('a()\1(ab)(ab)\3', 'a()\1ab(ab)\2'),
            array('()\1a(ab)(ab)\3', '()\1aab(ab)\2'),
            array('a()\1a(ab)(ab)\3', 'a()\1aab(ab)\2'),
            array('ab()\1ab(ab)(ab)\3', 'ab()\1abab(ab)\2'),
            array('a|()|(ab)\1(ab)\3', 'a|()|ab\1(ab)\2'),
            array('()\1(?:aa)(ab)(ab)\3', '()\1(?:aa)ab(ab)\2'),
            array('a(){3}\1(ab)(ab)\3', 'a(){3}\1ab(ab)\2'),

            array('\1()(ab(ab))\3', '\1()ab(ab)\2'),
            array('\1a()(ab(ab))\3', '\1a()ab(ab)\2'),
            array('\1()a(ab(ab))\3', '\1()aab(ab)\2'),
            array('\1a()a(ab(ab))\3', '\1a()aab(ab)\2'),
            array('\1ab()ab(ab(ab))\3', '\1ab()abab(ab)\2'),
            array('\1a|()|(ab(ab))\3', '\1a|()|ab(ab)\2'),
            array('\1()(?:aa)(ab(ab))\3', '\1()(?:aa)ab(ab)\2'),
            array('\1a(){3}(ab(ab))\3', '\1a(){3}ab(ab)\2'),
            array('()\1(ab(ab))\3', '()\1ab(ab)\2'),
            array('a()\1(ab(ab))\3', 'a()\1ab(ab)\2'),
            array('()\1a(ab(ab))\3', '()\1aab(ab)\2'),
            array('a()\1a(ab(ab))\3', 'a()\1aab(ab)\2'),
            array('ab()\1ab(ab(ab))\3', 'ab()\1abab(ab)\2'),
            array('a|()|(ab(ab))\1\3', 'a|()|ab(ab)\1\2'),
            array('()\1(?:aa)(ab(ab))\3', '()\1(?:aa)ab(ab)\2'),
            array('a(){3}\1(ab(ab))\3', 'a(){3}\1ab(ab)\2'),

            array('\2()(ab)()\3', '\1(ab)()\2'),
            array('\2a()(ab)()\3', '\1a(ab)()\2'),
            array('\2()a(ab)()\3', '\1a(ab)()\2'),
            array('\2a()a(ab)()\3', '\1aa(ab)()\2'),
            array('\2ab()ab(ab)()\3', '\1abab(ab)()\2'),
            array('\2a|()|(ab)()\3', '\1a|(ab)()\2'),
            array('\2()(?:aa)(ab)()\3', '\1(?:aa)(ab)()\2'),
            array('\2a(){3}(ab)()\3', '\1a(ab)()\2'),
            array('()\2(ab)()\3', '\1(ab)()\2'),
            array('a()\2(ab)()\3', 'a\1(ab)()\2'),
            array('()\2a(ab)()\3', '\1a(ab)()\2'),
            array('a()\2a(ab)()\3', 'a\1a(ab)()\2'),
            array('ab()\2ab(ab)()\3', 'ab\1ab(ab)()\2'),
            array('a|()|(ab)\2()\3', 'a|(ab)\1()\2'),
            array('()\2(?:aa)(ab)()\3', '\1(?:aa)(ab)()\2'),
            array('a(){3}\2(ab)()\3', 'a\1(ab)()\2'),

            array('\2()(ab())\3', '\1(ab())\2'),
            array('\2a()(ab())\3', '\1a(ab())\2'),
            array('\2()a(ab())\3', '\1a(ab())\2'),
            array('\2a()a(ab())\3', '\1aa(ab())\2'),
            array('\2ab()ab(ab())\3', '\1abab(ab())\2'),
            array('\2a|()|(ab())\3', '\1a|(ab())\2'),
            array('\2()(?:aa)(ab())\3', '\1(?:aa)(ab())\2'),
            array('\2a(){3}(ab())\3', '\1a(ab())\2'),
            array('()\2(ab())\3', '\1(ab())\2'),
            array('a()\2(ab())\3', 'a\1(ab())\2'),
            array('()\2a(ab())\3', '\1a(ab())\2'),
            array('a()\2a(ab())\3', 'a\1a(ab())\2'),
            array('ab()\2ab(ab())\3', 'ab\1ab(ab())\2'),
            array('a|()|(ab())\2\3', 'a|(ab())\1\2'),
            array('()\2(?:aa)(ab())\3', '\1(?:aa)(ab())\2'),
            array('a(){3}\2(ab())\3', 'a\1(ab())\2'),

            array('\1()(ab)()\3', '\1()ab()\2'),
            array('\1a()(ab)()\3', '\1a()ab()\2'),
            array('\1()a(ab)()\3', '\1()aab()\2'),
            array('\1a()a(ab)()\3', '\1a()aab()\2'),
            array('\1ab()ab(ab)()\3', '\1ab()abab()\2'),
            array('\1a|()|(ab)()\3', '\1a|()|ab()\2'),
            array('\1()(?:aa)(ab)()\3', '\1()(?:aa)ab()\2'),
            array('\1a(){3}(ab)()\3', '\1a(){3}ab()\2'),
            array('()\1(ab)()\3', '()\1ab()\2'),
            array('a()\1(ab)()\3', 'a()\1ab()\2'),
            array('()\1a(ab)()\3', '()\1aab()\2'),
            array('a()\1a(ab)()\3', 'a()\1aab()\2'),
            array('ab()\1ab(ab)()\3', 'ab()\1abab()\2'),
            array('a|()|(ab)\1()\3', 'a|()|ab\1()\2'),
            array('()\1(?:aa)(ab)()\3', '()\1(?:aa)ab()\2'),
            array('a(){3}\1(ab)()\3', 'a(){3}\1ab()\2'),

            array('\1()(ab())\3', '\1()ab()\2'),
            array('\1a()(ab())\3', '\1a()ab()\2'),
            array('\1()a(ab())\3', '\1()aab()\2'),
            array('\1a()a(ab())\3', '\1a()aab()\2'),
            array('\1ab()ab(ab())\3', '\1ab()abab()\2'),
            array('\1a|()|(ab())\3', '\1a|()|ab()\2'),
            array('\1()(?:aa)(ab())\3', '\1()(?:aa)ab()\2'),
            array('\1a(){3}(ab())\3', '\1a(){3}ab()\2'),
            array('()\1(ab())\3', '()\1ab()\2'),
            array('a()\1(ab())\3', 'a()\1ab()\2'),
            array('()\1a(ab())\3', '()\1aab()\2'),
            array('a()\1a(ab())\3', 'a()\1aab()\2'),
            array('ab()\1ab(ab())\3', 'ab()\1abab()\2'),
            array('a|()|(ab())\1\3', 'a|()|ab()\1\2'),
            array('()\1(?:aa)(ab())\3', '()\1(?:aa)ab()\2'),
            array('a(){3}\1(ab())\3', 'a(){3}\1ab()\2'),


            array('\2()(ab)()\2', '\1(ab)()\1'),
            array('\1(ab)()\1', '\1(ab)\1'),
            array('\2a()(ab)()\2', '\1a(ab)()\1'),
            array('\1a(ab)()\1', '\1a(ab)\1'),
            array('\2()a(ab)()\2', '\1a(ab)()\1'),
            array('\2a()a(ab)()\2', '\1aa(ab)()\1'),
            array('\2ab()ab(ab)()\2', '\1abab(ab)()\1'),
            array('\2a|()|(ab)()\2', '\1a|(ab)()\1'),
            array('\2()(?:aa)(ab)()\2', '\1(?:aa)(ab)()\1'),
            array('\2a(){3}(ab)()\2', '\1a(ab)()\1'),
            array('()\2(ab)()\2', '\1(ab)()\1'),
            array('a()\2(ab)()\2', 'a\1(ab)()\1'),
            array('()\2a(ab)()\2', '\1a(ab)()\1'),
            array('a()\2a(ab)()\2', 'a\1a(ab)()\1'),
            array('ab()\2ab(ab)()\2', 'ab\1ab(ab)()\1'),
            array('a|()|(ab)\2()\2', 'a|(ab)\1()\1'),
            array('()\2(?:aa)(ab)()\2', '\1(?:aa)(ab)()\1'),
            array('a(){3}\2(ab)()\2', 'a\1(ab)()\1'),

            array('\2()(ab())\2', '\1(ab())\1'),
            array('\2a()(ab())\2', '\1a(ab())\1'),
            array('\2()a(ab())\2', '\1a(ab())\1'),
            array('\2a()a(ab())\2', '\1aa(ab())\1'),
            array('\2ab()ab(ab())\2', '\1abab(ab())\1'),
            array('\2a|()|(ab())\2', '\1a|(ab())\1'),
            array('\2()(?:aa)(ab())\2', '\1(?:aa)(ab())\1'),
            array('\2a(){3}(ab())\2', '\1a(ab())\1'),
            array('()\2(ab())\2', '\1(ab())\1'),
            array('a()\2(ab())\2', 'a\1(ab())\1'),
            array('()\2a(ab())\2', '\1a(ab())\1'),
            array('a()\2a(ab())\2', 'a\1a(ab())\1'),
            array('ab()\2ab(ab())\2', 'ab\1ab(ab())\1'),
            array('a|()|(ab())\2\2', 'a|(ab())\1\1'),
            array('()\2(?:aa)(ab())\2', '\1(?:aa)(ab())\1'),
            array('a(){3}\2(ab())\2', 'a\1(ab())\1'),

            array('\1()(ab)()\2', '\1()(ab)\2'),
            array('\1a()(ab)\2', '\1a()(ab)\2'),
            array('\1()a(ab)()\2', '\1()a(ab)\2'),
            array('\1a()a(ab)()\2', '\1a()a(ab)\2'),
            array('\1ab()ab(ab)()\2', '\1ab()ab(ab)\2'),
            array('\1a|()|(ab)()\2', '\1a|()|(ab)\2'),
            array('\1()(?:aa)(ab)()\2', '\1()(?:aa)(ab)\2'),
            array('\1a(){3}(ab)()\2', '\1a(){3}(ab)\2'),
            array('()\1(ab)()\2', '()\1(ab)\2'),
            array('a()\1(ab)()\2', 'a()\1(ab)\2'),
            array('()\1a(ab)()\2', '()\1a(ab)\2'),
            array('a()\1a(ab)()\2', 'a()\1a(ab)\2'),
            array('ab()\1ab(ab)()\2', 'ab()\1ab(ab)\2'),
            array('a|()|(ab)\1()\2', 'a|()|(ab)\1\2'),
            array('()\1(?:aa)(ab)\2', '()\1(?:aa)(ab)\2'),
            array('a(){3}\1(ab)()\2', 'a(){3}\1(ab)\2'),

            array('\1()(ab())\2', '\1()(ab)\2'),
            array('\1a()(ab())\2', '\1a()(ab)\2'),
            array('\1()a(ab())\2', '\1()a(ab)\2'),
            array('\1a()a(ab())\2', '\1a()a(ab)\2'),
            array('\1ab()ab(ab())\2', '\1ab()ab(ab)\2'),
            array('\1a|()|(ab())\2', '\1a|()|(ab)\2'),
            array('\1()(?:aa)(ab())\2', '\1()(?:aa)(ab)\2'),
            array('\1a(){3}(ab())\2', '\1a(){3}(ab)\2'),
            array('()\1(ab())\2', '()\1(ab)\2'),
            array('a()\1(ab())\2', 'a()\1(ab)\2'),
            array('()\1a(ab())\2', '()\1a(ab)\2'),
            array('a()\1a(ab())\2', 'a()\1a(ab)\2'),
            array('ab()\1ab(ab())\2', 'ab()\1ab(ab)\2'),
            array('a|()|(ab())\1\2', 'a|()|(ab)\1\2'),
            array('()\1(?:aa)(ab())\2', '()\1(?:aa)(ab)\2'),
            array('a(){3}\1(ab())\2', 'a(){3}\1(ab)\2'),


            array('()(?(1)a|b)', '()(?(1)a|b)'),
            array('(?(1)a|b)()', '(?(1)a|b)()'),
            array('()(?(2)a|b)', '(?(2)a|b)'),
            array('(?(2)a|b)()', '(?(2)a|b)'),
            array('()()(?(2)a|b)', '()(?(1)a|b)'),
            array('(?(2)a|b)()()', '(?(1)a|b)()'),
            array('()(?(1)a|b)()', '()(?(1)a|b)'),
            array('()(?(2)a|b)()', '(?(1)a|b)()'),

            array('a()(?(1)a|b)', 'a()(?(1)a|b)'),
            array('a(?(1)a|b)()', 'a(?(1)a|b)()'),
            array('a()(?(2)a|b)', 'a(?(2)a|b)'),
            array('a(?(2)a|b)()', 'a(?(2)a|b)'),
            array('a()()(?(2)a|b)', 'a()(?(1)a|b)'),
            array('a(?(2)a|b)()()', 'a(?(1)a|b)()'),
            array('a()(?(1)a|b)()', 'a()(?(1)a|b)'),
            array('a()(?(2)a|b)()', 'a(?(1)a|b)()'),

            array('()(?(1)a|b)a', '()(?(1)a|b)a'),
            array('(?(1)a|b)()a', '(?(1)a|b)()a'),
            array('()(?(2)a|b)a', '(?(2)a|b)a'),
            array('(?(2)a|b)()a', '(?(2)a|b)a'),
            array('()()(?(2)a|b)a', '()(?(1)a|b)a'),
            array('(?(2)a|b)()()a', '(?(1)a|b)()a'),
            array('()(?(1)a|b)()a', '()(?(1)a|b)a'),
            array('()(?(2)a|b)()a', '(?(1)a|b)()a'),

            array('a()(?(1)a|b)a', 'a()(?(1)a|b)a'),
            array('a(?(1)a|b)()a', 'a(?(1)a|b)()a'),
            array('a()(?(2)a|b)a', 'a(?(2)a|b)a'),
            array('a(?(2)a|b)()a', 'a(?(2)a|b)a'),
            array('a()()(?(2)a|b)a', 'a()(?(1)a|b)a'),
            array('a(?(2)a|b)()()a', 'a(?(1)a|b)()a'),
            array('a()(?(1)a|b)()a', 'a()(?(1)a|b)a'),
            array('a()(?(2)a|b)()a', 'a(?(1)a|b)()a'),

            array('()a(?(1)a|b)', '()a(?(1)a|b)'),
            array('(?(1)a|b)a()', '(?(1)a|b)a()'),
            array('()a(?(2)a|b)', 'a(?(2)a|b)'),
            array('(?(2)a|b)a()', '(?(2)a|b)a'),
            array('()a()a(?(2)a|b)', 'a()a(?(1)a|b)'),
            array('(?(2)a|b)a()a()', '(?(1)a|b)aa()'),
            array('()(?(1)a|b)a()', '()(?(1)a|b)a'),
            array('()(?(2)a|b)a()', '(?(1)a|b)a()'),

            array('()av()\2', 'av()\1'),
            array('(()cd|)av()\1', '(cd|)av()\1'),

            array('(ab)', 'ab'),

            array('([ab])?', '[ab]?'),
        );
    }

    protected function get_test_alt_without_question_quant_trivial() {
        return array(
            array('a|', '(?:a)?'),
            array('a|b|', '(?:a|b)?'),

            array('(?:a|b|)?', '(?:a|b|)?'),
            array('(?:a|b|)+', '(?:(?:a|b)?)+'),
            array('(?:a|b|)*', '(?:a|b|)*'),
            array('(?:a|b|)?|c', '(?:a|b|)?|c'),
            array('(?:a|b|)+|c', '(?:(?:a|b)?)+|c'),
            array('(?:a|b|)*|c', '(?:a|b|)*|c'),
            array('(?:(?:a|b|)|c)?', '(?:(?:a|b|)|c)?'),
            array('(?:(?:a|b|)|c)+', '(?:(?:a|b)?|c)+'),
            array('(?:(?:a|b|)|c)*', '(?:(?:a|b|)|c)*'),
        );
    }

    protected function get_test_alt_with_question_quant_trivial() {
        return array(
            array('(?:a|b)?', '(?:a|b|)'),
            array('(?:a|b){0,1}', '(?:a|b|)'),

            array('(?:a||b)?', '(?:a||b)?'),
            array('(?:a||b){0,1}', '(?:a||b){0,1}'),
        );
    }

    protected function get_test_quant_node_trivial() {
        return array(
            array('a{0,}', 'a*'),
            array('a{1,}', 'a+'),
            array('a{0,1}', 'a?'),
            array('a*', 'a*'),
            array('a+', 'a+'),
            array('a?', 'a?'),
            array('a{0,}b', 'a*b'),
            array('a{1,}b', 'a+b'),
            array('a{0,1}b', 'a?b'),
            array('a*b', 'a*b'),
            array('a+b', 'a+b'),
            array('a?b', 'a?b'),
            array('ab{0,}', 'ab*'),
            array('ab{1,}', 'ab+'),
            array('ab{0,1}', 'ab?'),
            array('ab*ab', 'ab*ab'),
            array('ab+ab', 'ab+ab'),
            array('ab?ab', 'ab?ab'),
            array('ab{0,}bc', 'ab*bc'),
            array('ab{1,}bc', 'ab+bc'),
            array('ab{0,1}bc', 'ab?bc'),
            array('ab*bc', 'ab*bc'),
            array('ab+bc', 'ab+bc'),
            array('ab?bc', 'ab?bc'),
            array('(a{0,})', '(a*)'),
            array('(a{1,})', '(a+)'),
            array('(a{0,1})', '(a?)'),
            array('(a*)', '(a*)'),
            array('(a+)', '(a+)'),
            array('(a?)', '(a?)'),
            array('(a{0,}b)', '(a*b)'),
            array('(a{1,}b)', '(a+b)'),
            array('(a{0,1}b)', '(a?b)'),
            array('(a*b)', '(a*b)'),
            array('(a+b)', '(a+b)'),
            array('(a?b)', '(a?b)'),
            array('(ab{0,})', '(ab*)'),
            array('(ab{1,})', '(ab+)'),
            array('(ab{0,1})', '(ab?)'),
            array('(ab*ab)', '(ab*ab)'),
            array('(ab+ab)', '(ab+ab)'),
            array('(ab?ab)', '(ab?ab)'),
            array('(ab{0,}bc)', '(ab*bc)'),
            array('(ab{1,}bc)', '(ab+bc)'),
            array('(ab{0,1}bc)', '(ab?bc)'),
            array('(ab*bc)', '(ab*bc)'),
            array('(ab+bc)', '(ab+bc)'),
            array('(ab?bc)', '(ab?bc)'),

            array('a{0,1}{1,}{0,}', 'a{0,1}{1,}*'),
            array('a{0,1}{1,}{1,}', '(?:a{0,1}{1,})+'),
            array('a{0,1}{1,}{0,1}', '(?:a{0,1}{1,})?'),
            array('a{,1}', 'a{,1}'),
            array('a{0,1}+', 'a{0,1}+'),
            array('a{0,1}?', 'a{0,1}?'),
        );
    }

    protected function get_test_question_quant_for_alternative_node_trivial() {
        return array(
            array('(?:a|)?', '(?:a|)'),
            array('(?:a|)+', '(?:a|)+'),
            array('(?:a|)*', '(?:a|)*'),
            array('(?:a|){2,3}', '(?:a|){2,3}'),

            array('(?:a|)?|c', '(?:a|)|c'),
        );
    }

    protected function get_test_nullable_alternative_node_trivial() {
        return array(
            array('a|', 'a|'),
            array('a*|', 'a*'),
            array('(?:a*|)', '(?:a*)'),
            array('(?:a*|)c', '(?:a*)c'),
            array('(?:a*|)|c', '(?:a*)|c'),
            array('a*|b+|', 'a*|b+|'),
            array('a*|b*|', 'a*|b*'),
            array('a*||b*|', 'a*|b*'),
            array('a*|||b*', 'a*|b*'),
            array('a*|||b*|', 'a*|b*'),
            array('(?:a*|b+|)', '(?:a*|b+|)'),
            array('(?:a*|b+|)|', '(?:a*|b+|)'),
            array('(?:a*|b*|)|c', '(?:a*|b*)|c'),
            array('(?:a*|b+|)|c', '(?:a*|b+|)|c'),
        );
    }

    protected function get_test_quant_node_1_to_1_trivial() {
        return array(
            array('a{1,1}', 'a'),
            array('a{1,1}|b', 'a|b'),
            array('(?:a{1,1})', '(?:a)'),
            array('(?:a){1,1}', '(?:a)'),
            array('(a{1,1})', '(a)'),
            array('(a){1,1}', '(a)'),

            array('a{1}', 'a'),
        );
    }

    protected function get_test_repeat_assertions_trivial() {
        return array(
            array('^^a', '^a'),
            array('a$$', 'a$'),
            array('^^a$$', '^a$'),
        );
    }

    protected function aaa() {
        return array(
            array('(ab|a)', '(ab?)'),
            array('(?:ab|a)', 'ab?'),
            array('ab|a', 'ab?'),
            array('ba|a', 'b?a'),
            array('ba|a?', 'ba|a?'),
            array('ba|a+', 'ba|+'),
            array('ba|a*', 'ba|a*'),
            array('ba?|a', 'ba?|a'),
            array('ba+|a', 'b?a+'),
            array('ba*|a', 'b?a*'),
            array('b?a|a', 'b?a'),
            array('b+a|a', 'b+a|a'),
            array('b*a|a', 'b*a'),
            array('(?:ba|a)?', '(b?a)?'),
            array('(?:ba|a)+', '(b?a)+'),
            array('(?:ba|a)*', '(b?a)*'),
            array('ab|a', 'ab?'),
            array('ab|a?', '(?:ab|a|)'),
            array('ab|a+', 'ab|a+'),
            array('ab|a*', 'ab|a*'),
            array('ab?|a', 'ab?'),
            array('ab+|a', 'ab*'),
            array('ab*|a', 'ab*'),
            array('a?b|a', 'a?b?'),
            array('a+b|a', 'a+b?'),
            // Conversion alternatives
            array('a*b|a', 'a+b?'),
            array('(?:ab|a)?', '(?:a|ab|)'),
            array('(?:ab|a)+', '(?ab)+|a+'),
            array('(?:ab|a)*', '(?:ab)*|a*'),
            array('(?:a|)?', 'a?'),
            array('(?:a|)+', 'a*'),
            array('(?:a|)*', 'a*'),
            array('(?:a|)', 'a?'),
            array('(?:a|b)+', 'a+|b+'),
            array('(?:a|b)*', 'a*|b*'),
            array('a|aaa|aaaa', 'a|a{3,4}'),
            array('a|aa?', 'aa?'),
            array('a|aaa?', 'a{1,3}'),
            array('a|aaaa?', 'a|a{3,4}'),
            array('a|(?:ab)', '(?:ab?)'),
            array('a|(?:aab)', 'a(?:ab)?'),
            // Alternative with emptiness
            array('a?|', 'a?'),
            array('a*|', 'a*'),
            array('a+|', 'a*'),
            array('a(a?|)', 'a(a?)'),
            array('a(a+|)', 'a(a*)'),
            array('a(a*|)', 'a(a*)'),
            array('a(?:a?|)', 'a{0,2}'),
            array('a(?:a+|)', 'a*'),
            array('a(?:a*|)', 'a*'),
            array('a{0,}|', 'a*'),
            array('a{1,}|', 'a*'),
            array('a{0,1}|', 'a?'),
            array('a{0,3}|', 'a{0,3}'),
        );
    }

    protected function bbb() {
        return array(
            array('(?:a*)+', 'a*'),
            array('(?:a*)?', 'a*'),
            array('(?:a*)*', 'a*'),
            array('(?:a?)+', 'a*'),
            array('(?:a?)?', 'a?'),
            array('(?:a?)*', 'a*'),
            array('(?:a+)+', 'a+'),
            array('(?:a+)?', 'a*'),
            array('(?:a+)*', 'a*'),
        );
    }

    protected function get_test_single_alternative_node_trivial() {
        return array(
            // Cast to character classes
            array('a|b', '[ab]'),
            //array('a|b|', '[ab]?'),
            array('a|c|b|d', '[acbd]'),
            array('a|b|c|e|f|g', '[abcefg]'),
            array('a|b|[c-d]', '[abc-d]'),
            array('a|b|c|[e-g]', '[abce-g]'),
//            array('a|b|c|[x-z]?', '[a-cx-z]?'),
//            array('a|b|c|[x-z]+', '[a-c]|[x-z]+'),
//            array('a|b|c|[x-z]*', '[a-c]|[x-z]*'),
            array('\s|a', '[\sa]'),
            array('\S|a', '[\Sa]'),
            array('\w|a', '[\wa]'),
            array('\W|a', '[\Wa]'),
            array('\d|a', '[\da]'),
            array('\D|a', '[\Da]'),
//            array('a?|b', '[ab]?'),
            array('a+|b', 'a+|b'),
            array('a*|b', 'a*|b'),
//            array('a|b?', '[ab]?'),
            array('a|b+', 'a|b+'),
            array('a|b*', 'a|b*'),
            array('[aa]|b', '[aab]'),
            array('a|[bb]', '[abb]'),
            array('[aa]|[bb]', '[aabb]'),
            array('[^aa]|b', '[^aa]|b'),
            array('a|[^bb]', 'a|[^bb]'),
            array('[^aa]|[bb]', '[^aa]|[bb]'),
            array('[aa]|[^bb]', '[aa]|[^bb]'),
            array('[^aa]|[^bb]', '[^aa]|[^bb]'),
            array('(?:a|b)?', '(?:[ab])?'),

            array('a|-|b', '[a\-b]'),
            array('a|.|b', '.|[ab]'),
            array('a|-|]|{|}|b', '[a\-\]\{\}b]'),
            array('a|\|b', 'a|\|b'),
            array('a|^|b', '^|[ab]'),
            array('a|$|b', '$|[ab]'),
            array('a|[|b', 'a|[|b'),
            array('a|||b', '||[ab]'),
            array('a|(|b', 'a|(|b'),
            array('a|)|b', 'a|)|b'),
            //array('a|?|b', 'a|?|b'),
            //array('a|+|b', 'a|+|b'),
            //array('a|*|b', 'a|*|b'),

            array('[ab]|c|ab', 'ab|[abc]'),
            array('ab|c|[ab]', 'ab|[cab]'),
            array('[ab]|c|a', '[abca]'),

            array('[ab]|dc|e', 'dc|[abe]'),
            array('a|dc|e', 'dc|[ae]'),
            array('a|dc|a', 'dc|[aa]'),
            array('a|[a-c]|\^|^|[01]', '^|[aa-c\^01]'),
        );
    }

    protected function get_test_space_charset_trivial() {
        return array(
            array(' ', '\s'),
            array('a ', 'a\s'),
            array(' a', '\sa'),
            array('a a', 'a\sa'),
            array('a| ', 'a|\s'),
            array('(?: )', '(?:\s)'),
            array('a| |b', 'a|\s|b'),
            array(' {3}', '\s{3}'),
            array('[ ]', '[\s]'),
            array('[^ ]', '[^\s]'),
            array('[a ]', '[a\s]'),
            array('[ a]', '[\sa]'),
            array('[a b]', '[a\sb]'),
            array('[^a ]', '[^a\s]'),
            array('[^ a]', '[^\sa]'),
            array('[^a b]', '[^a\sb]'),
        );
    }

    protected function get_test_subpattern_without_backref_trivial() {
        return array(
            array('(ab)', '(?:ab)'),
            array('(ab)a', '(?:ab)a'),
            array('(ab)\1a', '(ab)\1a'),
            array('(a(b))\1a', '(a(?:b))\1a'),
            array('(a(b))\1\2a', '(a(b))\1\2a'),
            array('(a(b)(c))\1\3a', '(a(?:b)(c))\1\2a'),

            array('(ab)(?(1)a|b)a', '(ab)(?(1)a|b)a'),
            array('(a(b))(?(1)a|b)a', '(a(?:b))(?(1)a|b)a'),
            array('(a(b))(?(1)a|b)(?(2)a|b)a', '(a(b))(?(1)a|b)(?(2)a|b)a'),
            array('(a(b)(c))(?(1)a|b)(?(3)a|b)a', '(a(?:b)(c))(?(1)a|b)(?(2)a|b)a'),

            array('([ab])?', '(?:[ab])?'),
        );
    }

    protected function get_test_space_charset_without_quant_trivial() {
        return array(
            array('a ', 'a +'),
            array('a\s', 'a\s+'),
            array('a[ ]', 'a[ ]+'),
            array('a[\s]', 'a[\s]+'),
            array('a[[:space:]]', 'a[[:space:]]+'),
            array('a a', 'a +a'),
            array('a\sa', 'a\s+a'),
            array('a[ ]a', 'a[ ]+a'),
            array('a[\s]a', 'a[\s]+a'),
            array('a[[:space:]]a', 'a[[:space:]]+a'),
            array(' a', ' +a'),
            array('\sa', '\s+a'),
            array('[ ]a', '[ ]+a'),
            array('[\s]a', '[\s]+a'),
            array('[[:space:]]a', '[[:space:]]+a'),

            array(' ', ' +'),
            array('\s', '\s+'),
            array('[ ]', '[ ]+'),
            array('[\s]', '[\s]+'),
            array('[[:space:]]', '[[:space:]]+'),
            array('( )', '( +)'),
            array('(\s)', '(\s+)'),
            array('([ ])', '([ ]+)'),
            array('([\s])', '([\s]+)'),
            array('([[:space:]])', '([[:space:]]+)'),
            array('(?: )', '(?: +)'),
            array('(?:\s)', '(?:\s+)'),
            array('(?:[ ])', '(?:[ ]+)'),
            array('(?:[\s])', '(?:[\s]+)'),
            array('(?:[[:space:]])', '(?:[[:space:]]+)'),
            array('((( )))', '((( +)))'),
            array('(((\s)))', '(((\s+)))'),
            array('((([ ])))', '((([ ]+)))'),
            array('((([\s])))', '((([\s]+)))'),
            array('((([[:space:]])))', '((([[:space:]]+)))'),
            array('(?:(?:(?: )))', '(?:(?:(?: +)))'),
            array('(?:(?:(?:\s)))', '(?:(?:(?:\s+)))'),
            array('(?:(?:(?:[ ])))', '(?:(?:(?:[ ]+)))'),
            array('(?:(?:(?:[\s])))', '(?:(?:(?:[\s]+)))'),
            array('(?:(?:(?:[[:space:]])))', '(?:(?:(?:[[:space:]]+)))'),

            array(' +', ' +'),
            array('\s+', '\s+'),
            array('[ ]+', '[ ]+'),
            array('[\s]+', '[\s]+'),
            array('[[:space:]]+', '[[:space:]]+'),
            array('( +)', '( +)'),
            array('(\s+)', '(\s+)'),
            array('([ ]+)', '([ ]+)'),
            array('([\s]+)', '([\s]+)'),
            array('([[:space:]]+)', '([[:space:]]+)'),
            array('(?: +)', '(?: +)'),
            array('(?:\s+)', '(?:\s+)'),
            array('(?:[ ]+)', '(?:[ ]+)'),
            array('(?:[\s]+)', '(?:[\s]+)'),
            array('(?:[[:space:]]+)', '(?:[[:space:]]+)'),
            array('((( +)))', '((( +)))'),
            array('(((\s+)))', '(((\s+)))'),
            array('((([ ]+)))', '((([ ]+)))'),
            array('((([\s]+)))', '((([\s]+)))'),
            array('((([[:space:]]+)))', '((([[:space:]]+)))'),
            array('(?:(?:(?: +)))', '(?:(?:(?: +)))'),
            array('(?:(?:(?:\s+)))', '(?:(?:(?:\s+)))'),
            array('(?:(?:(?:[ ]+)))', '(?:(?:(?:[ ]+)))'),
            array('(?:(?:(?:[\s]+)))', '(?:(?:(?:[\s]+)))'),
            array('(?:(?:(?:[[:space:]]+)))', '(?:(?:(?:[[:space:]]+)))'),
            array('((( )+))', '((( )+))'),
            array('(((\s)+))', '(((\s)+))'),
            array('((([ ])+))', '((([ ])+))'),
            array('((([\s])+))', '((([\s])+))'),
            array('((([[:space:]])+))', '((([[:space:]])+))'),
            array('(?:(?:(?: )+))', '(?:(?:(?: )+))'),
            array('(?:(?:(?:\s)+))', '(?:(?:(?:\s)+))'),
            array('(?:(?:(?:[ ])+))', '(?:(?:(?:[ ])+))'),
            array('(?:(?:(?:[\s])+))', '(?:(?:(?:[\s])+))'),
            array('(?:(?:(?:[[:space:]])+))', '(?:(?:(?:[[:space:]])+))'),
            array('((( ))+)', '((( ))+)'),
            array('(((\s))+)', '(((\s))+)'),
            array('((([ ]))+)', '((([ ]))+)'),
            array('((([\s]))+)', '((([\s]))+)'),
            array('((([[:space:]]))+)', '((([[:space:]]))+)'),
            array('(?:(?:(?: ))+)', '(?:(?:(?: ))+)'),
            array('(?:(?:(?:\s))+)', '(?:(?:(?:\s))+)'),
            array('(?:(?:(?:[ ]))+)', '(?:(?:(?:[ ]))+)'),
            array('(?:(?:(?:[\s]))+)', '(?:(?:(?:[\s]))+)'),
            array('(?:(?:(?:[[:space:]]))+)', '(?:(?:(?:[[:space:]]))+)'),
            array('((( )))+', '((( )))+'),
            array('(((\s)))+', '(((\s)))+'),
            array('((([ ])))+', '((([ ])))+'),
            array('((([\s])))+', '((([\s])))+'),
            array('((([[:space:]])))+', '((([[:space:]])))+'),
            array('(?:(?:(?: )))+', '(?:(?:(?: )))+'),
            array('(?:(?:(?:\s)))+', '(?:(?:(?:\s)))+'),
            array('(?:(?:(?:[ ])))+', '(?:(?:(?:[ ])))+'),
            array('(?:(?:(?:[\s])))+', '(?:(?:(?:[\s])))+'),
            array('(?:(?:(?:[[:space:]])))+', '(?:(?:(?:[[:space:]])))+'),

            array(' *', ' *'),
            array('\s*', '\s*'),
            array('[ ]*', '[ ]*'),
            array('[\s]*', '[\s]*'),
            array('[[:space:]]*', '[[:space:]]*'),
            array('( *)', '( *)'),
            array('(\s*)', '(\s*)'),
            array('([ ]*)', '([ ]*)'),
            array('([\s]*)', '([\s]*)'),
            array('([[:space:]]*)', '([[:space:]]*)'),
            array('(?: *)', '(?: *)'),
            array('(?:\s*)', '(?:\s*)'),
            array('(?:[ ]*)', '(?:[ ]*)'),
            array('(?:[\s]*)', '(?:[\s]*)'),
            array('(?:[[:space:]]*)', '(?:[[:space:]]*)'),
            array('((( *)))', '((( *)))'),
            array('(((\s*)))', '(((\s*)))'),
            array('((([ ]*)))', '((([ ]*)))'),
            array('((([\s]*)))', '((([\s]*)))'),
            array('((([[:space:]]*)))', '((([[:space:]]*)))'),
            array('(?:(?:(?: *)))', '(?:(?:(?: *)))'),
            array('(?:(?:(?:\s*)))', '(?:(?:(?:\s*)))'),
            array('(?:(?:(?:[ ]*)))', '(?:(?:(?:[ ]*)))'),
            array('(?:(?:(?:[\s]*)))', '(?:(?:(?:[\s]*)))'),
            array('(?:(?:(?:[[:space:]]*)))', '(?:(?:(?:[[:space:]]*)))'),
            array('((( )*))', '((( )*))'),
            array('(((\s)*))', '(((\s)*))'),
            array('((([ ])*))', '((([ ])*))'),
            array('((([\s])*))', '((([\s])*))'),
            array('((([[:space:]])*))', '((([[:space:]])*))'),
            array('(?:(?:(?: )*))', '(?:(?:(?: )*))'),
            array('(?:(?:(?:\s)*))', '(?:(?:(?:\s)*))'),
            array('(?:(?:(?:[ ])*))', '(?:(?:(?:[ ])*))'),
            array('(?:(?:(?:[\s])*))', '(?:(?:(?:[\s])*))'),
            array('(?:(?:(?:[[:space:]])*))', '(?:(?:(?:[[:space:]])*))'),
            array('((( ))*)', '((( ))*)'),
            array('(((\s))*)', '(((\s))*)'),
            array('((([ ]))*)', '((([ ]))*)'),
            array('((([\s]))*)', '((([\s]))*)'),
            array('((([[:space:]]))*)', '((([[:space:]]))*)'),
            array('(?:(?:(?: ))*)', '(?:(?:(?: ))*)'),
            array('(?:(?:(?:\s))*)', '(?:(?:(?:\s))*)'),
            array('(?:(?:(?:[ ]))*)', '(?:(?:(?:[ ]))*)'),
            array('(?:(?:(?:[\s]))*)', '(?:(?:(?:[\s]))*)'),
            array('(?:(?:(?:[[:space:]]))*)', '(?:(?:(?:[[:space:]]))*)'),
            array('((( )))*', '((( )))*'),
            array('(((\s)))*', '(((\s)))*'),
            array('((([ ])))*', '((([ ])))*'),
            array('((([\s])))*', '((([\s])))*'),
            array('((([[:space:]])))*', '((([[:space:]])))*'),
            array('(?:(?:(?: )))*', '(?:(?:(?: )))*'),
            array('(?:(?:(?:\s)))*', '(?:(?:(?:\s)))*'),
            array('(?:(?:(?:[ ])))*', '(?:(?:(?:[ ])))*'),
            array('(?:(?:(?:[\s])))*', '(?:(?:(?:[\s])))*'),
            array('(?:(?:(?:[[:space:]])))*', '(?:(?:(?:[[:space:]])))*'),

            array(' ?', ' ?'),
            array('\s?', '\s?'),
            array('[ ]?', '[ ]?'),
            array('[\s]?', '[\s]?'),
            array('[[:space:]]?', '[[:space:]]?'),
            array('( ?)', '( ?)'),
            array('(\s?)', '(\s?)'),
            array('([ ]?)', '([ ]?)'),
            array('([\s]?)', '([\s]?)'),
            array('([[:space:]]?)', '([[:space:]]?)'),
            array('(?: ?)', '(?: ?)'),
            array('(?:\s?)', '(?:\s?)'),
            array('(?:[ ]?)', '(?:[ ]?)'),
            array('(?:[\s]?)', '(?:[\s]?)'),
            array('(?:[[:space:]]?)', '(?:[[:space:]]?)'),
            array('((( ?)))', '((( ?)))'),
            array('(((\s?)))', '(((\s?)))'),
            array('((([ ]?)))', '((([ ]?)))'),
            array('((([\s]?)))', '((([\s]?)))'),
            array('((([[:space:]]?)))', '((([[:space:]]?)))'),
            array('(?:(?:(?: ?)))', '(?:(?:(?: ?)))'),
            array('(?:(?:(?:\s?)))', '(?:(?:(?:\s?)))'),
            array('(?:(?:(?:[ ]?)))', '(?:(?:(?:[ ]?)))'),
            array('(?:(?:(?:[\s]?)))', '(?:(?:(?:[\s]?)))'),
            array('(?:(?:(?:[[:space:]]?)))', '(?:(?:(?:[[:space:]]?)))'),
            array('((( )?))', '((( )?))'),
            array('(((\s)?))', '(((\s)?))'),
            array('((([ ])?))', '((([ ])?))'),
            array('((([\s])?))', '((([\s])?))'),
            array('((([[:space:]])?))', '((([[:space:]])?))'),
            array('(?:(?:(?: )?))', '(?:(?:(?: )?))'),
            array('(?:(?:(?:\s)?))', '(?:(?:(?:\s)?))'),
            array('(?:(?:(?:[ ])?))', '(?:(?:(?:[ ])?))'),
            array('(?:(?:(?:[\s])?))', '(?:(?:(?:[\s])?))'),
            array('(?:(?:(?:[[:space:]])?))', '(?:(?:(?:[[:space:]])?))'),
            array('((( ))?)', '((( ))?)'),
            array('(((\s))?)', '(((\s))?)'),
            array('((([ ]))?)', '((([ ]))?)'),
            array('((([\s]))?)', '((([\s]))?)'),
            array('((([[:space:]]))?)', '((([[:space:]]))?)'),
            array('(?:(?:(?: ))?)', '(?:(?:(?: ))?)'),
            array('(?:(?:(?:\s))?)', '(?:(?:(?:\s))?)'),
            array('(?:(?:(?:[ ]))?)', '(?:(?:(?:[ ]))?)'),
            array('(?:(?:(?:[\s]))?)', '(?:(?:(?:[\s]))?)'),
            array('(?:(?:(?:[[:space:]]))?)', '(?:(?:(?:[[:space:]]))?)'),
            array('((( )))?', '((( )))?'),
            array('(((\s)))?', '(((\s)))?'),
            array('((([ ])))?', '((([ ])))?'),
            array('((([\s])))?', '((([\s])))?'),
            array('((([[:space:]])))?', '((([[:space:]])))?'),
            array('(?:(?:(?: )))?', '(?:(?:(?: )))?'),
            array('(?:(?:(?:\s)))?', '(?:(?:(?:\s)))?'),
            array('(?:(?:(?:[ ])))?', '(?:(?:(?:[ ])))?'),
            array('(?:(?:(?:[\s])))?', '(?:(?:(?:[\s])))?'),
            array('(?:(?:(?:[[:space:]])))?', '(?:(?:(?:[[:space:]])))?'),

            array(' {2,5}', ' {2,5}'),
            array('\s{2,5}', '\s{2,5}'),
            array('[ ]{2,5}', '[ ]{2,5}'),
            array('[\s]{2,5}', '[\s]{2,5}'),
            array('[[:space:]]{2,5}', '[[:space:]]{2,5}'),
            array('( {2,5})', '( {2,5})'),
            array('(\s{2,5})', '(\s{2,5})'),
            array('([ ]{2,5})', '([ ]{2,5})'),
            array('([\s]{2,5})', '([\s]{2,5})'),
            array('([[:space:]]{2,5})', '([[:space:]]{2,5})'),
            array('(?: {2,5})', '(?: {2,5})'),
            array('(?:\s{2,5})', '(?:\s{2,5})'),
            array('(?:[ ]{2,5})', '(?:[ ]{2,5})'),
            array('(?:[\s]{2,5})', '(?:[\s]{2,5})'),
            array('(?:[[:space:]]{2,5})', '(?:[[:space:]]{2,5})'),
            array('((( {2,5})))', '((( {2,5})))'),
            array('(((\s{2,5})))', '(((\s{2,5})))'),
            array('((([ ]{2,5})))', '((([ ]{2,5})))'),
            array('((([\s]{2,5})))', '((([\s]{2,5})))'),
            array('((([[:space:]]{2,5})))', '((([[:space:]]{2,5})))'),
            array('(?:(?:(?: {2,5})))', '(?:(?:(?: {2,5})))'),
            array('(?:(?:(?:\s{2,5})))', '(?:(?:(?:\s{2,5})))'),
            array('(?:(?:(?:[ ]{2,5})))', '(?:(?:(?:[ ]{2,5})))'),
            array('(?:(?:(?:[\s]{2,5})))', '(?:(?:(?:[\s]{2,5})))'),
            array('(?:(?:(?:[[:space:]]{2,5})))', '(?:(?:(?:[[:space:]]{2,5})))'),
            array('((( ){2,5}))', '((( ){2,5}))'),
            array('(((\s){2,5}))', '(((\s){2,5}))'),
            array('((([ ]){2,5}))', '((([ ]){2,5}))'),
            array('((([\s]){2,5}))', '((([\s]){2,5}))'),
            array('((([[:space:]]){2,5}))', '((([[:space:]]){2,5}))'),
            array('(?:(?:(?: ){2,5}))', '(?:(?:(?: ){2,5}))'),
            array('(?:(?:(?:\s){2,5}))', '(?:(?:(?:\s){2,5}))'),
            array('(?:(?:(?:[ ]){2,5}))', '(?:(?:(?:[ ]){2,5}))'),
            array('(?:(?:(?:[\s]){2,5}))', '(?:(?:(?:[\s]){2,5}))'),
            array('(?:(?:(?:[[:space:]]){2,5}))', '(?:(?:(?:[[:space:]]){2,5}))'),
            array('((( )){2,5})', '((( )){2,5})'),
            array('(((\s)){2,5})', '(((\s)){2,5})'),
            array('((([ ])){2,5})', '((([ ])){2,5})'),
            array('((([\s])){2,5})', '((([\s])){2,5})'),
            array('((([[:space:]])){2,5})', '((([[:space:]])){2,5})'),
            array('(?:(?:(?: )){2,5})', '(?:(?:(?: )){2,5})'),
            array('(?:(?:(?:\s)){2,5})', '(?:(?:(?:\s)){2,5})'),
            array('(?:(?:(?:[ ])){2,5})', '(?:(?:(?:[ ])){2,5})'),
            array('(?:(?:(?:[\s])){2,5})', '(?:(?:(?:[\s])){2,5})'),
            array('(?:(?:(?:[[:space:]])){2,5})', '(?:(?:(?:[[:space:]])){2,5})'),
            array('((( ))){2,5}', '((( ))){2,5}'),
            array('(((\s))){2,5}', '(((\s))){2,5}'),
            array('((([ ]))){2,5}', '((([ ]))){2,5}'),
            array('((([\s]))){2,5}', '((([\s]))){2,5}'),
            array('((([[:space:]]))){2,5}', '((([[:space:]]))){2,5}'),
            array('(?:(?:(?: ))){2,5}', '(?:(?:(?: ))){2,5}'),
            array('(?:(?:(?:\s))){2,5}', '(?:(?:(?:\s))){2,5}'),
            array('(?:(?:(?:[ ]))){2,5}', '(?:(?:(?:[ ]))){2,5}'),
            array('(?:(?:(?:[\s]))){2,5}', '(?:(?:(?:[\s]))){2,5}'),
            array('(?:(?:(?:[[:space:]]))){2,5}', '(?:(?:(?:[[:space:]]))){2,5}'),
        );
    }

    protected function get_test_space_charset_with_finit_quant_trivial() {
        return array(
            array('a ?', 'a *'),
            array('a\s?', 'a\s*'),
            array('a[ ]?', 'a[ ]*'),
            array('a[\s]?', 'a[\s]*'),
            array('a[[:space:]]?', 'a[[:space:]]*'),
            array('a ?a', 'a *a'),
            array('a\s?a', 'a\s*a'),
            array('a[ ]?a', 'a[ ]*a'),
            array('a[\s]?a', 'a[\s]*a'),
            array('a[[:space:]]?a', 'a[[:space:]]*a'),
            array(' ?a', ' *a'),
            array('\s?a', '\s*a'),
            array('[ ]?a', '[ ]*a'),
            array('[\s]?a', '[\s]*a'),
            array('[[:space:]]?a', '[[:space:]]*a'),

            array('a {0,1}', 'a *'),
            array('a\s{0,1}', 'a\s*'),
            array('a[ ]{0,1}', 'a[ ]*'),
            array('a[\s]{0,1}', 'a[\s]*'),
            array('a[[:space:]]{0,1}', 'a[[:space:]]*'),
            array('a {0,1}a', 'a *a'),
            array('a\s{0,1}a', 'a\s*a'),
            array('a[ ]{0,1}a', 'a[ ]*a'),
            array('a[\s]{0,1}a', 'a[\s]*a'),
            array('a[[:space:]]{0,1}a', 'a[[:space:]]*a'),
            array(' {0,1}a', ' *a'),
            array('\s{0,1}a', '\s*a'),
            array('[ ]{0,1}a', '[ ]*a'),
            array('[\s]{0,1}a', '[\s]*a'),
            array('[[:space:]]{0,1}a', '[[:space:]]*a'),

            array(' *', ' *'),
            array('\s*', '\s*'),
            array('[ ]*', '[ ]*'),
            array('[\s]*', '[\s]*'),
            array('[[:space:]]*', '[[:space:]]*'),
            array('( *)', '( *)'),
            array('(\s*)', '(\s*)'),
            array('([ ]*)', '([ ]*)'),
            array('([\s]*)', '([\s]*)'),
            array('([[:space:]]*)', '([[:space:]]*)'),
            array('(?: *)', '(?: *)'),
            array('(?:\s*)', '(?:\s*)'),
            array('(?:[ ]*)', '(?:[ ]*)'),
            array('(?:[\s]*)', '(?:[\s]*)'),
            array('(?:[[:space:]]*)', '(?:[[:space:]]*)'),
            array('((( *)))', '((( *)))'),
            array('(((\s*)))', '(((\s*)))'),
            array('((([ ]*)))', '((([ ]*)))'),
            array('((([\s]*)))', '((([\s]*)))'),
            array('((([[:space:]]*)))', '((([[:space:]]*)))'),
            array('(?:(?:(?: *)))', '(?:(?:(?: *)))'),
            array('(?:(?:(?:\s*)))', '(?:(?:(?:\s*)))'),
            array('(?:(?:(?:[ ]*)))', '(?:(?:(?:[ ]*)))'),
            array('(?:(?:(?:[\s]*)))', '(?:(?:(?:[\s]*)))'),
            array('(?:(?:(?:[[:space:]]*)))', '(?:(?:(?:[[:space:]]*)))'),
            array('((( )*))', '((( )*))'),
            array('(((\s)*))', '(((\s)*))'),
            array('((([ ])*))', '((([ ])*))'),
            array('((([\s])*))', '((([\s])*))'),
            array('((([[:space:]])*))', '((([[:space:]])*))'),
            array('(?:(?:(?: )*))', '(?:(?:(?: )*))'),
            array('(?:(?:(?:\s)*))', '(?:(?:(?:\s)*))'),
            array('(?:(?:(?:[ ])*))', '(?:(?:(?:[ ])*))'),
            array('(?:(?:(?:[\s])*))', '(?:(?:(?:[\s])*))'),
            array('(?:(?:(?:[[:space:]])*))', '(?:(?:(?:[[:space:]])*))'),
            array('((( ))*)', '((( ))*)'),
            array('(((\s))*)', '(((\s))*)'),
            array('((([ ]))*)', '((([ ]))*)'),
            array('((([\s]))*)', '((([\s]))*)'),
            array('((([[:space:]]))*)', '((([[:space:]]))*)'),
            array('(?:(?:(?: ))*)', '(?:(?:(?: ))*)'),
            array('(?:(?:(?:\s))*)', '(?:(?:(?:\s))*)'),
            array('(?:(?:(?:[ ]))*)', '(?:(?:(?:[ ]))*)'),
            array('(?:(?:(?:[\s]))*)', '(?:(?:(?:[\s]))*)'),
            array('(?:(?:(?:[[:space:]]))*)', '(?:(?:(?:[[:space:]]))*)'),
            array('((( )))*', '((( )))*'),
            array('(((\s)))*', '(((\s)))*'),
            array('((([ ])))*', '((([ ])))*'),
            array('((([\s])))*', '((([\s])))*'),
            array('((([[:space:]])))*', '((([[:space:]])))*'),
            array('(?:(?:(?: )))*', '(?:(?:(?: )))*'),
            array('(?:(?:(?:\s)))*', '(?:(?:(?:\s)))*'),
            array('(?:(?:(?:[ ])))*', '(?:(?:(?:[ ])))*'),
            array('(?:(?:(?:[\s])))*', '(?:(?:(?:[\s])))*'),
            array('(?:(?:(?:[[:space:]])))*', '(?:(?:(?:[[:space:]])))*'),

            array(' +', ' +'),
            array('\s+', '\s+'),
            array('[ ]+', '[ ]+'),
            array('[\s]+', '[\s]+'),
            array('[[:space:]]+', '[[:space:]]+'),
            array('( +)', '( +)'),
            array('(\s+)', '(\s+)'),
            array('([ ]+)', '([ ]+)'),
            array('([\s]+)', '([\s]+)'),
            array('([[:space:]]+)', '([[:space:]]+)'),
            array('(?: +)', '(?: +)'),
            array('(?:\s+)', '(?:\s+)'),
            array('(?:[ ]+)', '(?:[ ]+)'),
            array('(?:[\s]+)', '(?:[\s]+)'),
            array('(?:[[:space:]]+)', '(?:[[:space:]]+)'),
            array('((( +)))', '((( +)))'),
            array('(((\s+)))', '(((\s+)))'),
            array('((([ ]+)))', '((([ ]+)))'),
            array('((([\s]+)))', '((([\s]+)))'),
            array('((([[:space:]]+)))', '((([[:space:]]+)))'),
            array('(?:(?:(?: +)))', '(?:(?:(?: +)))'),
            array('(?:(?:(?:\s+)))', '(?:(?:(?:\s+)))'),
            array('(?:(?:(?:[ ]+)))', '(?:(?:(?:[ ]+)))'),
            array('(?:(?:(?:[\s]+)))', '(?:(?:(?:[\s]+)))'),
            array('(?:(?:(?:[[:space:]]+)))', '(?:(?:(?:[[:space:]]+)))'),
            array('((( )+))', '((( )+))'),
            array('(((\s)+))', '(((\s)+))'),
            array('((([ ])+))', '((([ ])+))'),
            array('((([\s])+))', '((([\s])+))'),
            array('((([[:space:]])+))', '((([[:space:]])+))'),
            array('(?:(?:(?: )+))', '(?:(?:(?: )+))'),
            array('(?:(?:(?:\s)+))', '(?:(?:(?:\s)+))'),
            array('(?:(?:(?:[ ])+))', '(?:(?:(?:[ ])+))'),
            array('(?:(?:(?:[\s])+))', '(?:(?:(?:[\s])+))'),
            array('(?:(?:(?:[[:space:]])+))', '(?:(?:(?:[[:space:]])+))'),
            array('((( ))+)', '((( ))+)'),
            array('(((\s))+)', '(((\s))+)'),
            array('((([ ]))+)', '((([ ]))+)'),
            array('((([\s]))+)', '((([\s]))+)'),
            array('((([[:space:]]))+)', '((([[:space:]]))+)'),
            array('(?:(?:(?: ))+)', '(?:(?:(?: ))+)'),
            array('(?:(?:(?:\s))+)', '(?:(?:(?:\s))+)'),
            array('(?:(?:(?:[ ]))+)', '(?:(?:(?:[ ]))+)'),
            array('(?:(?:(?:[\s]))+)', '(?:(?:(?:[\s]))+)'),
            array('(?:(?:(?:[[:space:]]))+)', '(?:(?:(?:[[:space:]]))+)'),
            array('((( )))+', '((( )))+'),
            array('(((\s)))+', '(((\s)))+'),
            array('((([ ])))+', '((([ ])))+'),
            array('((([\s])))+', '((([\s])))+'),
            array('((([[:space:]])))+', '((([[:space:]])))+'),
            array('(?:(?:(?: )))+', '(?:(?:(?: )))+'),
            array('(?:(?:(?:\s)))+', '(?:(?:(?:\s)))+'),
            array('(?:(?:(?:[ ])))+', '(?:(?:(?:[ ])))+'),
            array('(?:(?:(?:[\s])))+', '(?:(?:(?:[\s])))+'),
            array('(?:(?:(?:[[:space:]])))+', '(?:(?:(?:[[:space:]])))+'),

            array(' ?', ' *'),
            array('\s?', '\s*'),
            array('[ ]?', '[ ]*'),
            array('[\s]?', '[\s]*'),
            array('[[:space:]]?', '[[:space:]]*'),
            array('( ?)', '( *)'),
            array('(\s?)', '(\s*)'),
            array('([ ]?)', '([ ]*)'),
            array('([\s]?)', '([\s]*)'),
            array('([[:space:]]?)', '([[:space:]]*)'),
            array('(?: ?)', '(?: *)'),
            array('(?:\s?)', '(?:\s*)'),
            array('(?:[ ]?)', '(?:[ ]*)'),
            array('(?:[\s]?)', '(?:[\s]*)'),
            array('(?:[[:space:]]?)', '(?:[[:space:]]*)'),
            array('((( ?)))', '((( *)))'),
            array('(((\s?)))', '(((\s*)))'),
            array('((([ ]?)))', '((([ ]*)))'),
            array('((([\s]?)))', '((([\s]*)))'),
            array('((([[:space:]]?)))', '((([[:space:]]*)))'),
            array('(?:(?:(?: ?)))', '(?:(?:(?: *)))'),
            array('(?:(?:(?:\s?)))', '(?:(?:(?:\s*)))'),
            array('(?:(?:(?:[ ]?)))', '(?:(?:(?:[ ]*)))'),
            array('(?:(?:(?:[\s]?)))', '(?:(?:(?:[\s]*)))'),
            array('(?:(?:(?:[[:space:]]?)))', '(?:(?:(?:[[:space:]]*)))'),
            array('((( )?))', '((( )*))'),
            array('(((\s)?))', '(((\s)*))'),
            array('((([ ])?))', '((([ ])*))'),
            array('((([\s])?))', '((([\s])*))'),
            array('((([[:space:]])?))', '((([[:space:]])*))'),
            array('(?:(?:(?: )?))', '(?:(?:(?: )*))'),
            array('(?:(?:(?:\s)?))', '(?:(?:(?:\s)*))'),
            array('(?:(?:(?:[ ])?))', '(?:(?:(?:[ ])*))'),
            array('(?:(?:(?:[\s])?))', '(?:(?:(?:[\s])*))'),
            array('(?:(?:(?:[[:space:]])?))', '(?:(?:(?:[[:space:]])*))'),
            array('((( ))?)', '((( ))*)'),
            array('(((\s))?)', '(((\s))*)'),
            array('((([ ]))?)', '((([ ]))*)'),
            array('((([\s]))?)', '((([\s]))*)'),
            array('((([[:space:]]))?)', '((([[:space:]]))*)'),
            array('(?:(?:(?: ))?)', '(?:(?:(?: ))*)'),
            array('(?:(?:(?:\s))?)', '(?:(?:(?:\s))*)'),
            array('(?:(?:(?:[ ]))?)', '(?:(?:(?:[ ]))*)'),
            array('(?:(?:(?:[\s]))?)', '(?:(?:(?:[\s]))*)'),
            array('(?:(?:(?:[[:space:]]))?)', '(?:(?:(?:[[:space:]]))*)'),
            array('((( )))?', '((( )))*'),
            array('(((\s)))?', '(((\s)))*'),
            array('((([ ]?)))?', '((([ ]*)))?'),
            array('((([\s])))?', '((([\s])))*'),
            array('((([[:space:]])))?', '((([[:space:]])))*'),
            array('(?:(?:(?: )))?', '(?:(?:(?: )))*'),
            array('(?:(?:(?:\s)))?', '(?:(?:(?:\s)))*'),
            array('(?:(?:(?:[ ])))?', '(?:(?:(?:[ ])))*'),
            array('(?:(?:(?:[\s])))?', '(?:(?:(?:[\s])))*'),
            array('(?:(?:(?:[[:space:]])))?', '(?:(?:(?:[[:space:]])))*'),

            array(' {2,5}', ' {2,5}'),
            array('\s{2,5}', '\s{2,5}'),
            array('[ ]{2,5}', '[ ]{2,5}'),
            array('[\s]{2,5}', '[\s]{2,5}'),
            array('[[:space:]]{2,5}', '[[:space:]]{2,5}'),
            array('( {2,5})', '( {2,5})'),
            array('(\s{2,5})', '(\s{2,5})'),
            array('([ ]{2,5})', '([ ]{2,5})'),
            array('([\s]{2,5})', '([\s]{2,5})'),
            array('([[:space:]]{2,5})', '([[:space:]]{2,5})'),
            array('(?: {2,5})', '(?: {2,5})'),
            array('(?:\s{2,5})', '(?:\s{2,5})'),
            array('(?:[ ]{2,5})', '(?:[ ]{2,5})'),
            array('(?:[\s]{2,5})', '(?:[\s]{2,5})'),
            array('(?:[[:space:]]{2,5})', '(?:[[:space:]]{2,5})'),
            array('((( {2,5})))', '((( {2,5})))'),
            array('(((\s{2,5})))', '(((\s{2,5})))'),
            array('((([ ]{2,5})))', '((([ ]{2,5})))'),
            array('((([\s]{2,5})))', '((([\s]{2,5})))'),
            array('((([[:space:]]{2,5})))', '((([[:space:]]{2,5})))'),
            array('(?:(?:(?: {2,5})))', '(?:(?:(?: {2,5})))'),
            array('(?:(?:(?:\s{2,5})))', '(?:(?:(?:\s{2,5})))'),
            array('(?:(?:(?:[ ]{2,5})))', '(?:(?:(?:[ ]{2,5})))'),
            array('(?:(?:(?:[\s]{2,5})))', '(?:(?:(?:[\s]{2,5})))'),
            array('(?:(?:(?:[[:space:]]{2,5})))', '(?:(?:(?:[[:space:]]{2,5})))'),
            array('((( ){2,5}))', '((( ){2,5}))'),
            array('(((\s){2,5}))', '(((\s){2,5}))'),
            array('((([ ]){2,5}))', '((([ ]){2,5}))'),
            array('((([\s]){2,5}))', '((([\s]){2,5}))'),
            array('((([[:space:]]){2,5}))', '((([[:space:]]){2,5}))'),
            array('(?:(?:(?: ){2,5}))', '(?:(?:(?: ){2,5}))'),
            array('(?:(?:(?:\s){2,5}))', '(?:(?:(?:\s){2,5}))'),
            array('(?:(?:(?:[ ]){2,5}))', '(?:(?:(?:[ ]){2,5}))'),
            array('(?:(?:(?:[\s]){2,5}))', '(?:(?:(?:[\s]){2,5}))'),
            array('(?:(?:(?:[[:space:]]){2,5}))', '(?:(?:(?:[[:space:]]){2,5}))'),
            array('((( )){2,5})', '((( )){2,5})'),
            array('(((\s)){2,5})', '(((\s)){2,5})'),
            array('((([ ])){2,5})', '((([ ])){2,5})'),
            array('((([\s])){2,5})', '((([\s])){2,5})'),
            array('((([[:space:]])){2,5})', '((([[:space:]])){2,5})'),
            array('(?:(?:(?: )){2,5})', '(?:(?:(?: )){2,5})'),
            array('(?:(?:(?:\s)){2,5})', '(?:(?:(?:\s)){2,5})'),
            array('(?:(?:(?:[ ])){2,5})', '(?:(?:(?:[ ])){2,5})'),
            array('(?:(?:(?:[\s])){2,5})', '(?:(?:(?:[\s])){2,5})'),
            array('(?:(?:(?:[[:space:]])){2,5})', '(?:(?:(?:[[:space:]])){2,5})'),
            array('((( ))){2,5}', '((( ))){2,5}'),
            array('(((\s))){2,5}', '(((\s))){2,5}'),
            array('((([ ]))){2,5}', '((([ ]))){2,5}'),
            array('((([\s]))){2,5}', '((([\s]))){2,5}'),
            array('((([[:space:]]))){2,5}', '((([[:space:]]))){2,5}'),
            array('(?:(?:(?: ))){2,5}', '(?:(?:(?: ))){2,5}'),
            array('(?:(?:(?:\s))){2,5}', '(?:(?:(?:\s))){2,5}'),
            array('(?:(?:(?:[ ]))){2,5}', '(?:(?:(?:[ ]))){2,5}'),
            array('(?:(?:(?:[\s]))){2,5}', '(?:(?:(?:[\s]))){2,5}'),
            array('(?:(?:(?:[[:space:]]))){2,5}', '(?:(?:(?:[[:space:]]))){2,5}'),
        );
    }

    protected function get_test_consecutive_quant_nodes_trivial() {
        return array(
            array('(?:a?)?', '(?:a?)'),
            array('(?:a?)+', '(?:a*)'),
            array('(?:a?)*', '(?:a*)'),
            array('(?:a+)?', '(?:a*)'),
            array('(?:a+)+', '(?:a+)'),
            array('(?:a+)*', '(?:a*)'),
            array('(?:a*)?', '(?:a*)'),
            array('(?:a*)+', '(?:a*)'),
            array('(?:a*)*', '(?:a*)'),
            array('(?:a?){0,1}', '(?:a?)'),
            array('(?:a?){0,}', '(?:a*)'),
            array('(?:a?){1,2}', '(?:a{0,2})'),
            array('(?:a?){1,}', '(?:a*)'),
            array('(?:a?){2,4}', '(?:a{0,4})'),
            array('(?:a+){0,1}', '(?:a*)'),
            array('(?:a+){0,}', '(?:a*)'),
            array('(?:a+){1,2}', '(?:a+)'),
            array('(?:a+){1,}', '(?:a+)'),
            array('(?:a+){2,4}', '(?:a+)'),
            array('(?:a*){0,1}', '(?:a*)'),
            array('(?:a*){0,}', '(?:a*)'),
            array('(?:a*){1,2}', '(?:a*)'),
            array('(?:a*){1,}', '(?:a*)'),
            array('(?:a*){2,4}', '(?:a*)'),

            array('(?:a{0,1})?', '(?:a?)'),
            array('(?:a{0,})?', '(?:a*)'),
            array('(?:a{1,2})?', '(?:a{0,2})'),
            array('(?:a{1,})?', '(?:a*)'),
            array('(?:a{2,4})?', '(?:a{0,4})'),
            array('(?:a{0,1})+', '(?:a*)'),
            array('(?:a{0,})+', '(?:a*)'),
            array('(?:a{1,2})+', '(?:a+)'),
            array('(?:a{1,})+', '(?:a+)'),
            array('(?:a{2,4})+', '(?:a+)'),
            array('(?:a{0,1})*', '(?:a*)'),
            array('(?:a{0,})*', '(?:a*)'),
            array('(?:a{1,2})*', '(?:a*)'),
            array('(?:a{1,})*', '(?:a*)'),
            array('(?:a{2,4})*', '(?:a*)'),

            array('(?:a{2,}){5,}', '(?:a{2,})'),
            array('(?:a{5,}){2,}', '(?:a{2,})'),
            array('(?:a{2,}){2,}', '(?:a{2,})'),
            array('(?:a{2,3}){2,3}', '(?:a{2,3})'),

            array('(?:a{2,3}){4,5}', '(?:a{2,5})'),
            array('(?:a{4,5}){2,3}', '(?:a{2,5})'),
            array('(?:a{2,3}){3,4}', '(?:a{2,4})'),
            array('(?:a{3,4}){2,3}', '(?:a{2,4})'),

            array('(?:a?)?|c', '(?:a?)|c'),

            array('(?:a?b?)?|c', '(?:a?b?)?|c'), //TODO

            array('a??', 'a??'),
            array('a?+', 'a?+'),
            array('a?*', 'a*'),
            array('a?{0,1}', 'a?'),
            array('a+?', 'a+?'),
            array('a++', 'a++'),
            array('a+*', 'a*'),
            array('a+{0,1}', 'a*'),
            array('a*?', 'a*?'),
            array('a*+', 'a*+'),
            array('a**', 'a*'),
            array('a*{0,1}', 'a*'),

            array('a{1,2}{3,4}', 'a{1,4}'),
//            array('a{2,3}{4,5}', 'a{2,3}{4,5}'),
            array('a{2,3}{3,5}', 'a{2,5}'),
        );
    }
}

