<?php
error_reporting(E_ALL);
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/question/type/preg/preg_lexer.lex.php');

class parser_test extends UnitTestCase {
    var $qtype;
    
    #function setUp() {
    #    $this->qtype = new preg_lexer();
    #}
    
    #function tearDown() {
    #    $this->qtype = null;   
    #}
    //Unit test for lexer
    function test_lexer_quantificators() {
        $lexer = new Yylex(fopen('C:\\denwer\\installed\\home\\moodle19\\www\\question\\type\\preg\\simpletest\\mainlexems.txt', 'r'));//?*+{1,5}{,5}{1,}{5}??*?+?{1,5}?{,5}?{1,}?{5}?
        $token = $lexer->nextToken();//?
        $this->assertTrue($token->type === preg_parser_yyParser::QUEST);
        $token = $lexer->nextToken();//*
        $this->assertTrue($token->type === preg_parser_yyParser::ITER);
        $token = $lexer->nextToken();//+
        $this->assertTrue($token->type === preg_parser_yyParser::PLUS);
        $token = $lexer->nextToken();//{1,5}
        $this->assertTrue($token->type === preg_parser_yyParser::QUANT);
        $this->assertTrue($token->value->type == NODE && $token->value->subtype == NODE_QUANT && $token->value->leftborder == 1 && $token->value->rightborder == 5 && $token->value->greed);
        $token = $lexer->nextToken();//{,5}
        $this->assertTrue($token->type === preg_parser_yyParser::QUANT);
        $this->assertTrue($token->value->type == NODE && $token->value->subtype == NODE_QUANT && $token->value->leftborder == 0 && $token->value->rightborder == 5 && $token->value->greed);
        $token = $lexer->nextToken();//{1,}
        $this->assertTrue($token->type === preg_parser_yyParser::QUANT);
        $this->assertTrue($token->value->type == NODE && $token->value->subtype == NODE_QUANT && $token->value->leftborder == 1 && $token->value->rightborder == -1 && $token->value->greed);
        $token = $lexer->nextToken();//{5}
        $this->assertTrue($token->type === preg_parser_yyParser::QUANT);
        $this->assertTrue($token->value->type == NODE && $token->value->subtype == NODE_QUANT && $token->value->leftborder == 5 && $token->value->rightborder == 5 && $token->value->greed);
        $token = $lexer->nextToken();//*?
        $this->assertTrue($token->type == preg_parser_yyParser::LAZY_ITER);
        $token = $lexer->nextToken();//??
        $this->assertTrue($token->type == preg_parser_yyParser::LAZY_QUEST);
        $token = $lexer->nextToken();//+?
        $this->assertTrue($token->type == preg_parser_yyParser::LAZY_PLUS);
        $token = $lexer->nextToken();//{1,5}?
        $this->assertTrue($token->type === preg_parser_yyParser::LAZY_QUANT);
        $this->assertTrue($token->value->type == NODE && $token->value->subtype == NODE_QUANT && $token->value->leftborder == 1 && $token->value->rightborder == 5 && !$token->value->greed);
        $token = $lexer->nextToken();//{,5}?
        $this->assertTrue($token->type === preg_parser_yyParser::LAZY_QUANT);
        $this->assertTrue($token->value->type == NODE && $token->value->subtype == NODE_QUANT && $token->value->leftborder == 0 && $token->value->rightborder == 5 && !$token->value->greed);
        $token = $lexer->nextToken();//{1,}?
        $this->assertTrue($token->type === preg_parser_yyParser::LAZY_QUANT);
        $this->assertTrue($token->value->type == NODE && $token->value->subtype == NODE_QUANT && $token->value->leftborder == 1 && $token->value->rightborder == -1 && !$token->value->greed);
        $token = $lexer->nextToken();//{5}?
        $this->assertTrue($token->type === preg_parser_yyParser::LAZY_QUANT);
        $this->assertTrue($token->value->type == NODE && $token->value->subtype == NODE_QUANT && $token->value->leftborder == 5 && $token->value->rightborder == 5 && !$token->value->greed);
    }
    function test_lexer_backslach() {
        $lexer = new Yylex(fopen('C:\\denwer\\installed\\home\\moodle19\\www\\question\\type\\preg\\simpletest\\backslash.txt', 'r'));//\\\*\[\23\023\x23\d\s\t
        $token = $lexer->nextToken();//\\
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == '\\');
        $token = $lexer->nextToken();//\*
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == '*');
        $token = $lexer->nextToken();//\[
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == '[');
        $token = $lexer->nextToken();//\23
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_LINK);
        $token = $lexer->nextToken();//\023
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && ord($token->value->chars) == 023);
        $token = $lexer->nextToken();//\x23
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && ord($token->value->chars) == 0x23);
        $token = $lexer->nextToken();//\d
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == '0123456789');
        $token = $lexer->nextToken();//\s
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == ' ');
        $token = $lexer->nextToken();//\t
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == chr(9));
    }
    function test_lexer_charclass() {
        $lexer = new Yylex(fopen('C:\\denwer\\installed\\home\\moodle19\\www\\question\\type\\preg\\simpletest\\charclass.txt', 'r'));//[a][abc][ab{][ab\\][ab\]][a\db][a-d][3-6]
        $token = $lexer->nextToken();//[a]
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == 'a');
        $token = $lexer->nextToken();//[abc]
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == 'abc');
        $token = $lexer->nextToken();//[ab{]
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == 'ab{');
        $token = $lexer->nextToken();//[ab\\]
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == 'ab\\');
        $token = $lexer->nextToken();//[ab\]]
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == 'ab]');
        $token = $lexer->nextToken();//[a\db]
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == 'a0123456789b');
        $token = $lexer->nextToken();//[a-d]
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == 'abcd');
        $token = $lexer->nextToken();//[3-6]
        $this->assertTrue($token->type === preg_parser_yyParser::PARSLEAF);
        $this->assertTrue($token->value->type == LEAF && $token->value->subtype == LEAF_CHARCLASS && $token->value->chars == '3456');
    }
}
?>