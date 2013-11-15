<?php

require_once("{$CFG->libdir}/formslib.php");
 
class mod_lessontype_form extends moodleform {
 
    function definition() {
 
        $mform =& $this->_form;
        
        // add group
        $mform->addElement('header', 'general', get_string('general', 'form'));
        // add name element.
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        $mform->addRule('name', null, 'required', null, 'client');

        // hidden elements
        $mform->addElement('hidden', 'blockid');
        $mform->addElement('hidden', 'courseid');
        
        $this->add_action_buttons();
    }
}