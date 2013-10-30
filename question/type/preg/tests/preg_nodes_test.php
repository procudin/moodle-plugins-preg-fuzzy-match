<?php

/**
 * Unit tests for question/type/preg/preg_nodes.php.
 *
 * @package    qtype_preg
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Oleg Sychev <oasychev@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/preg/preg_nodes.php');
require_once($CFG->dirroot . '/question/type/preg/nfa_matcher/nfa_matcher.php');
require_once($CFG->dirroot . '/question/type/poasquestion/stringstream/stringstream.php');
require_once($CFG->dirroot . '/question/type/preg/preg_lexer.lex.php');

class qtype_preg_nodes_test extends PHPUnit_Framework_TestCase {

    function test_clone_preg_operator() {
        //Try copying tree for a|b*
        $anode = new qtype_preg_leaf_charset();
        $anode->set_user_info(new qtype_preg_position());
        $anode->charset = 'a';
        $bnode = new qtype_preg_leaf_charset();
        $bnode->set_user_info(new qtype_preg_position());
        $bnode->charset = 'b';
        $astnode = new qtype_preg_node_infinite_quant();
        $astnode->set_user_info(new qtype_preg_position());
        $astnode->leftborder = 0;
        $astnode->operands[] = $bnode;
        $altnode = new qtype_preg_node_alt();
        $altnode->set_user_info(new qtype_preg_position());
        $altnode->operands[] = $anode;
        $altnode->operands[] = $astnode;

        $copyroot = clone $altnode;

        $this->assertTrue($copyroot == $altnode, 'Root node contents copied wrong');
        $this->assertTrue($copyroot !== $altnode, 'Root node wasn\'t copied');
        $this->assertTrue($copyroot->operands[0] == $altnode->operands[0], 'A character node contents copied wrong');
        $this->assertTrue($copyroot->operands[0] !== $altnode->operands[0], 'A character node wasn\'t copied');
        $this->assertTrue($copyroot->operands[1] == $altnode->operands[1], 'Asterisk node contents copied wrong');
        $this->assertTrue($copyroot->operands[1] !== $altnode->operands[1], 'Asterisk node wasn\'t copied');
        $this->assertTrue($copyroot->operands[1]->operands[0] == $altnode->operands[1]->operands[0], 'B character node contents copied wrong');
        $this->assertTrue($copyroot->operands[1]->operands[0] !== $altnode->operands[1]->operands[0], 'B character node wasn\'t copied');
    }
    function test_backref_no_match() {
        $regex = '(abc)';
        $length = 0;
        $matchoptions = new qtype_preg_matching_options();  // Forced subexpression catupring.
        $matcher = new qtype_preg_nfa_matcher($regex, $matchoptions);
        $matcher->match('abc');
        $backref = new qtype_preg_leaf_backref();
        $backref->number = 1;
        $backref->matcher = $matcher;

        // Matching at the end of the string.
        $res = $backref->match(new qtype_poasquestion_string('abc'), 3, $length, $matcher->get_match_results());
        $ch = $backref->next_character(new qtype_poasquestion_string('abc'), 2, $length, $matcher->get_match_results());
        $this->assertFalse($res);
        $this->assertEquals($length, 0);
        $this->assertEquals($ch, 'abc');
        // The string doesn't match with backref at all.
        $res = $backref->match(new qtype_poasquestion_string('abcdef'), 3, $length, $matcher->get_match_results());
        $ch = $backref->next_character(new qtype_poasquestion_string('abcdef'), 2, $length, $matcher->get_match_results());
        $this->assertFalse($res);
        $this->assertEquals($length, 0);
        $this->assertEquals($ch, 'abc');
    }
    function test_backref_partial_match() {
        $regex = '(abc)';
        $length = 0;
        $matchoptions = new qtype_preg_matching_options();  // Forced subexpression catupring.
        $matcher = new qtype_preg_nfa_matcher($regex, $matchoptions);
        $matcher->match('abc');
        $backref = new qtype_preg_leaf_backref();
        $backref->number = 1;
        $backref->matcher = $matcher;

        // Reaching the end of the string.
        $res = $backref->match(new qtype_poasquestion_string('abcab'), 3, $length, $matcher->get_match_results());
        $ch = $backref->next_character(new qtype_poasquestion_string('abc'), 2, $length, $matcher->get_match_results());
        $this->assertFalse($res);
        $this->assertEquals($length, 2);
        $this->assertEquals($ch, 'c');
        // The string matches backref partially.
        $res = $backref->match(new qtype_poasquestion_string('abcacd'), 3, $length, $matcher->get_match_results());
        $ch = $backref->next_character(new qtype_poasquestion_string('abcdef'), 2, $length, $matcher->get_match_results());
        $this->assertFalse($res);
        $this->assertEquals($length, 1);
        $this->assertEquals($ch, 'bc');
    }
    function test_backref_full_match() {
        $regex = '(abc)';
        $length = 0;
        $matchoptions = new qtype_preg_matching_options();  // Forced subexpression catupring.
        $matcher = new qtype_preg_nfa_matcher($regex, $matchoptions);
        $matcher->match('abc');
        $backref = new qtype_preg_leaf_backref();
        $backref->number = 1;
        $backref->matcher = $matcher;

        $res = $backref->match(new qtype_poasquestion_string('abcabc'), 3, $length, $matcher->get_match_results());
        $ch = $backref->next_character(new qtype_poasquestion_string('abc'), 3, $length, $matcher->get_match_results());
        $this->assertTrue($res);
        $this->assertEquals($length, 3);
        $this->assertEquals($ch, '');
    }
    function test_backref_empty_match() {
        $regex = '(^$)';
        $length = 0;
        $matchoptions = new qtype_preg_matching_options();  // Forced subexpression catupring.
        $matcher = new qtype_preg_nfa_matcher($regex, $matchoptions);
        $matcher->match('');
        $this->assertTrue($matcher->get_match_results()->full);
        $backref = new qtype_preg_leaf_backref();
        $backref->number = 1;
        $backref->matcher = $matcher;

        $res = $backref->match(new qtype_poasquestion_string(''), 0, $length, $matcher->get_match_results());
        $ch = $backref->next_character(new qtype_poasquestion_string(''), -1, $length, $matcher->get_match_results());
        $this->assertTrue($res);
        $this->assertEquals($length, 0);
        $this->assertEquals($ch, '');
    }
    function test_backref_alt_match() {
        $regex = '(ab|cd|)';
        $length = 0;
        $matchoptions = new qtype_preg_matching_options();  // Forced subexpression catupring.
        $matcher = new qtype_preg_nfa_matcher($regex, $matchoptions);
        $matcher->match('ab');
        $backref = new qtype_preg_leaf_backref();
        $backref->number = 1;
        $backref->matcher = $matcher;

        // 2 characters matched
        $res = $backref->match(new qtype_poasquestion_string('aba'), 2, $length, $matcher->get_match_results());
        $ch = $backref->next_character(new qtype_poasquestion_string('abc'), 2, $length, $matcher->get_match_results());
        $this->assertFalse($res);
        $this->assertEquals($length, 1);
        $this->assertEquals($ch, 'b');
        // Emptiness matched.
        $matcher->match('xyz');
        $res = $backref->match(new qtype_poasquestion_string('xyz'), 0, $length, $matcher->get_match_results());
        $this->assertTrue($res);
        $this->assertEquals($length, 0);
    }
    function test_anchoring() {
        $handler = new qtype_preg_nfa_matcher('^');
        $this->assertTrue($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^|^');
        $this->assertTrue($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.*cd|(^a|.*x)|^');
        $this->assertTrue($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('(?:a.+$)|.*cd|(^a|.*x)|^');        // (?:a.+$) breaks anchoring
        $this->assertFalse($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.+cd|(^a|.*x)|^');       // .+cd breaks anchoring
        $this->assertFalse($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.cd|(^a|.*x)|^');        // .cd breaks anchoring
        $this->assertFalse($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.*cd|(a|.*x)|^');        // (a|.*x) breaks anchoring
        $this->assertFalse($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.*cd|(^a|x)|^');         // (^a|x) breaks anchoring
        $this->assertFalse($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.*cd|(^a|.x)|^');        // (^a|.x) breaks anchoring
        $this->assertFalse($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.*cd|(^a|.?x)|^');       // (^a|.?x) breaks anchoring
        $this->assertFalse($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.*cd|(^a|.*x)|^');
        $this->assertTrue($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.*cd|(^a|.*x)||||');     // Emptiness makes anchoring
        $this->assertTrue($handler->is_regex_anchored());
        $handler = new qtype_preg_nfa_matcher('^(?:a.+$)|.*cd|(^a|.*x)|(|c)');    // (|c) makes anchoring
        $this->assertTrue($handler->is_regex_anchored());
    }
    function test_syntax_errors() {
        $handler = new qtype_preg_regex_handler('(*UTF9))((?(?=x)a|b|c)()({5,4})(?i-i)[[:hamster:]]\p{Squirrel}[abc');
        $errors = $handler->get_errors();
        $this->assertTrue(count($errors) == 11);
        /*$this->assertTrue($errors[0]->index_first == 31); // Setting and unsetting modifier.
        $this->assertTrue($errors[0]->index_last == 36);
        $this->assertTrue($errors[1]->index_first == 62); // Unclosed charset.
        $this->assertTrue($errors[1]->index_last == 65);
        $this->assertTrue($errors[2]->index_first == 0);  // Unknown control sequence.
        $this->assertTrue($errors[2]->index_last == 6);
        $this->assertTrue($errors[3]->index_first == 7);  // Wrong closing paren.
        $this->assertTrue($errors[3]->index_last == 7);
        $this->assertTrue($errors[4]->index_first == 9);  // Three alternations in the conditional subexpression.
        $this->assertTrue($errors[4]->index_last == 21);
        $this->assertTrue($errors[5]->index_first == 25); // Quantifier without operand.
        $this->assertTrue($errors[5]->index_last == 29);
        $this->assertTrue($errors[6]->index_first == 26); // Wrong quantifier ranges.
        $this->assertTrue($errors[6]->index_last == 28);
        $this->assertTrue($errors[7]->index_first == 38); // Unknown POSIX class.
        $this->assertTrue($errors[7]->index_last == 48);
        $this->assertTrue($errors[8]->index_first == 50); // Unknown Unicode property.
        $this->assertTrue($errors[8]->index_last == 61);
        $this->assertTrue($errors[9]->index_first == 22); // Empty parens.
        $this->assertTrue($errors[9]->index_last == 23);
        $this->assertTrue($errors[10]->index_first == 8); // Wrong opening paren.
        $this->assertTrue($errors[10]->index_last == 8);*/
        $handler = new qtype_preg_regex_handler('(?z)a(b)\1\2');
        $errors = $handler->get_errors();
        $this->assertTrue(count($errors) == 3);
        /*$this->assertTrue($errors[0]->index_first == 0);  // Wrong modifier.
        $this->assertTrue($errors[0]->index_last == 3);
        $this->assertTrue($errors[1]->index_first == 10); // Backreference to unexisting subexpression.
        $this->assertTrue($errors[1]->index_last == 11);*/
    }

    function test_expand_concat() {
        $handler = new qtype_preg_regex_handler("abcd");
        $idcounter = 1000;
        $node = $handler->get_ast_root();
        $node->expand(0, 2, $idcounter, true);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT && $node->position->indfirst == 0 && $node->position->indlast == 3);
        $node = $node->operands[0];
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT && $node->position->indfirst == 0 && $node->position->indlast == 2);
        $node = $node->operands[0];
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT && $node->position->indfirst == 0 && $node->position->indlast == 1);
    }

    function test_expand_emptiness() {
        $handler = new qtype_preg_regex_handler("a|b|");
        $idcounter = 1000;

        $root = $handler->get_ast_root();
        $root->expand(1, 2, $idcounter);
        $this->assertTrue($root->operands[1]->operands[0]->position->indfirst == 2 && $root->operands[1]->operands[0]->position->indlast == 2);
        $this->assertTrue($root->operands[1]->operands[1]->position->indfirst == 4 && $root->operands[1]->operands[1]->position->indlast == 3);
        $this->assertTrue($root->operands[1]->position->indfirst == 2 && $root->operands[1]->position->indlast == 3);
    }

    function test_node_by_regex_fragment_one_char() {
        $handler = new qtype_preg_regex_handler("a");
        $idcounter = 1000;

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 0, $idcounter);
        $this->assertTrue($node === $root);

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 11, $idcounter);
        $this->assertTrue($node === null);
    }

    function test_node_by_regex_fragment_concat() {
        $handler = new qtype_preg_regex_handler("abcd");
        $idcounter = 1000;

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 1, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT && count($node->operands) == 2 && $node->operands[0]->flags[0][0]->data == 'a' && $node->operands[1]->flags[0][0]->data == 'b');

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 2, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT && count($node->operands) == 3 &&
                          $node->operands[0]->flags[0][0]->data == 'a' && $node->operands[2]->flags[0][0]->data == 'c');
    }

    function test_node_by_regex_fragment_concat_subpatt_quant() {
        $handler = new qtype_preg_regex_handler("(abcd)+");
        $idcounter = 1000;

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 5, $idcounter);
        $this->assertTrue($node === $root->operands[0]);

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 4, $idcounter);
        $this->assertTrue($node === $root->operands[0]);

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(2, 6, $idcounter);
        $this->assertTrue($node === $root);

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(1, 4, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT && count($node->operands) == 4 &&
                          $node->operands[0]->flags[0][0]->data == 'a' && $node->operands[3]->flags[0][0]->data == 'd');

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(2, 3, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT && count($node->operands) == 2);
        $this->assertTrue($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_CHARSET && $node->operands[0]->flags[0][0]->data == 'b');
        $this->assertTrue($node->operands[1]->type == qtype_preg_node::TYPE_LEAF_CHARSET && $node->operands[1]->flags[0][0]->data == 'c');
    }

    function test_node_by_regex_fragment_alt() {
        $handler = new qtype_preg_regex_handler("ab|cde");
        $idcounter = 1000;

        // Exact selection: 'c'.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(3, 3, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_LEAF_CHARSET);

        // Exact selection: 'cd'.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(3, 4, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT);

        // Exact selection: 'ab|cde'.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 5, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT && count($node->operands) == 2);
        $this->assertTrue($node->operands[0]->type == qtype_preg_node::TYPE_NODE_CONCAT && count($node->operands[0]->operands) == 2);
        $this->assertTrue($node->operands[1]->type == qtype_preg_node::TYPE_NODE_CONCAT && count($node->operands[1]->operands) == 3);

        // Selection to be expanded: 'b|c'.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(1, 3, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT && count($node->operands) == 2);
        $node = $node->operands[1];
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT && count($node->operands) == 3 && $node->operands[2]->flags[0][0]->data == 'e');
    }

    function test_node_by_regex_fragment_multiline() {
        $handler = new qtype_preg_regex_handler("ab|d\n(abcd)+\nqwe(?#comment\n)|alt");
        $idcounter = 1000;

        // Exact selection 'b'.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 1, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT);

        // Exact selection 'b'.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(7, 7, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_LEAF_CHARSET);

        // Exact selection 't'.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(30, 30, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_LEAF_CHARSET);

        // Exact selection 'alt'.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(28, 30, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT);

        // Selection 'qwe' to be expanded.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(13, 15, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_CONCAT);

        // Comment selection, should be expanded to the whole alternation.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(19, 25, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT);

        // Selection '+' to be expanded.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(11, 11, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT);

        // Selection '|' to be expanded.
        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(28, 28, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT);
    }
    
    function test_node_by_regex_fragment_emptiness() {
        $handler = new qtype_preg_regex_handler("a|b|");
        $idcounter = 1000;

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(3, 3, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT && $node->position->indfirst == 2 && $node->position->indlast == 3);
        $this->assertTrue($node->operands[0]->flags[0][0]->data == 'b');
        $this->assertTrue($node->operands[1]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY);

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(2, 3, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT && $node->position->indfirst == 2 && $node->position->indlast == 3);
        $this->assertTrue($node->operands[0]->flags[0][0]->data == 'b');
        $this->assertTrue($node->operands[1]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY);

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(1, 1, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT && $node->position->indfirst == 0 && $node->position->indlast == 2);
        $this->assertTrue($node->operands[0]->flags[0][0]->data == 'a');
        $this->assertTrue($node->operands[1]->flags[0][0]->data == 'b');

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(4, 3, $idcounter);
        $this->assertTrue($node->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY && $node->position->indfirst == 4 && $node->position->indlast == 3);

        $handler = new qtype_preg_regex_handler("|a|b");
        $idcounter = 1000;

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, -1, $idcounter);
        $this->assertTrue($node->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY && $node->position->indfirst == 0 && $node->position->indlast == -1);
    }

    function test_node_by_regex_fragment_whitespaces() {
        $handler = new qtype_preg_regex_handler("a  \t");
        $idcounter = 1000;

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(0, 0, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_LEAF_CHARSET && $node->position->indfirst == 0 && $node->position->indlast == 0);
        $this->assertTrue($node->flags[0][0]->data == 'a');

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(1, 1, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_LEAF_CHARSET && $node->position->indfirst == 1 && $node->position->indlast == 1);
        $this->assertTrue($node->flags[0][0]->data == ' ');

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(2, 2, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_LEAF_CHARSET && $node->position->indfirst == 2 && $node->position->indlast == 2);
        $this->assertTrue($node->flags[0][0]->data == ' ');

        $root = clone $handler->get_ast_root();
        $node = $root->node_by_regex_fragment(3, 3, $idcounter);
        $this->assertTrue($node->type == qtype_preg_node::TYPE_LEAF_CHARSET && $node->position->indfirst == 3 && $node->position->indlast == 3);
        $this->assertTrue($node->flags[0][0]->data == "\t");
    }

    function test_selection_as_option() {
        $options = new qtype_preg_handling_options();
        $options->selection = new qtype_preg_position(3, 3);
        $handler = new qtype_preg_regex_handler("a|b|", $options);
        $node = $handler->get_selected_node();
        $this->assertTrue($node->type == qtype_preg_node::TYPE_NODE_ALT && $node->position->indfirst == 2 && $node->position->indlast == 3);
        $this->assertTrue($node->operands[0]->flags[0][0]->data == 'b');
        $this->assertTrue($node->operands[1]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY);
    }
}

