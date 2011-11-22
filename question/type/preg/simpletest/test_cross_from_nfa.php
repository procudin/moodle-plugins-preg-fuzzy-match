<?php
/**
 * Unit tests for matchers
 *
 * @copyright &copy; 2011  Valeriy Streltsov
 * @author Valeriy Streltsov, Volgograd State Technical University
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questions
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/question/type/preg/simpletest/crosstester.php');

class test_cross_from_nfa extends preg_cross_tester {

    function data_for_test_concat() {
        $test1 = array( 'str'=>'the matcher works',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>16),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'_the matcher works',
                        'is_match'=>false,
                        'full'=>false,
                        'index_first'=>array(0=>18),
                        'index_last'=>array(0=>17),
                        'left'=>17,
                        'next'=>'t');

        $test3 = array( 'str'=>'the matcher',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>10),
                        'left'=>6,
                        'next'=>' ');

        $test4 = array( 'str'=>'',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>-1),
                        'left'=>17,
                        'next'=>'t');

        return array('regex'=>'^the matcher works',
                     'tests'=>array($test1, $test2, $test3, $test4));
    }

    function data_for_test_alt() {
        $test1 = array( 'str'=>'abcf',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'def',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'deff',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'^abc|def$',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_assertions_simple_1() {
        $test1 = array( 'str'=>' abc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>' 9bc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'  b',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>0),
                        'left'=>2,
                        'next'=>'abcdefghijklmnopqrstuvwxyz');

        return array('regex'=>'^[a-z 0-9]\b[a-z 0-9]\B[a-z 0-9]',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_assertions_simple_2() {
        $test1 = array( 'str'=>'abc?z',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>4),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'abcaa',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>3),
                        'left'=>1,
                        'next'=>'');    // can't generate a character

        return array('regex'=>'^abc[a-z.?!]\b[a-zA-Z]',
                     'tests'=>array($test1, $test2));
    }

    function data_for_test_zero_length_loop() {
        $test1 = array( 'str'=>' a',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>1),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'[prefix] a',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>8),
                        'index_last'=>array(0=>9),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'^*[a-z 0-9](?:\b)+a${1,}',
                     'tests'=>array($test1, $test2));
    }

    function data_for_test_subpatterns_nested() {
        $test1 = array( 'str'=>'abcbcd',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>1,2=>3,3=>4),
                        'index_last'=>array(0=>5,1=>4,2=>4,3=>4),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'ad',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>1,2=>-1,3=>-1),    // the quantifier is outside subpatterns 2 and 3 so they are not matched!
                        'index_last'=>array(0=>1,1=>0,2=>-2,3=>-2),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'^a((b(c))*)d$',
                     'tests'=>array($test1, $test2));
    }

    function data_for_test_subpatterns_concatenated() {
        $test1 = array( 'str'=>'_abcdef',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>1,1=>1,2=>3,3=>5),
                        'index_last'=>array(0=>6,1=>2,2=>4,3=>6),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'[prefix] abef',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>9,1=>9,2=>-1,3=>11),
                        'index_last'=>array(0=>12,1=>10,2=>-2,3=>12),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(ab)(cd)?(ef)',
                     'tests'=>array($test1, $test2));
    }

    function data_for_test_subpatterns_alternated() {
        $test1 = array( 'str'=>'abcdefgh',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>0,2=>0,3=>-1,4=>-1),
                        'index_last'=>array(0=>1,1=>1,2=>1,3=>-2,4=>-2),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'((ab)|(cd)|(efgh))',
                     'tests'=>array($test1));
    }

    function data_for_test_subpatterns_quantifier_inside() {
        $test1 = array( 'str'=>'aaaaa',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>0),
                        'index_last'=>array(0=>4,1=>4),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(a*)',
                     'tests'=>array($test1));
    }

    function data_for_test_subpatterns_quantifier_outside() {
        $test1 = array( 'str'=>'aaaaa',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>4),
                        'index_last'=>array(0=>4,1=>4),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(a)*',
                     'tests'=>array($test1));
    }

    function data_for_test_subpatterns_tricky() {
        $test1 = array( 'str'=>'abc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>3,2=>3,3=>3,4=>3),
                        'index_last'=>array(0=>2,1=>2,2=>2,3=>2,4=>2),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(([a*]|\b)([b*]|\b)([c*]|\b))+',
                     'tests'=>array($test1));
    }

    function data_for_test_questquant() {
        $test1 = array( 'str'=>'ac',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>1),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'abc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'abbc',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>1),
                        'left'=>1,
                        'next'=>'c');

        return array('regex'=>'^ab?c$',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_negative_charset() {
        $test1 = array( 'str'=>'abcd',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>0),
                        'left'=>3,
                        'next'=>' acdefghijklmnopqrstuvwxyz0123456789!?.,');

        $test2 = array( 'str'=>'axcd',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>3),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'aacde',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>3),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'^a[^b]cd$',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_many_alternatives() {
        $test1 = array( 'str'=>'abi',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'cdi',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'efi',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test4 = array( 'str'=>'ghi',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test5 = array( 'str'=>'yzi',
                        'is_match'=>false,
                        'full'=>false,
                        'index_first'=>array(0=>3),
                        'index_last'=>array(0=>2),
                        'left'=>3,
                        'next'=>'aceg');

        return array('regex'=>'^(?:ab|cd|ef|gh)i$',
                     'tests'=>array($test1, $test2, $test3, $test4, $test5));
    }

    function data_for_test_repeated_chars() {
        $test1 = array( 'str'=>'ab',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>1),
                        'left'=>1,    // !!!
                        'next'=>'ab');

        $test2 = array( 'str'=>'abb',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'...ababababababababababbabababaabbbbbbbbbbbbaaaaaaaaaaaaabbbbbbbbbababababababb',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>3),
                        'index_last'=>array(0=>78),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(?:a|b)*abb$',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_brace_finite() {
        $test1 = array( 'str'=>'abbbbbc',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>5),
                        'left'=>11,
                        'next'=>'b');

        $test2 = array( 'str'=>'abbbbbbbbbbbbbbbbbbbbbbbbbc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>26),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'abbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbc',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>35),
                        'left'=>1,
                        'next'=>'c');

        return array('regex'=>'^ab{15,35}c$',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_brace_infinite() {
        $test1 = array( 'str'=>'abbbbbc',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>5),
                        'left'=>11,
                        'next'=>'b');

        $test2 = array( 'str'=>'abbbbbbbbbbbbbbbbbbbbbbbbbc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>26),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'abbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>103),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'^ab{15,}c$',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_plus() {
        $test1 = array( 'str'=>'ac',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>0),
                        'left'=>2,
                        'next'=>'b');

        $test2 = array( 'str'=>'abc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>2),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'abbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>100),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'^ab+c$',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_aster() {
        $test1 = array( 'str'=>'abcabcabcabcabcabcabcabcabcabc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>29),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'abcabcabcabcabcabcabcabcabcab',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>26),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'ab',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>2),
                        'index_last'=>array(0=>1),
                        'left'=>0,
                        'next'=>'');

        $test4 = array( 'str'=>'',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>-1),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(?:abc)*',
                     'tests'=>array($test1, $test2, $test3, $test4));
    }

    function data_for_test_cs() {
        $test1 = array( 'str'=>'abcd',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>0),
                        'left'=>3,
                        'next'=>'B');

        $test2 = array( 'str'=>'aBC',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>1),
                        'left'=>2,
                        'next'=>'c');

        return array('regex'=>'aBcD',
                     'tests'=>array($test1, $test2));
    }

    function data_for_test_cins() {
        $test1 = array( 'str'=>'abcd',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>3),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'aBcD',
                     'modifiers'=>'i',
                     'tests'=>array($test1));
    }

    function data_for_test_characters_left_simple() {
        $test1 = array( 'str'=>'ab cd',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>4),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'abc',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>1),
                        'left'=>3,
                        'next'=>' ');

        $test3 = array( 'str'=>'a',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0),
                        'index_last'=>array(0=>0),
                        'left'=>4,
                        'next'=>'b');

        $test4 = array( 'str'=>'',
                        'is_match'=>false,
                        'full'=>false,
                        'index_first'=>array(0=>-1),
                        'index_last'=>array(0=>-2),
                        'left'=>5,
                        'next'=>'a');

        return array('regex'=>'ab\b cd',
                     'tests'=>array($test1, $test2, $test3, $test4));
    }

    function data_for_test_characters_left() {
        $test1 = array( 'str'=>'abefg',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>2),
                        'index_last'=>array(0=>4,1=>4),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'ab',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>-1),
                        'index_last'=>array(0=>1,1=>-2),
                        'left'=>1,
                        'next'=>'h');

        $test3 = array( 'str'=>'abe',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>-1),
                        'index_last'=>array(0=>2,1=>-2),
                        'left'=>2,
                        'next'=>'f');

        return array('regex'=>'ab(cd|efg|h)',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_backref_simple() {
        $test1 = array( 'str'=>'abcabcabcabc',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>0,2=>0),
                        'index_last'=>array(0=>11,1=>5,2=>2),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'abcabc',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>0,2=>0),
                        'index_last'=>array(0=>5,1=>5,2=>2),
                        'left'=>6,
                        'next'=>'a');    // backref #1 not captured at all

        $test3 = array( 'str'=>'abcabcab',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>0,2=>0),
                        'index_last'=>array(0=>7,1=>5,2=>2),
                        'left'=>4,
                        'next'=>'c');    // backref #1 captured partially

        return array('regex'=>'((abc)\2)\1',
                     'tests'=>array($test1, $test2, $test3));
    }

    function data_for_test_alternated_backrefs() {
        $test1 = array( 'str'=>'abab',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>0,2=>-1,3=>2),
                        'index_last'=>array(0=>3,1=>1,2=>-2,3=>3),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'cdcd',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>-1,2=>0,3=>2),
                        'index_last'=>array(0=>3,1=>-2,2=>1,3=>3),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'aba',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>0,2=>-1,3=>-1),
                        'index_last'=>array(0=>2,1=>1,2=>-2,3=>-2),
                        'left'=>1,
                        'next'=>'b');

        $test4 = array( 'str'=>'abc',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>0,2=>-1,3=>-1),
                        'index_last'=>array(0=>1,1=>1,2=>-2,3=>-2),
                        'left'=>2,
                        'next'=>'a');

        return array('regex'=>'(?:(ab)|(cd))(\1|\2)',
                     'tests'=>array($test1, $test2, $test3, $test4));
    }

    function data_for_test_backref_quantified() {
        $test1 = array( 'str'=>'ababcdababcdababcdababcd',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>12,2=>12),
                        'index_last'=>array(0=>23,1=>17,2=>13),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'cdcd',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>4,1=>-1,2=>-1),
                        'index_last'=>array(0=>3,1=>-2,2=>-2),
                        'left'=>10000000,                    // TODO: standardize this value
                        'next'=>'');

        return array('regex'=>'((ab)\2cd)*\1',
                     'tests'=>array($test1, $test2));
    }

    function data_for_test_backref_full_and_partial() {
        $test1 = array( 'str'=>'abcdabcd',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>0),
                        'index_last'=>array(0=>7,1=>3),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'abcdab',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>0),
                        'index_last'=>array(0=>5,1=>3),
                        'left'=>2,
                        'next'=>'c');

        $test3 = array( 'str'=>'abcd',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>0),
                        'index_last'=>array(0=>3,1=>3),
                        'left'=>4,
                        'next'=>'a');

        $test4 = array( 'str'=>'abc',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(0=>0,1=>-1),
                        'index_last'=>array(0=>2,1=>-2),
                        'left'=>5,
                        'next'=>'d');

        return array('regex'=>'(abcd)\1',
                     'tests'=>array($test1, $test2, $test3, $test4));
    }

    function data_for_test_backref_tricky_1() {
        $test1 = array( 'str'=>'abxab',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>2,2=>0),
                        'index_last'=>array(0=>4,1=>4,2=>1),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'xabxab',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>1,1=>3,2=>1),
                        'index_last'=>array(0=>5,1=>5,2=>2),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(x\2|(ab))+',
                     'tests'=>array($test1, $test2));
    }

    function data_for_test_backref_tricky_2() {
        $test1 = array( 'str'=>'aaa',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>2),
                        'index_last'=>array(0=>2,1=>2),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'ababba',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>3),
                        'index_last'=>array(0=>5,1=>5),
                        'left'=>0,
                        'next'=>'');

        $test3 = array( 'str'=>'ababbabbba',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>6),
                        'index_last'=>array(0=>9,1=>9),
                        'left'=>0,
                        'next'=>'');


        $test4 = array( 'str'=>'ababbabbbabbbba',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>10),
                        'index_last'=>array(0=>14,1=>14),
                        'left'=>0,
                        'next'=>'');

        $test5 = array( 'str'=>'ababbabbbabbbb',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>6),
                        'index_last'=>array(0=>9,1=>9),
                        'left'=>0,
                        'next'=>'');

        $test6 = array( 'str'=>'',
                        'is_match'=>false,
                        'full'=>false,
                        'index_first'=>array(0=>-1,1=>-2),
                        'index_last'=>array(0=>-1,1=>-2),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(a|b\1)+',
                     'tests'=>array($test1, $test2, $test3, $test4, $test5, $test6));
    }

    function data_for_test_empty_match() {
        $test1 = array( 'str'=>'abcd',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>0),
                        'index_last'=>array(0=>3,1=>3),
                        'left'=>0,
                        'next'=>'');

        $test2 = array( 'str'=>'',
                        'is_match'=>true,
                        'full'=>true,
                        'index_first'=>array(0=>0,1=>0),
                        'index_last'=>array(0=>-1,1=>-1),
                        'left'=>0,
                        'next'=>'');

        return array('regex'=>'(abcd|)',
                     'tests'=>array($test1, $test2));
    }

    function data_for_test_quant_greedy() {
        $test1 = array( 'str'=>'abacd',
                        'is_match'=>true,
                        'full'=>false,
                        'index_first'=>array(array(0=>0),
                                             array(0=>0)),
                        'index_last'=>array(array(0=>2),
                                            array(0=>4)),
                        'left'=>array(4, 4),
                        'next'=>array('b', 'b'));

        return array('regex'=>'ab+[a-z]*bacd',
                     'tests'=>array($test1));
    }
}
?>