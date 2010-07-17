<?php  // $Id: testquestiontype.php,put version put time dvkolesov Exp $
/**
 * Unit tests for (some of) question/type/preg/reasc.php.
 *
 * @copyright &copy; 2010 Dmitriy Kolesov
 * @author Dmitriy Kolesov
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package question
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/question/type/preg/reasc.php');

class reasc_test extends UnitTestCase {
    var $qtype;
    
    function setUp() {
        $this->qtype = new reasc();
    }
    
    function tearDown() {
        $this->qtype = null;   
    }

    function test_name() {
        $this->assertEqual($this->qtype->name(), 'reasc');
    }
	//service function for testing
	/**
	*form the tree of the regexp from prefix form, for unit tests only!
	*this function is unsafe, input data must be correct!
	*
	*@param prefixform string with regexp in prefix form
	*@return croot of formed tree
	*/
	function form_tree($prefixform){
		$result = new node;
		//forming the node or leaf
		switch($prefixform[1]){ //analyze first character, type of node/leaf
			case 'l': //simple leaf with char class
				$result->type = LEAF;
				$result->subtype = LEAF_CHARCLASS;
				$result->chars = null;
				for($i=2; $prefixform[$i+1]!=')'; $i++){
					$result->chars.=$prefixform[$i];
				}
				if($prefixform[$i]=='0'){
					$result->direction=false;
				} else {
					$result->direction=true;
				}
				break;
			case 'e': //empty leaf
				$result->type = LEAF;
				$result->subtype = LEAF_EMPTY;
				break;
			case 'd': //metasymbol dot
				$result->type = LEAF;
				$result->subtype = LEAF_METASYMBOLDOT;
				break;
			case 'n':
				$result->type = NODE;
				switch($prefixform[2]){
					case 'o': //concatenation node
						$result->subtype = NODE_CONC;
						break;
					case '|': //alternative node
						$result->subtype = NODE_ALT;
						break;
					case '*': //iteration node
						$result->subtype = NODE_ITER;
						break;
					case '?': //quantificator ? node
						$result->subtype = NODE_QUESTQUANT;
						break;
					case 'A': //true forward assert node
						$result->subtype = NODE_ASSERTTF;
						break;
				}
				//forming operand
				$brackets=0;
				$tmp=null;
				for($i=4; $brackets!=0||$i==4; $i++){
					$tmp.=$prefixform[$i];
					if($prefixform[$i]=='('){
						$brackets++;
					}
					if($prefixform[$i]==')'){
						$brackets--;
					}
				}
				//forming second operand
	//			$result->firop = form_tree($tmp);
				if($result->subtype==NODE_CONC||$result->subtype==NODE_ALT){
					$tmp = null;
					do{
						$tmp.=$prefixform[$i];
						if($prefixform[$i]=='('){
							$brackets++;
						}
						if($prefixform[$i]==')'){
							$brackets--;
						}
						$i++;
					}while($brackets!=0);
		//			$result->secop = form_tree($tmp);
				}
				break;
		}
		return $result;
	}
	//Unit test for nullable function
	function test_nullable_leaf(){
		$node = $this->form_tree('(la1)');
		$this->assertFalse($this->qtype->nullable($node));
	}
	function test_nullable_leaf_iteration_node(){
		$node = $this->form_tree('(n* (la1))');
		$this->assertTrue($this->qtype->nullable($node));
	}
	function test_nullable_leaf_concatenation_node(){
		$node = $this->form_tree('(no (la1)(lb1))');
		$this->assertFalse($this->qtype->nullable($node));
	}
	function test_nullable_leaf_alternative_node(){
		$node = $this->form_tree('(n| (la1)(lb1))');
		$this->assertFalse($this->qtype->nullable($node));
	}
	function test_nullable_node_concatenation_node(){
		$node = $this->form_tree('(no (n* (la1))(no (lb1)(lc1)))');
		$this->assertFalse($this->qtype->nullable($node));
	}
	function test_nullable_node_alternative_node(){
		$node = $this->form_tree('(n| (n* (la1))(no (lb1)(lc1)))');
		$this->assertTrue($this->qtype->nullable($node));
	}
	function test_nullable_third_level_node(){
		$node = $this->form_tree('(n| (n| (n| (la1)(lb1))(n* (lc1)))(n* (ld1)))');
		$this->assertTrue($this->qtype->nullable($node));
	}
	function test_nullable_question_quantificator(){
		$node = $this->form_tree('(n? (la1))');
		$this->assertTrue($this->qtype->nullable($node));
	}
	function test_nullable_negative_character_class(){
		$node = $this->form_tree('(la0)');
		$this->assertFalse($this->qtype->nullable($node));
	}
	function test_nullable_assert(){
		$node = $this->form_tree('(no (la1)(no (nA (no (n* (d))(lb1)))(n* (lxcvbnm1))))');
		$this->assertTrue($this->qtype->nullable($node->secop->firop));
	}
	//Unit test for firstpos function
	function test_firstpos_leaf(){
		$node = $this->form_tree('(la1)');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->firstpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_firstpos_leaf_concatenation_node(){
		$node = $this->form_tree('(no (la1)(lb1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->firstpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_firstpos_leaf_alternative_node(){
		$node = $this->form_tree('(n| (la1)(lb1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result=$this->qtype->firstpos($node);
		$this->assertTrue(count($result)==2&&$result[0]==1&&$result[1]==2);
	}
	function test_firstpos_three_leaf_alternative(){
		$node = $this->form_tree('(n| (la1)(n| (lb1)(lc1)))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->firstpos($node);
		$this->assertTrue(count($result)==3&&$result[0]==1&&$result[1]==2&&$result[2]==3);
	}
	function test_firstpos_leaf_iteration_node(){
		$node = $this->form_tree('(n* (la1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->firstpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_firstpos_node_concatenation_node(){
		$node = $this->form_tree('(no (n* (lc1))(n| (la1)(lb1)))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->firstpos($node);
		$this->assertTrue(count($result)==3&&$result[0]==1&&$result[1]==2&&$result[2]==3);
	}
	function test_firstpos_node_alternative_node(){
		$node = $this->form_tree('(n| (n| (la1)(lb1))(n* (lc1)))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->firstpos($node);
		$this->assertTrue(count($result)==3&&$result[0]==1&&$result[1]==2&&$result[2]==3);
	}
	function test_firstpos_node_iteration_node(){
		$node = $this->form_tree('(n* (n* (la1)))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->firstpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_firstpos_question_quantificator(){
		$node = $this->form_tree('(n? (la1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->firstpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_firstpos_negative_character_class(){
		$node = $this->form_tree('(no (la0)(lb1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$this->qtype->firstpos($node);
		$this->assertTrue(count($node->firstpos)==1&&$node->firstpos[0]==-1);
		$this->assertTrue(count($node->firop->firstpos)==1&&$node->firop->firstpos==-1);
	}
	function test_firstpos_assert(){
		$node = $this->form_tree('(no (la1)(no (nA (no (n* (d))(lb1)))(n* (lxcvbnm1))))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$this->qtype->firstpos($node);
		$this->assertTrue(count($node->secop->firop->firstpos)&&$node->secop->firop->firstpos[0]>ASSERT);
	}
	//Unit test for lastpos function
	function test_lastpos_leaf(){
		$node = $this->form_tree('(la1)');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_lastpos_leaf_concatenation_node(){
		$node = $this->form_tree('(no (la1)(lb1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==2);
	}
	function test_lastpos_leaf_alterbative_node(){
		$node = $this->form_tree('(n| (la1)(lb1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==2&&$result[0]==1&&$result[1]==2);
	}
	function test_lastpos_leaf_iteration_node(){
		$node = $this->form_tree('(n* (la1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_lastpos_node_concatenation_node(){
		$node = $this->form_tree('(no (n| (la1)(lb1))(n* (lc1)))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==3&&$result[0]==1&&$result[1]==2&&$result[2]==3);
	}
	function test_lastpos_node_alternative_node(){
		$node = $this->form_tree('(n| (n| (la1)(lb1))(n* (lc1)))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==3&&$result[0]==1&&$result[1]==2&&$result[2]==3);
	}
	function test_lastpos_node_iteration_node(){
		$node = $this->form_tree('(n* (n* (la1)))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_lastpos_question_quantificator(){
		$node = $this->form_tree('(n? (la1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==1&&$result[0]==1);
	}
	function test_lastpos_negative_character_class(){
		$node = $this->form_tree('(n| (la0)(lb1))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$result = $this->qtype->lastpos($node);
		$this->assertTrue(count($result)==2&&$result[0]==-1&&$result[1]==2);
	}
	function test_lastpos_assert(){
		$node = $this->form_tree('(no (la1)(no (nA (no (n* (d))(lb1)))(n* (lxcvbnm1))))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$this->qtype->lastpos($node);
		$this->assertTrue(count($node->secop->firop->lastpos)&&$node->secop->firop->lastpos[0]>ASSERT);
	}
	//Unit tests for followpos function
	function test_followpos_node_concatenation_node(){
		$node = $this->form_tree('(no (n* (n| (la1)(lb1)))(no (la1)(lb1)))');
		$this->qtype->numeration($node);
		$this->qtype->nullable($node);
		$this->qtype->firstpos($node);
		$this->qtype->lastpos($node);
		$result=null;
		$this->qtype->followpos($node, $result);
		$res1 = (count($result[1])==3&&$result[1][0]==1&&$result[1][1]==2&&$result[1][2]==3);
		$res2 = (count($result[2])==3&&$result[2][0]==1&&$result[2][1]==2&&$result[2][2]==3);
		$res3 = (count($result[3])==1&&$result[3][0]==4);
		$this->assertTrue($res1&&$res2&&$res3);
	}
	function test_followpos_three_node_alternative(){
		$node = $this->form_tree('((n| (n| (no (la1)(lb1))(no (lc1)(ld1)))(no (le1)(lf1)))');
		$this->qtype->numeration($node);
		$this->qtype->firstpos($node);
		$this->qtype->lastpos($node);
		$result=null;
		$this->qtype->followpos($node, $result);
		$res1 = (count($result[1])==1&&$result[1][0]==2);
		$res2 = (count($result[3])==1&&$result[3][0]==4);
		$res3 = (count($result[5])==1&&$result[5][0]==6);
		$this->assertTrue($res1&&$res2&&$res3);
	}
	function test_followpos_question_quantificator(){
		$node = $this->form_tree('(no (n? (la1))(lb1))');
		$this->qtype->numeration($node);
		$this->qtype->firstpos($node);
		$this->qtype->lastpos($node);
		$result=null;
		$this->qtype->followpos($node, $result);
		$this->assertTrue(count($result[1])==1&&$result[1][0]==2);
	}
	function test_followpos_negative_character_class(){
		$node = $this->form_tree('(no (la0)(lb1))');
		$this->qtype->numeration($node);
		$this->qtype->firstpos($node);
		$this->qtype->lastpos($node);
		$result=null;
		$this->qtype->followpos($node, $result);
		$this->assertTrue(count($result[-1])==1&&$result[-1][0]==2);
	}
	//Unit test for buildfa function
	function test_buildfa_easy(){//ab
		$this->qtype->croot = $this->form_tree('(no (la1)(lb1))');
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==1&&$this->qtype->finiteautomate[0]->passages[1]==1);
		$this->assertTrue(count($this->qtype->finiteautomate[1]->passages)==1&&$this->qtype->finiteautomate[1]->passages[2]==2);
		$this->assertTrue(count($this->qtype->finiteautomate[2]->passages)==1&&$this->qtype->finiteautomate[2]->passages[STREND]==-1);
	}
	function test_buildfa_iteration(){//ab*
		$this->qtype->croot = $this->form_tree('(no (la1)(n* (lb1)))');
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==2);
		$n1 = $this->qtype->finiteautomate[0]->passages[1];
		$n2 = $this->qtype->finiteautomate[0]->passages[2];
		$this->assertTrue(count($this->qtype->finiteautomate[$n1]->passages)==2&&$this->qtype->finiteautomate[$n1]->passages[2]==$n2&&
							$this->qtype->finiteautomate[$n1]->passages[1]==$n1);
		$this->assertTrue(count($this->qtype->finiteautomate[$n2]->passages)==1&&$this->qtype->finiteautomate[$n2]->passages[STREND]==-1);
	}
	function test_buildfa_alternative(){//a|b
		$this->qtype->croot = $this->form_tree('(n| (la1)(lb1))');
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==2&&$this->qtype->finiteautomate[0]->passages[1]==1&&
							$this->qtype->finiteautomate[0]->passages[2]==1);
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==1&&$this->qtype->finiteautomate[1]->passages[STREND]==-1);
	}
	function test_buildfa_alternative_and_iteration(){//(a|b)c*
		$this->qtype->croot = $this->form_tree('(no (n| (la1)(lb1))(n* (lc1)))');
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==2);
		$n1 = $this->qtype->finiteautomate[0]->passages[1];
		$n2 = $this->qtype->finiteautomate[0]->passages[2];
		$this->assertTrue(count($this->qtype->finiteautomate[$n1]->passages)==2&&$this->qtype->finiteautomate[$n1]->passages[3]==2&&
							$this->qtype->finiteautomate[$n1]->passages[STREND]==-1);
		$this->assertTrue(count($this->qtype->finiteautomate[$n2]->passages)==1&&$this->qtype->finiteautomate[$n2]->passages[STREND]==-1);
	}
	function test_buildfa_nesting_alternative_and_iteration(){//(ab|cd)*
		$this->qtype->croot = $this->form_tree('(n* (n| (no (la1)(lb1))(no (lc1)(ld1))))');
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==3&&$this->qtype->finiteautomate[0]->passages[STREND]==-1);
		$n1 = $this->qtype->finiteautomate[0]->passages[1];
		$n2 = $this->qtype->finiteautomate[0]->passages[3];
		$this->assertTrue(count($this->qtype->finiteautomate[$n1]->passages)==1&&$this->qtype->finiteautomate[$n1]->passages[2]==0);
		$this->assertTrue(count($this->qtype->finiteautomate[$n2]->passages)==1&&$this->qtype->finiteautomate[$n2]->passages[4]==0);
	}
	function test_buildfa_question_quantificator(){//a?b
		$this->qtype->croot = $this->form_tree('(no (n? (la1))(lb1))');
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==2);
		$n1 = $this->qtype->finiteautomate[0]->passages[1];
		$n2 = $this->qtype->finiteautomate[0]->passages[2];
		$this->assertTrue(count($this->qtype->finiteautomate[$n1]->passages)==1&&$this->qtype->finiteautomate[$n1]->passages[2]==$n2);
		$this->assertTrue(count($this->qtype->finiteautomate[$n2]->passages)==1&&$this->qtype->finiteautomate[$n2]->passages[STREND]==-1);
	}
	function test_buildfa_negative_character_class(){//(a[^b]|c[^d])*
		$this->qtype->croot = $this->form_tree('(n* (n| (no (la1)(lb0))(no (lc1)(ld0))))');
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==3);
		$n1 = $this->qtype->finiteautomate[0]->passages[1];
		$n2 = $this->qtype->finiteautomate[0]->passages[3];
		$this->assertTrue(count($this->qtype->finiteautomate[$n1]->passages)==1&&$this->qtype->finiteautomate[$n1]->passages[-2]==0);
		$this->assertTrue(count($this->qtype->finiteautomate[$n2]->passages)==1&&$this->qtype->finiteautomate[$n2]->passages[-4]==0);
	}
	function test_buildfa_assert(){//a(?=.*b)[xcvbnm]*
		$this->qtype->croot = $this->form_tree('(no (la1)(no (nA (no (n* (d))(lb1)))(n* (lxcvbnm))))');
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->asserts)==1&&count($this->qtype->finiteautomate[0]->passages)==1);
		$this->assertTrue(count($this->qtype->finiteautomate[1]->passages)==2&&$this->qtype->finiteautomate[1]->passages[3]==1&&
							$this->qtype->finiteautomate[1]->passages[STREND]==-1);
		$this->assertTrue(count($this->qtype->roots)==2&&next($this->qtype->roots)->subtype==NODE_ASSERTTF);
		$this->qtype->croot = current($this->qtype->roots);
		$this->qtype->append_end();
		$this->qtype->buildfa();
		$this->assertTrue(count($this->qtype->finiteautomate[0]->passages)==2&&$this->qtype->finiteautomate[0]->passages[DOT+1]==0&&
							$this->qtype->finiteautomate[0]->passages[2]==2);
		$this->assertTrue(count($this->qtype->finiteautomate[1]->passages)==1&&$this->qtype->finiteautomate[1]->passages[2]==2);
		$this->assertTrue(count($this->qtype->finiteautomate[2]->passages)==1&&$this->qtype->finiteautomate[2]->passages[STREND]==-1);
	}
}
?>