<?php


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/poasquestion/hints.php');

/**
 * Class qtype_writeregex_syntaxtreehint Class of syntax tree hint.
 */
class qtype_writeregex_syntaxtreehint extends qtype_specific_hint {

    /** @var  int Mode of hint. */
    protected $mode;
    /** @var  object Object on question. */
    protected $question;
    /** @var  string Hint key. */
    protected $hintkey;
    /** @var array Options of syntax tree hint. */
    protected $syntaxtreeoptions = array();

    /**
     * Init all fields.
     * @param $question object Object of question's class.
     * @param $hintkey string Hint key.
     * @param $mode int Mode of current hint.
     */
    public function __construct ($question, $hintkey, $mode) {

        $this->question = $question;
        $this->hintkey = $hintkey;
        $this->mode = $mode;
        $this->syntaxtreeoptions = array(
            '0' => get_string('none', 'qtype_writeregex'),
            '1' => get_string('student', 'qtype_writeregex'),
            '2' => get_string('answer', 'qtype_writeregex'),
            '3' => get_string('both', 'qtype_writeregex')
        );
    }

    /**
     * Get options of syntax tree hint.
     * @return array Options of syntax tree hint.
     */
    public function syntaxtreeoptions() {
        return $this->syntaxtreeoptions;
    }

    /**
     * Get hint type.
     * @return int hint type.
     */
    public function hint_type() {
        return qtype_specific_hint::SINGLE_INSTANCE_HINT;
    }

    /**
     * Get hint description.
     * @return string hint description.
     */
    public function hint_description() {
        return get_string('wre_st', 'qtype_writeregex');
    }

    /**
     * Get value of hint response based or not.
     * @return bool hint response based.
     */
    public function hint_response_based() {
        return true;
    }

    /**
     * Get penalty value.
     * @param null $response object Response.
     * @return float Value of current hint penalty.
     */
    public function penalty_for_specific_hint ($response = null) {
        return $this->question->syntaxtreehintpenalty;
    }

    public function can_available_hint_for_answer ($answer) {
        // TODO: template code

        if ($this->mode == 3) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get hint available.
     * @param null $response Response.
     * @return bool Hint available.
     */
    public function hint_available ($response = null) {

        if ($this->question->syntaxtreehinttype > 0) {
            return true;
        }

        if ($response !== null) {
            $bestfit = $this->question->get_best_fit_answer($response);
            $isavailableforanswer = $this->can_available_hint_for_answer($bestfit);
            $isavailableforresponse = $this->can_available_hint_for_answer($response);
            return ($isavailableforanswer && $isavailableforresponse);
        }

        return true;
    }

    /**
     * Render hint function.
     * @param question $renderer
     * @param question_attempt $qa
     * @param question_display_options $options
     * @param null $response
     * @return string Template code value.
     */
    public function render_hint ($renderer, question_attempt $qa = null, question_display_options $options = null, $response = null) {

        switch($this->mode){
            case 1: return 'Hint stack analyzer: the student\'s answer';
            case 2: return 'Hint stack analyzer: the correct answer';
            case 3: return 'Hint stack analyzer: the student\'s answer and the correct answer (both)';
            default: return 'defstack';
        }
    }
}

/**
 * Class qtype_writeregex_explgraphhint Class of explanation graph hint.
 */
class qtype_writeregex_explgraphhint extends qtype_specific_hint {

    /** @var  int Mode of hint. */
    protected $mode;
    /** @var  object Object on question. */
    protected $question;
    /** @var  string Hint key. */
    protected $hintkey;
    /** @var array Options of explanation graph hint. */
    protected $explgraphhintoptions = array();

    /**
     * Init all fields.
     * @param $question object Object of question's class.
     * @param $hintkey string Hint key.
     * @param $mode int Mode of current hint.
     */
    public function __construct ($question, $hintkey, $mode) {

        $this->question = $question;
        $this->hintkey = $hintkey;
        $this->mode = $mode;
        $this->explgraphhintoptions = array(
            '0' => get_string('none', 'qtype_writeregex'),
            '1' => get_string('student', 'qtype_writeregex'),
            '2' => get_string('answer', 'qtype_writeregex'),
            '3' => get_string('both', 'qtype_writeregex')
        );
    }

    /**
     * Get options of explanation graph.
     * @return array Options of explanation graph.
     */
    public function explgraphhintoptions() {
        return $this->explgraphhintoptions;
    }

    /**
     * Get hint type.
     * @return int Hint type.
     */
    public function hint_type() {
        return qtype_specific_hint::SINGLE_INSTANCE_HINT;
    }

    /**
     * Get hint description.
     * @return string Hint description.
     */
    public function hint_description() {
        return get_string('wre_eg', 'qtype_writeregex');
    }

    /**
     * Get value of hint response based or not.
     * @return bool hint response based.
     */
    public function hint_response_based() {
        return true;
    }

