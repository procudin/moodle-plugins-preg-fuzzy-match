<?php # vim:ft=php
require_once($CFG->dirroot . '/question/type/preg/jlex.php');
require_once($CFG->dirroot . '/question/type/preg/preg_parser.php');
require_once($CFG->dirroot . '/question/type/preg/preg_nodes.php');
require_once($CFG->dirroot . '/question/type/preg/preg_unicode.php');

%%
%class qtype_preg_lexer
%function nextToken
%char
%unicode
%state CHARCLASS
%init{
    $this->errors = array();
    $this->lastsubpatt = 0;
    $this->maxsubpatt = 0;
    $this->subpatternmap = array();
    $this->lexemcount = 0;
    $this->optstack = array();
    $this->optstack[0] = new stdClass;
    // Set all modifier's fields to false, it must be set to correct values before initializing lexer and doing lexical analysis.
    $this->optstack[0]->i = false;
    $this->optstack[0]->subpattnum = -1;
    $this->optstack[0]->parennum = -1;
    $this->optcount = 1;
%init}
%{
    protected $errors;
    protected $lastsubpatt;
    protected $maxsubpatt;
    protected $subpatternmap;
    protected $lexemcount;
    protected $optstack;
    protected $optcount;

    // A reference to the matcher object to be passed to some nodes.
    public $matcher = null;
    // Global modifiers as a string - defined for entire expression.
    public $globalmodifiers = '';
    // Local modifiers - turned on (or off) using options in the expression.
    // It's contains copy of a global modifiers at start, but could be changed later.
    public $localmodifiers ='';

    public function get_errors() {
        return $this->errors;
    }

    public function get_max_subpattern() {
        return $this->maxsubpatt;
    }

    public function get_subpattern_map() {
        return $this->subpatternmap;
    }

    public function get_lexem_count() {
        return $this->lexemcount;
    }

    protected function form_node($name, $subtype = null, $data = null, $leftborder = null, $rightborder = null, $greed = true) {
        $result = new $name;
        if ($subtype !== null) {
            $result->subtype = $subtype;
        }
        // Set i modifier for leafs.
        if (is_a($result, 'preg_leaf') && $this->optcount > 0 && $this->optstack[$this->optcount - 1]->i) {
            $result->caseinsensitive = true;
        }
        switch($name) {
        case 'preg_leaf_charset':
            $result->charset = $data;
            break;
        case 'preg_leaf_backref':
            $result->number = $data;
            break;
        case 'preg_node_finite_quant':
            $result->rightborder = $rightborder;
        case 'preg_node_infinite_quant':
            $result->greed = $greed;
            $result->leftborder = $leftborder;
            break;
        case 'preg_leaf_option':
            $text = qtype_preg_substr($data, 2, qtype_preg_strlen($data) - 3);
            $index = qtype_preg_strpos($text, '-');
            if ($index === false) {
                $result->posopt = $text;
            } else {
                $result->posopt = qtype_preg_substr($text, 0, $index);
                $result->negopt = qtype_preg_substr($text, $index + 1);
            }
            break;
        case 'preg_leaf_recursion':
            if ($data[2] == 'R') {
                $result->number = 0;
            } else {
                $result->number = qtype_preg_substr($data, 2, qtype_preg_strlen($data) - 3);
            }
            break;
        }
        $result->indfirst = $this->yychar;
        $text = $this->yytext();
        $result->indlast = $this->yychar + $this->yylength() - 1;
        return $result;
    }

    protected function form_res($type, $value) {
        $result = new stdClass();
        $result->type = $type;
        $result->value = $value;
        return $result;
    }

    protected function form_num_interval(&$cc, $startchar, $endchar) {
        if(qtype_preg_ord($startchar) < qtype_preg_ord($endchar)) {
            $char = qtype_preg_ord($startchar);
            while($char <= qtype_preg_ord($endchar)) {
                $cc->charset .= qtype_preg_chr($char);
                $char++;
            }
        } else {
            $cc->error = 1;
        }
    }
    protected function push_opt_lvl($subpattnum = -1) {
        if ($this->optcount > 0) {
            $this->optstack[$this->optcount] = clone $this->optstack[$this->optcount - 1];

            if ($subpattnum != -1) {
                $this->optstack[$this->optcount]->subpattnum = $subpattnum;
                $this->optstack[$this->optcount]->parennum = $this->optcount;
            }
            $this->optcount++;
        } /*else
            error will be found in parser, lexer does nothing for this error (close unopened bracket)*/
    }
    protected function pop_opt_lvl() {
        if ($this->optcount > 0) {
            $item = $this->optstack[$this->optcount - 1];
            $this->optcount--;
            // Is it a pair for (?|
            if ($item->parennum == $this->optcount) {
                // Are we out of a (?|...) block?
                if ($this->optstack[$this->optcount - 1]->subpattnum != -1) {
                    $this->lastsubpatt = $this->optstack[$this->optcount - 1]->subpattnum;    // Reset subpattern numeration.
                } else {
                    $this->lastsubpatt = $this->maxsubpatt;
                }
            }
        }
    }
    public function mod_top_opt($set, $unset) {
        for ($i = 0; $i < qtype_preg_strlen($set); $i++) {
            if (qtype_preg_strpos($unset, qtype_preg_substr($set, $i, 1))) {// Setting and unsetting modifier at the same time is error.
                $text = $this->yytext;
                $this->errors[] = new preg_lexem(preg_node_error::SUBTYPE_SET_UNSET_MODIFIER, $this->yychar - qtype_preg_strlen($text), $this->yychar - 1);
                return;
            }
        }
        // If error does not exist, set and unset local modifiers.
        for ($i = 0; $i < qtype_preg_strlen($set); $i++) {
            $tmp = qtype_preg_substr($set, $i, 1);
            $this->optstack[$this->optcount - 1]->$tmp = true;
        }
        for ($i = 0; $i < qtype_preg_strlen($unset); $i++) {
            $tmp = qtype_preg_substr($unset, $i, 1);
            $this->optstack[$this->optcount - 1]->$tmp = false;
        }
    }
%}
%eof{
        if (isset($this->cc) && is_object($this->cc)) {// End of the expression inside a character class.
            $this->errors[] = new preg_lexem (preg_node_error::SUBTYPE_UNCLOSED_CHARCLASS, $this->cc->indfirst, $this->yychar - 1);
            $this->cc = null;
        }
%eof}
%%

