<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Implementaton of the quizaccess_supervisedcheck plugin.
 *
 * @package   quizaccess_supervisedcheck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Andrey Ushakov <andrey200964@yandex.ru>
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');



/**
 * A rule for supervised block.
 *
 * @package   quizaccess_supervisedcheck
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Andrey Ushakov <andrey200964@yandex.ru>
 */
class quizaccess_supervisedcheck extends quiz_access_rule_base {

    public static function add_settings_form_fields(
        mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        global $DB, $COURSE, $PAGE, $CFG;

        //Radiobuttons
        $radioarray = array();
        $radioarray[] =& $mform->createElement('radio', 'supervisedcheckrequired', '', get_string('checknotrequired', 'quizaccess_supervisedcheck'), 0);
        $radioarray[] =& $mform->createElement('radio', 'supervisedcheckrequired', '', get_string('checkforall', 'quizaccess_supervisedcheck'), 1);
        $radioarray[] =& $mform->createElement('radio', 'supervisedcheckrequired', '', get_string('customcheck', 'quizaccess_supervisedcheck'), 2);
        $mform->addGroup($radioarray, 'radioar', get_string('allowcontrol', 'quizaccess_supervisedcheck'), '<br/>', false);

        $cbarray = array();
        $cbarray[] =& $mform->createElement('advcheckbox', 'supervisedlessontype_0', '', get_string('notspecified', 'block_supervised'));
        $lessontypes = $DB->get_records('block_supervised_lessontype', array('courseid'=>$COURSE->id));
        foreach($lessontypes as $id=>$lessontype){
            $cbarray[] =& $mform->createElement('advcheckbox', 'supervisedlessontype_'.$id, '', $lessontype->name);
        }
        $mform->addGroup($cbarray, 'lessontypesgroup', '', '<br/>', false);


        $PAGE->requires->jquery();
        $PAGE->requires->js( new moodle_url($CFG->wwwroot . '/mod/quiz/accessrule/supervisedcheck/lib.js') );
        $PAGE->requires->css( new moodle_url($CFG->wwwroot . '/mod/quiz/accessrule/supervisedcheck/style.css') );
    }

    public static function save_settings($quiz) {
        global $DB, $COURSE;
        $oldrules = $DB->get_records('quizaccess_supervisedcheck', array('quizid' => $quiz->id));


        if($quiz->supervisedcheckrequired == 2){
            // Find checked lessontypes.
            $lessontypesincourse = $DB->get_records('block_supervised_lessontype', array('courseid' => $COURSE->id));
            $lessontypesinquiz = array();

            // Check for "Not specified" lessontype.
            if($quiz->supervisedlessontype_0){
                $lessontypesinquiz[] = 0;
            }
            // Checks for all other lessontypes.
            foreach($lessontypesincourse as $id=>$lessontype){
                if($quiz->{'supervisedlessontype_'.$id}){
                    $lessontypesinquiz[] = $id;
                }

            }

            // Update rules.
            for ($i=0; $i<count($lessontypesinquiz); $i++) {
                // Update an existing rule if possible.
                $rule = array_shift($oldrules);
                if (!$rule) {
                    $rule                   = new stdClass();
                    $rule->quizid           = $quiz->id;
                    $rule->lessontypeid     = 0;
                    $rule->supervisedmode   = $quiz->supervisedcheckrequired; // must be 2
                    $rule->id               = $DB->insert_record('quizaccess_supervisedcheck', $rule);
                }
                $rule->lessontypeid         = $lessontypesinquiz[$i];
                $DB->update_record('quizaccess_supervisedcheck', $rule);
            }
            // Delete any remaining old rules.
            foreach ($oldrules as $oldrule) {
                $DB->delete_records('quizaccess_supervisedcheck', array('id' => $oldrule->id));
            }
        }
        else{
            // Update an existing rule if possible.
            $rule = array_shift($oldrules);
            if (!$rule) {
                $rule                   = new stdClass();
                $rule->quizid           = $quiz->id;
                $rule->lessontypeid     = NULL;
                $rule->supervisedmode   = $quiz->supervisedcheckrequired;   // 0 or 1
                $rule->id               = $DB->insert_record('quizaccess_supervisedcheck', $rule);
            }
            $rule->lessontypeid         = NULL;
            $rule->supervisedmode       = $quiz->supervisedcheckrequired;   // 0 or 1
            $DB->update_record('quizaccess_supervisedcheck', $rule);
            // Delete any remaining old rules.
            foreach ($oldrules as $oldrule) {
                $DB->delete_records('quizaccess_supervisedcheck', array('id' => $oldrule->id));
            }
        }
    }


    /*public static function get_settings_sql($quizid) {
        return array(
            'honestycheckrequired',
            'LEFT JOIN {quizaccess_honestycheck} honestycheck ON honestycheck.quizid = quiz.id',
            array());
    }*/
}