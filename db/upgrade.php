<?php

/**
 * WriteRegEx question type upgrade code.
 */
function xmldb_qtype_writeregex_upgrade($oldversion = 0)
{
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

    return true;

}