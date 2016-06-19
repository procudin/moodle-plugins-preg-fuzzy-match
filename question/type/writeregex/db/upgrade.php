<?php
// This file is part of WriteRegex question type - https://code.google.com/p/oasychev-moodle-plugins/
//
// WriteRegex is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// WriteRegex is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * WriteRegEx question type upgrade code.
 *
 * @package qtype
 * @subpackage writeregex
 * @copyright  2014 onwards Oleg Sychev, Volgograd State Technical University.
 * @author Mikhail Navrotskiy <m.navrotskiy@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_qtype_writeregex_upgrade($oldversion = 0) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2014022400) {

        $table = new xmldb_table(('qtype_writeregex_options'));
        $usecase = new xmldb_field('usecase', XMLDB_TYPE_INTEGER, '2', '0', XMLDB_NOTNULL, null, '0', 'questionid');

        if (!$dbman->field_exists($table, $usecase)) {
            $dbman->add_field($table, $usecase);
        }

        $engine = new xmldb_field('engine', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null,
            'preg_php_matcher', 'usecase');

        if (!$dbman->field_exists($table, $engine)) {
            $dbman->add_field($table, $engine);
        }

        upgrade_plugin_savepoint(true, 2014022400, 'qtype', 'writeregex');
    }

    if ($oldversion < 2015121700) {

        // Rename field compareregexpercentage on table qtype_writeregex_options to comparetreepercentage.
        $table = new xmldb_table('qtype_writeregex_options');
        $field = new xmldb_field('compareregexpercentage', XMLDB_TYPE_FLOAT, '12, 7', null,
            XMLDB_NOTNULL, null, null, 'teststringshintpenalty');

        // Launch rename field compareregexpercentage.
        $dbman->rename_field($table, $field, 'comparetreepercentage');

        // Rename field compareregexpteststrings on table qtype_writeregex_options to comparestringspercentage.
        $table = new xmldb_table('qtype_writeregex_options');
        $field = new xmldb_field('compareregexpteststrings', XMLDB_TYPE_FLOAT, '12, 7', null,
            XMLDB_NOTNULL, null, null, 'compareautomatapercentage');

        // Launch rename field compareregexpteststrings.
        $dbman->rename_field($table, $field, 'comparestringspercentage');

        // Writeregex savepoint reached.
        upgrade_plugin_savepoint(true, 2015121700, 'qtype', 'writeregex');
    }    
    if ($oldversion < 2016060802) {

        // Define field stringmismatchpenalty to be added to qtype_writeregex_options.
        $table = new xmldb_table('qtype_writeregex_options');
        $field = new xmldb_field('stringmismatchpenalty', XMLDB_TYPE_NUMBER, '12, 7', null, null, null, null, 'comparestringspercentage');

        // Conditionally launch add field stringmismatchpenalty.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('comparewithsubpatterns', XMLDB_TYPE_BINARY, null, null, null, null, null, 'stringmismatchpenalty');

        // Conditionally launch add field comparewithsubpatterns.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('subpatternmismatchpenalty', XMLDB_TYPE_NUMBER, '12, 7', null, null, null, null, 'comparewithsubpatterns');

        // Conditionally launch add field subpatternmismatchpenalty.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('mismatchesshowncount', XMLDB_TYPE_INTEGER, '3', null, null, null, '5', 'subpatternmismatchpenalty');

        // Conditionally launch add field mismatchesshouncount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Writeregex savepoint reached.
        upgrade_plugin_savepoint(true, 2016060802, 'qtype', 'writeregex');
    }
    if ($oldversion < 2016061401) {

        // Changing the default of field comparetreepercentage on table qtype_writeregex_options to 0.
        $table = new xmldb_table('qtype_writeregex_options');
        $field = new xmldb_field('comparetreepercentage', XMLDB_TYPE_FLOAT, '12, 7', null, XMLDB_NOTNULL, null, '0', 'teststringshintpenalty');

        // Launch change of default for field comparetreepercentage.
        $dbman->change_field_default($table, $field);

        // Writeregex savepoint reached.
        upgrade_plugin_savepoint(true, 2016061401, 'qtype', 'writeregex');
    }
    if ($oldversion < 2016061900) {

        // Define field showmismatchedteststrings to be added to qtype_writeregex_options.
        $table = new xmldb_table('qtype_writeregex_options');
        $field = new xmldb_field('showmismatchedteststrings', XMLDB_TYPE_BINARY, null, null, null, null, null, 'mismatchesshowncount');

        // Conditionally launch add field showmismatchedteststrings.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Writeregex savepoint reached.
        upgrade_plugin_savepoint(true, 2016061900, 'qtype', 'writeregex');
    }

    return true;

}