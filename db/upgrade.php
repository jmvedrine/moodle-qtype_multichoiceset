<?php

// This file keeps track of upgrades to
// the multichoiceset qtype plugin
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

function xmldb_qtype_multichoiceset_upgrade($oldversion) {
    global $CFG, $DB, $QTYPES;

    $dbman = $DB->get_manager();

    if ($oldversion < 2011010400) {

    /// Define field correctfeedbackformat to be added to question_multichoiceset
        $table = new xmldb_table('question_multichoiceset');
        $field = new xmldb_field('correctfeedbackformat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'correctfeedback');

    /// Conditionally launch add field correctfeedbackformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

    /// Define field incorrectfeedbackformat to be added to question_multichoiceset
        $field = new xmldb_field('incorrectfeedbackformat', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'incorrectfeedback');

    /// Conditionally launch add field incorrectfeedbackformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // In the past, the correctfeedback, partiallycorrectfeedback,
        // incorrectfeedback columns were assumed to contain content of the same
        // form as questiontextformat. If we are using the HTML editor, then
        // convert FORMAT_MOODLE content to FORMAT_HTML.

        // Because this question type was updated later than the core types,
        // the available/relevant version dates make it hard to differentiate
        // early 2.0 installs from 1.9 updates, hence the extra check for
        // the presence of oldquestiontextformat
        $table = new xmldb_table('question');
        $field = new xmldb_field('oldquestiontextformat');
        if ($dbman->field_exists($table, $field)) {
            $rs = $DB->get_recordset_sql('
                    SELECT qm.*, q.oldquestiontextformat
                    FROM {question_multichoiceset} qm
                    JOIN {question} q ON qm.question = q.id');
            foreach ($rs as $record) {
                if ($CFG->texteditors !== 'textarea' && $record->oldquestiontextformat == FORMAT_MOODLE) {
                    $record->correctfeedback = text_to_html($record->correctfeedback, false, false, true);
                    $record->correctfeedbackformat = FORMAT_HTML;
                    $record->incorrectfeedback = text_to_html($record->incorrectfeedback, false, false, true);
                    $record->incorrectfeedbackformat = FORMAT_HTML;
                } else {
                    $record->correctfeedbackformat = $record->oldquestiontextformat;
                    $record->incorrectfeedbackformat = $record->oldquestiontextformat;
                }
                $DB->update_record('question_multichoiceset', $record);
            }
            $rs->close();
        } 
    /// multichoiceset savepoint reached
        upgrade_plugin_savepoint(true, 2011010400, 'qtype', 'multichoiceset');
    }
	
    // Add new shownumcorrect field. If this is true, then when the user gets a
    // multiple-response question partially correct, tell them how many choices
    // they got correct alongside the feedback.
    if ($oldversion < 2011011200) {

        // Define field shownumcorrect to be added to question_multichoice
        $table = new xmldb_table('question_multichoiceset');
        $field = new xmldb_field('shownumcorrect', XMLDB_TYPE_INTEGER, '2', null,
                XMLDB_NOTNULL, null, '0', 'answernumbering');

        // Launch add field shownumcorrect
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // multichoice savepoint reached
        upgrade_plugin_savepoint(true, 2011011200, 'qtype', 'multichoiceset');
    }

    // Moodle v2.1.0 release upgrade line
    // Put any upgrade step following this

    // Moodle v2.2.0 release upgrade line
    // Put any upgrade step following this
    return true;
}


