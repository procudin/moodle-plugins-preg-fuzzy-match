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
            $i = 0;
            $result = $this->grouping_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
            }

            $result = $this->subpattern_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
            }
        }

        return $equivalences;
    }


    /* The 2th rule */
    protected function grouping_node() {
        $equivalences = array();

        if ($this->search_grouping_node($this->get_dst_root())) {
            $equivalences['problem'] = 'Пустая группировка "(?:)"';
            $equivalences['solve'] = 'Пустые скобки не влияют на работу регулярного выражения, их можно удалить';
        }

        return $equivalences;
    }

    private function search_grouping_node($node) {
        if ($node->type == 'node_subexpr' && $node->subtype == 'grouping_node_subexpr') {
            if ($node->operands[0]->type == 'leaf_meta' && $node->operands[0]->subtype == 'empty_leaf_meta') {
                return true;
            }
        }
        foreach($node->operands as $operand) {
            if ($this->search_grouping_node($operand)) {
                return true;
            }
        }
        return false;
    }


    /* The 3th rule */
    protected function subpattern_node() {
        $equivalences = array();

        if ($this->search_subpattern_node($this->get_dst_root())) {
            $equivalences['problem'] = 'Пустая подмаска "()"';
            $equivalences['solve'] = 'Пустые скобки не влияют на работу регулярного выражения, т.к. на них нет обратных ссылок или условных подмасок, их можно удалить';
        }

        return $equivalences;
    }

    private function search_subpattern_node($node) {
        if ($node->type == 'node_subexpr' && $node->subtype == 'subexpr_node_subexpr') {
            if ($node->operands[0]->type == 'leaf_meta' && $node->operands[0]->subtype == 'empty_leaf_meta') {
                return !$this->check_backref_to_subexpr($this->get_dst_root(), $node->number);
            }
        }
        foreach($node->operands as $operand) {
            if ($this->search_subpattern_node($operand)) {
                return true;
            }
        }
        return false;
    }

    private function check_backref_to_subexpr($node, $number) {
        if ($node->type == 'leaf_backref' && $node->subtype == 'leaf_backref' && $node->number == $number) {
            return true;
        }
        foreach($node->operands as $operand) {
            if ($this->check_backref_to_subexpr($operand, $number)) {
                return true;
            }
        }
        return false;
    }
}
