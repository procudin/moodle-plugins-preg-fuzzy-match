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
    public $problem_id = -2;
    public $problem_type = -2;
}

class qtype_preg_simplification_tool extends qtype_preg_authoring_tool {

    private $problem_id = -2;
    private $problem_type = -2;
    private $is_find_assert = false;

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


    //--- CHECK ALL RULES ---
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
            $result = $this->repeated_assertions();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
            }

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

            $result = $this->single_charset_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
            }
        }

        return $equivalences;
    }


    //--- CHECK RULES ---
    /* The 1st rule */
    protected function repeated_assertions() {
        $equivalences = array();

        if ($this->search_repeated_assertions($this->get_dst_root())) {
            $equivalences['problem'] = 'Подряд идущие простые утверждения';
            $equivalences['solve'] = 'Подрядищущее простое утверждения является лишним, т.к. рядом уже есть такое же';
            $equivalences['problem_id'] = $this->problem_id;
            $equivalences['problem_type'] = $this->problem_type;
        }

        return $equivalences;
    }

    private function search_repeated_assertions($node) {
        if ($node->type == qtype_preg_node::TYPE_LEAF_ASSERT
            && ($node->subtype == qtype_preg_leaf_assert::SUBTYPE_CIRCUMFLEX || $node->subtype == qtype_preg_leaf_assert::SUBTYPE_DOLLAR)) {
            if ($this->is_find_assert == true) {
                $this->problem_id = $node->id;
                $this->problem_type = 1;
                return true;
            } else {
                $this->is_find_assert = true;
            }
        } else if ($node->type == qtype_preg_node::TYPE_LEAF_CHARSET
                   || $node->type == qtype_preg_node::TYPE_LEAF_META
                   || $node->type == qtype_preg_node::TYPE_LEAF_BACKREF
                   || $node->type == qtype_preg_node::TYPE_LEAF_SUBEXPR_CALL
                   || $node->type == qtype_preg_node::TYPE_LEAF_TEMPLATE
                   || $node->type == qtype_preg_node::TYPE_LEAF_CONTROL
                   || $node->type == qtype_preg_node::TYPE_LEAF_OPTIONS
                   || $node->type == qtype_preg_node::TYPE_LEAF_COMPLEX_ASSERT) {
            $this->is_find_assert = false;
        }
        foreach($node->operands as $operand) {
            if ($this->search_repeated_assertions($operand)) {
                return true;
            }
        }
        $this->problem_id = -2;
        $this->problem_type = -2;
        return false;
    }



    /* The 2nd rule */
    protected function grouping_node() {
        $equivalences = array();

        if ($this->search_grouping_node($this->get_dst_root())) {
            $equivalences['problem'] = 'Пустая группировка "(?:)"';
            $equivalences['solve'] = 'Пустые скобки не влияют на работу регулярного выражения, их можно удалить';
            $equivalences['problem_id'] = $this->problem_id;
            $equivalences['problem_type'] = $this->problem_type;
        }

        return $equivalences;
    }

    private function search_grouping_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING) {
            if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                $this->problem_id = $node->id;
                $this->problem_type = 2;
                return true;
            }
        }
        foreach($node->operands as $operand) {
            if ($this->search_grouping_node($operand)) {
                return true;
            }
        }
        $this->problem_id = -2;
        $this->problem_type = -2;
        return false;
    }



    /* The 3rd rule */
    protected function subpattern_node() {
        $equivalences = array();

        if ($this->search_subpattern_node($this->get_dst_root())) {
            $equivalences['problem'] = 'Пустая подмаска "()"';
            $equivalences['solve'] = 'Пустые скобки не влияют на работу регулярного выражения, т.к. на них нет обратных ссылок или условных подмасок, их можно удалить';
            $equivalences['problem_id'] = $this->problem_id;
            $equivalences['problem_type'] = $this->problem_type;
        }

        return $equivalences;
    }

    private function search_subpattern_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
            if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                if (!$this->check_backref_to_subexpr($this->get_dst_root(), $node->number)) {
                    $this->problem_id = $node->id;
                    $this->problem_type = 3;
                    return true;
                }
            }
        }
        foreach($node->operands as $operand) {
            if ($this->search_subpattern_node($operand)) {
                return true;
            }
        }
        $this->problem_id = -2;
        $this->problem_type = -2;
        return false;
    }

    private function check_backref_to_subexpr($node, $number) {
        if ($node->type == qtype_preg_node::TYPE_LEAF_BACKREF && $node->subtype == qtype_preg_node::TYPE_LEAF_BACKREF && $node->number == $number) {
            return true;
        }
        foreach($node->operands as $operand) {
            if ($this->check_backref_to_subexpr($operand, $number)) {
                return true;
            }
        }
        return false;
    }



    /* The 5th rule */
    protected function single_charset_node() {
        $equivalences = array();

        if ($this->search_single_charset_node($this->get_dst_root())) {
            $equivalences['problem'] = 'Лишние квадратные скобки "[]"';
            $equivalences['solve'] = 'Квадратные скобки можно убрать, т.к. в них всего один символ';
            $equivalences['problem_id'] = $this->problem_id;
            $equivalences['problem_type'] = $this->problem_type;
        }

        return $equivalences;
    }

    private function search_single_charset_node($node) {
        if ($node->type == qtype_preg_node::TYPE_LEAF_CHARSET && $node->subtype == NULL) {
            if ($node->is_single_character() == true && $node->negative == false) {
                $this->problem_id = $node->id;
                $this->problem_type = 5;
                return true;
            }
        }
        foreach($node->operands as $operand) {
            if ($this->search_single_charset_node($operand)) {
                return true;
            }
        }
        $this->problem_id = -2;
        $this->problem_type = -2;
        return false;
    }


    //--- OPTIMIZATION ---
    public function optimization() {
        if ($this->options->problem_id != -2 && $this->options->problem_type != -2) {
            $function_name = ('optimize_' . $this->options->problem_type);
            return $this->$function_name($this->get_dst_root());
        }
        return false;
    }


    // The 1st rule
    protected function optimize_1($node) {
        return remove_subtree($node, $this->options->problem_id);
    }

    // The 2nd rule
    protected function optimize_2($node) {
        return remove_subtree($node, $this->options->problem_id);
    }

    // The 3rd rule
    protected function optimize_3($node) {
        return remove_subtree($node, $this->options->problem_id);
    }

    // The 5th rule
    protected function optimize_5($node) {
        return $this->remove_square_brackets_from_charset($node, $this->options->problem_id);
    }

    private function remove_subtree($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            return true;
        }

        foreach($node->operands as $i => $operand) {
            if ($this->remove_subtree($operand, $remove_node_id)) {
                array_splice($node->operands, $i, 1);
                return true;
            }
        }

        return false;
    }

    private function remove_square_brackets_from_charset($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            return true;
        }

        foreach($node->operands as $operand) {
            if ($this->remove_subtree($operand, $remove_node_id)) {
                array_shift($operand->userinscription);
                array_pop($operand->userinscription);
                return true;
            }
        }
    }
}