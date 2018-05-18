<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/preg/tests/cross_tester.php');

$CFG->qtype_preg_fa_transition_limit = 10000;
$CFG->qtype_preg_fa_state_limit = 10000;

class qtype_preg_fuzzy_fa_cross_tester extends qtype_preg_cross_tester {

    public function engine_name() {
        return 'fa_matcher';
    }
    protected $passednormaltests = [];

    protected $logfilename = 'C:/Users/Admin/YandexDisk/Diplom/moodle/server/moodle/question/type/preg/tests/errorslog.txt';
    protected $logcontent = '';

    public function log($message) {
        $this->logcontent .= $message;
        file_put_contents($this->logfilename, $this->logcontent);
    }

    public function accept_regex($regex) {
        return !preg_match('/\\\\\d+|\*\?|\+\?|\?\?|\}\?|\^|\$|\\\\b|\\\\B|\\\\A|\\\\z|\\\\Z|\\\\g|\(\?\=|\(\?\!|\(\?\<\=|\(\?\<\!|\(\?/', $regex);
    }

    protected function serialize_test_data($filename = 'C:/Users/Admin/YandexDisk/Diplom/moodle/server/moodle/question/type/preg/tests/fuzzytests.txt') {
        $serialized = serialize($this->passednormaltests);
        echo $serialized;
        $content = file_get_contents($filename);
        mb_internal_encoding('UTF-8');
        file_put_contents($filename, /*$content .*/ $serialized);
        $result = 'Data Added ok!!';
        return $result;
    }
    protected function unserialize_test_data($filename = 'C:/Users/Admin/YandexDisk/Diplom/moodle/server/moodle/question/type/preg/tests/fuzzytests.txt') {
        $content = file_get_contents($filename);
        $this->passednormaltests = unserialize($content);
    }

    protected function run_fuzzy_tests() {
        $passcount = 0;
        $failcount = 0;
        $skipcount = 0;

        $options = new qtype_preg_matching_options();  // Forced subexpression catupring.
        $blacklist = array_merge($this->blacklist_tags(), $this->blacklist);

        echo "Test fuzzy matching:\n";

        foreach ($this->passednormaltests as $data) {
            // Get current test data.
            $regex = $data['regex'];
            $modifiersstr = '';
            $regextags = array();
            $notation = self::NOTATION_NATIVE;
            if (array_key_exists('modifiers', $data)) {
                $modifiersstr = $data['modifiers'];
            }
            if (array_key_exists('tags', $data)) {
                $regextags = $data['tags'];
            }
            if (array_key_exists('notation', $data)) {
                $notation = $data['notation'];
            }

            $regextags [] = self::TAG_DONT_CHECK_PARTIAL;
            $regextags [] = self::TAG_FAIL_MODE_MERGE;
            $regextags [] = self::TAG_ALLOW_FUZZY;

            if ($regex !== 'XXXXXX' /*&& ($str == 'ab'*/ /*&& $expectederrorslimit === 0*/) {
                //continue;
            }

            $matcher_merged = null;
            $matcher_unmerged = null;

            foreach ($data['tests'] as $expected) {
                // Generate tests for fuzzy matching
                $fuzzytests = $this->make_errors($expected);

                foreach ($fuzzytests as $fuzzyexpected) {
                    $str = $fuzzyexpected['str'];
                    $strtags = array();
                    if (array_key_exists('tags', $fuzzyexpected)) {
                        $strtags = $fuzzyexpected['tags'];
                    }

                    $tags = array_merge($regextags, $strtags);

                    // Create matcher
                    $timestart = round(microtime(true) * 1000);
                    $options->mode = in_array(self::TAG_MODE_POSIX, $regextags) ? qtype_preg_handling_options::MODE_POSIX : qtype_preg_handling_options::MODE_PCRE;
                    $options->modifiers = qtype_preg_handling_options::string_to_modifiers($modifiersstr);
                    $options->debugmode = in_array(self::TAG_DEBUG_MODE, $regextags);
                    $options->fuzzymatch = true;
                    $options->mergeassertions = true;
                    $options->extensionneeded = !in_array(self::TAG_DONT_CHECK_PARTIAL, $regextags);
                    $matcher = $this->get_matcher($this->engine_name(), $regex, $options);
                    $timeend = round(microtime(true) * 1000);
                    if ($timeend - $timestart > self::MAX_BUILDING_TIME) {
                        //$slowbuildtests[] = $classname . ' : ' . $methodname;
                    }

                    $matcher->maxerrors = !isset($fuzzyexpected['errorslimit'])? 0 : $fuzzyexpected['errorslimit'];

                    //if ($regex =='a\Sb' && ($str == 'ab') /*&& $expectederrorslimit === 1*/) {
                    //    $qweqwe = 0;
                    //}

                    $timestart = round(microtime(true) * 1000);
                    try {
                        $matcher->match($str);
                        $obtained = $matcher->get_match_results();
                    } catch (Exception $e) {
                        continue;
                    }
                    $timeend = round(microtime(true) * 1000);
                    if ($timeend - $timestart > self::MAX_BUILDING_TIME) {
                        //$slowmatchtests[] = $classname . ' : ' . $methodname;
                    }

                    // Results obtained, check them.
                    try {
                        if ($this->compare_better_or_equal($regex,$str,$modifiersstr,$tags,$matcher,$fuzzyexpected,$obtained,true)) {
                            $passcount++;
                        } else {
                            $failcount++;
                        }
                    } catch (Exception $e) {
                        $message = "\nFailed error applying on regex '$regex' and string '$str', applying errors: {$obtained->errors}";
                        $this->log($message);
                        $werew = 0;
                    }
                    //if ($this->compare_results($regex, $notation, $str, $modifiersstr, $tags, $matcher, $fuzzyexpected, $obtained, $classname, $methodname, $skippartialcheck, true)) {
                    //    $passcount++;
                    //} else {
                    //    $failcount++;
                    //}
                }
            }
        }
    }

