<?php

$string['addmoreanswerblanks'] = 'Adding a regular expression options';
$string['answersinstruct'] = '<p>Enter (at least one) regular expressions in the choosen notation as answers. If a correct answer is given, it should match at least one regular expression with 100% grade.</p><p>You can use placeholders like {$0} in the feedback to insert captured parts of a student\'s response. {$0} will be replaced by the whole match, {$1} with the first subpattern match etc. If the choosen engine doesn\'t support subpatterns capturing you should use only {$0}.</p>';
$string['answerno'] = 'Answer {$a}';
$string['charhintpenalty'] = 'Penalty for the next character hint';
$string['charhintpenalty_help'] = 'Penalty for getting the one-character hint. Typically will be greater than usual Moodle question penalty (which applies to any new attempt to answer question without hints). These penalties are mutually exclusive.';
$string['lexemhintpenalty'] = 'Penalty for the next lexem hint';
$string['lexemhintpenalty_help'] = 'Penalty for getting the next lexem hint. Typically will be greater than usual Moodle question penalty (which applies to any new attempt to answer question without hints) and next character one. These penalties are mutually exclusive.';
$string['correctanswer'] = 'Correct answer';
$string['correctanswer_help'] = 'Enter a correct answer (not a regular expression) to be shown to students. If you leave it empty the matching engine will try to generate a correct answer itself, taking heed to get the closest one to the student\'s response. For now only NFA engine can generate correct answers.';
$string['debugheading'] = 'Debug settings';
$string['defaultenginedescription'] = 'Matching engine selected by default when creating a new question';
$string['defaultenginelabel'] = 'Default matching engine';
$string['defaultlangdescription'] = 'Language selected by default when creating a new question';
$string['defaultlanglabel'] = 'Default language';
$string['defaultnotationdescription'] = 'Notation selected by default when creating a new question';
$string['defaultnotationlabel'] = 'Default notation';
$string['dfa_matcher'] = 'Deterministic finite state automata';
$string['engine'] = 'Matching engine';
$string['engine_help'] = '<p>There is no \'best\' matching enginge, so you can choose the engine that fits the particular question best.</p><p>Native <b>PHP preg matching engine</b> works using preg_match() function from PHP langugage. It\'s almost 100% bug-free and able to work with full PCRE syntax, but can\'t support advanced features (showing partial matches and hinting).</p><p>The <b>NFA matching engine</b> and the <b>DFA matching engine</b> are engines that use custom matching code. They support partial matching and hinting, but don\'t support lookaround assertions (you\'ll be notified when trying to save a question with unsupported expressions) and potentially can contain bugs (different for each engine: regular expression matching is still a very complex thing).</p><p>If the difference between engines is too hard to you, just try them all to see how their capabilities suit your needs. If one engine fails in a question then try another engines to see if they can handle it better.</p><p>The NFA engine is probably the best choise if you don\'t use lookaround assertions.</p><p>Avoid using the DFA engine for the Moodle shortanswer notation.</p>';
$string['exactmatch'] = 'Exact matching';
$string['exactmatch_help'] = '<p>By default regular expression matching returns true if there is at least one match in the given string (answer). Exact matching means that the match must be the entire string.</p><p>Set this to Yes, if you write regular expressions for full student\'s answers. Setting this to No gives you additional flexibility: you can specify an answer with low (or zero) grade to catch common errors and give comments on them. You still can specify exact matches for some of your regular expressions if you start them with ^ and end with $.</p>';
$string['hintgradeborder'] = 'Hint grade border';
$string['hintgradeborder_help'] = 'Answers with the grade less than the hint grade border won\'t be used in hinting.';
$string['hintnextchar'] = 'next correct character';
$string['langselect'] = 'Language';
$string['langselect_help'] = 'For next lexem hint you should choose a language, which is used to break answers down to lexems. Each language has it own rules for lexems. Languages are defined using \'Formal languages block\'';
$string['largefa'] = 'Too large finite automaton';
$string['lexemusername'] = 'Student-visible name for lexem';
$string['lexemusername_help'] = 'Your students probably won\'t know that an atomic part of the language they learn is called <b>lexem</b>. They may prefer to call it "word" or "number" or something. You may define a name for lexem that would be shown on the "Hint next lexem" button there.';
$string['maxerrorsshowndescription'] = 'Maximum number of errors shown for each regular expression in the question editing form';
$string['maxerrorsshownlabel'] = 'Maximum number of errors shown';
$string['nfa_matcher'] = 'Nondeterministic finite state automata';
$string['noabstractaccept'] = 'Matching by abstract matcher';
$string['nocorrectanswermatch'] = 'No maximum grade regular expression matches the correct answer';
$string['nohintgradeborderpass'] = 'No answer has a grade greater or equal the hint grade border. This disables hinting.';
$string['nohintsupport'] = '{$a} engine doesn\'t support hinting';
$string['notation'] = 'Regular expression notation';
$string['notation_help'] = '<p>You can choose the notation to enter regular expressions. If you just want to write a regular expression, please use the default, <b>Regular expression</b> notation which is very close to PCRE, but has additional error-proof capabilities.</p><p><b>Moodle shortanswer</b> notation allows you to use preg as a usual Moodle shortanswer question with the hinting capability - with no need to understand regular expressions. Just copy you answers from shortanswer question. The \'*\' wildcard is supported.</p>';
$string['notation_native'] = 'Regular expression';
$string['notation_mdlshortanswer'] = 'Moodle shortanswer';
$string['noregex'] = 'No regex supplied for matching';
$string['nosubpatterncapturing'] = '{$a} engine doesn\'t support subpattern capturing, please remove placeholders (except {$0}) from the feedback or choose another engine';
$string['pluginname'] = 'Regular expression';
$string['pluginname_help'] = '<p>Regular expressions are a form of writing patterns to match different strings. You can use it to verify answers in two ways: an expression to match with full (usually correct) answer, or an expression to match a part of the answer (which can be used, for example, to catch common errors and give appropriate comments).</p><p>This question uses the PHP perl-compatible regular expression syntax as the default notation. There are many tutorials about creating and using regular expression, here is one <a href="http://www.phpfreaks.com/content/print/126">example</a>. You can find detailed syntax of expression here: <a href="http://www.nusphere.com/kb/phpmanual/reference.pcre.pattern.syntax.htm">php manual</a>. Note that you should neither enclose regular expression in delimiters nor specify any modifiers - Moodle will do it for you.</p><p>You can also use this question as the advanced form of shortanswer with hinting, even if you don\'t know a bit about regular expressions! Just select <b>Moodle shortanswer</b> as notation for your questions.</p>';
$string['php_preg_matcher'] = 'PHP preg extension';
$string['pluginname_link'] = 'question/type/preg';
$string['pluginnameadding'] = 'Adding a regular expression question';
$string['pluginnameediting'] = 'Editing a regular expression question';
$string['pluginnamesummary'] = 'Enter a string response from student that can be matched against several regular expressions. Shows to the student the correct part of his response. Using behaviours with multiple tries can give a hint by telling a next correct character.<br/>You can use it without knowing regular expression to get hinting by using the \'Moodle shortanswer\' notation.';
$string['preg_regex_handler'] = 'Regex handler';
$string['questioneditingheading'] = 'Question editing settings';
$string['subpattern'] = 'Subpattern';
$string['tobecontinued'] = '...';
$string['toomanyerrors'] = '.......{$a} more errors';
$string['ungreedyquant'] = 'ungreedy quantifiers';
$string['unsupported'] = '{$a->nodename} in position from  {$a->indfirst} to {$a->indlast} is unsupported by the {$a->engine}';
$string['unsupportedmodifier'] = 'Error: modifier {$a->modifier} isn\'t supported by the {$a->classname}.';
$string['usecharhint'] = 'Allow next character hinting';
$string['usehint_help'] = 'In behaviours which allow multiple tries (e.g. adaptive or interactive) show students the \'Hint next character\' button that allows to get a one-character hint with applying the \'Hint next character penalty\'. Not all matching engines support hinting.';
$string['usecharhint_help'] = 'In behaviours which allow multiple tries (e.g. adaptive or interactive) show students the \'Hint next character\' button that allows to get a one-character hint with applying the \'Hint next character penalty\'. Not all matching engines support hinting.';
$string['uselexemhint'] = 'Allow next lexem (word, number, punctuation mark) hinting';
$string['uselexemhint_help'] = '<p>In behaviours which allow multiple tries (e.g. adaptive or interactive) show students the \'Hint next word\' button that allows to get a hint either completing current lexem or showing next one if lexem is complete with applying the \'Hint next lexem penalty\'. Not all matching engines support hinting.</p><p><b>Lexem</b> is an atomic part of the language: a word, number, punctuation mark, operator etc.</p>';

