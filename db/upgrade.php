<?php

/**
 * WriteRegEx question type upgrade code.
 */
function xmldb_qtype_writeregex_upgrade($oldversion = 0)
{
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    // In this version types of matching stored in one field.
    if ($oldversion < 2013122300) {

        $table = new xmldb_table('qtype_writeregex_options');
        $field = new xmldb_field('compareautomatercentage');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2013122400, 'qtype', 'writeregex');
    }

    return true;

}