    protected function compare_better_or_equal_by_errors($expected, $obtained, &$equalserrorscount = false , &$equalserrors = false, &$leftmostlongest = false, &$betterbypriorty = false) {
        $equalserrorscount = false;
        $equalserrors = false;
        $leftmostlongest = false;
        $betterbypriorty = false;

        $expectederrorscount = isset($expected['errorscount']) ? $expected['errorscount'] : 0;

        // Check by errors count.
        if ($expectederrorscount > $obtained->errors->count()) {
            return true;
        } else if ($expectederrorscount < $obtained->errors->count()) {
            return false;
        }

        // Check errors by equals.
        $equalserrors = true;
        $equalserrorscount = true;
        $expectederrors = isset($expected['errors']) ? $expected['errors'] : [];
        foreach ($expectederrors as $type => $errors) {
            if (!$equalserrors) {
                break;
            }
            foreach ($errors as $err) {
                $equalserrors = $equalserrors && $obtained->errors->contains($type, $err['pos'], $err['char']);
            }
        }
        if ($equalserrors) {
            return true;
        }

        // Check by leftmostlongest.
        $leftmost = //((!array_key_exists(0, $expected['index_first']) && $obtained->indexfirst[0] === qtype_preg_matching_results::NO_MATCH_FOUND) ||
                (array_key_exists(0, $expected['index_first']) && $expected['index_first'][0] > $obtained->indexfirst[0]);

        if ($leftmost) {
            return $leftmostlongest = true;
        }

        $equalindexfirst = (array_key_exists(0, $expected['index_first']) && $expected['index_first'][0] === $obtained->indexfirst[0]);
        $longestorequal = (array_key_exists(0, $expected['length']) && $expected['length'][0] <= $obtained->length[0]);

        $leftmostlongest = $equalindexfirst && $longestorequal;

        return $leftmostlongest;
    }