class qtype_preg_match_test extends PHPUnit_Framework_TestCase {
    function create_lexer($regex, $options = null) {
        if ($options === null) {
            $options = new qtype_preg_handling_options();
            $options->preserveallnodes = true;
        }
        StringStreamController::createRef('regex', $regex);
        $pseudofile = fopen('string://regex', 'r');
        $lexer = new qtype_preg_lexer($pseudofile);
        $lexer->set_options($options);
        return $lexer;
    }

    function test_string_ends() {
        $str = new qtype_poasquestion_string("a\n");
        $length = 0;
        $lexer = $this->create_lexer("[ab\n]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsafter[] = $assert;
        $pos = 1;
        $a = $leaf->match($str, $pos, $length);
        $this->assertTrue($a, 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 1, 'Return length is not equal to expected');
    }

    function test_character_with_circumflex() {
        $str = new qtype_poasquestion_string("ab\n");
        $length = 0;
        $lexer = $this->create_lexer("[ab]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsafter[] = $assert;
        $pos = 0;
        $this->assertFalse($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 0, 'Return length is not equal to expected');
    }

    function test_string_ends_dollar_assert() {
        $str = new qtype_poasquestion_string("ab\na\nas");
        $length = 0;
        $lexer = $this->create_lexer("[ab\n]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_dollar;
        $leaf->assertionsbefore[] = $assert;
        $pos = 2;
        $this->assertTrue($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 1, 'Return length is not equal to expected');
    }

    function test_character_with_dollar() {
        $str = new qtype_poasquestion_string("ab\na\nas");
        $length = 0;
        $lexer = $this->create_lexer("[ab]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_dollar;
        $leaf->assertionsbefore[] = $assert;
        $pos = 2;
        $this->assertFalse($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 0, 'Return length is not equal to expected');
    }

    function test_one_string() {
        $str = new qtype_poasquestion_string("ab");
        $length = 0;
        $lexer = $this->create_lexer("[a]");
        $leaf = $lexer->nextToken()->value;
        $pos = 0;
        $this->assertTrue($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 1, 'Return length is not equal to expected');
    }

    function test_single_assert() {
        $str = new qtype_poasquestion_string("ab\na\nas");
        $length = 0;
        $leaf= new qtype_preg_leaf_assert_circumflex;
        $pos = 0;
        $this->assertTrue($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 0, 'Return length is not equal to expected');
    }

    function test_before_and_after_asserts_true() {
        $str = new qtype_poasquestion_string("ab\na\nas");
        $length = 0;
        $lexer = $this->create_lexer("[ab\n]");
        $leaf = $lexer->nextToken()->value;
        $assert1 = new qtype_preg_leaf_assert_dollar;
        $assert2 = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsbefore[] = $assert1;
        $leaf->assertionsafter[] = $assert2;
        $pos = 2;
        $this->assertTrue($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 1, 'Return length is not equal to expected');
    }

    function test_before_and_after_asserts_false() {
        $str = new qtype_poasquestion_string("ab\na\nas");
        $length = 0;
        $lexer = $this->create_lexer("[ab]");
        $leaf = $lexer->nextToken()->value;
        $assert1 = new qtype_preg_leaf_assert_dollar;
        $assert2 = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsbefore[] = $assert1;
        $leaf->assertionsafter[] = $assert2;
        $pos = 2;
        $this->assertFalse($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 0, 'Return length is not equal to expected');
    }

    function test_empty_string_true() {
        $str = new qtype_poasquestion_string("ab\n\nas");
        $length = 0;
        $lexer = $this->create_lexer("[a-z\n]");
        $leaf = $lexer->nextToken()->value;
        $assert1 = new qtype_preg_leaf_assert_dollar;
        $assert2 = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsbefore[] = $assert1;
        $leaf->assertionsafter[] = $assert2;
        $pos = 3;
        $this->assertTrue($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 1, 'Return length is not equal to expected');
    }

    function test_empty_string_false() {
        $str = new qtype_poasquestion_string("ab\n\nas");
        $length = 0;
        $lexer = $this->create_lexer("[a-z]");
        $leaf = $lexer->nextToken()->value;
        $assert1 = new qtype_preg_leaf_assert_dollar;
        $assert2 = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsbefore[] = $assert1;
        $leaf->assertionsafter[] = $assert2;
        $pos = 3;
        $this->assertFalse($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 0, 'Return length is not equal to expected');
    }

    function test_single_dollar_in_the_end() {
        $str = new qtype_poasquestion_string("ab\n\nas");
        $length = 0;
        $leaf = new qtype_preg_leaf_assert_dollar;
        $pos = 6;
        $this->assertTrue($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 0, 'Return length is not equal to expected');
    }

    function test_middle_of_the_string() {
        $str = new qtype_poasquestion_string("bcd");
        $length = 0;
        $lexer = $this->create_lexer("[a-c\n]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsafter[] = $assert;
        $pos = 1;
        $this->assertFalse($leaf->match($str, $pos, $length), 'Return boolean flag is not equal to expected');
        $this->assertEquals($length, 0, 'Return length is not equal to expected');
    }
}

class qtype_preg_next_character_test extends PHPUnit_Framework_TestCase {
    function create_lexer($regex, $options = null) {
        if ($options === null) {
            $options = new qtype_preg_handling_options();
            $options->preserveallnodes = true;
        }
        StringStreamController::createRef('regex', $regex);
        $pseudofile = fopen('string://regex', 'r');
        $lexer = new qtype_preg_lexer($pseudofile);
        $lexer->set_options($options);
        return $lexer;
    }

    function test_empty_string() {
        $str = new qtype_poasquestion_string("ax");
        $length = 1;
        $lexer = $this->create_lexer("[ab\n]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsafter[] = $assert;
        $pos = 1;
        $this->assertEquals($leaf->next_character($str, $pos, $length), "\n", 'Return character is not equal to expected');
    }

    function test_string_ends_false() {
        $str = new qtype_poasquestion_string("b\n");
        $length = 1;
        $lexer = $this->create_lexer("[ab]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsafter[] = $assert;
        $pos = 1;
        $this->assertEquals($leaf->next_character($str, $pos, $length), qtype_preg_leaf::NEXT_CHAR_CANNOT_GENERATE, 'Return character is not equal to expected');
    }

    function test_string_ends_dollar_assert() {
        $str = new qtype_poasquestion_string("bx\na\nas");
        $length = 2;
        $lexer = $this->create_lexer("[ab\n]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_dollar;
        $leaf->assertionsbefore[] = $assert;
        $pos = 2;
        $this->assertEquals($leaf->next_character($str, $pos, $length), "\n", 'Return character is not equal to expected');
    }

    function test_character_with_dollar() {
        $str = new qtype_poasquestion_string("b\na\nas");
        $length = 1;
        $lexer = $this->create_lexer("[ab]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_dollar;
        $leaf->assertionsbefore[] = $assert;
        $pos = 1;
        $this->assertEquals($leaf->next_character($str, $pos, $length), qtype_preg_leaf::NEXT_CHAR_CANNOT_GENERATE, 'Return character is not equal to expected');
    }

    function test_one_string() {
        $str = new qtype_poasquestion_string("ab");
        $length = 1;
        $lexer = $this->create_lexer("[x-z]");
        $leaf = $lexer->nextToken()->value;
        $pos = 1;
        $this->assertEquals($leaf->next_character($str, $pos, $length), 'x', 'Return character is not equal to expected');
    }

    function test_single_assert() {
        $str = new qtype_poasquestion_string("\n\nas");
        $length = 0;
        $leaf = new qtype_preg_leaf_assert_circumflex;
        $pos = 0;
        $this->assertEquals($leaf->next_character($str, $pos, $length), qtype_preg_leaf::NEXT_CHAR_NOT_NEEDED, 'Return character is not equal to expected');
    }

    function test_before_and_after_asserts_false() {
        $str = new qtype_poasquestion_string("a\na\nas");
        $length = 1;
        $lexer = $this->create_lexer("[ab]");
        $leaf = $lexer->nextToken()->value;
        $assert1 = new qtype_preg_leaf_assert_dollar;
        $assert2 = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsbefore[] = $assert1;
        $leaf->assertionsafter[] = $assert2;
        $pos = 1;
        $this->assertEquals($leaf->next_character($str, $pos, $length), qtype_preg_leaf::NEXT_CHAR_CANNOT_GENERATE, 'Return character is not equal to expected');
    }

    function test_before_and_after_asserts_true() {
        $str = new qtype_poasquestion_string("abcd\nas");
        $length = 1;
        $lexer = $this->create_lexer("[a-z\n]");
        $leaf = $lexer->nextToken()->value;
        $assert1 = new qtype_preg_leaf_assert_dollar;
        $assert2 = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsbefore[] = $assert1;
        $leaf->assertionsafter[] = $assert2;
        $pos = 1;
        $this->assertEquals($leaf->next_character($str, $pos, $length), "\n", 'Return character is not equal to expected');
    }

    function test_single_dollar_in_the_end() {
        $str = new qtype_poasquestion_string("as");
        $length = 2;
        $leaf = new qtype_preg_leaf_assert_dollar;
        $pos = 2;
        $this->assertEquals($leaf->next_character($str, $pos, $length), qtype_preg_leaf::NEXT_CHAR_END_HERE, 'Return character is not equal to expected');
    }

    function test_middle_of_the_string() {
        $str = new qtype_poasquestion_string("bcd");
        $length = 1;
        $lexer = $this->create_lexer("[c\n]");
        $leaf = $lexer->nextToken()->value;
        $assert = new qtype_preg_leaf_assert_circumflex;
        $leaf->assertionsafter[] = $assert;
        $pos = 1;
        $this->assertEquals($leaf->next_character($str, $pos, $length), "\n", 'Return character is not equal to expected');
    }
}
