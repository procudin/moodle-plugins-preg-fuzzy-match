<?php

$string['addmoreanswerblanks'] = 'Adding a regular expression options';
$string['answersinstruct'] = '<p>Enter (at least one) regular expressions in the choosen notation as answers. If a correct answer is given, it should match at least one regular expression with 100% grade.</p><p>You can use placeholders like {$0} in the feedback to insert captured parts of a student\'s response. {$0} will be replaced by the whole match, {$1} with the first subpattern match etc. If the choosen engine doesn\'t support subpatterns capturing you should use only {$0}.</p>';
$string['answerno'] = 'Answer {$a}';
$string['asserttf'] = 'Positive lookahead assertion';
$string['assertff'] = 'Negative lookahead assertion';
$string['asserttb'] = 'Positive lookbehind assertion';
$string['assertfb'] = 'Negative lookbehind assertion';
$string['escg'] = 'Escape G';
$string['correctanswer'] = 'Correct answer';
$string['correctanswer_help'] = 'Enter a correct answer (not a regular expression) to be shown to students. If you leave it empty the matching engine will try to generate a correct answer itself, taking heed to get the closest one to the student\'s response. For now only NFA engine can generate correct answers.';
$string['debugheading'] = 'Debug settings';
$string['defaultenginedescription'] = 'Matching engine selected by default when creating a new question';
$string['defaultenginelabel'] = 'Default matching engine';
$string['defaultnotationdescription'] = 'Notation selected by default when creating a new question';
$string['defaultnotationlabel'] = 'Default notation';
$string['dfa_matcher'] = 'Deterministic finite state automata';
$string['dfaheading'] = 'Deterministic finite state automata engine settings';
$string['dfalimitsdescription'] = 'Allows you to tune time and memory limits for the DFA engine when matching complex regexes.';
$string['emptyparens'] = 'Regex syntax error: empty parentheses in position from {$a->indfirst} to {$a->indlast}';
$string['engine'] = 'Matching engine';
$string['engine_help'] = '<p>There is no \'best\' matching enginge, so you can choose the engine that fits the particular question best.</p><p>Native <b>PHP preg matching engine</b> works using preg_match() function from PHP langugage. It\'s almost 100% bug-free and able to work with full PCRE syntax, but can\'t support advanced features (showing partial matches and hinting).</p><p>The <b>NFA matching engine</b> and the <b>DFA matching engine</b> are engines that use custom matching code. They support partial matching and hinting, but don\'t support lookaround assertions (you\'ll be notified when trying to save a question with unsupported expressions) and potentially can contain bugs (different for each engine: regular expression matching is still a very complex thing).</p><p>If the difference between engines is too hard to you, just try them all to see how their capabilities suit your needs. If one engine fails in a question then try another engines to see if they can handle it better.</p><p>The NFA engine is probably the best choise if you don\'t use lookaround assertions.</p><p>Avoid using the DFA engine for the Moodle shortanswer notation.</p>';
$string['engineheadingdescriptions'] = 'Matching regular expressions can be time and memory consuming. These settings allow you to control limits of time and memory usage by the matching engines. Increase them when you get messages that the regular expression is too complex, but do mind your server\'s performance (you may also want to increase PHP time and memory limits). Decrease them if you get blank page when saving or running a preg question.';
$string['exactmatch'] = 'Exact matching';
$string['exactmatch_help'] = '<p>By default regular expression matching returns true if there is at least one match in the given string (answer). Exact matching means that the match must be the entire string.</p><p>Set this to Yes, if you write regular expressions for full student\'s answers. Setting this to No gives you additional flexibility: you can specify an answer with low (or zero) grade to catch common errors and give comments on them. You still can specify exact matches for some of your regular expressions if you start them with ^ and end with $.</p>';
$string['gvpath'] = 'Path to the bin folder of GraphViz installation';
$string['gvdescription'] = 'Graphviz is used for debug output of finite automata and syntax trees in human readable form';
$string['hintgradeborder'] = 'Hint grade border';
$string['hintgradeborder_help'] = 'Answers with the grade less then the hint grade border won\'t be used in hinting.';
$string['hintnextchar'] = 'next correct character';
$string['hintpenalty'] = 'Penalty for the next character hint';
$string['hintpenalty_help'] = 'Penalty for getting the one-character hint. Typically will be greater than usual Moodle question penalty (which applies to any new attempt to answer question without hints). These penalties are mutually exclusive.';
$string['PCREincorrectregex'] = 'Incorrect regular expression - syntax error! Consult <a href="http://pcre.org/pcre.txt">PCRE documentation</a> for more information.';
$string['largefa'] = 'Too large finite automaton';
$string['leaf_assert'] = 'simple assertion';
$string['leaf_backref'] = 'backreference';
$string['leaf_charset'] = 'character set';
$string['leaf_meta'] = 'meta-character or escape-sequence';
$string['maxfasizestates'] = 'Automata size limit: states';
$string['maxfasizetransitions'] = 'Automata size limit: transitions';
$string['maxerrorsshowndescription'] = 'Maximum number of errors shown for each regular expression in the question editing form';
$string['maxerrorsshownlabel'] = 'Maximum number of errors shown';
$string['nfa_matcher'] = 'Nondeterministic finite state automata';
$string['nfaheading'] = 'Nondeterministic finite state automata engine settings';
$string['nfalimitsdescription'] = 'Allows you to tune time and memory limits for the NFA engine to use when matching complex regexes';
$string['nfasizelimit'] = 'Maximum size of an NFA';
$string['nfastatelimitdescription'] = 'Maximum count of states in an NFA';
$string['nfatransitionlimitdescription'] = 'Maximum count of transitions in an NFA';
$string['noabstractaccept'] = 'Matching by abstract matcher';
$string['nocorrectanswermatch'] = 'No maximum grade regular expression matches the correct answer';
$string['node_alt'] = 'alternative';
$string['node_assert'] = 'assertion';
$string['node_concat'] = 'concatenation';
$string['node_cond_subpatt'] = 'conditional subpattern';
$string['node_finite_quant'] = 'finite quantifier';
$string['node_infinite_quant'] = 'infinite quantifier';
$string['node_subpatt'] = 'subpattern';
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
$string['quantifieratstart'] = 'Regex syntax error: quantifier in position from {$a->indfirst} to {$a->indlast} doesn\'t have an operand - nothing to repeat';
$string['questioneditingheading'] = 'Question editing settings';
$string['subpattern'] = 'Subpattern';
$string['threealtincondsubpatt'] = 'Regex syntax error: three or more top-level alternatives in the conditional subpattern in position from {$a->indfirst} to {$a->indlast}. Use parentheses if you want to include alternatives in yes-expr on no-expr';
$string['tobecontinued'] = '...';
$string['toolargefa'] = 'Regular expression is too complex to be matched by {$a->engine} due to the time and/or memory limits. Please try another matching engine, ask your administrator to <a href="'.$CFG->wwwroot.'/admin/settings.php?section=qtypesettingpreg"> increase time and memory limits</a> or simplify you regular expression.';
$string['toomanyerrors'] = '.......{$a} more errors';
$string['unclosedparen'] = 'Regex syntax error: missing a closing parenthesis \')\' for the opening parenthesis in position {$a->indfirst}';
$string['unclosedsqbrackets'] = 'Regex syntax error: missing a closing bracket \']\' for the character set starting in position {$a->indfirst}';
$string['ungreedyquant'] = 'ungreedy quantifiers';
$string['unopenedparen'] = 'Regex syntax error: missing opening parenthesis \'(\' for the closing parenthesis in position {$a->indfirst}';
$string['setunsetmod'] = 'Regex syntax error: set and unset same modifier between {$a->indfirst} {$a->indlast}';
$string['unknownunicodeproperty'] = 'Unknown unicode property: {$a->addinfo}';
$string['unknownposixclass'] = 'Unknown posix class: {$a->addinfo}';
$string['unsupported'] = '{$a->nodename} in position from  {$a->indfirst} to {$a->indlast} is unsupported by the {$a->engine}';
$string['unsupportedmodifier'] = 'Error: modifier {$a->modifier} isn\'t supported by the {$a->classname}.';
$string['usehint'] = 'Allow next characters hinting';
$string['usehint_help'] = 'In behaviours which allow multiple tries (e.g. adaptive or interactive) show students the \'Hint next character\' button that allows to get a one-character hint with applying the \'Hint next character penalty\'. Not all matching engines support hinting.';