    protected function compare_better_or_equal($regex, $str, $modstr, $tags, $matcher, $expected, $obtained, $dumpfails) {
        // Do some initialization.
        $fullpassed = ($expected['full'] === $obtained->full);
        $indexfirstpassed = true;
        $lengthpassed = true;
        $equalserrorscount = true;
        $equalserrors = true;
        $leftmostlongest = true;

        $expectederrorscount = isset($expected['errorscount']) ? $expected['errorscount'] : 0;
        $expectederrorslimit = isset($expected['errorslimit']) ? $expected['errorslimit'] : 0;
        $expectederrors = isset($expected['errors']) ? $expected['errors'] : [];

        if ($regex =='>\s{2,3}?<' /*&& ($str == 'ab'*/ && $expectederrorslimit === 0) {
            $qweqwe = 0;
        }

        $lowererrcount = $obtained->errors->count() < $expectederrorscount;

        $isbetter = false;
        $isbetter = $isbetter || !$expected['full'] && $obtained->full;
        $isbetter = $isbetter || $lowererrcount;
        $isbetter = $isbetter || $this->compare_better_or_equal_by_errors($expected, $obtained , $equalserrorscount,$equalserrors,$leftmostlongest);

        $checkindexes = $isbetter && $expected['full'] && ($equalserrors) && !$lowererrcount;
        // Match existance, indexes and lengths
        if ($checkindexes) {
            if ($matcher->is_supporting(qtype_preg_matcher::PARTIAL_MATCHING)) {
                $ismatchpassed = ($expected['is_match'] === $obtained->is_match());
            } else {
                $ismatchpassed = $fullpassed;
            }

            $subexprsupported = $matcher->is_supporting(qtype_preg_matcher::SUBEXPRESSION_CAPTURING);
            foreach ($obtained->indexfirst as $key => $index) {
                if (!$subexprsupported && $key != 0) {
                    continue;
                }
                $indexfirstpassed = $indexfirstpassed && ((!array_key_exists($key, $expected['index_first']) && $index === qtype_preg_matching_results::NO_MATCH_FOUND) ||
                                (array_key_exists($key, $expected['index_first']) && $expected['index_first'][$key] === $obtained->indexfirst[$key]));
            }
            foreach ($obtained->length as $key => $index) {
                if (!$subexprsupported && $key != 0) {
                    continue;
                }
                $lengthpassed = $lengthpassed && ((!array_key_exists($key, $expected['length']) && $index === qtype_preg_matching_results::NO_MATCH_FOUND) ||
                                (array_key_exists($key, $expected['length']) && $expected['length'][$key] === $obtained->length[$key]));
            }
        }

        // Apply errors to string && run normal match
        $errorsapplyed = false;
        if ($isbetter && $obtained->full) {
            $strafterapplying = $obtained->errors->apply($str);

            $matcher->maxerrors = 0;

            $obtainedafterapplying = $matcher->match($strafterapplying);

            $fullpassedafteraaplying = $obtainedafterapplying->full;
            $errorsapplyed = true;
        }


        $passed = $isbetter && $indexfirstpassed && $lengthpassed &&
                (!$errorsapplyed || $fullpassedafteraaplying);

        if (!$passed && $dumpfails) {
            $obtainedstr = '';
            $expectedstr = '';

            if (!$isbetter) {
                if (!$fullpassed) {
                    $obtainedstr .= $this->dump_boolean('FULL:            ', $obtained->full);
                    $expectedstr .= $this->dump_boolean('FULL:            ', $expected['full']);
                }

                if (!$equalserrorscount) {
                    $obtainedstr .= $this->dump_indexes('ERRORS COUNT:        ', $obtained->errors->count());
                    $expectedstr .= $this->dump_indexes('ERRORS COUNT:        ', $expectederrorscount);
                }

                if (!$equalserrors && !$leftmostlongest) {
                    $obtainedstr .= "ERRORS:        \n" . $obtained->errors;
                    $expectedstr .= "ERRORS:        \n" . $this->dump_errors($expectederrors);
                }
            }

            if ($errorsapplyed && !$fullpassedafteraaplying) {
                $obtainedstr .= $this->dump_boolean('FULL AFTER ERRORS APPLYING:            ', $obtainedafterapplying->full);
                $expectedstr .= $this->dump_boolean('FULL AFTER ERRORS APPLYING:            ', true);
            }

            if ($checkindexes) {
                // index_first
                if (!$indexfirstpassed) {
                    $obtainedstr .= $this->dump_indexes('INDEX_FIRST:     ', $obtained->indexfirst);
                    $expectedstr .= $this->dump_indexes('INDEX_FIRST:     ', $expected['index_first']);
                }

                // length
                if (!$lengthpassed) {
                    $obtainedstr .= $this->dump_indexes('LENGTH:          ', $obtained->length);
                    $expectedstr .= $this->dump_indexes('LENGTH:          ', $expected['length']);
                }
            }


            $enginename = $matcher->name();
            $merging = in_array(self::TAG_FAIL_MODE_MERGE, $tags) ? "merging is on" : "merging is off";

            $message = $modstr == '' ?
                    "$enginename failed on regex '$regex' and string '$str', $merging with errorslimit : $expectederrorslimit:\n" :
                    "$enginename failed on regex '$regex' string '$str' and modifiers '$modstr', $merging with errorslimit : $expectederrorslimit:\n";
            $message .= $obtainedstr;
            $message .= "expected:\n";
            $message .= $expectedstr;
            $message .= "\n";

            echo $message;
            $this->log($message);
        }

        // Return true if everything is correct, false otherwise.
        return $passed;
    }

