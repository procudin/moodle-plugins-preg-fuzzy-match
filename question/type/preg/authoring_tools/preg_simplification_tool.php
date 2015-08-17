<?php
/**
 * Defines simplification tool.
 *
 * @copyright &copy; 2012 Oleg Sychev, Volgograd State Technical University
 * @author Terechov Grigory <grvlter@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package qtype_preg
 */

require_once($CFG->dirroot . '/question/type/preg/authoring_tools/preg_authoring_tool.php');

class qtype_preg_simplification_tool_options extends qtype_preg_handling_options {
    public $is_check_equivalences = true;
    public $is_check_errors = true;
    public $is_check_tips = true;
}

class qtype_preg_simplification_tool extends qtype_preg_authoring_tool {

    public function __construct($regex = null, $options = null) {
        parent::__construct($regex, $options);
    }

    /**
     * Overloaded from qtype_preg_regex_handler.
     */
    public function name() {
        return 'simplification_tool';
    }

    /**
     * Overloaded from qtype_preg_regex_handler.
     */
    protected function node_infix() {
        return 'simplification';
    }

    /**
     * Overloaded from qtype_preg_regex_handler.
     */
    protected function get_engine_node_name($nodetype, $nodesubtype) {
        return parent::get_engine_node_name($nodetype, $nodesubtype);
    }

    /**
     * Overloaded from qtype_preg_regex_handler.
     */
    protected function is_preg_node_acceptable($pregnode) {
        return true;
    }

    /**
     * Overloaded from qtype_preg_authoring_tool.
     */
    public function json_key() {
        return 'simplification';
    }

    public function generate_html() {
        if ($this->regex->string() == '') {
            return $this->data_for_empty_regex();
        } else if ($this->errors_exist() || $this->get_ast_root() == null) {
            return $this->data_for_unaccepted_regex();
        }
        return $this->data_for_accepted_regex();
    }

    /**
     * Overloaded from qtype_preg_authoring_tool.
     */
    public function data_for_accepted_regex() {
        $data = array();
        $data['errors'] = $this->get_errors_description();
        $data['tips'] = $this->get_tips_description();
        $data['equivalences'] = $this->get_equivalences_description();
        return $data;
    }

    protected function get_errors_description() {
        $errors = array();
//        if ($this->options->is_check_errors == true) {
//            //to do something
//        }
        return $errors;

//        return array(array('problem' => 'Описание ошибки', 'solve' => 'Подробное описание информации'));
    }

    protected function get_tips_description() {
        $tips = array();
//        if ($this->options->is_check_tips == true) {
//            //to do something
//        }
        return $tips;

//        return array(array('problem' => 'Описание совета', 'solve' => 'Подробное описание совета'),
//                     array('problem' => 'А вот тут ещё совет', 'solve' => 'И ещё описание совета'));
    }

    protected function get_equivalences_description() {
        $equivalences = array();

        if ($this->options->is_check_equivalences == true) {
            $result = $this->remove_grouping_node();
            if ($result != array()) {
                $equivalences[0] = array();
                $equivalences[0] += $result;
            }
        }

        return $equivalences;
    }

    protected function remove_grouping_node() {
        $equivalences = array();

        if ($this->check_is_remove_grouping_node()) {
            $equivalences['problem'] = 'Пустая группировка "(?:)"';
            $equivalences['solve'] = 'Пустые скобки не влияют на работу регулярного выражения, их можно удалить';
        }

        return $equivalences;
    }

    private function check_is_remove_grouping_node() {
        return $this->delete_grouping_node($this->get_dst_root());
    }

    private function delete_grouping_node($node) {
        if ($node->type == 'node_subexpr' && $node->subtype == 'grouping_node_subexpr') {
            if ($node->operands[0]->type == 'leaf_meta' && $node->operands[0]->subtype == 'empty_leaf_meta') {
                return true;
            }
        }
        foreach($node->operands as $operand) {
            if ($this->delete_grouping_node($operand)) {
                return true;
            }
        }
        return false;
    }
}
