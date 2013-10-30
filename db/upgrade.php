<?php

/**
 * WriteRegEx question type upgrade code.
 */
function xmldb_qtype_writeregex_upgrade($oldversion = 0)
{
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    // new version is: 2013102500
    if ($oldversion < 2013102500) // Now define current version, but new is '2013102500'.
    {
        // Define field wikipage to be added to mdl_question_writeregex
        $table = new xmldb_table('qtype_writeregex_options');
        $field = new xmldb_field('compareautomatercentage', XMLDB_TYPE_FLOAT, '4', '2', XMLDB_NOTNULL, null, '0', 'compareregexpercentage');

        // Add field wikipage
        if (!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }

        // Upgrade version
        upgrade_plugin_savepoint(true, 2013102500, 'qtype', 'writeregex');

    }

    return true;

}