    function dump_errors($values) {
        $result = "";
        foreach ($values as $type => $errors) {
            if (count($errors)) {
                $result.= "\t" . qtype_preg_typo::typo_description($type) . "s:\n";
            }
            foreach($errors as $err) {
                $result.= "\t\tpos = {$err['pos']}, char = {$err['char']}" . "\n";
            }
        }
        return $result;
    }

    protected function make_errors($testdata) {
        $result = [];
        $errorspositions = [];

        $str = $testdata['str'];
        $ind0 = $testdata['index_first'][0];
        $len0 = $testdata['length'][0];

        // Test data without modification, same as normal matching
        $tmpdata = $testdata;
        $tmpdata['errorslimit'] = 1;
        $result [] = $tmpdata;

        // Test data without modification with bigger error limit, same as normal matching
        $tmpdata = $testdata;
        $tmpdata['errorslimit'] = rand(2,4);
        $result []= $tmpdata;

        // If string too short
        if ($len0 < 2) {
            return $result;
        }

        // Test data with 1 substitution
        $ind = $this->generate_unique_random_number($ind0, $ind0 + $len0 - 1);
        $result [] = $this->create_error($testdata, qtype_preg_typo::SUBSTITUTION, $ind, chr(rand(0, 127)));

        // Test data with 1 insertion
        $ind = $this->generate_unique_random_number($ind0, $ind0 + $len0 - 1);
        $result [] = $this->create_error($testdata, qtype_preg_typo::INSERTION, $ind, chr(rand(0, 127)));

        // Test data with 1 deletion
        $ind = $this->generate_unique_random_number($ind0, $ind0 + $len0 - 1);
        $result [] = $this->create_error($testdata, qtype_preg_typo::DELETION, $ind, chr(rand(0, 127)));

        // Test data with 1 transposition
        $ind = $this->generate_unique_random_number($ind0, $ind0 + $len0 - 2);
        $result [] = $this->create_error($testdata, qtype_preg_typo::TRANSPOSITION, $ind);

        // Test data with 1 random error and 0-error limit (should fails or returns better result)
        $ind = $this->generate_unique_random_number($ind0, $ind0 + $len0 - 2);
        $tmpdata = $this->create_error($testdata, 2 ** rand(0, 3), $ind, rand(1, 127));
        $tmpdata['is_match'] = false;
        $tmpdata['full'] = false;
        $tmpdata['errorslimit'] = 0;
        $result [] = $tmpdata;

        return $result;
    }

    protected function generate_unique_random_number($from, $to, $oldnumbers = [], $typotype = -1) {
        $numb = mt_rand($from, $to);

        if (empty($oldnumbers)) {
            return $numb;
        }

        if (!array_key_exists($numb, $oldnumbers)) {
            if ($typotype === qtype_preg_typo::TRANSPOSITION) {
                if (!array_key_exists($numb + 1, $oldnumbers)) {
                    $oldnumbers[$numb] = $numb;
                    $oldnumbers[$numb + 1] = $numb + 1;
                    return $numb;
                }
            } else {
                $oldnumbers[$numb] = $numb;
                return $numb;
            }
        }

        for ($numb = $from; $numb <= $to; $numb++) {
            if (!array_key_exists($numb, $oldnumbers)) {
                if ($typotype === qtype_preg_typo::TRANSPOSITION) {
                    if (!array_key_exists($numb + 1, $oldnumbers)) {
                        $oldnumbers[$numb] = $numb;
                        $oldnumbers[$numb + 1] = $numb + 1;
                        return $numb;
                    }
                } else {
                    $oldnumbers[$numb] = $numb;
                    return $numb;
                }
            }
        }

        return null;
    }

