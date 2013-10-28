<?php

/**
 * WriteRegEx question type upgrade code.
 */
function xmldb_qtype_writeregex_upgrade($oldversion = 0)
{
    global $CFG, $DB;
    $dbman = $DB->get_manager();

    // new version is: 2013102500
    if ($oldversion < 2013102200) // Now define current version, but new is '2013102500'.
    {
        // Define field wikipage to be added to mdl_question_writeregex
        $table = new xmldb_table('question_writeregex'); // TODO: Проверить префикс дб
        $field = new xmldb_field('wikipage', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'showerrorformat');

        // Add field wikipage
        if (!$dbman->field_exists($table, $field))
        {
            $dbman->add_field($table, $field);
        }

        // Upgrade version
        upgrade_plugin_savepoint(true, 2013102500, 'question', 'writeregex'); // TODO: Проверить правильность ввода

    }

    return true;

}