    /**
     * Get penalty value.
     * @param null $response object Response.
     * @return float Value of current hint penalty.
     */
    public function penalty_for_specific_hint ($response = null) {
        return $this->question->explgraphhintpenalty;
    }

    public function can_available_hint_for_answer ($answer) {
        // TODO: template code

        if ($this->mode == 3) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get hint available.
     * @param null $response Response.
     * @return bool Hint available.
     */
    public function hint_available ($response = null) {

        if ($this->question->explgraphhinttype > 0) {
            return true;
        }

        if ($response !== null) {
            $bestfit = $this->question->get_best_fit_answer($response);
            $isavailableforanswer = $this->can_available_hint_for_answer($bestfit);
            $isavailableforresponse = $this->can_available_hint_for_answer($response);
            return ($isavailableforanswer && $isavailableforresponse);
        }

        return true;
    }

    /**
     * Render hint function.
     * @param question $renderer
     * @param question_attempt $qa
     * @param question_display_options $options
     * @param null $response
     * @return string Template code value.
     */
    public function render_hint($renderer, question_attempt $qa = null, question_display_options $options = null, $response = null) {

        switch($this->mode){
            case 1: return 'Hint stack analyzer: the student\'s answer';
            case 2: return 'Hint stack analyzer: the correct answer';
            case 3: return 'Hint stack analyzer: the student\'s answer and the correct answer (both)';
            default: return 'defstack';
        }
    }
}

/**
 * Class qtype_writeregex_descriptionhint Class of text description hint.
 */
class qtype_writeregex_descriptionhint extends qtype_specific_hint {

    /** @var  int Mode of hint. */
    protected $mode;
    /** @var  object Object on question. */
    protected $question;
    /** @var  string Hint key. */
    protected $hintkey;
    /** @var array Options of text description hint. */
    protected $descriptionhintoptions = array();

    /**
     * Init all fields.
     * @param $question object Object of question's class.
     * @param $hintkey string Hint key.
     * @param $mode int Mode of current hint.
     */
    public function __construct ($question, $hintkey, $mode) {

        $this->question = $question;
        $this->hintkey = $hintkey;
        $this->mode = $mode;
        $this->descriptionhintoptions = array(
            '0' => get_string('none', 'qtype_writeregex'),
            '1' => get_string('student', 'qtype_writeregex'),
            '2' => get_string('answer', 'qtype_writeregex'),
            '3' => get_string('both', 'qtype_writeregex')
        );
    }

    /**
     * Get options of text description hint.
     * @return array Options of text description hint.
     */
    public function descriptionhintoptions() {
        return $this->descriptionhintoptions;
    }

    /**
     * Get hint type.
     * @return int hint type.
     */
    public function hint_type() {
        return qtype_specific_hint::SINGLE_INSTANCE_HINT;
    }

    /**
     * Get hint description.
     * @return string hint description.
     */
    public function hint_description() {
        return get_string('wre_d', 'qtype_writeregex');
    }

    /**
     * Get value of hint response based or not.
     * @return bool hint response based.
     */
    public function hint_response_based() {
        return true;
    }

    /**
     * Get penalty value.
     * @param null $response object Response.
     * @return float Value of current hint penalty.
     */
    public function penalty_for_specific_hint ($response = null) {
        return $this->question->descriptionhintpenalty;
    }

    public function can_available_hint_for_answer ($answer) {
        // TODO: template code

        if ($this->mode == 3) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Render hint function.
     * @param question $renderer
     * @param question_attempt $qa
     * @param question_display_options $options
     * @param null $response
     * @return string Template code value.
     */
    public function render_hint($renderer, question_attempt $qa = null, question_display_options $options = null, $response = null) {

        switch($this->mode){
            case 1: return 'Hint stack analyzer: the student\'s answer';
            case 2: return 'Hint stack analyzer: the correct answer';
            case 3: return 'Hint stack analyzer: the student\'s answer and the correct answer (both)';
            default: return 'defstack';
        }
    }
}

/**
 * Class qtype_writeregex_teststringshint Class of test strings hint.
 */
class qtype_writeregex_teststringshint extends qtype_specific_hint {

    /** @var  int Mode of hint. */
    protected $mode;
    /** @var  object Object on question. */
    protected $question;
    /** @var  string Hint key. */
    protected $hintkey;
    /** @var array Options of syntax tree hint. */
    protected $syntaxtreehintoptions = array();

    /**
     * Render hint function.
     * @param question $renderer
     * @param question_attempt $qa
     * @param question_display_options $options
     * @param null $response
     * @return string Template code value.
     */
    public function render_hint($renderer, question_attempt $qa = null, question_display_options $options = null, $response = null) {

        switch($this->mode){
            case 1: return 'Hint stack analyzer: the student\'s answer';
            case 2: return 'Hint stack analyzer: the correct answer';
            case 3: return 'Hint stack analyzer: the student\'s answer and the correct answer (both)';
            default: return 'defstack';
        }
    }
}