    protected function create_error($testdata, $errtype = -1, $pos = -1, $char = '') {
        $str = $testdata['str'];
        $newstr = $str;
        $result = $testdata;

        switch ($errtype) {
            case qtype_preg_typo::SUBSTITUTION:
                $newstr[$pos] = $char;
                $result['errors'][qtype_preg_typo::SUBSTITUTION] []= ['pos' => $pos, 'char' => $str[$pos]];
                break;
            case qtype_preg_typo::INSERTION:
                // Delete insertable char
                $newstr = substr_replace($newstr, '', $pos, 1);
                $result['errors'][qtype_preg_typo::INSERTION] []= ['pos' => $pos, 'char' => $str[$pos]];
                break;
            case qtype_preg_typo::DELETION:
                // Insert deletable char
                $newstr = substr_replace($newstr, $char, $pos, 0);
                $result['errors'][qtype_preg_typo::DELETION] []= ['pos' => $pos, 'char' => $char];
                break;
            case qtype_preg_typo::TRANSPOSITION:
                $tmp = $newstr[$pos];
                $newstr[$pos] = $newstr[$pos + 1];
                $newstr[$pos + 1] = $tmp;
                $result['errors'][qtype_preg_typo::TRANSPOSITION] []= ['pos' => $pos, 'char' => $char];
                break;
        }

        $result['str'] = $newstr;
        if (!isset($result['errorscount'])) {
            $result['errorscount'] = 0;
        }
        $result['errorscount']++;

        if (!isset($result['errorslimit'])) {
            $result['errorslimit'] = 0;
        }
        $result['errorslimit']++;

        // Random errorslimit increase.
        if (rand(0,4) === 0) {
            $result['errorslimit']++;
        }


        // Update submatches
        if ($errtype === qtype_preg_typo::DELETION) {
            foreach ($result['index_first'] as $key => $value) {
                if ($pos > $result['index_first'][$key] && $pos < $result['index_first'][$key] + $result['length'][$key]) {
                    $result['length'][$key]++;
                }
            }
            foreach ($result['index_first'] as $key => $value) {
                if ($pos <= $result['index_first'][$key]) {
                    $result['index_first'][$key]++;
                }
            }

        }
        if ($errtype === qtype_preg_typo::INSERTION) {

            foreach ($result['index_first'] as $key => $value) {
                if ($pos >= $result['index_first'][$key] && $pos < $result['index_first'][$key] + $result['length'][$key]) {
                    $result['length'][$key]--;
                }
            }
            foreach ($result['index_first'] as $key => $value) {
                if ($pos < $result['index_first'][$key]) {
                    $result['index_first'][$key]--;
                }
            }
        }
        /*
        if ($errtype === qtype_preg_typo::INSERTION) {
            $subcount = count($result['index_first']);
            for($i = 0; $i < $subcount; $i++) {
                if ($pos >= $result['index_first'][$i] && $pos < $result['index_first'][$i] + $result['length'][$i]) {
                    $result['length'][$i]--;
                    for($j = $i + 1; $j < $subcount; $j++) {
                        if ($pos < $result['index_first'][$j]) {
                            $result['index_first'][$j]--;
                        }
                    }
                }
            }
        }*/

        return $result;
    }

