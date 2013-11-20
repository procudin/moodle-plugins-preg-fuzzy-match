<?php
global $CFG;
require_once("{$CFG->libdir}/formslib.php");
 
class addedit_session_form extends moodleform {
 
    function definition() {
        global $DB;

        $mform =& $this->_form;

        // TODO see report_log_print_selector_form function

        // TODO find only teachers
        if ($cteachers = $DB->get_records('user')) {
            foreach ($cteachers as $cteacher) {
                $teachers[$cteacher->id] = $cteacher->lastname . " " . $cteacher->firstname;
            }
        }

        // Find course.
        $course = $DB->get_record('course', array('id' => $this->_customdata['courseid']));

        // Find all classrooms.
        if ($cclassrooms = $DB->get_records('block_supervised_classroom')) {
            foreach ($cclassrooms as $cclassroom) {
                $classrooms[$cclassroom->id] = $cclassroom->name;
            }
        }

        // Gets array of all groups in current course.
        $groups[0] = get_string('allgroups', 'block_supervised');
        if ($cgroups = groups_get_all_groups($this->_customdata['courseid'])) {
            foreach ($cgroups as $cgroup) {
                $groups[$cgroup->id] = $cgroup->name;
            }
        }

        // Find lessontypes in current course.
        $lessontypes[0] = get_string('notspecified', 'block_supervised');
        if ($clessontypes = $DB->get_records('block_supervised_lessontype', array('courseid'=>$this->_customdata['courseid']))) {
            foreach ($clessontypes as $clessontype) {
                $lessontypes[$clessontype->id] = $clessontype->name;
            }
        }



        // add group
        $mform->addElement('header', 'general', get_string('general', 'form'));
        // add teacher combobox
        $mform->addElement('select', 'teacherid', get_string('teacher', 'block_supervised'), $teachers);
        $mform->addRule('teacherid', null, 'required', null, 'client');
        // add send e-mail checkbox
        $mform->addElement('advcheckbox', 'sendemail', get_string("sendemail", 'block_supervised'));
        $mform->addHelpButton('sendemail', 'sendemail', 'block_supervised');
        // add course label     // TODO what is difference with course.name (or fullname)?
        $mform->addElement('static', 'course', get_string('course', 'block_supervised'), get_course_display_name_for_list($course));
        // add classroom combobox
        $mform->addElement('select', 'classroomid', get_string('classroom', 'block_supervised'), $classrooms);
        $mform->addRule('classroomid', null, 'required', null, 'client');
        // add group combobox
        $mform->addElement('select', 'groupid', get_string('group', 'block_supervised'), $groups);
        $mform->addRule('groupid', null, 'required', null, 'client');
        // add lessontype combobox
        $mform->addElement('select', 'lessontypeid', get_string('lessontype', 'block_supervised'), $lessontypes);
        $mform->addRule('lessontypeid', null, 'required', null, 'client');
        // add time start
        $mform->addElement('date_time_selector', 'timestart', get_string('timestart', 'block_supervised'));
        $mform->addRule('timestart', null, 'required', null, 'client');
        // add duration
        $mform->addElement('text', 'duration', get_string('duration', 'block_supervised'), 'size="4"');
        $mform->addRule('duration', null, 'required', null, 'client');
        // add comment
        $mform->addElement('textarea', 'sessioncomment', get_string("sessioncomment", "block_supervised"), 'rows="4" cols="30"');



        // hidden elements
        $mform->addElement('hidden', 'id');
        $mform->addElement('hidden', 'courseid');
        
        $this->add_action_buttons();
    }
}