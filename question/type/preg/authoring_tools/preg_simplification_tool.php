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
    public $problem_ids = array();
    public $problem_type = -2;
    public $indfirst = -2;
    public $indlast = -2;
}

class qtype_preg_simplification_tool extends qtype_preg_authoring_tool {

    /** Array with root ids of problem subtree */
    private $problem_ids = array();
    /** Number type of problem */
    private $problem_type = -2;
    /** First index of something in regex string (absolute positioning). */
    private $indfirst = -2;
    /** Last index of something in regex string (absolute positioning). */
    private $indlast = -2;

    private $deleted_grouping_positions = array();
    private $deleted_subpattern_positions = array();

    private $regex_from_tree = '';

    // TODO: delete this
    private $problem_message = '';
    private $solve_message = '';

    private $is_subpattern_node_searched = false;

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

    /**
     * Overloaded from qtype_preg_authoring_tool.
     */
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
        $data['equivalences'] = $this->get_equivalences_description();
        $data['tips'] = $this->get_tips_description();

        return $data;
    }



    /**
     * Get array of errors in regex.
     */
    protected function get_errors_description() {
        $errors = array();
//        if ($this->options->is_check_errors == true) {
//            //to do something
//        }
        return $errors;

//        return array(array('problem' => 'Описание ошибки', 'solve' => 'Подробное описание информации'));
    }

    /**
     * Get array of tips in regex.
     */
    protected function get_tips_description() {
        $tips = array();

        if ($this->options->is_check_tips == true) {
            $i = 0;
            $result = $this->space_charset();
            if ($result != array()) {
                $tips[$i] = array();
                $tips[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->space_charset_without_quant();
            if ($result != array()) {
                $tips[$i] = array();
                $tips[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            if (!$this->is_subpattern_node_searched) {
                $result = $this->subpattern_without_backref();
                if ($result != array()) {
                    $tips[$i] = array();
                    $tips[$i] += $result;
                    ++$i;
                    $this->problem_ids = array();
                }
            }

            $result = $this->space_charset_with_finit_quant();
            if ($result != array()) {
                $tips[$i] = array();
                $tips[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->nullable_regex();
            if ($result != array()) {
                $tips[$i] = array();
                $tips[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->exact_match();
            if ($result != array()) {
                $tips[$i] = array();
                $tips[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }
        }
        return $tips;
    }

    /**
     * Get array of equivalences in regex.
     */
    protected function get_equivalences_description() {
        $equivalences = array();

        if ($this->options->is_check_equivalences == true) {
            $i = 0;
            $result = $this->repeated_assertions();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->grouping_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->subpattern_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
                $this->is_subpattern_node_searched = true;
            }

            $result = $this->single_charset_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->single_alternative_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

//            $result = $this->partial_match_alternative_operands();
//            if ($result != array()) {
//                $equivalences[$i] = array();
//                $equivalences[$i] += $result;
//                ++$i;
//                $this->problem_ids = array();
//            }

            $result = $this->quant_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->alt_without_question_quant();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            } /*else {
                $result = $this->nullable_alternative_node();
                if ($result != array()) {
                    $equivalences[$i] = array();
                    $equivalences[$i] += $result;
                    ++$i;
                    $this->problem_ids = array();
                }
            }*/

            $result = $this->alt_with_question_quant();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->quant_node_1_to_1();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->question_quant_for_alternative_node();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->consecutive_quant_nodes();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }

            $result = $this->common_subexpressions();
            if ($result != array()) {
                $equivalences[$i] = array();
                $equivalences[$i] += $result;
                ++$i;
                $this->problem_ids = array();
            }
        }

        return $equivalences;
    }



    /**
     * Check repeated assertions.
     */
    protected function repeated_assertions() {
        $equivalences = array();

        if ($this->search_repeated_assertions($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_1', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_1', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search repeated assertions in tree.
     */
    private function search_repeated_assertions($node) {
        if ($node->type == qtype_preg_node::TYPE_LEAF_ASSERT
            && ($node->subtype == qtype_preg_leaf_assert::SUBTYPE_CIRCUMFLEX || $node->subtype == qtype_preg_leaf_assert::SUBTYPE_DOLLAR)) {
            if ($this->is_find_assert == true) {
                $this->problem_ids[] = $node->id;
                $this->problem_type = 1;
                $this->indfirst = $node->position->indfirst;
                $this->indlast = $node->position->indlast;
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
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_repeated_assertions($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }



    /**
     * Check empty grouping node.
     */
    public function grouping_node() {
        $equivalences = array();

        if ($this->search_grouping_node($this->get_dst_root())) {
            $equivalences['problem'] = $this->problem_message;
            $equivalences['solve'] = $this->solve_message;
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search repeated assertions assertions in tree.
     */
    private function search_grouping_node($node) {
        if ($node !== null) {
            if ($this->search_not_empty_grouping_node($node)) {
                return true;
            }
            return $this->search_empty_grouping_node($node);
        }
        return false;
    }

    private function search_empty_grouping_node($node) {
        if ($node !== null) {
            if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
                && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING
            ) {
                if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META
                    && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY
                ) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 2;
                    $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_2', 'qtype_preg'));
                    $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_2', 'qtype_preg'));
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                } else {
                    if ($this->check_other_grouping_node($node->operands[0])) {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 2;
                        $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_2', 'qtype_preg'));
                        $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_2', 'qtype_preg'));
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    }
                }
            }
            if ($this->is_operator($node)) {
                foreach ($node->operands as $operand) {
                    if ($this->search_empty_grouping_node($operand)) {
                        return true;
                    }
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function search_not_empty_grouping_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING) {
            $parent = $this->get_parent_node($this->get_dst_root(), $node->id);
            if ($parent !== null) {
                $group_operand = $node->operands[0];
                if (/*$parent->type != qtype_preg_node::TYPE_NODE_CONCAT
                    &&*/ $parent->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
                    && $parent->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                    && $group_operand->type != qtype_preg_node::TYPE_LEAF_META
                    && $group_operand->subtype != qtype_preg_leaf_meta::SUBTYPE_EMPTY
                    && $group_operand->type != qtype_preg_node::TYPE_NODE_ALT) {

                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 2;
                        $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_2_1', 'qtype_preg'));
                        $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_2_1', 'qtype_preg'));
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                } else if (($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                            || $parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)
                          && $group_operand->type != qtype_preg_node::TYPE_NODE_CONCAT
                          && $group_operand->type != qtype_preg_node::TYPE_NODE_ALT
                          && $group_operand->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
                          && $group_operand->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 2;
                    $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_2_1', 'qtype_preg'));
                    $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_2_1', 'qtype_preg'));
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                }
            } else {
                if ($node->position != NULL) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 2;
                    $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_2_1', 'qtype_preg'));
                    $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_2_1', 'qtype_preg'));
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                }
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_not_empty_grouping_node($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    /**
     * Check included empty grouping node in empty grouping node.
     */
    private function check_other_grouping_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING) {
            if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META
                && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                return true;
            } else {
                return $this->check_other_grouping_node($node->operands[0]);
            }
        }
        return false;
    }



    /**
     * Check empty subpattern node.
     */
    public function subpattern_node() {
        $equivalences = array();

        if ($this->search_subpattern_node($this->get_dst_root())) {
            $equivalences['problem'] = $this->problem_message;
            $equivalences['solve'] = $this->solve_message;
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search empty subpattern node.
     */
    private function search_subpattern_node($node) {
        if ($node !== null) {
            if ($this->search_not_empty_subpattern_node($node)) {
                return true;
            }
            return $this->search_empty_subpattern_node($node);
        }
        return false;
    }

    private function search_empty_subpattern_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
            if (!$this->check_backref_to_subexpr($this->get_dst_root(), $node->number)) {
                if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META
                    && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY
                ) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 3;
                    $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_3', 'qtype_preg'));
                    $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_3', 'qtype_preg'));
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                } /*else {
                    if ($this->check_other_subpattern_node($node->operands[0])) {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 3;
                        $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_3', 'qtype_preg'));
                        $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_3', 'qtype_preg'));
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    }
                }*/
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_empty_subpattern_node($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function search_not_empty_subpattern_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
            if (!$this->check_backref_to_subexpr($this->get_dst_root(), $node->number)) {
                $parent = $this->get_parent_node($this->get_dst_root(), $node->id);
                if ($parent !== null) {
                    $group_operand = $node->operands[0];
                    if ($parent->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        && $parent->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                        && $group_operand->type != qtype_preg_node::TYPE_LEAF_META
                        && $group_operand->subtype != qtype_preg_leaf_meta::SUBTYPE_EMPTY
                        && $group_operand->type != qtype_preg_node::TYPE_NODE_ALT
                    ) {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 3;
                        $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_3_1', 'qtype_preg'));
                        $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_3_1', 'qtype_preg'));
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    } else if (($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                                || $parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)
                               && $group_operand->type != qtype_preg_node::TYPE_NODE_CONCAT
                               && $group_operand->type != qtype_preg_node::TYPE_NODE_ALT
                               && $group_operand->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
                               && $group_operand->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                    ) {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 3;
                        $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_3_1', 'qtype_preg'));
                        $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_3_1', 'qtype_preg'));
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    }
                } else {
                    if ($node->position != NULL) {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 3;
                        $this->problem_message = htmlspecialchars(get_string('simplification_equivalences_short_3_1', 'qtype_preg'));
                        $this->solve_message = htmlspecialchars(get_string('simplification_equivalences_full_3_1', 'qtype_preg'));
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    }
                }
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_not_empty_subpattern_node($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    /**
     * Check backreference to subexpression.
     */
    private function check_backref_to_subexpr($node, $number) {
        if (($node->type == qtype_preg_node::TYPE_LEAF_BACKREF
             && $node->subtype == qtype_preg_node::TYPE_LEAF_BACKREF && $node->number == $number)
            || ($node->type == qtype_preg_node::TYPE_NODE_COND_SUBEXPR
                && $node->subtype == qtype_preg_node_cond_subexpr::SUBTYPE_SUBEXPR && $node->number == $number)) {
            return true;
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->check_backref_to_subexpr($operand, $number)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check included empty subpattern node in empty subpattern node.
     */
    private function check_other_subpattern_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
            if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META
                && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                if (!$this->check_backref_to_subexpr($this->get_dst_root(), $node->number)) {
                    return true;
                }
            } else {
                return $this->check_other_subpattern_node($node->operands[0]);
            }
        }
        return false;
    }



    /**
     * Check common subexpressions in tree.
     */
    public function common_subexpressions() {
        $equivalences = array();

        if ($this->search_common_subexpressions($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_4', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_4', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search common subexpressions in tree.
     */
    private function search_common_subexpressions($tree_root, &$leafs = null) {
        if ($leafs == NULL) {
            $leafs = array();
            $leafs[0] = array();
            array_push($leafs[0], $tree_root);

            $this->normalization($tree_root);
        }

        if ($tree_root !== null) {
            if ($this->is_operator($tree_root)) {
                foreach ($tree_root->operands as $operand) {
                    if ($this->search_subexpr($leafs, $operand, $tree_root)) {
                        return true;
                    }
                    $leafs[count($leafs)] = array();
                    for ($i = 0; $i < count($leafs); $i++) {
                        array_push($leafs[$i], $operand);
                    }

                    if ($this->search_common_subexpressions($operand, $leafs)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Search suitable common subexpressions in tree.
     */
    private function search_subexpr($leafs, $current_leaf, $tree_root) {
        foreach ($leafs as $leaf) {
            if ($leaf[0]->is_equal($current_leaf, null) /*|| $this->compare_quants($leaf[0], $current_leaf)*/) {
                $count_nodes = 0;
                $tmp_leafs = $this->delete_useless_nodes($current_leaf, $leaf, $count_nodes);

                $tmp_root = $this->get_parent_node($this->get_dst_root(), $leaf[0]->id);

                if ($this->compare_parent_nodes($tmp_root, $tree_root, $count_nodes)) {
                    if ($this->compare_right_set_of_leafs(/*$leaf*/$tmp_leafs, $current_leaf, $tree_root, $count_nodes)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function delete_useless_nodes($current_leaf, $leaf, &$count_nodes, $operand = null) {
        $parent = $this->get_parent_node($this->get_dst_root(), $current_leaf->id);

        $count_nodes = count($leaf);

        $tmp_leafs = null;
        if ($parent != null && $leaf[$count_nodes - 1]->id == $parent->id
            && (($parent->type == qtype_preg_node::TYPE_NODE_SUBEXPR
                 && $parent->operands[0]->type == qtype_preg_node::TYPE_NODE_CONCAT)
                || (($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                    || $parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)
                    /*&& $parent->operands[0]->type != qtype_preg_node::TYPE_NODE_SUBEXPR*/)
                || $parent->type == qtype_preg_node::TYPE_NODE_CONCAT)) {
            $count_nodes--;

            $tmp_leafs = array_slice($leaf, 0, $count_nodes);

            if ($operand === null && isset($leaf[$count_nodes - 1]->operands)) {
                $operand = $current_leaf;
            }

            if ($operand !== null) {
                if ($operand->position->indfirst > $leaf[$count_nodes - 1]->position->indfirst) {
                    $operand->position->indfirst = $leaf[$count_nodes - 1]->position->indfirst;
                }

                if ($operand->position->indlast < $leaf[$count_nodes - 1]->position->indlast) {
                    $operand->position->indlast = $leaf[$count_nodes - 1]->position->indlast;
                }
            }

            $tmp_leafs = $this->delete_useless_nodes($parent, $tmp_leafs, $count_nodes, $operand);
        } else {
            $tmp_leafs = $leaf;
        }

        return $tmp_leafs;
    }

    // TODO
    private function compare_quants($quant1, $quant2) {
        if (($quant1->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
             || $quant1->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)
            && ($quant2->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
             || $quant2->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)) {
            return true;
        }
        return false;
    }


    private function get_next1($tree_root, $next_leaf, $count1, $is_fount2) {
        $right_leafs = null;
        if (($next_leaf->type == qtype_preg_node::TYPE_NODE_SUBEXPR && $next_leaf->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING)
            || $next_leaf->type == qtype_preg_node::TYPE_NODE_CONCAT) {
            $next_leaf->operands[0]->position->indfirst = $next_leaf->position->indfirst;
            $next_leaf->operands[0]->position->indlast = $next_leaf->position->indlast;
            $right_leafs = $this->get_next1($tree_root, $next_leaf->operands[0], $count1, $is_fount2);
        } else {
            $right_leafs = $this->get_next_right_leafs($tree_root, $next_leaf, $count1, $is_fount2);
        }
        return $right_leafs;
    }

    /**
     * Trying to get a set of equivalent $leafs nodes from $current_leaf.
     */
    private function compare_right_set_of_leafs($leafs, $current_leaf, $tree_root, $count_nodes) {
        $is_found = false;
        $right_leafs = $this->get_right_leafs($this->get_dst_root(), $current_leaf, count($leafs), $is_found);
        $right_leafs_tmp = $right_leafs;

        if ($this->leafs_compare($leafs, $right_leafs)) {
            if ($leafs[0]->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                || $leafs[0]->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {

                $node_counts = 0;
                $this->get_subtree_nodes_count($leafs[0], $node_counts);

                if ($node_counts < count($leafs)) {
                    $this->problem_ids[] = count($leafs);//count($leafs);//length
                } else {
                    $this->problem_ids[] = count($leafs) - 1;
                }
            } else {
                $this->problem_ids[] = $count_nodes;//count($leafs);//length
            }

//            $this->problem_ids[] = count($leafs);

            $this->problem_ids[] = $leafs[0]->id;
            $is_found = true;
            while ($is_found) {
                $this->problem_ids[] = $right_leafs_tmp[0]->id;

                $right_leafs_tmp = $right_leafs;
                $is_fount1 = false;

                $next_leafs = $this->get_right_leafs($this->get_dst_root(),
                                                     $right_leafs_tmp[count($right_leafs_tmp) - 1], 2, $is_fount1);

                $next_leaf = null;
                if (count($next_leafs) > 1) {
                    $next_leaf = $next_leafs[1];
                }

                if ($next_leaf != null
                    && ($next_leaf->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        || $next_leaf->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)) {

                    $parent = $this->get_parent_node($this->get_dst_root(), $leafs[0]->id);
                    $parent_curcur = $this->get_parent_node($this->get_dst_root(), $next_leaf->id);
                    if ($parent != null && $parent_curcur != null && $parent_curcur->id == $parent->id) {
                        $is_fount2 = false;
//                        $right_leafs = $this->get_next_right_leafs($this->get_dst_root()/*$tree_root*/, $next_leaf->operands[0], count($leafs), $is_fount2);
                        $next_leaf->operands[0]->position->indfirst = $next_leaf->position->indfirst;
                        $next_leaf->operands[0]->position->indlast = $next_leaf->position->indlast;
                        $right_leafs = $this->get_next1($this->get_dst_root(),
                                                        $next_leaf->operands[0], count($leafs), $is_fount2);
                    } else {
                        if ($parent != null
                            && ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                                || $parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)) {

                            $parent_cur = $this->get_parent_node($this->get_dst_root(), $parent->id);

                            if ($parent_cur != null && $parent_curcur != null && $parent_curcur->id == $parent_cur->id) {
                                $is_fount2 = false;
                                $right_leafs = $this->get_next_right_leafs($this->get_dst_root(),
                                                                           $next_leaf->operands[0], count($leafs), $is_fount2);
                            } else {
                                $right_leafs = array();
                            }
                        } else {
                            $right_leafs = array();
//                          $is_fount2 = false;
//                          $right_leafs = $this->get_next_right_leafs($this->get_dst_root()/*$tree_root*/, $next_leaf->operands[0], count($leafs), $is_fount2);
                        }
                    }
                } else {
                    $is_fount2 = false;
                    $right_leafs = $this->get_right_leafs($this->get_dst_root(),
                                                          $next_leaf, /*count($leafs)*/$this->problem_ids[0], $is_fount2);
                }

                $this->get_subexpression_regex_position_for_nodes($leafs, $right_leafs_tmp);

                $right_leafs_tmp = $right_leafs;

                $is_found = $this->leafs_compare($leafs, $right_leafs);
                //$is_found = false;
            }
            $this->problem_type = 4;
            return true;
        }

        return false;
    }

    /**
     * Get parent of node.
     * TODO: delete
     */
    private function get_parent_node($tree_root, $node_id) {
        $local_root = null;
        /*if ($tree_root->id == $node_id) {
            return $tree_root;
        }*/
        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                if ($operand->id == $node_id) {
                    return $tree_root;
                }
                $local_root = $this->get_parent_node($operand, $node_id);
                if ($local_root !== null) {
                    return $local_root;
                }
            }
        }
        return $local_root;
    }

    /**
     * Trying to get a set of equivalent $leafs nodes from $current_leaf.
     * TODO: get N nodes from subtree where $current_leaf is root
     */
    private function get_right_leafs($tree_root, $current_leaf, $size, &$is_found, &$leafs = null) {
        if ($current_leaf == NULL) {
            return array();
        }
        if ($leafs == NULL) {
            $leafs = array();
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                if ($current_leaf->id == $operand->id) {
                    $is_found = true;
                }

                if ($is_found && count($leafs) < $size) {
                    array_push($leafs, $operand);
                    if (count($leafs) >= $size) {
                        return $leafs;
                    }
                }

                $this->get_right_leafs($operand, $current_leaf, $size, $is_found, $leafs);
            }
        }

        return $leafs;
    }

    private function get_next_right_leafs($tree_root, $current_leaf, $size, &$is_found, &$leafs = null) {
        if ($current_leaf == NULL) {
            return array();
        }
        if ($leafs == NULL) {
            $leafs = array();
        }

        if ($current_leaf->id == $tree_root->id){
            $is_found = false;
            $leafs = $this->get_right_leafs($this->get_parent_node($this->get_dst_root(), $tree_root->id), $current_leaf, $size, $is_found, $leafs);
            return $leafs;
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                $this->get_next_right_leafs($operand, $current_leaf, $size, $is_found, $leafs);
            }
        }

        return $leafs;
    }

    /**
     * Compare two arrays with nodes
     */
    private function leafs_compare($leafs1, $leafs2) {
        if (count($leafs1) > 0) {
            if ($leafs1[0]->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                || $leafs1[0]->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
            ) {
                $leafs1 = array_slice($leafs1, 1, count($leafs1));
            }
        }

        if (count($leafs2) > 0) {
            if ($leafs2[0]->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                || $leafs2[0]->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
            ) {
                $leafs2 = array_slice($leafs2, 1, count($leafs2));
            }
        }

        if (count($leafs1) != count($leafs2)) {
            return false;
        }

        for ($i = 0; $i < count($leafs1); $i++) {
            if (!$leafs1[$i]->is_equal($leafs2[$i], null)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Compare two nodes who are parents
     */
    private function compare_parent_nodes($local_root1, $local_root2, $count_leafs) {
        if ($local_root1 != null && $local_root2 != null) {
            if ($local_root1->is_equal($local_root2, null)) {
                //return $this->is_can_parent_node($local_root1) && $this->is_can_parent_node($local_root2);
                return true;
            } else if (($local_root1->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        || $local_root1->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)
                       && ($local_root2->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        || $local_root2->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)) {
                return $this->compare_quants($local_root1, $local_root2);
            } else if (($local_root1->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        || $local_root1->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)
                       && ($local_root2->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        || $local_root2->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT)) {
                $new_local_root1 = $this->get_parent_node($this->get_dst_root(), $local_root1->id);
                if ($new_local_root1 != null && $count_leafs == 1) {
                    return $this->compare_parent_nodes($new_local_root1, $local_root2, $count_leafs);
                } else {
                    return false;
                }
            } else if (($local_root1->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        || $local_root1->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT)
                       && ($local_root2->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        || $local_root2->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)) {
                $new_local_root2 = $this->get_parent_node($this->get_dst_root(), $local_root2->id);
                if ($new_local_root2 != null && $count_leafs == 1) {
                    return $this->compare_parent_nodes($local_root1, $new_local_root2, $count_leafs);
                } else {
                    return false;
                }
            } else if ($local_root1->type == qtype_preg_node::TYPE_NODE_CONCAT
                        && $local_root2->type == qtype_preg_node::TYPE_NODE_CONCAT
                        && count($local_root1->operands) > 1 && count($local_root2->operands) > 1) {
                return $this->compare_concats($local_root1, $local_root2);
            } else {
                return true;
            }
        }
        return false;
    }

    // TODO: delete
    private function compare_concats($node1, $node2) {
        if (is_a($node1, get_class($node2)) // subclass?
                && $node1->type == $node2->type
                && $node1->subtype == $node2->subtype) {
            if (count($node1->operands) < count($node2->operands)) {
                $is_match = true;
                foreach ($node1->operands as $i => $operand) {
                    if ($operand->is_equal($node2->operands[$i], null) === false) {
                        $is_match = false;
                        break;
                    }
                }

                if ($is_match === false) {
                    $is_match = true;
                    for ($j = 0, $i = count($node1->operands) - 1; $i > -1; $i--) {
                        $j++;
                        if ($node1->operands[$i]->is_equal($node2->operands[count($node2->operands) - $j], null) === false) {
                            $is_match = false;
                            break;
                        }
                    }
                }

                return $is_match;
            } else {
                $is_match = true;
                foreach ($node2->operands as $i => $operand) {
                    if ($operand->is_equal($node1->operands[$i], null) === false) {
                        $is_match = false;
                        break;
                    }
                }

                if ($is_match === false) {
                    $is_match = true;
                    for ($j = 0, $i = count($node2->operands) - 1; $i > -1; $i--) {
                        $j++;
                        if ($node2->operands[$i]->is_equal($node1->operands[count($node1->operands) - $j], null) === false) {
                            $is_match = false;
                            break;
                        }
                    }
                }

                return $is_match;
            }

        } else {
            return false;
        }
        return true;
    }

    /**
     * Whether the node is a parent for common sybexpression
     */
    private function is_can_parent_node($local_root) {
        if ($local_root !== null) {
            return !($local_root->type == qtype_preg_node::TYPE_NODE_ALT);
        }
        return true;
    }

    /**
     * Whether the node is operator
     */
    private function is_operator($node) {
        return !($node->type == qtype_preg_node::TYPE_LEAF_CHARSET
            || $node->type == qtype_preg_node::TYPE_LEAF_ASSERT
            || $node->type == qtype_preg_node::TYPE_LEAF_META
            || $node->type == qtype_preg_node::TYPE_LEAF_BACKREF
            || $node->type == qtype_preg_node::TYPE_LEAF_SUBEXPR_CALL
            || $node->type == qtype_preg_node::TYPE_LEAF_TEMPLATE
            || $node->type == qtype_preg_node::TYPE_LEAF_CONTROL
            || $node->type == qtype_preg_node::TYPE_LEAF_OPTIONS
            || $node->type == qtype_preg_node::TYPE_LEAF_COMPLEX_ASSERT);
    }

    /**
     * Find and sort leafs for associative-commutative operators
     */
    private function associative_commutative_operator_sort($tree_root){
        if ($tree_root !== null) {
            if ($this->is_associative_commutative_operator($tree_root)) {
                for ($j = 0; $j < count($tree_root->operands) - 1; $j++) {
                    for ($i = 0; $i < count($tree_root->operands) - $j - 1; $i++) {
                        if ($tree_root->operands[$i]->get_regex_string() > $tree_root->operands[$i + 1]->get_regex_string()) {
                            $b = $tree_root->operands[$i];
                            $tree_root->operands[$i] = $tree_root->operands[$i + 1];
                            $tree_root->operands[$i + 1] = $b;
                        }
                    }
                }
            }

            if ($this->is_operator($tree_root)) {
                foreach ($tree_root->operands as $operand) {
                    $this->associative_commutative_operator_sort($operand);
                }
            }
        }
    }

    /**
     * Whether the node is associative commutative operator
     */
    private function is_associative_commutative_operator($node) {
        return $node->type == qtype_preg_node::TYPE_NODE_ALT;
    }

    /**
     * Tree normalization
     */
    protected function normalization($tree_root) {
        $this->deleted_grouping_positions = array();
        $this->delete_not_empty_grouping_node($tree_root, $tree_root);

        $problem_exist = true;
        while($problem_exist) {
            if ($this->search_single_charset_node($tree_root)) {
                $this->remove_square_brackets_from_charset($tree_root, $this->problem_ids[0]);
            } else {
                $problem_exist = false;
            }
            $this->problem_ids = array();
        }

        $problem_exist = true;
        while($problem_exist) {
            if ($this->search_empty_grouping_node($tree_root)) {
                $this->delete_empty_groping_node($tree_root, $tree_root, $this->problem_ids[0]);
            } else {
                $problem_exist = false;
            }
            $this->problem_ids = array();
        }

        $problem_exist = true;
        $count = 0;
        while($problem_exist && $count < 999) {
            if ($this->search_not_empty_subpattern_node($tree_root)) {
                $this->remove_subpattern_node($tree_root, $tree_root, $this->problem_ids[0]);
                $this->rename_backreferences_for_subpattern($tree_root, $tree_root);
                $count++;
            } else {
                $problem_exist = false;
            }
            $this->problem_ids = array();
        }

        $problem_exist = true;
        $count = 0;
        while($problem_exist && $count < 999) {
            if ($this->search_single_not_repeat_alternative_node($tree_root)) {
                $this->change_alternative_to_charset($tree_root, $this->problem_ids[0]);
                $count++;
            } else {
                $problem_exist = false;
            }
            $this->problem_ids = array();
        }

        //$this->delete_not_empty_grouping_node($tree_root, $tree_root);

        $this->associative_commutative_operator_sort($tree_root);

        $this->problem_ids = array();
    }

    /**
     * Search alternative node with only charsets operands with one character
     */
    private function search_single_not_repeat_alternative_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_ALT) {
            if ($this->is_single_not_repeat_alternative($node)) {
                $this->problem_ids[] = $node->id;
                return true;
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_single_not_repeat_alternative_node($operand)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check found alternative node with only charsets operands with one character
     */
    private function is_single_not_repeat_alternative($node) {
        $repeats_count = 0;
        foreach ($node->operands as $i => $operand) {
            if ($operand->type == qtype_preg_node::TYPE_LEAF_CHARSET && !$operand->negative
                && $operand->userinscription[0]->data != '.') {

                foreach ($node->operands as $j => $tmpoperand) {
                    if ($i !== $j && $tmpoperand->is_equal($operand, null)) {
                        return false;
                    }
                }

                $repeats_count++;
            }
        }
        return $repeats_count > 1;
    }

    // TODO: delete
    private function delete_empty_groping_node($tree_root, $node, $remove_node_id) {
        if ($tree_root != null) {
            if ($node->id == $remove_node_id) {
                if ($node->id == $tree_root->id) {
                    $tree_root = null;
                }
                return true;
            }

            if ($this->is_operator($node)) {
                foreach ($node->operands as $i => $operand) {
                    if ($this->delete_empty_groping_node($tree_root, $operand, $remove_node_id)) {
                        if (count($node->operands) === 1) {
                            return $this->delete_empty_groping_node($tree_root, $tree_root, $node->id);
                        }

                        array_splice($node->operands, $i, 1);
                        if ($this->is_associative_commutative_operator($node) && count($node->operands) < 2) {
                            $node->operands[] = new qtype_preg_leaf_meta(qtype_preg_leaf_meta::SUBTYPE_EMPTY);
                        }

                        return false;
                    }
                }
            }
        }

        return false;
    }

    private function delete_not_empty_grouping_node($tree_root, $node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING) {
            $parent = $this->get_parent_node($tree_root, $node->id);
            $group_operand = $node->operands[0];
            if ($parent !== null) {
                if ($node->operands[0]->type !== qtype_preg_node::TYPE_LEAF_META
                    && $node->operands[0]->subtype !== qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                    if (/*$parent->type != qtype_preg_node::TYPE_NODE_CONCAT
                    &&*/
                        $parent->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        && $parent->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                        && $group_operand->type != qtype_preg_node::TYPE_LEAF_META
                        && $group_operand->subtype != qtype_preg_leaf_meta::SUBTYPE_EMPTY
                        && $group_operand->type != qtype_preg_node::TYPE_NODE_ALT
                        && $group_operand->id != -1
                    ) {

                        $group_operand->position->indfirst = $node->position->indfirst;
                        $group_operand->position->indlast = $node->position->indlast;

                        $this->deleted_grouping_positions[] = array($node->position->indfirst, $node->position->indlast);

                        foreach ($parent->operands as $i => $operand) {
                            if ($operand->id == $node->id) {
                                if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT
                                    && $group_operand->type == qtype_preg_node::TYPE_NODE_CONCAT
                                ) {
                                    //$group_operand->operands[0]->position->indfirst = $group_operand->position->indfirst;
                                    //$group_operand->operands[count($group_operand->operands) - 1]->position->indlast = $group_operand->position->indlast;

                                    $parent->operands = array_merge(array_slice($parent->operands, 0, $i),
                                        $group_operand->operands,
                                        array_slice($parent->operands, $i + 1));
                                } else {
                                    $parent->operands[$i] = $group_operand;
                                }
                                break;
                            }
                        }
                    } else if (($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                            || $parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)
                        && $group_operand->type != qtype_preg_node::TYPE_NODE_CONCAT
                        && $group_operand->type != qtype_preg_node::TYPE_NODE_ALT
                        && $group_operand->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        && $group_operand->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                    ) {

                        if ($node->position !== null) {
                            $group_operand->position->indfirst = $node->position->indfirst;
                            $group_operand->position->indlast = $node->position->indlast;

                            $this->deleted_grouping_positions[] = array($node->position->indfirst, $node->position->indlast);
                        }

                        foreach ($parent->operands as $i => $operand) {
                            if ($operand->id == $node->id) {
                                if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT
                                    && $group_operand->type == qtype_preg_node::TYPE_NODE_CONCAT
                                ) {
                                    //$group_operand->operands[0]->position->indfirst = $group_operand->position->indfirst;
                                    //$group_operand->operands[count($group_operand->operands) - 1]->position->indlast = $group_operand->position->indlast;

                                    $parent->operands = array_merge(array_slice($parent->operands, 0, $i),
                                        $group_operand->operands,
                                        array_slice($parent->operands, $i + 1));
                                } else {
                                    $parent->operands[$i] = $group_operand;
                                }
                                break;
                            }
                        }
                    } else if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT
                        && $group_operand->type != qtype_preg_node::TYPE_LEAF_CHARSET) {

                        $group_operand->position->indfirst = $node->position->indfirst;
                        $group_operand->position->indlast = $node->position->indlast;

                        $this->deleted_grouping_positions[] = array($node->position->indfirst, $node->position->indlast);

                        foreach ($parent->operands as $i => $operand) {
                            if ($operand->id == $node->id) {
                                if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT
                                    && $group_operand->type == qtype_preg_node::TYPE_NODE_CONCAT
                                ) {
                                    //$group_operand->operands[0]->position->indfirst = $group_operand->position->indfirst;
                                    //$group_operand->operands[count($group_operand->operands) - 1]->position->indlast = $group_operand->position->indlast;

                                    $parent->operands = array_merge(array_slice($parent->operands, 0, $i),
                                        $group_operand->operands,
                                        array_slice($parent->operands, $i + 1));
                                } else {
                                    $parent->operands[$i] = $group_operand;
                                }
                                break;
                            }
                        }
                    }
                }
            } else {
                /*$group_operand->position->indfirst = $node->position->indfirst;
                $group_operand->position->indlast = $node->position->indlast;*/
                $tree_root = $group_operand;
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                $this->delete_not_empty_grouping_node($tree_root, $operand);
            }
        }
    }



    /**
     * Elimination of common subexpressions
     */
    private function fold_common_subexpressions($tree_root) {
        if ($tree_root->id != $this->options->problem_ids[1]) {
            if ($this->is_operator($tree_root)) {
                foreach ($tree_root->operands as $operand) {
                    if ($operand->id == $this->options->problem_ids[1]) {
                        $this->tree_folding($operand, $tree_root);
                        return true;
                    }
                    if ($this->fold_common_subexpressions($operand)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Generate new fixed regex
     */
    private function tree_folding($current_leaf, $parent_node) {
        // Old regex string
        $regex_string = $this->get_regex_string();

        // Calculate quantifier borders
        $counts = $this->subexpressions_repeats($parent_node, $current_leaf);

        // Create new quantifier with needed borders
        $text = $this->get_quant_text_from_borders($counts[0], $counts[1]);

        // New part of regex string
        $new_regex_string_part = '';

        if ($text !== '') {
            $qu = new qtype_preg_node_finite_quant($counts[0], $counts[1]);
            $qu->set_user_info(null, array(new qtype_preg_userinscription($text)));

            // Current operand is operand of quantifier node
            if (/*$this->options->problem_ids[0] > 1 &&*/ $current_leaf->type != qtype_preg_node::TYPE_NODE_SUBEXPR) {
                $se = new qtype_preg_node_subexpr(qtype_preg_node_subexpr::SUBTYPE_GROUPING, -1, '', false);
                $se->set_user_info(null, array(new qtype_preg_userinscription('(?:...)')));

                // Add needed nodes to grouping node
                if (!$this->is_operator($current_leaf)) {
                    // Get right leafs from current node
                    $is_fount = false;
                    $right_leafs = $this->get_right_leafs($this->get_dst_root(), $current_leaf, $this->options->problem_ids[0], $is_fount);

                    // Add this nodes while node is not operator
                    if (count($right_leafs) > 1) {
                        $concat = new qtype_preg_node_concat();

                        for ($i = 0; $i < $this->options->problem_ids[0];) {
                            $concat->operands[] = $right_leafs[$i];

                            $count_nodes = 0;
                            $this->get_subtree_nodes_count($right_leafs[$i], $count_nodes);
                            $i += $count_nodes;
                        }

                        $se->operands[] = $concat;
                    } else {
                        //$se->operands[] = $right_leafs[0];
                        if ($right_leafs[0]->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                            || $current_leaf->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                            $se->operands[] = $right_leafs[0]->operands[0];
                        } else {
                            $se->operands[] = $right_leafs[0];
                        }
                    }
                } else {
                    $is_fount = false;
                    $right_leafs = $this->get_right_leafs($this->get_dst_root(), $current_leaf, $this->options->problem_ids[0], $is_fount);

                    if (count($right_leafs) > 1) {
                        for ($i = 0; $i < $this->options->problem_ids[0];) {
                            $se->operands[] = $right_leafs[$i];

                            $count_nodes = 0;
                            $this->get_subtree_nodes_count($right_leafs[$i], $count_nodes);
                            $i += $count_nodes;
                        }
                    } else {
                        //$se->operands[] = $right_leafs[0];
                        if ($right_leafs[0]->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                            || $current_leaf->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                            $se->operands[] = $right_leafs[0]->operands[0];
                        } else {
                            $se->operands[] = $right_leafs[0];
                        }
                    }
                }

                $qu->operands[] = $se;
            } else {
                if ($current_leaf->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                    || $current_leaf->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                    $qu->operands[] = $current_leaf->operands[0];
                } else {
                    $qu->operands[] = $current_leaf;
                }
            }

            $this->normalization($qu/*->operands[0]*/);
            $new_regex_string_part = $qu->get_regex_string();
        } else {
            $this->normalization($current_leaf);
            $new_regex_string_part = $current_leaf->get_regex_string();
        }

        // New fixed regex
        // Delete ')' for deleted "(?:)"
        $tmp_dst_root = $this->get_dst_root();

        // Rename all backreference
        $this->rename_all_backreference($tmp_dst_root, $regex_string);

        $this->normalization($tmp_dst_root);

        foreach($this->deleted_grouping_positions as $deleted_grouping_position) {
            if ($deleted_grouping_position[1] > $this->options->indlast
                && $deleted_grouping_position[0] < $this->options->indlast
                && $deleted_grouping_position[0] > $this->options->indfirst) {
                $regex_string = substr($regex_string, 0, $deleted_grouping_position[1])
                                . substr($regex_string, $deleted_grouping_position[1] + 1);
            }
        }

        foreach($this->deleted_subpattern_positions as $deleted_grouping_position) {
            if ($deleted_grouping_position[1] > $this->options->indlast
                && $deleted_grouping_position[0] < $this->options->indlast
                && $deleted_grouping_position[0] > $this->options->indfirst) {
                $regex_string = substr($regex_string, 0, $deleted_grouping_position[1])
                                . substr($regex_string, $deleted_grouping_position[1] + 1);
            }
        }

        //$old_regex = $regex_string;

        // Generate new regex
        $this->regex_from_tree = substr_replace($regex_string, $new_regex_string_part, $this->options->indfirst,
                                                $this->options->indlast - $this->options->indfirst + 1);

        // Delete '(?:' for deleted "(?:)"
        foreach($this->deleted_grouping_positions as $deleted_grouping_position) {
            if ($deleted_grouping_position[0] < $this->options->indfirst
                && $deleted_grouping_position[1] > $this->options->indfirst
                && $deleted_grouping_position[1] < $this->options->indlast) {
                $this->regex_from_tree = substr($this->regex_from_tree, 0, $deleted_grouping_position[0])
                                         . substr($this->regex_from_tree, $deleted_grouping_position[0] + 3);
            }
        }

        foreach($this->deleted_subpattern_positions as $deleted_grouping_position) {
            if ($deleted_grouping_position[0] < $this->options->indfirst
                && $deleted_grouping_position[1] > $this->options->indfirst
                && $deleted_grouping_position[1] < $this->options->indlast) {
                $this->regex_from_tree = substr($this->regex_from_tree, 0, $deleted_grouping_position[0])
                                         . substr($this->regex_from_tree, $deleted_grouping_position[0] + 1);
            }
        }

        $tmp_regex = substr($this->regex_from_tree, $this->options->indfirst - 3, 3);

//        var_dump($regex_string);

        /*$flag = (strlen($regex_string) <= $this->options->indlast + 1
            || $regex_string[$this->options->indlast + 1] !== ')');*/

//        var_dump($flag);



        if ($tmp_regex === '(?:'
            && substr_count($this->regex_from_tree, '(') !== substr_count($this->regex_from_tree, ')')) {
            $this->regex_from_tree = substr($this->regex_from_tree, 0, $this->options->indfirst - 3)
                . substr($this->regex_from_tree, $this->options->indfirst);
        }

        return $this->regex_from_tree;
    }

    private function rename_backref_in_regex($tree_root, &$regex_string) {
        if ($tree_root->type == qtype_preg_node::TYPE_LEAF_BACKREF) {
            $regex_string = substr_replace($regex_string, $tree_root->get_regex_string(), $tree_root->position->indfirst,
                $tree_root->position->indlast - $tree_root->position->indfirst + 1);
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                $this->rename_backref_in_regex($operand, $regex_string);
            }
        }
    }

    private function rename_all_backreference($tree_root, &$regex_string, &$backrefnumb = 0) {
        if ($tree_root->type == qtype_preg_node::TYPE_NODE_SUBEXPR && $tree_root->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
            /*if (($tree_root->position->indfirst < $this->options->indfirst && $tree_root->position->indlast < $this->options->indfirst)
               || ($tree_root->position->indfirst > $this->options->indlast && $tree_root->position->indlast > $this->options->indlast)
               || ($tree_root->position->indfirst < $this->options->indlast && $tree_root->position->indlast > $this->options->indlast)
               /*|| ($tree_root->position->indfirst > $this->options->indlast && $tree_root->position->indlast < $this->options->indlast)*) {*/
            if (!($tree_root->position->indlast > $this->options->indlast
                && $tree_root->position->indfirst < $this->options->indlast
                && $tree_root->position->indfirst > $this->options->indfirst)
                && !($tree_root->position->indfirst < $this->options->indfirst
                    && $tree_root->position->indlast > $this->options->indfirst
                    && $tree_root->position->indlast < $this->options->indlast)
                && !(($tree_root->position->indfirst > $this->options->indfirst
                    && $tree_root->position->indlast < $this->options->indlast))) {
                ++$backrefnumb;
                if ($tree_root->number !== $backrefnumb) {
                    // Rename all backref, linked with this subexpr
                    $this->rename_backref1($this->get_dst_root(), $regex_string, $tree_root->number, $backrefnumb);
                    $tree_root->number = $backrefnumb;
                }
            } else {
                $this->deleted_subpattern_positions[] = array($tree_root->position->indfirst, $tree_root->position->indlast);
            }
        }
        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                $this->rename_all_backreference($operand, $regex_string, $backrefnumb);
            }
        }
    }

    private function rename_backref1($tree_root, &$regex_string, $oldbackrefnumb, $newbackrefnumb) {
        if ($tree_root->type == qtype_preg_node::TYPE_LEAF_BACKREF && $tree_root->number === $oldbackrefnumb) {
            $tree_root->number = $newbackrefnumb;
            $regex_string = substr_replace($regex_string, "\\{$newbackrefnumb}", $tree_root->position->indfirst,
                $tree_root->position->indlast - $tree_root->position->indfirst + 1);
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                $this->rename_backref1($operand, $regex_string, $oldbackrefnumb, $newbackrefnumb);
            }
        }
    }

    private function get_subtree_nodes_count($node, &$count_operands) {
        $count_operands++;
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                $this->get_subtree_nodes_count($operand, $count_operands);
            }
        }
    }


    private function get_current_node_repeats($current_root, $node, $current_problem_id) {
        $counts = array(0,0);
        $node = $this->get_node_from_id($this->get_dst_root(), $current_problem_id);
        if ($node != null) {
            if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                $node_counts = 0;
                $this->get_subtree_nodes_count($node, $node_counts);
                if ($node_counts < $this->options->problem_ids[0]) {
                    $counts[0] += 1;
                    $counts[1] += 1;
                } else {
                    $counts[0] += $node->leftborder;
                    $counts[1] += $node->rightborder;
                }
            } else if ($node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                $node_counts = 0;
                $this->get_subtree_nodes_count($node, $node_counts);
                if ($node_counts < $this->options->problem_ids[0]) {
                    $counts[0] += 1;
                    $counts[1] += 1;
                } else {
                    $counts[0] += $node->leftborder;
                    $counts[1] = -999;
                }
            } else {
//                $parent = $this->get_parent_node($this->get_dst_root(), $current_problem_id);
//                $tmp_counts = $this->get_repeats($current_root, $node, $parent->id);
//                $counts[0] += $tmp_counts[0];
//                $counts[1] += $tmp_counts[1];
                $counts[0] += 1;
                $counts[1] += 1;
            }
        } else {
            $counts[0] += 1;
            $counts[1] += 1;
        }
        return $counts;
    }

    private function get_repeats($current_root, $node, $current_problem_id) {
        $counts = array(0,0);
        $parent = $this->get_parent_node($this->get_dst_root(), $current_problem_id);

        if ($this->is_can_parent_node($parent)) {
            if ($parent != null) {
                if ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                    $counts[0] += $parent->leftborder;
                    $counts[1] += $parent->rightborder;
                } else if ($parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                    $counts[0] += $parent->leftborder;
                    $counts[1] = -999;
                } else if ($parent->type == qtype_preg_node::TYPE_NODE_SUBEXPR) {

                    $parent_tmp = $this->get_parent_node($this->get_dst_root(), $parent->id);
                    if ($parent_tmp != null) {
                        if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                            $counts[0] += $parent_tmp->leftborder;
                            $counts[1] += $parent_tmp->rightborder;
                        } else if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                            $counts[0] += $parent_tmp->leftborder;
                            $counts[1] = -999;
                        } else if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_CONCAT) {
                            if ($this->options->problem_ids[0] > 1) {
                                $tmp_counts = $this->get_repeats($current_root, $node, $parent_tmp->id);
                                $counts[0] += $tmp_counts[0];
                                $counts[1] += $tmp_counts[1];
                            } else {
                                $tmp_counts = $this->get_current_node_repeats($current_root, $node, $current_problem_id);
                                $counts[0] += $tmp_counts[0];
                                $counts[1] += $tmp_counts[1];
                            }
                        } else {
                            $counts[0] += 1;
                            $counts[1] += 1;
                        }
                    } else {
                        $counts[0] += 1;
                        $counts[1] += 1;
                    }
                    //------------------------------------------------
                } else if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT) {

                    if ($this->options->problem_ids[0] > 1) {
                        $tmp_counts = $this->get_repeats($current_root, $node, $parent->id);
                        $counts[0] += $tmp_counts[0];
                        $counts[1] += $tmp_counts[1];
                    } else {
                        $tmp_counts = $this->get_current_node_repeats($current_root, $node, $current_problem_id);
                        $counts[0] += $tmp_counts[0];
                        $counts[1] += $tmp_counts[1];
                    }

                    /*$node = $this->get_node_from_id($this->get_dst_root(), $current_problem_id);
                    if ($node != null) {
                        if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                            $node_counts = 0;
                            $this->get_subtree_nodes_count($node, $node_counts);
                            if ($node_counts < $this->options->problem_ids[0]) {
                                $counts[0] += 1;
                                $counts[1] += 1;
                            } else {
                                $counts[0] += $node->leftborder;
                                $counts[1] += $node->rightborder;
                            }
                        } else if ($node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                            $node_counts = 0;
                            $this->get_subtree_nodes_count($node, $node_counts);
                            if ($node_counts < $this->options->problem_ids[0]) {
                                $counts[0] += 1;
                                $counts[1] += 1;
                            } else {
                                $counts[0] += $node->leftborder;
                                $counts[1] = -999;
                            }
                        } else {
                            $counts[0] += 1;
                            $counts[1] += 1;
                        }
                    } else {

                        if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT) {
                            $parent = $this->get_parent_node($this->get_dst_root(), $parent->id);

                            if ($parent != null) {
                                if ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                                    $counts[0] += $parent->leftborder;
                                    $counts[1] += $parent->rightborder;
                                } else if ($parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                                    $counts[0] += $parent->leftborder;
                                    $counts[1] = -999;
                                } else if ($parent->type == qtype_preg_node::TYPE_NODE_SUBEXPR) {

                                    $parent_tmp = $this->get_parent_node($this->get_dst_root(), $parent->id);
                                    if ($parent_tmp != null) {
                                        if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                                            $counts[0] += $parent_tmp->leftborder;
                                            $counts[1] += $parent_tmp->rightborder;
                                        } else if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                                            $counts[0] += $parent_tmp->leftborder;
                                            $counts[1] = -999;
                                        } else {
                                            $counts[0] += 1;
                                            $counts[1] += 1;
                                        }
                                    } else {
                                        $counts[0] += 1;
                                        $counts[1] += 1;
                                    }
                                } else {
                                    $node = $this->get_node_from_id($this->get_dst_root(), $current_problem_id);
                                    if ($node != null) {
                                        if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                                            $node_counts = 0;
                                            $this->get_subtree_nodes_count($node, $node_counts);
                                            if ($node_counts < $this->options->problem_ids[0]) {
                                                $counts[0] += 1;
                                                $counts[1] += 1;
                                            } else {
                                                $counts[0] += $node->leftborder;
                                                $counts[1] += $node->rightborder;
                                            }
                                        } else if ($node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                                            $node_counts = 0;
                                            $this->get_subtree_nodes_count($node, $node_counts);
                                            if ($node_counts < $this->options->problem_ids[0]) {
                                                $counts[0] += 1;
                                                $counts[1] += 1;
                                            } else {
                                                $counts[0] += $node->leftborder;
                                                $counts[1] = -999;
                                            }
                                        } else {
                                            $counts[0] += 1;
                                            $counts[1] += 1;
                                        }
                                    } else {
                                        $counts[0] += 1;
                                        $counts[1] += 1;
                                    }
                                }
                            } else {
                                $counts[0] += 1;
                                $counts[1] += 1;
                            }
                        } else {
                            $counts[0] += 1;
                            $counts[1] += 1;
                        }
                    }*

                    *$parent = $this->get_parent_node($this->get_dst_root(), $parent->id);

                    if ($parent != null) {
                        if ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                            $counts[0] += $parent->leftborder;
                            $counts[1] += $parent->rightborder;
                        } else if ($parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                            $counts[0] += $parent->leftborder;
                            $counts[1] = -999;
                        } else if ($parent->type == qtype_preg_node::TYPE_NODE_SUBEXPR) {
                            $parent_tmp = $this->get_parent_node($this->get_dst_root(), $parent->id);
                            if ($parent_tmp != null) {
                                if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                                    $counts[0] += $parent_tmp->leftborder;
                                    $counts[1] += $parent_tmp->rightborder;
                                } else if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                                    $counts[0] += $parent_tmp->leftborder;
                                    $counts[1] = -999;
                                } else {
                                    $counts[0] += 1;
                                    $counts[1] += 1;
                                }
                            } else {
                                $counts[0] += 1;
                                $counts[1] += 1;
                            }
                        } else {
                            $node = $this->get_node_from_id($this->get_dst_root(), $this->options->problem_ids[$i]);
                            if ($node != null) {
                                if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                                    $node_counts = 0;
                                    $this->get_subtree_nodes_count($node, $node_counts);
                                    if ($node_counts < $this->options->problem_ids[0]) {
                                        $counts[0] += 1;
                                        $counts[1] += 1;
                                    } else {
                                        $counts[0] += $node->leftborder;
                                        $counts[1] += $node->rightborder;
                                    }
                                } else if ($node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                                    $node_counts = 0;
                                    $this->get_subtree_nodes_count($node, $node_counts);
                                    if ($node_counts < $this->options->problem_ids[0]) {
                                        $counts[0] += 1;
                                        $counts[1] += 1;
                                    } else {
                                        $counts[0] += $node->leftborder;
                                        $counts[1] = -999;
                                    }
                                } else {
                                    $counts[0] += 1;
                                    $counts[1] += 1;
                                }
                            } else {
                                $counts[0] += 1;
                                $counts[1] += 1;
                            }
                        }
                    } else {
                        $counts[0] += 1;
                        $counts[1] += 1;
                    }*/
                    //------------------------------------------------
                } else {
                    $tmp_counts = $this->get_current_node_repeats($current_root, $node, $current_problem_id);
                    $counts[0] += $tmp_counts[0];
                    $counts[1] += $tmp_counts[1];

                    /*if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT) {
                        $parent = $this->get_parent_node($this->get_dst_root(), $parent->id);

                        if ($parent != null) {
                            if ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                                $counts[0] += $parent->leftborder;
                                $counts[1] += $parent->rightborder;
                            } else if ($parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                                $counts[0] += $parent->leftborder;
                                $counts[1] = -999;
                            } else if ($parent->type == qtype_preg_node::TYPE_NODE_SUBEXPR) {

                                $parent_tmp = $this->get_parent_node($this->get_dst_root(), $parent->id);
                                if ($parent_tmp != null) {
                                    if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                                        $counts[0] += $parent_tmp->leftborder;
                                        $counts[1] += $parent_tmp->rightborder;
                                    } else if ($parent_tmp->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                                        $counts[0] += $parent_tmp->leftborder;
                                        $counts[1] = -999;
                                    } else {
                                        $counts[0] += 1;
                                        $counts[1] += 1;
                                    }
                                } else {
                                    $counts[0] += 1;
                                    $counts[1] += 1;
                                }
                            } else {
                                $node = $this->get_node_from_id($this->get_dst_root(), $this->options->problem_ids[$i]);
                                if ($node != null) {
                                    if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                                        $node_counts = 0;
                                        $this->get_subtree_nodes_count($node, $node_counts);
                                        if ($node_counts < $this->options->problem_ids[0]) {
                                            $counts[0] += 1;
                                            $counts[1] += 1;
                                        } else {
                                            $counts[0] += $node->leftborder;
                                            $counts[1] += $node->rightborder;
                                        }
                                    } else if ($node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                                        $node_counts = 0;
                                        $this->get_subtree_nodes_count($node, $node_counts);
                                        if ($node_counts < $this->options->problem_ids[0]) {
                                            $counts[0] += 1;
                                            $counts[1] += 1;
                                        } else {
                                            $counts[0] += $node->leftborder;
                                            $counts[1] = -999;
                                        }
                                    } else {
                                        $counts[0] += 1;
                                        $counts[1] += 1;
                                    }
                                } else {
                                    $counts[0] += 1;
                                    $counts[1] += 1;
                                }
                            }
                        } else {
                            $counts[0] += 1;
                            $counts[1] += 1;
                        }
                    } else {
                        $counts[0] += 1;
                        $counts[1] += 1;
                    }*/
                }
            } else {
                $tmp_counts = $this->get_current_node_repeats($current_root, $node, $current_problem_id);
                $counts[0] += $tmp_counts[0];
                $counts[1] += $tmp_counts[1];
//                $counts[0] += 1;
//                $counts[1] += 1;
            }
        } else {
            $counts[0] -= 1;
        }

        return $counts;
    }

    /**
     * Calculate repeats of subexpression
     */
    private function subexpressions_repeats($current_root, $node) {
        $counts = array(0,0);
        for($i = 1; $i < count($this->options->problem_ids); $i++) {
            $tmp_counts = $this->get_repeats($current_root, $node, $this->options->problem_ids[$i]);
            $counts[0] += $tmp_counts[0];
            $counts[1] = $tmp_counts[1] === -999 ? $tmp_counts[1] : $counts[1] + $tmp_counts[1];
        }

        return $counts;
    }

    private function get_node_from_id($tree_root, $node_id) {
        $local_root = null;
        if ($tree_root->id == $node_id) {
            return $tree_root;
        }
        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                if ($operand->id == $node_id) {
                    return $operand;
                }
                $local_root = $this->get_node_from_id($operand, $node_id);
                if ($local_root !== null) {
                    return $local_root;
                }
            }
        }
        return $local_root;
    }

    private function subexpression_repeats($current_root, $node) {
        return array(1,1);
    }

    /**
     * Get subexpression position in regex string
     * TODO: in these two subexpressions should always be one parent node with positions
     */
    /*private function get_subexpression_regex_position_for_node($leaf1, $leaf2) {
        $this->indfirst = (($leaf1->position->indfirst < $leaf2->position->indfirst) ? $leaf1->position->indfirst : $leaf2->position->indfirst);
        $this->indlast = (($leaf1->position->indlast > $leaf2->position->indlast) ? $leaf1->position->indlast : $leaf2->position->indlast);
    }*/

    /**
     * Get subexpression position in regex string
     */
    private function get_subexpression_regex_position_for_nodes($leafs1, $leafs2) {
        $this->indfirst = $leafs1[0]->position->indfirst;

        $this->indlast = $leafs2[count($leafs2)-1]->position->indlast;
        foreach($leafs1 as $leaf) {
            if ($leaf->position->indfirst < $this->indfirst){
                $this->indfirst = $leaf->position->indfirst;
            }
            if ($leaf->position->indlast > $this->indlast){
                $this->indlast = $leaf->position->indlast;
            }
        }

        foreach($leafs2 as $leaf) {
            if ($leaf->position->indfirst < $this->indfirst){
                $this->indfirst = $leaf->position->indfirst;
            }
            if ($leaf->position->indlast > $this->indlast){
                $this->indlast = $leaf->position->indlast;
            }
        }

        $this->aaa($leafs1, $leafs2);
        $this->bbb1($leafs1, $leafs2);

        /*$parent = $this->get_parent_node($this->get_dst_root(), $leafs1[0]->id);
        $parent_cur = $this->get_parent_node($this->get_dst_root(), $leafs2[0]->id);
        if ($parent_cur != null
            && ($parent_cur->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                || $parent_cur->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT)) {

            $parent_curcur = $this->get_parent_node($this->get_dst_root(), $parent_cur->id);
            if ($parent != null && $parent_curcur != null && $parent_curcur->id == $parent->id) {
                $this->indlast = $parent_cur->position->indlast;
            } else {
                $this->indlast = $parent_cur->position->indlast;
                /*foreach($leafs2 as $leaf) {
                    if ($leaf->position->indfirst < $this->indfirst){
                        $this->indfirst = $leaf->position->indfirst;
                    }
                    if ($leaf->position->indlast > $this->indlast){
                        $this->indlast = $leaf->position->indlast;
                    }
                }*
            }
        } else {
            foreach($leafs2 as $leaf) {
                if ($leaf->position->indfirst < $this->indfirst){
                    $this->indfirst = $leaf->position->indfirst;
                }
                if ($leaf->position->indlast > $this->indlast){
                    $this->indlast = $leaf->position->indlast;
                }
            }
        }*/
    }

    private function aaa($leafs1, $leafs2) {
        $parent = $this->get_parent_node($this->get_dst_root(), $leafs2[0]->id);
        $parent_cur = $this->get_parent_node($this->get_dst_root(), $leafs1[0]->id);
        if ($parent_cur != null
            && ($parent_cur->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                || $parent_cur->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                || $parent_cur->type == qtype_preg_node::TYPE_NODE_SUBEXPR)) {

            $parent_curcur = $this->get_parent_node($this->get_dst_root(), $parent_cur->id);
            if ($parent != null && $parent_curcur != null && $parent_curcur->id == $parent->id) {
                $this->indlast = $parent_cur->position->indlast;
            } else {
                $this->indlast = $parent_cur->position->indlast;
                /*foreach($leafs1 as $leaf) {
                    if ($leaf->position->indfirst < $this->indfirst){
                        $this->indfirst = $leaf->position->indfirst;
                    }
                    if ($leaf->position->indlast > $this->indlast){
                        $this->indlast = $leaf->position->indlast;
                    }
                }*/
                $this->aaa($leafs2, array($parent_cur));
            }
        } else {
            foreach($leafs1 as $leaf) {
                if ($leaf->position->indfirst < $this->indfirst){
                    $this->indfirst = $leaf->position->indfirst;
                }
                if ($leaf->position->indlast > $this->indlast){
                    $this->indlast = $leaf->position->indlast;
                }
            }
        }
    }

    private function bbb1($leafs1, $leafs2) {
        $parent = $this->get_parent_node($this->get_dst_root(), $leafs1[0]->id);
        $parent_cur = $this->get_parent_node($this->get_dst_root(), $leafs2[0]->id);
        if ($parent_cur != null
            && ($parent_cur->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                || $parent_cur->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                || $parent_cur->type == qtype_preg_node::TYPE_NODE_SUBEXPR)) {

            $parent_curcur = $this->get_parent_node($this->get_dst_root(), $parent_cur->id);
            if ($parent != null && $parent_curcur != null && $parent_curcur->id == $parent->id) {
                $this->indlast = $parent_cur->position->indlast;
            } else {
                $this->indlast = $parent_cur->position->indlast;
                /*foreach($leafs2 as $leaf) {
                    if ($leaf->position->indfirst < $this->indfirst){
                        $this->indfirst = $leaf->position->indfirst;
                    }
                    if ($leaf->position->indlast > $this->indlast){
                        $this->indlast = $leaf->position->indlast;
                    }
                }*/
                $this->bbb1($leafs1, array($parent_cur));
            }
        } else {
            foreach($leafs2 as $leaf) {
                if ($leaf->position->indfirst < $this->indfirst){
                    $this->indfirst = $leaf->position->indfirst;
                }
                if ($leaf->position->indlast > $this->indlast){
                    $this->indlast = $leaf->position->indlast;
                }
            }
        }
    }

    /**
     * Get quantifier regex text from borders
     */
    private function get_quant_text_from_borders($left_border, $right_border) {
        if ($left_border == 1 && $right_border == 1) {
            return '';
        }

        if ($left_border < 0) {
            return '';
        }

        if ($left_border == 0 && $right_border == 0) {
            return '';
        }

        if ($right_border < 0) {
            if ($left_border == 0) {
                return '*';
            } else if ($left_border == 1) {
                return '+';
            }
            return '{' . $left_border . ',}';
        }
        if ($left_border == 0 && $right_border == 1) {
            return '?';
        }
        return '{' . $left_border . ($left_border == $right_border ? '' : ',' . $right_border) . '}';

//        if (($left_border !== 1 && $right_border !== 1)
//            && ($left_border !== 0 && $right_border !== 0)) {
//            if ($right_border < 0) {
//                return '{' . $left_border . ',}';
//            }
//            return '{' . $left_border . ($left_border == $right_border ? '' : ',' . $right_border) . '}';
//        }
        return '';
    }




    /**
     * Check charset node with one character.
     */
    public function single_charset_node() {
        $equivalences = array();

        if ($this->search_single_charset_node($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_5', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_5', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search charset node with one character.
     */
    private function search_single_charset_node($node) {
        if ($node->type == qtype_preg_node::TYPE_LEAF_CHARSET && $node->subtype == NULL) {
            if (!$node->negative && count($node->userinscription) > 1) {
                if ($node->is_single_character()) {
                    $symbol = $node->userinscription[1]->data;
                    if (!$this->check_escaped_symbols($symbol)) {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 5;
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    }
                } else if ($this->check_many_charset_node($node)) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 5;
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                }
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_single_charset_node($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    /**
     * Check charset node with two and more same characters
     */
    private function check_many_charset_node($node) {
        if (count($node->userinscription) > 3) {
            $symbol = $node->userinscription[1]->data;
            if ($this->check_escaped_symbols($symbol)) {
                return false;
            }
            for ($i = 2; $i < count($node->userinscription) - 1; ++$i) {
                if ($node->userinscription[$i]->data !== $symbol) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    private function check_escaped_symbols($symbol) {
        return $symbol === '\\a'
               ||$symbol === '\\b' || $symbol === '\\e'
               || $symbol === '\\f' || $symbol === '\\n'
               || $symbol === '\\r' || $symbol === '\\t';
    }

    private function non_escaped_symbols($symbol) {
        if ($symbol === '\\z' || $symbol === '\\Z'
            /*|| $symbol === '\\a'*/ || $symbol === '\\A'
            /*|| $symbol === '\\b'*/ || $symbol === '\\B') {
            return $symbol[1];
        }
        return $symbol;
    }



    /**
     * Check alternative node with only charsets operands with one character
     */
    public function single_alternative_node() {
        $equivalences = array();

        $this->indfirst = -2;
        $this->indlast = -2;
        if ($this->search_single_alternative_node($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_6', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_6', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search alternative node with only charsets operands with one character
     */
    private function search_single_alternative_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_ALT) {
            if ($this->is_single_alternative($node)) {
                $this->problem_ids[] = $node->id;
                $this->problem_type = 6;
                //$this->indfirst = $node->position->indfirst;
                //$this->indlast = $node->position->indlast;
                return true;
            } else {
                $this->indfirst = -2;
                $this->indlast = -2;
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_single_alternative_node($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    /**
     * Check found alternative node with only charsets operands with one character
     */
    private function is_single_alternative($node) {
        $repeats_count = 0;
        foreach ($node->operands as $i => $operand) {
            if ($operand->type == qtype_preg_node::TYPE_LEAF_CHARSET && !$operand->negative
                && $operand->userinscription[0]->data != '.') {

                $repeats_count++;
                if ($this->indfirst == -2) {
                    $this->indfirst = $operand->position->indfirst;
                }
                $this->indlast = $operand->position->indlast;
            }
        }
        return $repeats_count > 1;
    }



    /**
     * Check partial match alternative operands
     */
    public function partial_match_alternative_operands() {
        $equivalences = array();

        $this->indfirst = -2;
        $this->indlast = -2;
        if ($this->search_partial_match_alternative_operands($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_7', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_7', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search partial match alternative operands
     */
    private function search_partial_match_alternative_operands($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_ALT) {
            $is_left_part_match = -1; // 1 if left part is match, 0 if right part is match
            $count = $this->partial_match_operands_count($node, $is_left_part_match);
            if ($count > 0) {
                $this->problem_ids[] = $count;
                $this->problem_ids[] = $is_left_part_match;
                $this->problem_ids[] = $node->id;
                $this->problem_type = 7;
                $this->indfirst = $node->position->indfirst;
                $this->indlast = $node->position->indlast;
                return true;
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_partial_match_alternative_operands($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    /**
     * Check found alternative node with partial match alternative operands
     */
    private function partial_match_operands_count($node, &$is_left_part_match) {
        $leafs = array();
        foreach ($node->operands as $i => $operand) {
            $leafs[$i] = array();
            $this->leafs_list($operand, $leafs[$i]);
        }

        $repeats_count = 0;
        for ($i = 1; $i < count ($leafs[0]); $i++) {
            if ($this->is_left_partial_match_leafs($leafs, $i)) {
                $repeats_count++;
                $is_left_part_match = 1;
            }
        }

        if ($repeats_count == 0) {
            for ($i = 1; $i < count ($leafs[0]); $i++) {
                if ($this->is_right_partial_match_leafs($leafs, $i)) {
                    $repeats_count++;
                    $is_left_part_match = 0;
                }
            }
        }

        return $repeats_count;
    }

    private function leafs_list($node, &$leafs) {
        $leafs[] = $node;
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                $this->leafs_list($operand, $leafs);
            }
        }
    }

    private function is_left_partial_match_leafs($leafs, $index) {
        $j = 1;
        for (; $j < count($leafs); $j++) {
            if (!$leafs[0][$index]->is_equal($leafs[$j][$index], null)) {
                break;
            }
        }
        return $j == count($leafs);
    }

    private function is_right_partial_match_leafs($leafs, $index) {
        $j = 1;
//        for (; $j < count($leafs); $j++) {
//            if (!$leafs[0][$index]->is_equal($leafs[$j][$index], null)) {
//                break;
//            }
//        }
        return $j == count($leafs);
    }



    /**
     * Check alternative node with empty operand without question quant
     */
    public function alt_without_question_quant() {
        $equivalences = array();

        $this->indfirst = -2;
        $this->indlast = -2;
        if ($this->search_alt_without_question_quant($this->get_dst_root())) {
            if ($this->problem_type == 8) {
                $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_8', 'qtype_preg'));
                $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_8', 'qtype_preg'));
            } else {
                $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_13', 'qtype_preg'));
                $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_13', 'qtype_preg'));
            }
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Check alternative node with empty operand without question quant
     */
    private function search_alt_without_question_quant($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_ALT) {
            if ($this->check_empty_node_for_alt($node)) {
                if (!$this->check_quant_with_zero_left_border_for_node($node)) {
                    $this->problem_ids[] = $node->id;

                    $alt_empty = false;
                    foreach ($node->operands as $tmp_operand) {
                        if ($tmp_operand->nullable
                            && $tmp_operand->type != qtype_preg_leaf::TYPE_LEAF_META
                            && $tmp_operand->type != qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                            $alt_empty = true;
                            break;
                        }
                    }
                    if ($alt_empty) {
                        $this->problem_type = 13;
                    } else {
                        $this->problem_type = 8;
                    }
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                }
            }
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_alt_without_question_quant($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function check_quant_with_zero_left_border_for_node($node) {
        $parent = $this->get_parent_node($this->get_dst_root(), $node->id);
        if ($parent == null) {
            return false;
        } else if ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                   || $parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
            if ($parent->leftborder == 0) {
                return true;
            }
        }

        return $this->check_quant_with_zero_left_border_for_node($parent);
    }



    /**
     * Check alternative node without empty operand with question quant
     */
    public function alt_with_question_quant() {
        $equivalences = array();

        $this->indfirst = -2;
        $this->indlast = -2;
        if ($this->search_alt_with_question_quant($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_9', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_9', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Check alternative node without empty operand with question quant
     */
    private function search_alt_with_question_quant($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_ALT) {
            if (!$this->check_empty_node_for_alt($node)) {
                if ($this->get_question_quant_for_node($node) != null) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 9;
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                }
            }
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_alt_with_question_quant($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function get_question_quant_for_node($node) {
        $parent = $this->get_parent_node($this->get_dst_root(), $node->id);
        if ($parent == null) {
            return null;
        } else if ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
            && $parent->leftborder == 0 && $parent->rightborder == 1) {
            return $parent;
        } else if ($parent->type != qtype_preg_node::TYPE_NODE_SUBEXPR) {
            return null;
        }

        return $this->get_question_quant_for_node($parent);
    }



    /**
     * Check quantifier node who can convert to short quantifier
     */
    public function quant_node() {
        $equivalences = array();

        if ($this->search_quant_node($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_11', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_11', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search quantifier node who can convert to short quantifier
     */
    private function search_quant_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT || $node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
            if ($this->is_simple_quant_node($node)) {
                $this->problem_ids[] = $node->id;
                $this->problem_type = 11;
                $this->indfirst = $node->position->indfirst;
                $this->indlast = $node->position->indlast;
                return true;
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_quant_node($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    /**
     * Check found quantifier node who can convert to short quantifier
     */
    private function is_simple_quant_node($node) {
        if ($node->greedy) {
            if ($node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                return (($node->leftborder === 0 && $node->userinscription[0]->data !== '*')
                        || ($node->leftborder === 1 && $node->userinscription[0]->data !== '+'));
            } else if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                return ($node->leftborder === 0 && $node->rightborder === 1 && $node->userinscription[0]->data !== '?');
            }
        }
        return false;
    }



    /**
     * Check consecutive quantifier nodes
     */
    public function consecutive_quant_nodes() {
        $equivalences = array();

        if ($this->search_consecutive_quant_nodes($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_10', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_10', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search consecutive quantifier nodes
     */
    private function search_consecutive_quant_nodes($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
            || $node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {

            if ($this->check_other_quant_for_quant($node->operands[0])) {
                $oq = $this->get_other_quant_for_quant($node->operands[0]);

                if ($oq != null) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 10;
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;

                    /*$is_found = false;
                    if ($node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                        if ($oq->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                            $is_found = true;
                        } else {
                            if ($oq->rightborder >= $node->leftborder) {
                                $is_found = true;
                            }
                        }
                    } else {
                        if ($oq->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                            if ($node->rightborder >= $oq->leftborder) {
                                $is_found = true;
                            }
                        } else {
                            if (($node->leftborder <= $oq->leftborder && $node->rightborder >= $oq->rightborder)
                                || ($node->leftborder <= $oq->leftborder && $node->rightborder <= $oq->rightborder
                                    && $node->rightborder >= $oq->leftborder)
                                || ($node->leftborder >= $oq->leftborder && $node->rightborder >= $oq->rightborder
                                    && $node->leftborder <= $oq->rightborder)
                                || ($node->leftborder <= $oq->leftborder && $node->rightborder <= $oq->rightborder
                                    && $node->rightborder + 1 == $oq->leftborder
                                    && ($node->leftborder == 1 || ($node->leftborder == 0 && $node->rightborder > 0)))
                                || ($node->leftborder >= $oq->leftborder && $node->rightborder >= $oq->rightborder
                                    && $node->leftborder == $oq->rightborder + 1
                                    && ($oq->leftborder == 1 || ($oq->leftborder == 0 && $oq->rightborder > 0)))
                            ) {
                                $is_found = true;
                            }
                        }
                    }

                    if ($is_found) {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 10;
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    }*/
                }
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_consecutive_quant_nodes($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function check_other_quant_for_quant($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
            || $node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
            return true;
        } else if (!($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR)) {
            return false;
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->check_other_quant_for_quant($operand)) {
                    return true;
                }
            }
        }

        return false;
    }



    /**
     * Check question quantifier for alternative node
     */
    public function question_quant_for_alternative_node() {
        $equivalences = array();

        if ($this->search_question_quant_for_alternative_node($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_12', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_12', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search question quantifier for alternative node
     */
    private function search_question_quant_for_alternative_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
            && $node->leftborder === 0 && $node->rightborder === 1) {
            if ($this->check_alternative_node_for_question_quant($node->operands[0])) {
                $this->problem_ids[] = $node->id;
                $this->problem_type = 12;
                $this->indfirst = $node->position->indfirst;
                $this->indlast = $node->position->indlast;
                return true;
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_question_quant_for_alternative_node($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function check_alternative_node_for_question_quant($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_ALT) {
            if ($this->check_empty_node_for_alt($node)) {
                return true;
            }
        } else if (!($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR)) {
            return false;
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->check_alternative_node_for_question_quant($operand)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function check_empty_node_for_alt($alt) {
        foreach ($alt->operands as $operand) {
            if ($operand->type == qtype_preg_node::TYPE_LEAF_META
                && $operand->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                return true;
            }
        }
        return false;
    }



    /**
     * Check alternative with empty node and all operands may coincide with emptiness
     *
    public function nullable_alternative_node() {
        $equivalences = array();

        if ($this->search_nullable_alternative_node($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_13', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_13', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search alternative with empty node and all operands may coincide with emptiness
     *
    private function search_nullable_alternative_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_ALT) {
            if ($this->check_nullable_alternative_with_empty_node($node)) {
                $this->problem_ids[] = $node->id;
                $this->problem_type = 13;
                $this->indfirst = $node->position->indfirst;
                $this->indlast = $node->position->indlast;
                return true;
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_nullable_alternative_node($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function check_nullable_alternative_with_empty_node($alt) {
        foreach ($alt->operands as $operand) {
            if ($operand->type == qtype_preg_node::TYPE_LEAF_META
                && $operand->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                foreach ($alt->operands as $tmp_operand) {
                    if (!$tmp_operand->nullable) {
                        return false;
                    }
                }
                return true;
            }
        }

        return false;
    }*/



    /**
     * Check quantifier node from 1 to 1
     */
    public function quant_node_1_to_1() {
        $equivalences = array();

        if ($this->search_quant_node_1_to_1($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_equivalences_short_14', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_equivalences_full_14', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    /**
     * Search quantifier node from 1 to 1
     */
    private function search_quant_node_1_to_1($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
            && $node->leftborder === 1 && $node->rightborder === 1) {
            $this->problem_ids[] = $node->id;
            $this->problem_type = 14;
            $this->indfirst = $node->position->indfirst;
            $this->indlast = $node->position->indlast;
            return true;
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_quant_node_1_to_1($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }



    //--- tips ---
    /* The 1st rule */
    public function space_charset() {
        $equivalences = array();

        if ($this->search_space_charset($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_tips_short_1', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_tips_full_1', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    private function search_space_charset($node) {
        if ($node->type == qtype_preg_node::TYPE_LEAF_CHARSET) {
            if (($node->is_single_character() && $node->userinscription[0]->data === ' ')
                || ($this->check_many_charset_node($node) && $node->userinscription[1]->data === ' ')
                /*&& !$node->negative*/) {
                $this->problem_ids[] = $node->id;
                $this->problem_type = 101;
                $this->indfirst = $node->position->indfirst;
                $this->indlast = $node->position->indlast;
                return true;
            } else {
                foreach($node->userinscription as $ui) {
                    if ($ui->data === ' ') {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 101;
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    }
                }
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_space_charset($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }



    /* The 2nd rule */
    public function space_charset_without_quant() {
        $equivalences = array();

        if ($this->search_space_charset_without_quant($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_tips_short_2', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_tips_full_2', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    private function search_space_charset_without_quant($node) {
        if ($node->type == qtype_preg_node::TYPE_LEAF_CHARSET) {
            if ($this->check_space_charsets($node->userinscription[0]->data)
                || (count($node->userinscription) === 3 && $this->check_space_charsets($node->userinscription[1]->data))
                || ($this->check_many_charset_node($node) && $this->check_space_charsets($node->userinscription[1]->data))
                && !$node->negative) {

                if (!$this->check_other_quant_for_space_charset($node)) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 102;
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                }
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_space_charset_without_quant($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function check_space_charsets($charset_data) {
        return $charset_data === ' ' || $charset_data === '\s' || $charset_data === '[:space:]';
    }

    private function check_other_quant_for_space_charset($node) {
        $parent = $this->get_parent_node($this->get_dst_root(), $node->id);
        if ($parent != NULL) {
            if ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                || $parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                return true;
            } else if ($parent->type == qtype_preg_node::TYPE_NODE_SUBEXPR
                       && ($parent->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR
                           || $parent->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING)) {
                return $this->check_other_quant_for_space_charset($parent);
            }
        }
        return false;
    }



    /* The 3rd rule */
    public function subpattern_without_backref() {
        $equivalences = array();

        if ($this->search_subpattern_without_backref($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_tips_short_3', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_tips_full_3', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    private function search_subpattern_without_backref($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
            if (!($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META
                && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY)) {
                if (!$this->check_backref_to_subexpr($this->get_dst_root(), $node->number)) {
                    $this->problem_ids[] = $node->id;
                    $this->problem_type = 103;
                    $this->indfirst = $node->position->indfirst;
                    $this->indlast = $node->position->indlast;
                    return true;
                }
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_subpattern_without_backref($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }



    /* The 4th rule */
    public function space_charset_with_finit_quant() {
        $equivalences = array();

        if ($this->search_space_charset_with_finit_quant($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_tips_short_4', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_tips_full_4', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    private function search_space_charset_with_finit_quant($node) {
        if ($node->type == qtype_preg_node::TYPE_LEAF_CHARSET) {
            if ($this->check_space_charsets($node->userinscription[0]->data)
                || (count($node->userinscription) === 3 && $this->check_space_charsets($node->userinscription[1]->data))
                || ($this->check_many_charset_node($node) && $this->check_space_charsets($node->userinscription[1]->data))
                && !$node->negative) {

                $qu = $this->get_quant_for_space_charset($node);
                if ($qu !== NULL) {
                    if ($qu->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        && $qu->leftborder === 0 && $qu->rightborder === 1) {
                        $this->problem_ids[] = $node->id;
                        $this->problem_type = 104;
                        $this->indfirst = $node->position->indfirst;
                        $this->indlast = $node->position->indlast;
                        return true;
                    }
                }
            }
        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_space_charset_with_finit_quant($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    private function get_quant_for_space_charset($node) {
        $parent = $this->get_parent_node($this->get_dst_root(), $node->id);
        if ($parent != NULL) {
            if ($parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                || $parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                return $parent;
            } else if ($parent->type == qtype_preg_node::TYPE_NODE_SUBEXPR
                && ($parent->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR
                    || $parent->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING)) {
                return $this->get_quant_for_space_charset($parent);
            }
        }
        return NULL;
    }



    /* The 5th rule */
    public function nullable_regex() {
        $equivalences = array();

        if ($this->get_dst_root()->nullable === true) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_tips_short_5', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_tips_full_5', 'qtype_preg'));
            $equivalences['problem_ids'] = array($this->get_dst_root()->id);
            $equivalences['problem_type'] = 105;
            $equivalences['problem_indfirst'] = $this->get_dst_root()->position->indfirst;
            $equivalences['problem_indlast'] = $this->get_dst_root()->position->indlast;
        }

        return $equivalences;
    }



    /* The 8th rule */
    public function exact_match() {
        $equivalences = array();

        if ($this->search_exact_match($this->get_regex_string())) {
            if ($this->options->exactmatch) {
                $equivalences['problem'] = htmlspecialchars(get_string('simplification_tips_short_8', 'qtype_preg'));
                $equivalences['solve'] = htmlspecialchars(get_string('simplification_tips_full_8_alt', 'qtype_preg'));
            } else {
                $equivalences['problem'] = htmlspecialchars(get_string('simplification_tips_short_8', 'qtype_preg'));
                $equivalences['solve'] = htmlspecialchars(get_string('simplification_tips_full_8', 'qtype_preg'));
            }
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    private function search_exact_match($regex_string) {
        $regex_length = strlen($regex_string);
        if ($regex_length > 0) {
            if ($regex_string[0] === '^' && $regex_string[$regex_length - 1] === '$') {
                //$this->problem_ids[] = $node->id;
                $this->problem_type = 108;
                $this->indfirst = -2;
                $this->indlast = -2;
                return true;
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }



    /* The 9th rule */
    public function nested_subpatterns() {
        $equivalences = array();

        if ($this->search_nested_subpatterns($this->get_dst_root())) {
            $equivalences['problem'] = htmlspecialchars(get_string('simplification_tips_short_9', 'qtype_preg'));
            $equivalences['solve'] = htmlspecialchars(get_string('simplification_tips_full_9_alt', 'qtype_preg'));
            $equivalences['problem_ids'] = $this->problem_ids;
            $equivalences['problem_type'] = $this->problem_type;
            $equivalences['problem_indfirst'] = $this->indfirst;
            $equivalences['problem_indlast'] = $this->indlast;
        }

        return $equivalences;
    }

    private function search_nested_subpatterns($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR
            && $node->operand[0]->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->operand[0]->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {

            $this->problem_ids[] = $node->id;
            $this->problem_type = 109;
            $this->indfirst = $node->position->indfirst;
            $this->indlast = $node->position->indlast;
            return true;

        }
        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if ($this->search_nested_subpatterns($operand)) {
                    return true;
                }
            }
        }

        $this->problem_type = -2;
        $this->indfirst = -2;
        $this->indlast = -2;
        return false;
    }

    /**
     * Check included empty subpattern node in empty subpattern node.
     */
    /*private function check_other_subpattern_node($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
            return $this->check_other_subpattern_node($node->operand[0]);
            /*if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META
                && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                if (!$this->check_backref_to_subexpr($this->get_dst_root(), $node->number)) {
                    return true;
                }
            } else {
                return $this->check_other_subpattern_node($node->operands[0]);
            }*
        }
        return false;
    }*/


    //--- OPTIMIZATION ---
    public function optimization() {
        if (count($this->options->problem_ids) > 0 && $this->options->problem_type != -2) {
            if ($this->options->problem_type == 4) {
                if ($this->fold_common_subexpressions($this->get_dst_root())) {
                    return $this->regex_from_tree;
                } else {
                    return '';
                }
            }
            $function_name = ('optimize_' . $this->options->problem_type);
            $this->$function_name($this->get_dst_root());
        }
        return $this->get_regex_string();
    }


    // The 1st rule
    protected function optimize_1($node) {
        return $this->remove_subtree($this->get_dst_root(), $node, $this->options->problem_ids[0]);
    }

    // The 2nd rule
    protected function optimize_2($node) {
        $tree_root = $this->get_dst_root();
        return $this->remove_grouping_node($tree_root, $node, $this->options->problem_ids[0]);
    }

    // The 3rd rule
    protected function optimize_3($node) {
        $tree_root = $this->get_dst_root();
        $this->remove_subpattern_node($tree_root, $node, $this->options->problem_ids[0]);
        $this->rename_backreferences_for_subpattern($tree_root, $tree_root);
        return true;
    }

    // The 4rd rule
//    protected function optimize_4($node) {
//        return $this->fold_common_subexpressions($node/*, $this->options->problem_ids[0]*/);
//    }

    // The 5th rule
    protected function optimize_5($node) {
        return $this->remove_square_brackets_from_charset($node, $this->options->problem_ids[0]);
    }

    // The 6th rule
    protected function optimize_6($node) {
        return $this->change_alternative_to_charset($node, $this->options->problem_ids[0]);
    }

    // The 7th rule
    protected function optimize_7($node) {
        return $this->bracketing_common_subexpr_from_alt($node);
    }

    // The 8th rule
    protected function optimize_8($node) {
        return $this->add_question_quant_to_alt($node, $this->options->problem_ids[0]);
    }

    // The 9th rule
    protected function optimize_9($node) {
        return $this->remove_question_quant_for_alt($node, $this->options->problem_ids[0]);
    }

    // The 10th rule
    protected function optimize_10($node) {
        return $this->change_consecutive_quants($node, $this->options->problem_ids[0]);
    }

    // The 11th rule
    protected function optimize_11($node) {
        return $this->change_quant_to_equivalent($node, $this->options->problem_ids[0]);
    }

    // The 12th rule
    protected function optimize_12($node) {
        return $this->remove_quant($node, $this->options->problem_ids[0]);
    }

    // The 13th rule
    protected function optimize_13($node) {
        return $this->remove_empty_node_from_alternative($node, $this->options->problem_ids[0]);
    }

    // The 14th rule
    protected function optimize_14($node) {
        return $this->remove_quant($node, $this->options->problem_ids[0]);
    }

    protected function optimize_101($node) {
        return $this->change_space_to_charset_s($node, $this->options->problem_ids[0]);
    }

    protected function optimize_102($node) {
        return $this->add_quant_to_space_charset($node, $this->options->problem_ids[0]);
    }

    protected function optimize_103($node) {
        return $this->change_subpattern_to_group($node, $this->options->problem_ids[0]);
    }

    protected function optimize_104($node) {
        return $this->add_finit_quant_to_space_charset($node, $this->options->problem_ids[0]);
    }

    private function remove_subtree($tree_root, $node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            if ($node->id == /*$this->get_dst_root()*/$tree_root->id) {
                // TODO: fix this
                $this->dstroot = null;
            }
            return true;
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $i => $operand) {
                if ($this->remove_subtree($tree_root, $operand, $remove_node_id)) {
                    if (count($node->operands) === 1) {
                        return $this->remove_subtree(/*$this->get_dst_root()*/$tree_root, $tree_root, $node->id);
                    }

                    array_splice($node->operands, $i, 1);
                    if ($this->is_associative_commutative_operator($node) && count($node->operands) < 2) {
                        $node->operands[] = new qtype_preg_leaf_meta(qtype_preg_leaf_meta::SUBTYPE_EMPTY);
                    }

                    return false;
                }
            }
        }

        return false;
    }

    private function remove_quant($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            $parent = $this->get_parent_node($this->get_dst_root(), $node->id);

            if ($parent !== null) {
                foreach ($parent->operands as $i => $operand) {
                    if ($operand->id == $node->id) {
                        $parent->operands[$i] = $node->operands[0];
                        return true;
                    }
                }
            } else {
                $this->dstroot = $node->operands[0];
                return true;
            }
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if($this->remove_quant($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function remove_empty_node_from_alternative($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            return $this->delete_empty_node_from_alternative($node);
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if($this->remove_empty_node_from_alternative($operand, $remove_node_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function delete_empty_node_from_alternative($node) {
        if ($this->check_empty_node_for_alt($node)) {
            foreach ($node->operands as $i => $operand) {
                if ($operand->type == qtype_preg_node::TYPE_LEAF_META
                    && $operand->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                    $node->operands = array_merge(array_slice($node->operands, 0, $i), array_slice($node->operands, $i + 1));
                    $this->delete_empty_node_from_alternative($node);
                }
            }
        }
        return true;
    }

    private function remove_grouping_node($tree_root, $node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            $parent = $this->get_parent_node($tree_root, $node->id);
            if ($parent !== null) {
                $group_operand = $node->operands[0];
                if ($this->check_included_empty_grouping($node)) {
                    if (count($parent->operands) === 1) {
                        return $this->remove_subtree($tree_root, $tree_root, $parent->id);
                    }
                    foreach ($parent->operands as $i => $operand) {
                        if ($operand->id == $remove_node_id) {
                            array_splice($parent->operands, $i, 1);
                            if ($this->is_associative_commutative_operator($parent) && count($parent->operands) < 2) {
                                $parent->operands[] = new qtype_preg_leaf_meta(qtype_preg_leaf_meta::SUBTYPE_EMPTY);
                            }

                            return true;
                        }
                    }
                } else {
//                    if ($parent->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
//                        && $parent->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT
//                    ) {
                        foreach ($parent->operands as $i => $operand) {
                            if ($operand->id == $node->id) {
                                if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT
                                    && $group_operand->type == qtype_preg_node::TYPE_NODE_CONCAT
                                ) {
                                    $parent->operands = array_merge(array_slice($parent->operands, 0, $i),
                                        $group_operand->operands,
                                        array_slice($parent->operands, $i + 1));
                                } else {
                                    $parent->operands[$i] = $group_operand;
                                }
                                return true;
//                                break;
                            }
                        }
                    //}
                }
            } else {
                // TODO: fix this
                if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META
                    && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                    $this->dstroot = null;
                } else if ($this->check_other_grouping_node($node->operands[0])) {
                    $this->dstroot = null;
                } else {
                    $this->dstroot = $node->operands[0];
                }
                return true;
            }
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if($this->remove_grouping_node($tree_root, $operand, $remove_node_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function remove_subpattern_node($tree_root, $node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            $parent = $this->get_parent_node($tree_root, $node->id);
            if ($parent !== null) {
                $group_operand = $node->operands[0];
                if ($group_operand->type === qtype_preg_node::TYPE_NODE_CONCAT) {
                    /*if ($group_operand->operands[0]->type === qtype_preg_node::TYPE_NODE_FINITE_QUANT
                        || $group_operand->operands[0]->type === qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                        $group_operand->operands[0]->position->indfirst = $node->position->indfirst;
                        $group_operand->operands[0]->position->indlast = $node->position->indlast;
                        $group_operand->operands[0]->operands[0]->position->indfirst = $node->position->indfirst;
                        $group_operand->operands[0]->operands[0]->position->indlast = $node->position->indlast;
                    } else {
                        $group_operand->operands[0]->position->indfirst = $node->position->indfirst;
                        $group_operand->operands[0]->position->indlast = $node->position->indlast;
                    }*/
                    //$this->deleted_subpattern_positions[] = array($node->position->indfirst, $node->position->indlast);
                    $a=1;
                } else if ($group_operand->type === qtype_preg_node::TYPE_NODE_FINITE_QUANT
                           || $group_operand->type === qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                    //$this->deleted_subpattern_positions[] = array($node->position->indfirst, $node->position->indlast);

                    /*$group_operand->position->indfirst = $node->position->indfirst;
                    $group_operand->position->indlast = $node->position->indlast;
                    $group_operand->operands[0]->position->indfirst = $node->position->indfirst;
                    $group_operand->operands[0]->position->indlast = $node->position->indlast;*/
                    $a=1;
                } else {
                    //$this->deleted_subpattern_positions[] = array($node->position->indfirst, $node->position->indlast);
                    $group_operand->position->indfirst = $node->position->indfirst;
                    $group_operand->position->indlast = $node->position->indlast;
                }

                if ($this->check_included_empty_subpattern($node)) {
                    if (count($parent->operands) === 1) {
                        return $this->remove_subtree($tree_root, $tree_root, $parent->id);
                    }
                    foreach ($parent->operands as $i => $operand) {
                        if ($operand->id == $remove_node_id) {
                            array_splice($parent->operands, $i, 1);
                            if ($this->is_associative_commutative_operator($parent) && count($parent->operands) < 2) {
                                $parent->operands[] = new qtype_preg_leaf_meta(qtype_preg_leaf_meta::SUBTYPE_EMPTY);
                            }

                            return true;
                        }
                    }
                } else {
//                    if ($parent->type != qtype_preg_node::TYPE_NODE_FINITE_QUANT
//                        && $parent->type != qtype_preg_node::TYPE_NODE_INFINITE_QUANT
//                    ) {
                    foreach ($parent->operands as $i => $operand) {
                        if ($operand->id == $node->id) {
                            if ($parent->type == qtype_preg_node::TYPE_NODE_CONCAT
                                && $group_operand->type == qtype_preg_node::TYPE_NODE_CONCAT
                            ) {
                                $parent->operands = array_merge(array_slice($parent->operands, 0, $i),
                                    $group_operand->operands,
                                    array_slice($parent->operands, $i + 1));
                            } else {
                                $parent->operands[$i] = $group_operand;
                            }
                            return true;
                            break;
                        }
                    }
                    //}
                }
            } else {
                //$this->deleted_subpattern_positions[] = array($node->position->indfirst, $node->position->indlast);
                // TODO: fix this
                if ($node->operands[0]->type == qtype_preg_node::TYPE_LEAF_META
                    && $node->operands[0]->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
                    $this->dstroot = null;
                } else if ($this->check_other_subpattern_node($node->operands[0])) {
                    $this->dstroot = null;
                } else {
                    $this->dstroot = $node->operands[0];
                }
                return true;
            }
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                if($this->remove_subpattern_node($tree_root, $operand, $remove_node_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function check_included_empty_grouping($node) {
        $group_operand = $node->operands[0];
        if ($group_operand->type == qtype_preg_node::TYPE_LEAF_META
            && $group_operand->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
            return true;
        } else if ($group_operand->type == qtype_preg_node::TYPE_NODE_SUBEXPR
                   && $group_operand->subtype == qtype_preg_node_subexpr::SUBTYPE_GROUPING) {
            return $this->check_included_empty_grouping($group_operand);
        }
        return false;
    }

    private function check_included_empty_subpattern($node) {
        $group_operand = $node->operands[0];
        if ($group_operand->type == qtype_preg_node::TYPE_LEAF_META
            && $group_operand->subtype == qtype_preg_leaf_meta::SUBTYPE_EMPTY) {
            return true;
        } else if ($group_operand->type == qtype_preg_node::TYPE_NODE_SUBEXPR
            && $group_operand->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
            return $this->check_included_empty_subpattern($group_operand);
        }
        return false;
    }

    private function remove_square_brackets_from_charset($tree_root, $remove_node_id) {
        if ($tree_root->id == $remove_node_id) {
            if (count($tree_root->userinscription) > 1) {
                $tmp = $tree_root->userinscription[1];
                $tmp->data = $this->escape_character_for_single_charset($tmp->data);
                $tmp->data = $this->non_escaped_symbols($tmp->data);
                $tree_root->userinscription = array($tmp);
                $tree_root->flags[0][0]->data = new qtype_poasquestion\string($tmp->data);
                $tree_root->subtype = "enumerable_characters";
                return true;
            }
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                if ($this->remove_square_brackets_from_charset($operand, $remove_node_id)) {
                    return true;
                }
            }
        }
        return false;
    }

    private function escape_character_for_single_charset($character) {
        if ($character === '\\' || $character === '^' || $character === '$'
            || $character === '.' || $character === '[' || $character === ']'
            || $character === '|' || $character === '(' || $character === ')'
            || $character === '?' || $character === '*' || $character === '+'
            || $character === '{' || $character === '}') {
            return '\\' . $character;
        }
        return $character;
    }

    private function bracketing_common_subexpr_from_alt($tree_root) {
//        if ($tree_root->id == $this->problem_ids[2]) {
//            if ($this->problem_ids[1] == 1) {
//                $leafs = array();
//
//                $this->get_left_part_of_operands_from_alt($tree_root->operands, $this->problem_ids[0], $leafs);
//
//
//            } else if ($this->problem_ids[1] == 0) {
//                $this->get_right_part_of_operands_from_alt($tree_root, $this->problem_ids[0]);
//            } else {
//                return false;
//            }
//        }
//
//        if ($this->is_operator($tree_root)) {
//            foreach ($tree_root->operands as $operand) {
//                if ($this->change_consecutive_quants($operand, $remove_node_id)) {
//                    return true;
//                }
//            }
//        }

        return false;
    }

    private function get_left_part_of_operands_from_alt($node, &$count, &$leafs) {
        if ($count > 0) {
            $leafs[] = $node;
            $count--;
            if ($this->is_operator($node)) {
                foreach ($node->operands as $operand) {
                    $this->leafs_list($operand, $leafs);
                }
            }
        }
    }

    private function get_right_part_of_operands_from_alt($node, $count) {
        return true;
    }


    private function add_question_quant_to_alt($tree_root, $remove_node_id) {
        if ($tree_root->id == $remove_node_id) {
            $this->delete_empty_node_from_alternative($tree_root);

            /*$alt_match_empty = false;
            foreach ($tree_root->operands as $operand) {
                if ($operand->nullable === true) {
                    $alt_match_empty = true;
                    break;
                }
            }

            if (!$alt_match_empty) {*/
                $qu = new qtype_preg_node_finite_quant(0, 1);
                $qu->set_user_info(null, array(new qtype_preg_userinscription('?')));

                $parent = $this->get_parent_node($this->get_dst_root(), $tree_root->id);
                if ($parent == null) {
                    $se = new qtype_preg_node_subexpr(qtype_preg_node_subexpr::SUBTYPE_GROUPING, -1, '', false);
                    $se->set_user_info(null, array(new qtype_preg_userinscription('(?:...)')));
                    $se->operands[] = $tree_root;
                    $qu->operands[] = $se;

                    $this->dstroot = $qu;
                } else if ($parent->type == qtype_preg_node::TYPE_NODE_SUBEXPR) {
                    $new_parent = $this->get_parent_node($this->get_dst_root(), $parent->id);

                    if ($new_parent == null) {
                        $qu->operands[] = $parent;
                        $this->dstroot = $qu;
                    } else if ($new_parent->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                        || $new_parent->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                    ) {
                        $se = new qtype_preg_node_subexpr(qtype_preg_node_subexpr::SUBTYPE_GROUPING, -1, '', false);
                        $se->set_user_info(null, array(new qtype_preg_userinscription('(?:...)')));
                        $se->operands[] = $tree_root;
                        $qu->operands[] = $se;

                        $parent->operands[0] = $qu;
                    } else {
                        $qu->operands[] = $parent;

                        foreach ($new_parent->operands as $i => $operand) {
                            if ($parent->id == $operand->id) {
                                $new_parent->operands[$i] = $qu;
                                break;
                            }
                        }
                    }
                } else {
                    $se = new qtype_preg_node_subexpr(qtype_preg_node_subexpr::SUBTYPE_GROUPING, -1, '', false);
                    $se->set_user_info(null, array(new qtype_preg_userinscription('(?:...)')));
                    $se->operands[] = $tree_root;
                    $qu->operands[] = $se;

                    foreach ($parent->operands as $i => $operand) {
                        if ($tree_root->id == $operand->id) {
                            $parent->operands[$i] = $qu;
                            break;
                        }
                    }
                }
            //}
            return true;
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                if ($this->add_question_quant_to_alt($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }


    private function remove_question_quant_for_alt($tree_root, $remove_node_id) {
        if ($tree_root->id == $remove_node_id) {
            // Search quant
            $qu = $this->get_question_quant_for_node($tree_root);
            // Remove quant
            $this->remove_quant($this->get_dst_root(), $qu->id);
            if ($tree_root->nullable === false) {
                // Add empty node to alt
                $tree_root->operands[] = new qtype_preg_leaf_meta(qtype_preg_leaf_meta::SUBTYPE_EMPTY);
            }
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                if ($this->remove_question_quant_for_alt($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function change_consecutive_quants($tree_root, $remove_node_id) {
        if ($tree_root->id == $remove_node_id) {
            $oq = $this->get_other_quant_for_quant($tree_root->operands[0]);

            $text = '';
            if ($tree_root->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                && $tree_root->leftborder === 0 && $tree_root->rightborder === 0) {
                $text = '{0}';
            } else if ($oq->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
                && $oq->leftborder === 0 && $oq->rightborder === 0) {
                $text = '{0}';
            } else {
                //$leftborder = ($tree_root->leftborder < $oq->leftborder) ? $tree_root->leftborder : $oq->leftborder;
                $leftborder = $tree_root->leftborder * $oq->leftborder;
                $rightborder = 0;

                $infinite = false;
                if ($tree_root->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                    || $oq->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                ) {
                    $infinite = true;
                } else {
                    //$rightborder = ($tree_root->rightborder > $oq->rightborder) ? $tree_root->rightborder : $oq->rightborder;
                    $rightborder = $tree_root->rightborder * $oq->rightborder;
                }

                if ($infinite) {
                    if ($leftborder === 0) {
                        $text = '*';
                    } else if ($leftborder === 1) {
                        $text = '+';
                    } else {
                        $text = '{' . $leftborder . ',}';
                    }
                } else {
                    if ($leftborder === 0 && $rightborder === 1) {
                        $text = '?';
                    } else if ($leftborder === $rightborder) {
                        $text = '{' . $leftborder . '}';
                    } else {
                        $text = '{' . $leftborder . ',' . $rightborder . '}';
                    }
                }
            }

            $oq->set_user_info(null, array(new qtype_preg_userinscription($text)));

            $parenttr = $this->get_parent_node($this->get_dst_root(), $tree_root->id);

            if ($parenttr != null) {
                foreach ($parenttr->operands as $i => $operand) {
                    if ($operand->id == $tree_root->id) {
                        $parenttr->operands[$i] = $tree_root->operands[0];
                    }
                }
            } else {
                $this->dstroot = $tree_root->operands[0];
            }
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                if ($this->change_consecutive_quants($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function get_other_quant_for_quant($node) {
        if ($node->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT
            || $node->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
            return $node;
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $operand) {
                return $this->get_other_quant_for_quant($operand);
            }
        }

        return null;
    }

    private function change_quant_to_equivalent($tree_root, $remove_node_id) {
        if ($tree_root->id == $remove_node_id) {
            if ($tree_root->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT) {
                if ($tree_root->leftborder === 0 && $tree_root->userinscription[0]->data !== '*') {
                    $tmp = $tree_root->userinscription[0];
                    $tree_root->userinscription = array($tmp);
                    $tree_root->userinscription[0]->data = new qtype_poasquestion\string('*');

                    /*if ($tree_root->operands[0]->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                        || $tree_root->operands[0]->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                        $se = new qtype_preg_node_subexpr(qtype_preg_node_subexpr::SUBTYPE_GROUPING, -1, '', false);
                        $se->set_user_info(null, array(new qtype_preg_userinscription('(?:...)')));
                        $se->operands[] = $tree_root->operands[0];
                        $tree_root->operands[0] = $se;
                    }*/
                    return true;
                } else if ($tree_root->leftborder === 1 && $tree_root->userinscription[0]->data !== '+') {
                    $tmp = $tree_root->userinscription[0];
                    $tree_root->userinscription = array($tmp);
                    $tree_root->userinscription[0]->data = new qtype_poasquestion\string('+');
                    if ($tree_root->operands[0]->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                        || $tree_root->operands[0]->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                        $se = new qtype_preg_node_subexpr(qtype_preg_node_subexpr::SUBTYPE_GROUPING, -1, '', false);
                        $se->set_user_info(null, array(new qtype_preg_userinscription('(?:...)')));
                        $se->operands[] = $tree_root->operands[0];
                        $tree_root->operands[0] = $se;
                    }
                    return true;
                }
            } else if ($tree_root->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                if ($tree_root->leftborder === 0 && $tree_root->rightborder === 1 && $tree_root->userinscription[0]->data !== '?') {
                    $tmp = $tree_root->userinscription[0];
                    $tree_root->userinscription = array($tmp);
                    $tree_root->userinscription[0]->data = new qtype_poasquestion\string('?');
                    if ($tree_root->operands[0]->type == qtype_preg_node::TYPE_NODE_INFINITE_QUANT
                        || $tree_root->operands[0]->type == qtype_preg_node::TYPE_NODE_FINITE_QUANT) {
                        $se = new qtype_preg_node_subexpr(qtype_preg_node_subexpr::SUBTYPE_GROUPING, -1, '', false);
                        $se->set_user_info(null, array(new qtype_preg_userinscription('(?:...)')));
                        $se->operands[] = $tree_root->operands[0];
                        $tree_root->operands[0] = $se;
                    }
                    return true;
                }
            }
            return false;
        }

        if ($this->is_operator($tree_root)) {
            foreach ($tree_root->operands as $operand) {
                if ($this->change_quant_to_equivalent($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function change_space_to_charset_s($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            if ($node->is_single_character()) {
                if (count($node->userinscription) == 1) {
                    $node->userinscription[0]->data = '\s';
                } else {
                    $node->userinscription[1]->data = '\s';
                }
            } else {
                foreach ($node->userinscription as $i => $ui) {
                    if ($ui->data === ' ') {
                        $ui->data = '\s';
                    }
                }
            }
            return true;
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $i => $operand) {
                if ($this->change_space_to_charset_s($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function change_alternative_to_charset($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            $uicharacters = array();
            $uicharacters[] = new qtype_preg_userinscription('[', null);
//            $characters = '[';
            $characters = '';
            $count = 0;

            $alt = new qtype_preg_node_alt();

            foreach ($node->operands as $operand) {
                if ($operand->type == qtype_preg_node::TYPE_LEAF_CHARSET && !$operand->negative
                    && $operand->userinscription[0]->data != '.') {
                    $count++;
                    if (count($operand->userinscription) === 1) {
                        $uicharacters[] = new qtype_preg_userinscription($this->escape_character_for_charset($operand->userinscription[0]->data), null);
                        $characters .= $this->escape_character_for_charset($operand->userinscription[0]->data);
                    } else {
                        for ($i = 1; $i < count($operand->userinscription) - 1; ++$i) {
                            $uicharacters[] = new qtype_preg_userinscription($this->escape_character_for_charset($operand->userinscription[$i]->data), null);
                            $characters .= $this->escape_character_for_charset($operand->userinscription[$i]->data);
                        }
                    }
                } else {
                    $alt->operands[] = $operand;
                }
            }
//            $characters .= ']';
            $uicharacters[] = new qtype_preg_userinscription(']', null);

            $new_node = new qtype_preg_leaf_charset();
            $new_node->set_user_info(null, $uicharacters);
            $new_node->id = $remove_node_id; //$this->parser->get_max_id() + 1;
            //$this->parser->set_max_id($new_node->id + 1);

            if ($characters !== null) {
                $flag = new qtype_preg_charset_flag;
                $flag->negative = false;
//                $characters = new qtype_poasquestion\string($characters);
                $flag->set_data(qtype_preg_charset_flag::TYPE_SET, new qtype_poasquestion\string($characters));
                $new_node->flags = array(array($flag));
            }

            $new_node->position = new qtype_preg_position($node->position->indfirst, $node->position->indlast, null, null, null, null);

            if ($count == count($node->operands)) {
                if ($node->id == $this->get_dst_root()->id) {
                    $this->dstroot = $new_node;
                } else {
                    $local_root = $this->get_parent_node($this->get_dst_root(), $node->id);

                    if ($local_root !== NULL) {
                        foreach ($local_root->operands as $i => $operand) {
                            if ($operand->id == $node->id) {
                                $local_root->operands[$i] = $new_node;
                                break;
                            }
                        }
                    }
                }
            } else {
                $alt->operands[] = $new_node;
                $local_root = $this->get_parent_node($this->get_dst_root(), $node->id);

                if ($local_root === NULL) {
                    $this->dstroot = $alt;
                } else {
                    foreach ($local_root->operands as $i => $operand) {
                        if ($operand->id == $node->id) {
                            $local_root->operands[$i] = $alt;
                            break;
                        }
                    }
                }
            }

            return true;
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $i => $operand) {
                if ($this->change_alternative_to_charset($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function escape_character_for_charset($character) {
        if ($character === '-' || $character === ']' || $character === '{' || $character === '}') {
            return '\\' . $character;
        }
        return $character;
    }

    private function change_subpattern_to_group($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            $node->subtype = qtype_preg_node_subexpr::SUBTYPE_GROUPING;
            //$subpattern_last_number = 0;
            $tree_root = $this->get_dst_root();
            $this->rename_backreferences_for_subpattern($tree_root, $tree_root/*, $subpattern_last_number*/);
            return true;
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $i => $operand) {
                if ($this->change_subpattern_to_group($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function rename_backreferences_for_subpattern($tree_root, $node, &$subpattern_last_number = 0) {
        if ($node !== null) {
            if ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR) {
                ++$subpattern_last_number;
                $this->rename_backref($tree_root, $node->number, $subpattern_last_number);
            }
            if ($this->is_operator($node)) {
                foreach ($node->operands as $operand) {
                    if ($this->rename_backreferences_for_subpattern($tree_root, $operand, $subpattern_last_number)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function rename_backref($node, $old_number, $new_number) {
        if ($node !== null) {
            if (($node->type == qtype_preg_node::TYPE_LEAF_BACKREF
                    && $node->subtype == qtype_preg_node::TYPE_LEAF_BACKREF && $node->number == $old_number)
                || ($node->type == qtype_preg_node::TYPE_NODE_COND_SUBEXPR
                    && $node->subtype == qtype_preg_node_cond_subexpr::SUBTYPE_SUBEXPR && $node->number == $old_number)
                || ($node->type == qtype_preg_node::TYPE_NODE_SUBEXPR
                    && $node->subtype == qtype_preg_node_subexpr::SUBTYPE_SUBEXPR && $node->number == $old_number)
            ) {
                $node->number = $new_number;
            }
            if ($this->is_operator($node)) {
                foreach ($node->operands as $operand) {
                    $this->rename_backref($operand, $old_number, $new_number);
                }
            }
        }
    }

    private function add_quant_to_space_charset($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            $qu = new qtype_preg_node_infinite_quant(1, false, true, true);
            $qu->set_user_info(null, array(new qtype_preg_userinscription('+')));
            $qu->operands[] = $node;

            $parent = $this->get_parent_node($this->get_dst_root(), $node->id);
            if ($parent != NULL) {
                //if (count($parent->operands) > 1) {
                    foreach ($parent->operands as $i => $operand) {
                        if ($operand->id === $node->id) {
                            $parent->operands[$i] = $qu;

//                            $parent->operands = array_merge(array_slice($parent->operands, 0, $i),
//                                array($qu),
//                                array_slice($parent->operands, $i + 1));
                        }
                    }
                /*} else {
                    $parent->operands = array($qu);
                }*/
            } else {
                $this->dstroot = $qu;
            }

            return true;
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $i => $operand) {
                if ($this->add_quant_to_space_charset($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function add_finit_quant_to_space_charset($node, $remove_node_id) {
        if ($node->id == $remove_node_id) {
            $old_qu = $this->get_quant_for_space_charset($node);
            if ($old_qu != NULL) {
                $parent = $this->get_parent_node($this->get_dst_root(), $old_qu->id);

                $qu = new qtype_preg_node_infinite_quant(0, false, true, true);
                $qu->set_user_info(null, array(new qtype_preg_userinscription('*')));
                $qu->operands[] = $old_qu->operands[0];

                if ($parent != NULL) {
                    foreach ($parent->operands as $i => $operand) {
                        if ($operand->id === $old_qu->id) {
                            $parent->operands[$i] = $qu;

//                            $parent->operands = array_merge(array_slice($parent->operands, 0, $i),
//                                array($qu),
//                                array_slice($parent->operands, $i + 1));
                        }
                    }
                } else {
                    $this->dstroot = $qu;
                }
                return true;
            }
        }

        if ($this->is_operator($node)) {
            foreach ($node->operands as $i => $operand) {
                if ($this->add_finit_quant_to_space_charset($operand, $remove_node_id)) {
                    return true;
                }
            }
        }

        return false;
    }
}