<YYINITIAL> \? {
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_finite_quant', null, null, 0, 1));
    return $res;
}
<YYINITIAL> \* {
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_infinite_quant', null, null, 0));
    return $res;
}
<YYINITIAL> \+ {
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_infinite_quant', null, null, 1));
    return $res;
}
<YYINITIAL> \?\? {
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_finite_quant', null, null, 0, 1, false));
    return $res;
}
<YYINITIAL> \*\? {
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_infinite_quant', null, null, 0, null, false));
    return $res;
}
<YYINITIAL> \+\? {
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_infinite_quant', null, null, 1, null, false));
    return $res;
}
<YYINITIAL> \{[0-9]+,[0-9]+\} {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_finite_quant', null, null, qtype_preg_substr($text, 1, qtype_preg_strpos($text, ',') - 1), qtype_preg_substr($text, qtype_preg_strpos($text, ',') + 1, qtype_preg_strlen($text) - 2 - qtype_preg_strpos($text, ','))));
    return $res;
}
<YYINITIAL> \{[0-9]+,\} {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_infinite_quant', null, null, qtype_preg_substr($text, 1, qtype_preg_strpos($text, ',') - 1)));
    return $res;
}
<YYINITIAL> \{,[0-9]+\} {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_finite_quant', null, null, 0, qtype_preg_substr($text, 2, qtype_preg_strlen($text) - 3)));
    return $res;
}
<YYINITIAL> \{[0-9]+\} {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_finite_quant', null, null, qtype_preg_substr($text, 1, qtype_preg_strpos($text, ',') - 1), qtype_preg_substr($text, 1, qtype_preg_strpos($text, ',') - 1)));
    return $res;
}
<YYINITIAL> \{[0-9]+,[0-9]+\}\? {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_finite_quant', null, null, qtype_preg_substr($text, 1, qtype_preg_strpos($text, ',') - 1), qtype_preg_substr($text, qtype_preg_strpos($text, ',') + 1, qtype_preg_strlen($text) - 2 - qtype_preg_strpos($text, ',')), false));
    return $res;
}
<YYINITIAL> \{[0-9]+,\}\? {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_infinite_quant', null, null, qtype_preg_substr($text, 1, qtype_preg_strpos($text, ',') - 1), null, false));
    return $res;
}
<YYINITIAL> \{,[0-9]+\}\? {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_finite_quant', null, null, 0, qtype_preg_substr($text, 2, qtype_preg_strlen($text) - 3), false));
    return $res;
}
<YYINITIAL> \{[0-9]+\}\? {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::QUANT, $this->form_node('preg_node_finite_quant', null, null, qtype_preg_substr($text, 1, qtype_preg_strpos($text, ',') - 1), qtype_preg_substr($text, 1, qtype_preg_strpos($text, ',') - 1), false));
    return $res;
}
<YYINITIAL> \[ {
    $this->cc = new preg_leaf_charset;
    $this->cc->negative = false;
    $this->cccharnumber = 0;
    $this->cc->indfirst = $this->yychar;
    $this->yybegin(self::CHARCLASS);
}
<YYINITIAL> \( {
    $this->push_opt_lvl();
    $this->lastsubpatt++;
    $this->maxsubpatt = max($this->maxsubpatt, $this->lastsubpatt);
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem_subpatt(preg_node_subpatt::SUBTYPE_SUBPATT, $this->yychar, $this->yychar, $this->lastsubpatt));
    return $res;
}
<YYINITIAL> \(\?\#\{\{\) {        // Beginning of a lexem.
    $this->push_opt_lvl();
    $this->lexemcount++;
    $res = $this->form_res(preg_parser_yyParser::OPENLEXEM, new preg_lexem_subpatt(preg_node_subpatt::SUBTYPE_SUBPATT, $this->yychar, $this->yychar + $this->yylength() - 1, -$this->lexemcount));
    return $res;
}
<YYINITIAL> \) {
    $this->pop_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::CLOSEBRACK, new preg_lexem(0, $this->yychar, $this->yychar));
    return $res;
}
<YYINITIAL> \(\?\#\}\}\) {        // Ending of a lexem.
    $this->pop_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::CLOSELEXEM, new preg_lexem(0, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?\#[^)]*\) {        // Comment.
    return $this->nextToken();
}
<YYINITIAL> \(\?> {
    $this->push_opt_lvl();
    $this->lastsubpatt++;
    $this->maxsubpatt = max($this->maxsubpatt, $this->lastsubpatt);
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem_subpatt(preg_node_subpatt::SUBTYPE_ONCEONLY, $this->yychar, $this->yychar + $this->yylength() - 1, $this->lastsubpatt));
    return $res;
}
<YYINITIAL> \(\?\<[a-zA-Z_0-9]+\> {    // Named subpattern (?<name>...).
    $this->push_opt_lvl();
    $str = qtype_preg_substr($this->yytext(), 3);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    if (!array_key_exists($str, $this->subpatternmap)) {    // This subpattern does not exists.
        $num = ++$this->lastsubpatt;
        $this->subpatternmap[$str] = $num;
    } else {                                                // Subpatterns with same names should have same numbers.
        $num = $this->subpatternmap[$str];
        // TODO check if we are inside a (?|...) group.
    }
    $this->maxsubpatt = max($this->maxsubpatt, $this->lastsubpatt);
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem_subpatt(preg_node_subpatt::SUBTYPE_SUBPATT, $this->yychar, $this->yychar + $this->yylength() - 1, $num));
    return $res;
}
<YYINITIAL> \(\?\'[a-zA-Z_0-9]+\' {    // Named subpattern (?'name'...).
    $this->push_opt_lvl();
    $str = qtype_preg_substr($this->yytext(), 3);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    if (!array_key_exists($str, $this->subpatternmap)) {    // This subpattern does not exists.
        $num = ++$this->lastsubpatt;
        $this->subpatternmap[$str] = $num;
    } else {                                                // Subpatterns with same names should have same numbers.
        $num = $this->subpatternmap[$str];
        // TODO check if we are inside a (?|...) group.
    }
    $this->maxsubpatt = max($this->maxsubpatt, $this->lastsubpatt);
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem_subpatt(preg_node_subpatt::SUBTYPE_SUBPATT, $this->yychar, $this->yychar + $this->yylength() - 1, $num));
    return $res;
}
<YYINITIAL> \(\?P\<[a-zA-Z_0-9]+\> {   // Named subpattern (?P<name>...).
    $this->push_opt_lvl();
    $str = qtype_preg_substr($this->yytext(), 4);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    if (!array_key_exists($str, $this->subpatternmap)) {    // This subpattern does not exists.
        $num = ++$this->lastsubpatt;
        $this->subpatternmap[$str] = $num;
    } else {                                                // Subpatterns with same names should have same numbers.
        $num = $this->subpatternmap[$str];
        // TODO check if we are inside a (?|...) group.
    }
    $this->maxsubpatt = max($this->maxsubpatt, $this->lastsubpatt);
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem_subpatt(preg_node_subpatt::SUBTYPE_SUBPATT, $this->yychar, $this->yychar + $this->yylength() - 1, $num));
    return $res;
}
<YYINITIAL> \(\?: {
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem('grouping', $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?\| {
    $this->push_opt_lvl($this->lastsubpatt);    // Save the top-level subpattern number.
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem('grouping', $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?\(\?= {
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::CONDSUBPATT, new preg_lexem(preg_node_cond_subpatt::SUBTYPE_PLA, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?\(\?! {
    $this->push_opt_lvl();
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::CONDSUBPATT, new preg_lexem(preg_node_cond_subpatt::SUBTYPE_NLA, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?\(\?<= {
    $this->push_opt_lvl();
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::CONDSUBPATT, new preg_lexem(preg_node_cond_subpatt::SUBTYPE_PLB, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?\(\?<! {
    $this->push_opt_lvl();
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::CONDSUBPATT, new preg_lexem(preg_node_cond_subpatt::SUBTYPE_NLB, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?= {
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem(preg_node_assert::SUBTYPE_PLA, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?! {
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem(preg_node_assert::SUBTYPE_NLA, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?<= {
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem(preg_node_assert::SUBTYPE_PLB, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?<! {
    $this->push_opt_lvl();
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem(preg_node_assert::SUBTYPE_NLB, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \. {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_meta', preg_leaf_meta::SUBTYPE_DOT));
    return $res;
}
<YYINITIAL> [^\[\]\\*+?{}()|.^$] {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, $this->yytext()));
    return $res;
}
<YYINITIAL> \| {
    // Reset subpattern numeration inside a (?|...) group.
    if ($this->optcount > 0 && $this->optstack[$this->optcount - 1]->subpattnum != -1) {
        $this->lastsubpatt = $this->optstack[$this->optcount - 1]->subpattnum;
    }
    $res = $this->form_res(preg_parser_yyParser::ALT, new preg_lexem(0, $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \\[\[\]?*+{}|().] {
    $text = $this->yytext();
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, qtype_preg_substr($text, 1, 1)));
    return $res;
}
<YYINITIAL> \\[1-9][0-9]?[0-9]? {
    $numstr = qtype_preg_substr($this->yytext(), 1);
    $numdec = intval($numstr, 10);
    if ($numdec < 10 || ($numdec <= $this->maxsubpatt && $numdec < 100)) {
        // Return a backreference.
        $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_backref', null, $numstr));
        $res->value->matcher =& $this->matcher;
    } else {
        // Return a character.
        $octal = '';
        $failed = false;
        for ($i = 0; !$failed && $i < qtype_preg_strlen($numstr); $i++) {
            $tmp = qtype_preg_substr($numstr, $i, 1);
            if (intval($tmp) < 8) {
                $octal = $octal . $tmp;
            } else {
                $failed = true;
            }
        }
        if (qtype_preg_strlen($octal) == 0) {    // If no octal digits found, it should be 0.
            $octal = '0';
            $tail = $numstr;
        } else {                      // Octal digits found.
            $tail = qtype_preg_substr($numstr, qtype_preg_strlen($octal));
        }
        // Return a single lexem if all digits are octal, an array of lexems otherwise.
        if (qtype_preg_strlen($tail) == 0) {
            $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, qtype_preg_chr(octdec($octal))));
        } else {
            $res = array();
            $res[] = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, qtype_preg_chr(octdec($octal))));
            for ($i = 0; $i < qtype_preg_strlen($tail); $i++) {
                $res[] = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, qtype_preg_substr($tail, $i, 1)));
            }
        }
    }
    return $res;
}
<YYINITIAL> \\g[0-9][0-9]? {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_backref', null, qtype_preg_substr($this->yytext(), 2)));
    $res->value->matcher =& $this->matcher;
    return $res;
}
<YYINITIAL> \\g\{-?[0-9][0-9]?\} {
    $str = qtype_preg_substr($this->yytext(), 3);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    $numdec = intval($str, 10);
    // Is it a relative backreference? Is so, convert it to an absolute one.
    if ($numdec < 0) {
        $numdec = $this->lastsubpatt + $numdec + 1;
    }
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_backref', null, $numdec));
    $res->value->matcher =& $this->matcher;
    return $res;
}
<YYINITIAL> \\g\{[a-zA-Z_0-9]+\} {    // Named backreference.
    $str = qtype_preg_substr($this->yytext(), 3);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_backref', null, $str));
    $res->value->matcher =& $this->matcher;
    return $res;
}
<YYINITIAL> \\k\{[a-zA-Z_0-9]+\} {    // Named backreference.
    $str = qtype_preg_substr($this->yytext(), 3);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_backref', null, $str));
    $res->value->matcher =& $this->matcher;
    return $res;
}
<YYINITIAL> \\k\'[a-zA-Z_0-9]+\' {    // Named backreference.
    $str = qtype_preg_substr($this->yytext(), 3);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_backref', null, $str));
    $res->value->matcher =& $this->matcher;
    return $res;
}
<YYINITIAL> \\k\<[a-zA-Z_0-9]+\> {    // Named backreference.
    $str = qtype_preg_substr($this->yytext(), 3);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_backref', null, $str));
    $res->value->matcher =& $this->matcher;
    return $res;
}
<YYINITIAL> \(\?P=[a-zA-Z_0-9]+\) {    // Named backreference.
    $str = qtype_preg_substr($this->yytext(), 4);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_backref', null, $str));
    $res->value->matcher =& $this->matcher;
    return $res;
}
<YYINITIAL> \\0[0-7]?[0-7]? {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, qtype_preg_chr(octdec(qtype_preg_substr($this->yytext(), 1)))));
    return $res;
}
<YYINITIAL> \\x[0-9a-fA-F]?[0-9a-fA-F]? {
    $code = 0;
    $str = $this->yytext();
    if (qtype_preg_strlen($str) > 1) {
        $code = hexdec(qtype_preg_substr($str, 1));
    }
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, qtype_preg_chr($code)));
    return $res;
}
<YYINITIAL> \\x\{[0-9a-fA-F]*\} {
    $str = qtype_preg_substr($this->yytext(), 3);
    $str = qtype_preg_substr($str, 0, qtype_preg_strlen($str) - 1);
    $code = 0;
    if (qtype_preg_strlen($str) > 1) {
        $code = hexdec($str);
    }
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, qtype_preg_chr($code)));
    return $res;
}
<YYINITIAL> \\\\ {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, '\\'));
    return $res;
}
<YYINITIAL> \\b {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_assert', preg_leaf_assert::SUBTYPE_WORDBREAK));
    return $res;
}
<YYINITIAL> \\B {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_assert', preg_leaf_assert::SUBTYPE_WORDBREAK));
    $res->value->negative = true;
    return $res;
}
<YYINITIAL> \\d {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, '0123456789'));
    return $res;
}
<YYINITIAL> \\D {
    $PARSLEAF = $this->form_node('preg_leaf_charset', null, '0123456789');
    $PARSLEAF->negative = true;
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $PARSLEAF);
    return $res;
}
<YYINITIAL> \\w {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_meta', preg_leaf_meta::SUBTYPE_WORD_CHAR));
    return $res;
}
<YYINITIAL> \\W {
    $PARSLEAF = $this->form_node('preg_leaf_meta', preg_leaf_meta::SUBTYPE_WORD_CHAR);
    $PARSLEAF->negative = true;
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $PARSLEAF);
    return $res;
}
<YYINITIAL> \\s {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, ' '));
    return $res;
}
<YYINITIAL> \\S {
    $PARSLEAF = $this->form_node('preg_leaf_charset', null, ' ');
    $PARSLEAF->negative = true;
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $PARSLEAF);
    return $res;
}
<YYINITIAL> \\t {
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->form_node('preg_leaf_charset', null, qtype_preg_chr(9)));
    return $res;
}
<YYINITIAL> "^" {
    $leaf = $this->form_node('preg_leaf_assert', preg_leaf_assert::SUBTYPE_CIRCUMFLEX);
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $leaf);
    return $res;
}
<YYINITIAL> "$" {
    $leaf = $this->form_node('preg_leaf_assert', preg_leaf_assert::SUBTYPE_DOLLAR);
    $res = $this->form_res(preg_parser_yyPARSER::PARSLEAF, $leaf);
    return $res;
}
<YYINITIAL> \(\?i\) {/*TODO: refactor this rule at adding support other modifier*/
    $text = $this->yytext();
    $this->mod_top_opt('i', '');
}
<YYINITIAL> \(\?-i\) {/*TODO: refactor this rule at adding support other modifier*/
    $text = $this->yytext();
    $this->mod_top_opt('', 'i');
}
<YYINITIAL> \(\?i: {/*TODO: refactor this rule at adding support other modifier*/
    $text = $this->yytext();
    $this->push_opt_lvl();
    $this->mod_top_opt('i', '');
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem('grouping', $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?-i: {/*TODO: refactor this rule at adding support other modifier*/
    $text = $this->yytext();
    $this->push_opt_lvl();
    $this->mod_top_opt('', '-i');
    $res = $this->form_res(preg_parser_yyParser::OPENBRACK, new preg_lexem('grouping', $this->yychar, $this->yychar + $this->yylength() - 1));
    return $res;
}
<YYINITIAL> \(\?(R|[0-9]+)\) {
    $text = $this->yytext();
    $leaf = $this->form_node('preg_leaf_recursion', null, $text);
    $res = $this->form_res(preg_parser_yyPARSER::PARSLEAF, $leaf);
    return $res;
}
<YYINITIAL> \\[^0-9a-zA-Z] {
    $text = $this->yytext();
    $leaf = $this->form_node('preg_leaf_charset', null, qtype_preg_substr($text, 1, 1));
    $res = $this->form_res(preg_parser_yyPARSER::PARSLEAF, $leaf);
    return $res;
}
<CHARCLASS> \\\\ {
    $this->cc->charset .= '\\';
    $this->cccharnumber++;
}
<CHARCLASS> \\\[ {
    $this->cc->charset .= '[';
    $this->cccharnumber++;
}
<CHARCLASS> \\\] {
    $this->cc->charset .= ']';
    $this->cccharnumber++;
}
<CHARCLASS> \\0[0-9][0-9]|[0-9][0-9][0-9] {
    $this->cc->charset .= qtype_preg_chr(octdec(qtype_preg_substr($this->yytext(), 1)));
    $this->cccharnumber++;
}
<CHARCLASS> \\x[0-9][0-9] {
    $this->cccharnumber++;
    $this->cc->charset .= qtype_preg_chr(hexdec(qtype_preg_substr($this->yytext(), 1)));
}
<CHARCLASS> \\d {
    $this->cccharnumber++;
    $this->cc->charset .= '0123456789';
}
<CHARCLASS> \\w {
    $this->cc->w = true;
}
<CHARCLASS> \\W {
    $this->cc->W = true;
}
<CHARCLASS> \\s {
    $this->cccharnumber++;
    $this->cc->charset .= ' ';
}
<CHARCLASS> \\t {
    $this->cccharnumber++;
    $this->cc->charset .= qtype_preg_chr(9);
}
<CHARCLASS> "^" {
    if ($this->cccharnumber) {
        $this->cc->charset .= '^';
    } else {
        $this->cc->negative = true;
    }
    $this->cccharnumber++;
}
<CHARCLASS> "^-" {
    if (!$this->cccharnumber) {
        $this->cc->charset .= '-';
        $this->cc->negative = true;
        $this->cccharnumber++;
    }
}
<CHARCLASS> - {
    if (!$this->cccharnumber) {
        $this->cc->charset .= '-';
    }
    $this->cccharnumber++;
}
<CHARCLASS> [0-9]-[0-9]|[a-z]-[a-z]|[A-Z]-[A-Z] {
    $text = $this->yytext();
    $this->form_num_interval($this->cc, qtype_preg_substr($text, 0, 1), qtype_preg_substr($text, 2, 1));
}
<CHARCLASS> \\- {
    $this->cc->charset .= '-';
    $this->cccharnumber++;
}
<CHARCLASS> [^-\[\]\\^] {
    $this->cc->charset .= $this->yytext();
    $this->cccharnumber++;
}
<CHARCLASS> \] {
    $this->cc->indlast = $this->yychar;
    $res = $this->form_res(preg_parser_yyParser::PARSLEAF, $this->cc);
    $this->yybegin(self::YYINITIAL);
    $this->cc = null;
    return $res;
}