<?php
/**
 * Defines authoring tools form class.
 *
 * @copyright &copy; 2012 Oleg Sychev, Volgograd State Technical University
 * @author Terechov Grigory, Volgograd State Technical University
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questions
 */

//defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_syntax_tree_nodes.php');
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_regex_testing_tool.php');
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_textarea.php');
require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_explaining_graph_nodes.php');
require_once($CFG->dirroot . '/question/type/preg/question.php');
require_once($CFG->dirroot . '/question/type/preg/questiontype.php');
require_once($CFG->dirroot . '/question/type/preg/preg_hints.php');

class qtype_preg_authoring_form extends moodleform {

    private $isblock;

    function __construct($isblock = false) {
        $this->isblock = $isblock;
        $attributes = array('id' => 'mformauthoring');
        parent::moodleform(null, null, 'post', '', $attributes);
    }

    //Add elements to form
    function definition() {
        global $CFG;
        global $PAGE;

        $PAGE->requires->js('/question/type/poasquestion/jquery-textrange.js');
        $PAGE->requires->js('/question/type/poasquestion/jquerypanzoommin.js');
        $PAGE->requires->js('/question/type/poasquestion/interface.js');
        /*$PAGE->requires->css('/question/type/poasquestion/shadow.css');
        $PAGE->requires->css('/question/type/poasquestion/rect.css');*/

        // Create the form.
        $qtype = new qtype_preg();
        $mform = $this->_form;

        // Add header.
        $mform->addElement('html', '<div align="center"><h2>' . get_string('authoring_form_page_header', 'qtype_preg') . '</h2></div>');

        // Add the editing widgets.
        $mform->addElement('header', 'regex_input_header', get_string('authoring_form_edit_header', 'qtype_preg'));
        $mform->setExpanded('regex_input_header', (bool)get_user_preferences('qtype_preg_regex_input_expanded', true));
        $mform->addHelpButton('regex_input_header', 'authoring_form_edit_header', 'qtype_preg');
        $mform->addElement('textarea', 'regex_text', get_string('authoring_form_text', 'qtype_preg'), array('cols' => 150, 'rows' => 1));
        $mform->setType('regex_text', PARAM_RAW);

        $topline = array();
        $topline[] =& $mform->createElement('submit', 'regex_show', get_string('show', 'moodle'));
        if (!$this->isblock) {
            $topline[] =& $mform->createElement('button', 'regex_save', get_string('savechanges', 'moodle'));
        }
        $topline[] =& $mform->createElement('button', 'regex_cancel', get_string('cancel', 'moodle'));
        $mform->addGroup($topline, 'input_regex_line', '', array(' '), false);

        $radiocharsetprocessarray = array();
        $radiocharsetprocessarray[] =& $mform->createElement('radio', 'authoring_tools_charset_process', '', get_string('authoring_form_charset_userinscription', 'qtype_preg'), 'userinscription', null);
        $radiocharsetprocessarray[] =& $mform->createElement('radio', 'authoring_tools_charset_process', '', get_string('authoring_form_charset_flags', 'qtype_preg'), 'flags', null);
        $mform->addGroup($radiocharsetprocessarray, 'charset_process_radioset', get_string('authoring_form_charset_mode', 'qtype_preg'), array(' '), false);
        $mform->setDefault('authoring_tools_charset_process', 'userinscription');

        // Add matching options.
        $mform->addElement('header', 'regex_matching_options_header', get_string('authoring_form_options_header', 'qtype_preg'));
        $mform->setExpanded('regex_matching_options_header', (bool)get_user_preferences('qtype_preg_regex_matching_options_expanded', true));
        $mform->addHelpButton('regex_matching_options_header', 'authoring_form_options_header', 'qtype_preg');

        $engines = $qtype->available_engines();
        $mform->addElement('select', 'engine_auth', get_string('engine', 'qtype_preg'), $engines);
        $mform->setDefault('engine_auth', $CFG->qtype_preg_defaultengine);
        $mform->addHelpButton('engine_auth', 'engine', 'qtype_preg');

        $notations = $qtype->available_notations();
        $mform->addElement('select', 'notation_auth', get_string('notation', 'qtype_preg'), $notations);
        $mform->setDefault('notation_auth', $CFG->qtype_preg_defaultnotation);
        $mform->addHelpButton('notation_auth', 'notation', 'qtype_preg');

        $mform->addElement('selectyesno', 'exactmatch_auth', get_string('exactmatch', 'qtype_preg'));
        $mform->addHelpButton('exactmatch_auth', 'exactmatch', 'qtype_preg');
        $mform->setDefault('exactmatch_auth', 1);

        $mform->addElement('select', 'usecase_auth', get_string('casesensitive', 'qtype_shortanswer'), array(get_string('caseno', 'qtype_shortanswer'), get_string('caseyes', 'qtype_shortanswer')));

        // Add syntax tree tool.
        $mform->addElement('header', 'regex_tree_header', get_string('syntax_tree_tool', 'qtype_preg'));
        $mform->setExpanded('regex_tree_header', (bool)get_user_preferences('qtype_preg_regex_tree_expanded', true));
        $mform->addHelpButton('regex_tree_header', 'syntax_tree_tool', 'qtype_preg');

        // Add tree orientation radio buttons.
        $radiotreeorientationsarray = array();
        $radiotreeorientationsarray[] =& $mform->createElement('radio', 'authoring_tools_tree_orientation', '', get_string('vertical', 'editor'), 'vertical', null);
        $radiotreeorientationsarray[] =& $mform->createElement('radio', 'authoring_tools_tree_orientation', '', get_string('horizontal', 'editor'), 'horizontal', null);
        $radiotreeorientationsarray[] =& $mform->createElement('checkbox', 'selection_mode', '', 'Selection mode', '', null);
        $radiotreeorientationsarray[] =& $mform->createElement('submit', 'send_select', 'Send select');
        $radiotreeorientationsarray[] =& $mform->createElement('submit', 'cancel_select', 'Cancel select');
        $mform->addGroup($radiotreeorientationsarray, 'tree_orientation_radioset', '', array(' '), false);
        $mform->setDefault('authoring_tools_tree_orientation', 'vertical');

        // Add generated map.
        $mform->addElement('html', '<div id="tree_map" ></div></br>');
        $mform->addElement('html', '<div style="max-height:400px;position:relative;overflow:auto !important;width:100%;max-width:100%" id="tree_hnd">' .
                                        '<div id="tree_err"></div>' .
                                            '<img src="" id="tree_img" usemap="#' . qtype_preg_syntax_tree_node::get_graph_name() . '" alt="' . get_string('authoring_form_tree_build', 'qtype_preg') . '" />' .
                                        '</div></br>');

        // Add explaining graph tool.
        $mform->addElement('header', 'regex_graph_header', get_string('explaining_graph_tool', 'qtype_preg'));
        $mform->setExpanded('regex_graph_header', (bool)get_user_preferences('qtype_preg_regex_graph_expanded', true));
        $mform->addHelpButton('regex_graph_header', 'explaining_graph_tool', 'qtype_preg');
        $mform->addElement('html', '<div id="graph_map" ></div></br>');
        $abc = '<div style="max-height:400px;position:relative;overflow:auto !important;width:100%;max-width:100%" id="graph_hnd">' .
                   '<div id="graph_err"></div>' .
                   '<img src="" id="graph_img" usemap="#' . qtype_preg_explaining_graph_node_abstract::get_graph_name() . '" alt="' . get_string('authoring_form_graph_build', 'qtype_preg') . '" />' .
               '</div></br>';
        $mform->addElement('html', $abc);

        // Add description tool.
        $mform->addElement('header', 'regex_description_header', get_string('description_tool', 'qtype_preg'));
        $mform->setExpanded('regex_description_header', (bool)get_user_preferences('qtype_preg_regex_description_expanded', true));
        $mform->addHelpButton('regex_description_header', 'description_tool', 'qtype_preg');
        $mform->addElement('html', '<div id="description_handler"></div>');

        // Add testing tool.
        $mform->addElement('header', 'regex_testing_header', get_string('authoring_form_testing_header', 'qtype_preg'));
        $mform->setExpanded('regex_testing_header', (bool)get_user_preferences('qtype_preg_regex_testing_expanded', true));
        $mform->addHelpButton('regex_testing_header', 'authoring_form_testing_header', 'qtype_preg');

        $mform->addElement('preg_textarea', 'regex_match_text', get_string('authoring_form_testing_textarea', 'qtype_preg'), array('cols' => 50));

        $mform->registerNoSubmitButton('regex_check_strings');
        $mform->addElement('button', 'regex_check_strings', get_string('authoring_form_check_strings', 'qtype_preg'));
    }

    public function qtype() {
        return 'preg';
    }

    function validation($data, $files) {
        return array();
    }
}