/******* Abstract syntax tree nodes descriptions *******/
// Types.
$string['node_abstract']               = 'abstract node';
$string['leaf_charset']                = 'character set';
$string['leaf_meta']                   = 'meta-character or escape-sequence';
$string['leaf_assert']                 = 'simple assertion';
$string['leaf_backref']                = 'backreference';
$string['leaf_recursion']              = 'recursion';
$string['leaf_control']                = 'control sequence';
$string['leaf_options']                = 'modifier';   // TODO: remove?
$string['node_finite_quant']           = 'finite quantifier';
$string['node_infinite_quant']         = 'infinite quantifier';
$string['node_concat']                 = 'concatenation';
$string['node_alt']                    = 'alternative';
$string['node_assert']                 = 'lookaround assertion';
$string['node_subpatt']                = 'subpattern';
$string['node_cond_subpatt']           = 'conditional subpattern';
$string['node_error']                  = 'syntax error';
// Subtypes.
$string['empty_leaf_meta']             = 'emptiness';
$string['circumflex_leaf_assert']      = 'start of the subject assertion';
$string['dollar_leaf_assert']          = 'end of the subject assertion';
$string['esc_b_leaf_assert']           = 'word boundary assertion';
$string['esc_a_leaf_assert']           = 'start of the subject assertion';
$string['esc_z_leaf_assert']           = 'end of the subject assertion';
$string['esc_g_leaf_assert']           = 'first matching position in the subject assertion';
$string['accept_leaf_control']         = '';   // TODO
$string['fail_leaf_control']           = '';
$string['mark_name_leaf_control']      = '';
$string['commit_leaf_control']         = '';
$string['prune_leaf_control']          = '';
$string['skip_leaf_control']           = '';
$string['skip_name_leaf_control']      = '';
$string['then_leaf_control']           = '';
$string['cr_leaf_control']             = '';
$string['lf_leaf_control']             = '';
$string['crlf_leaf_control']           = '';
$string['anycrlf_leaf_control']        = '';
$string['any_leaf_control']            = '';
$string['bsr_anycrlf_leaf_control']    = '';
$string['bsr_unicode_leaf_control']    = '';
$string['no_start_opt_leaf_control']   = '';
$string['utf8_leaf_control']           = '';
$string['utf16_leaf_control']          = '';
$string['ucp_leaf_control']            = '';
$string['pla_node_assert']             = 'positive lookahead assert';
$string['nla_node_assert']             = 'negative lookahead assert';
$string['plb_node_assert']             = 'positive lookbehind assert';
$string['nlb_node_assert']             = 'negative lookbehind assert';
$string['subpatt_node_subpatt']        = 'subpattern';
$string['onceonly_node_subpatt']       = 'once-only subpattern';
$string['subpatt_node_cond_subpatt']   = '"subpattern"-conditional subpattern';
$string['recursion_node_cond_subpatt'] = 'recursive conditional subpattern';
$string['define_node_cond_subpatt']    = '"define"-conditional subpattern';
$string['pla_node_cond_subpatt']       = 'positive lookahead conditional subpattern';
$string['nla_node_cond_subpatt']       = 'negative lookahead conditional subpattern';
$string['plb_node_cond_subpatt']       = 'positive lookbehind conditional subpattern';
$string['nlb_node_cond_subpatt']       = 'negative lookbehind conditional subpattern';
$string['unknown_error_node_error']                                = 'unknown error';
$string['consubpatt_too_much_alter_node_error']                    = 'too much top-level alternatives in a conditional subpattern';
$string['wrong_close_paren_node_error']                            = 'closing paren without opening';
$string['wrong_open_paren_node_error']                             = 'opening paren without closing';
$string['empty_parens_node_error']                                 = 'empty parens';
$string['quantifier_without_parameter_node_error']                 = 'quantifier at the start of the expression';
$string['unclosed_charset_node_error']                             = 'unclosed brackets in a character set';
$string['set_and_unset_same_modifier_at_the_same_time_node_error'] = 'set and unset same modifier at ther same time';
$string['unknown_unicode_property_node_error']                     = 'unknown unicode property';
$string['unknown_posix_class_node_error']                          = 'unknown posix class';
$string['unknown_control_sequence_node_error']                     = 'unknown control sequence (*...)';
$string['incorrect_range_node_error']                              = 'incorrect ranges in a quantifier or a character set: {5,3} or [z-a]';