    public function run_normal_tests() {
        $passcount = 0;
        $failcount = 0;
        $skipcount = 0;

        $slowbuildtests = array();
        $slowmatchtests = array();
        $exceptiontests = array();

        $options = new qtype_preg_matching_options();  // Forced subexpression catupring.
        $blacklist = array_merge($this->blacklist_tags(), $this->blacklist);

        echo "Test full and partial matching:\n";

        foreach ($this->testdataobjects as $testdataobj) {
            $testmethods = get_class_methods($testdataobj);
            $classname = get_class($testdataobj);
            foreach ($testmethods as $methodname) {
                // Filtering class methods by names. A test method name should start with 'data_for_test_'.
                if (strpos($methodname, 'data_for_test_') !== 0) {
                    continue;
                }

                // Get current test data.
                $data = $testdataobj->$methodname();
                $regex = $data['regex'];
                $modifiersstr = '';
                $regextags = array();
                $notation = self::NOTATION_NATIVE;
                if (array_key_exists('modifiers', $data)) {
                    $modifiersstr = $data['modifiers'];
                }
                if (array_key_exists('tags', $data)) {
                    $regextags = $data['tags'];
                }
                if (array_key_exists('notation', $data)) {
                    $notation = $data['notation'];
                }

                // Skip empty regexes
                if ($regex == '') {
                    continue;
                }

                // Skip regexes with blacklisted tags.
                if (count(array_intersect($blacklist, $regextags)) > 0) {
                    continue;
                }

                $matcher_merged = null;
                $matcher_unmerged = null;

                $passeddata = $testdataobj->$methodname();
                $passeddata['tests'] = [];

                // Iterate over all tests.
                foreach ($data['tests'] as $expected) {
                    $str = $expected['str'];
                    $strtags = array();
                    if (array_key_exists('tags', $expected)) {
                        $strtags = $expected['tags'];
                    }

                    $tags = array_merge($regextags, $strtags);

                    // Skip tests with blacklisted tags.
                    if (count(array_intersect($blacklist, $tags)) > 0) {
                        continue;
                    }

                    // Lazy matcher building.
                    $merge = in_array(self::TAG_FAIL_MODE_MERGE, $tags);
                    if (($merge && $matcher_merged === null) || (!$merge && $matcher_unmerged === null)) {
                        $timestart = round(microtime(true) * 1000);
                        $options->mode = in_array(self::TAG_MODE_POSIX, $regextags) ? qtype_preg_handling_options::MODE_POSIX : qtype_preg_handling_options::MODE_PCRE;
                        $options->modifiers = qtype_preg_handling_options::string_to_modifiers($modifiersstr);
                        $options->debugmode = in_array(self::TAG_DEBUG_MODE, $regextags);
                        $options->mergeassertions = $merge;
                        $options->extensionneeded = !in_array(self::TAG_DONT_CHECK_PARTIAL, $regextags);
                        $tmpmatcher = $this->get_matcher($this->engine_name(), $regex, $options);
                        $timeend = round(microtime(true) * 1000);
                        if ($timeend - $timestart > self::MAX_BUILDING_TIME) {
                            $slowbuildtests[] = $classname . ' : ' . $methodname;
                        }

                        if ($merge) {
                            $matcher_merged = $tmpmatcher;
                        } else {
                            $matcher_unmerged = $tmpmatcher;
                        }
                    }

                    $matcher = $merge ? $matcher_merged : $matcher_unmerged;

                    // Move to the next test if there's something wrong.
                    if ($matcher === null || $this->check_for_errors($matcher)) {
                        ++$skipcount;
                        continue;
                    }

                    // There can be exceptions during matching.
                    $timestart = round(microtime(true) * 1000);
                    try {
                        $matcher->match($str);
                        $obtained = $matcher->get_match_results();
                    } catch (Exception $e) {
                        echo "EXCEPTION CATCHED DURING MATCHING, test name is " . $methodname .  "\n" . $e->getMessage() . "\n";
                        $exceptiontests[] = $classname . ' : ' . $methodname;
                        continue;
                    }
                    $timeend = round(microtime(true) * 1000);
                    if ($timeend - $timestart > self::MAX_BUILDING_TIME) {
                        $slowmatchtests[] = $classname . ' : ' . $methodname;
                    }

                    // Results obtained, check them.
                    $skippartialcheck = in_array(self::TAG_DONT_CHECK_PARTIAL, $tags);
                    if ($this->compare_results($regex, $notation, $str, $modifiersstr, $tags, $matcher, $expected, $obtained, $classname, $methodname, $skippartialcheck, true)) {
                        $passcount++;

                        if ($expected['is_match'] && $expected['full']) {
                            $passeddata['tests'] [] = $expected;
                        }
                    } else {
                        $failcount++;
                    }
                }

                if ($this->accept_regex($regex) && count($passeddata['tests'])) {
                    $this->passednormaltests [] = $passeddata;
                }
            }
        }
        if ($failcount == 0 && empty($exceptiontests) && $passcount > 0) {
            echo "\n\nWow! All tests passed!\n\n";
        }
        echo "======================\n";
        echo 'PASSED:     ' . $passcount . "\n";
        echo 'FAILED:     ' . $failcount . "\n";
        echo 'SKIPPED:    ' . $skipcount . "\n";
        echo "======================\n";
        if (!empty($slowbuildtests)) {
            echo "tests with slow matcher building:\n";
            echo implode("\n", $slowbuildtests) . "\n";
            echo "======================\n";
        }
        if (!empty($slowmatchtests)) {
            echo "tests with slow matching:\n";
            echo implode("\n", $slowmatchtests) . "\n";
            echo "======================\n";
        }
        if (!empty($exceptiontests)) {
            echo "tests with unhandled exceptions:\n";
            echo implode("\n", $exceptiontests) . "\n";
            echo "======================\n";
        }
    }



    public function test() {
        $this->run_normal_tests();
        $this->serialize_test_data();
        mt_srand(100);
        $this->unserialize_test_data();
        $this->run_fuzzy_tests();
    }
}