/******* Error messages *******/
$string['error_PCREincorrectregex']       = 'Incorrect regular expression - syntax error! Consult <a href="http://pcre.org/pcre.txt">PCRE documentation</a> for more information.';
$string['error_threealtincondsubpatt']    = 'Regex syntax error: three or more top-level alternatives in the conditional subpattern in position from {$a->indfirst} to {$a->indlast}. Use parentheses if you want to include alternatives in yes-expr on no-expr.';
$string['error_unopenedparen']            = 'Regex syntax error: missing opening parenthesis \'(\' for the closing parenthesis in position {$a->indfirst}.';
$string['error_unclosedparen']            = 'Regex syntax error: missing a closing parenthesis \')\' for the opening parenthesis in position {$a->indfirst}.';
$string['error_emptyparens']              = 'Regex syntax error: empty parentheses in position from {$a->indfirst} to {$a->indlast}.';
$string['error_quantifieratstart']        = 'Regex syntax error: quantifier in position from {$a->indfirst} to {$a->indlast} doesn\'t have an operand - nothing to repeat.';
$string['error_unclosedsqbrackets']       = 'Regex syntax error: missing a closing bracket \']\' for the character set starting in position {$a->indfirst}.';
$string['error_setunsetmod']              = 'Setting and unsetting the {$a->addinfo} modifier at the same time in position from {$a->indfirst} to {$a->indlast}.';
$string['error_unknownunicodeproperty']   = 'Unknown unicode property: {$a->addinfo}.';
$string['error_unknownposixclass']        = 'Unknown posix class: {$a->addinfo}.';
$string['error_unknowncontrolsequence']   = 'Unknown control sequence: {$a->addinfo}.';
$string['error_incorrectcharsetrange']    = 'Incorrect character range in position from  {$a->indfirst} to {$a->indlast}: the left character is "greater" than the right one.';
$string['error_incorrectquantrange']      = 'Incorrect quantifier range in position from  {$a->indfirst} to {$a->indlast}: the left border is greater than the right one.';
$string['error_slashatendofpattern']      = 'Regex syntax error: \\ at end of pattern.';
$string['error_catendofpattern']          = 'Regex syntax error: \c at end of pattern.';
$string['error_posixclassoutsidecharset'] = 'POSIX classes are not allowed outside character sets.';
$string['error_unexistingsubpatt']        = 'The subpattern "{$a->addinfo}" does not exist.';

$string['error_charcodetoobig']           = 'The character code {$a->addinfo} is too big.';

$string['error_missingcommentending']     = 'Missing closing parenthesis for the comment in position from {$a->indfirst} to {$a->indlast}.';


$string['error_calloutbignumber']         = 'The number {$a->addinfo} in the callout is too big, should not be greater than 255.';



/******* DFA and NFA limitations *******/
$string['engine_heading_descriptions'] = 'Matching regular expressions can be time and memory consuming. These settings allow you to control limits of time and memory usage by the matching engines. Increase them when you get messages that the regular expression is too complex, but do mind your server\'s performance (you may also want to increase PHP time and memory limits). Decrease them if you get blank page when saving or running a preg question.';
$string['too_large_fa'] = 'Regular expression is too complex to be matched by {$a->engine} due to the time and/or memory limits. Please try another matching engine, ask your administrator to <a href="'.$CFG->wwwroot.'/admin/settings.php?section=qtypesettingpreg"> increase time and memory limits</a> or simplify you regular expression.';
$string['fa_state_limit'] = 'Automata size limit: states';
$string['fa_transition_limit'] = 'Automata size limit: transitions';
$string['dfa_settings_heading'] = 'Deterministic finite state automata engine settings';
$string['nfa_settings_heading'] = 'Nondeterministic finite state automata engine settings';
$string['dfa_state_limit_description'] = 'Allows you to tune time and memory limits for the DFA engine when matching complex regexes';
$string['nfa_state_limit_description'] = 'Allows you to tune time and memory limits for the NFA engine when matching complex regexes';
$string['dfa_transition_limit_description'] = 'Maximum number of transitions in DFA';
$string['nfa_transition_limit_description'] = 'Maximum number of transitions in NFA';
