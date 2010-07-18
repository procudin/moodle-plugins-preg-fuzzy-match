<?php //$Id: reasc.php,put version put time dvkolesov Exp $

define('LEAF','0');
define('NODE','1');
define('LEAF_CHARCLASS','2');
define('LEAF_EMPTY','3');
define('LEAF_END','4');
define('LEAF_LINK','5');
define('LEAF_METASYMBOLDOT','6');
define('NODE_CONC','7');
define('NODE_ALT','8');
define('NODE_ITER','9');
define('NODE_SUBPATT','10');
define('NODE_CONDSUBPATT','11');
define('NODE_QUESTQUANT','12');
define('NODE_PLUSQUANT','13');
define('NODE_QUANT','14');
define('NODE_ASSERTTF','15');
define('NODE_ASSERTTB','16');
define('NODE_ASSERTFF','17');
define('NODE_ASSERTFB','18');
define('ASSERT','107741824');
define('DOT','987654321');
define('STREND','123456789');

/*
*���������� ��� ����������:
*buildfa 							��������, 	���������� �������� ������					!!	!
*	numeration 						������, 	���������� ���
*	nullable 						������, 	���������� ���
*	firstpos 						������, 	���������� ���
*	lastpos 						������, 	���������� ���
*	followpos 						������, 	���������� ������
*		fp_push 					������, 	���������� ���
*	find_asserts 					������, 	���������� ���								
*	not_marked_state 				������, 	���������� ���								
*	followposU 						������, 	���������� ������								
*		is_include_characters 		������, 	���������� ���								
*	state 							��������, 	���������� ���								!!
*��� ���������� ��� �������� 2 (���) �������.
*/

class node {
	var $type;
	var $subtype;
	var $firop;
	var $secop;
	var $thirdop;
	var $nullable;
	var $number;
	var $firstpos;
	var $lastpos;
	var $direction;
	var $greed;
	var $chars;
	
	function name() {
		return 'node';
	}
}

class fas {//finite automate state
	var $asserts;
	var $passages;//������ ������ ��������� � ������� �������
	var $marked;//if mrked then true else false.
	
	function name() {
		return 'fas';
	}
}

class compare_result {
	var $index;
	var $full;
	var $next;
	
	function name() {
		return 'compare_result';
	}
}

class reasc {
	var $connection;//array, $connection[0] for main regex, $connection[<assert number>] for asserts
	var $cconn;//for current connection
	var $roots;//array,[0] main root, [<assert number>] assert's root
	var $croot;//for current root
	var $maxnum;
	var $finiteautomate;// for current finite  automate
	var $finiteautomates;
	
	function name() {
		return 'reasc';
	}
	function append_end() {
	}
	/**
	*Function numerate leafs, nodes use for find leafs. Start on root and move to leafs.
	*Put pair of number=>character to $this->cconn.
	*@param $node current node (or leaf) for numerating.
	*/
	function numeration($node) {
		if($node->type==NODE&&$node->subtype==NODE_ASSERTTF) {//assert node need number
			$node->number = ++$this->maxnum + ASSERT;
		} else if($node->type==NODE) {//not need number for not assert node, numerate operands
			$this->numeration($node->firop);
			if ($node->subtype==NODE_CONC||$node->subtype==NODE_ALT) {//concatenation and alternative have second operand, numerate it.
				$this->numeration($node->secop);
			}
		}
		if($node->type==LEAF) {//leaf need number
			switch($node->subtype) {//number depend from subtype (charclass, metasymbol dot or end symbol)
				case LEAF_CHARCLASS://normal number for charclass
					$node->number = ++$this->maxnum;
					$this->cconn[$this->maxnum] = $node->chars;
					break;
				case LEAF_END://STREND number for end leaf
					$node->number = STREND;
					break;
				case LEAF_METASYMBOLDOT://normal + DOT for dot leaf
					$node->number = ++$this->maxnum + DOT;
					break;
			}
		}
	}
	/**
	*Function determine: subtree with root in this node can give empty word or not.
	*@param node - node fo analyze
	*@return true if can give empty word, else false
	*/
	function nullable($node) {
		$result = false;
		if($node->type==NODE) {
			switch($node->subtype) {
				case NODE_ALT://alternative can give empty word if one operand can.
					$result = ($this->nullable($node->firop)||$this->nullable($node->secop));
					break;
				case NODE_CONC://concatenation can give empty word if both operands can.
					$result = ($this->nullable($node->firop)&&$this->nullable($node->secop));
					$this->nullable($node->secop);
					break;
				case NODE_ITER://iteration and question quantificator can give empty word without dependence from operand.
				case NODE_QUESTQUANT:
					$result = true;
					$this->nullable($node->firop);
					break;
				case NODE_ASSERTTF://assert can give empty word.
					$result = true;
					break;//operand of assert not need for main finite automate. It form other finite automate.
			}
		}
		$node->nullable = $result;//save result in node
		return $result;
	}
	/**
	*������� ���������� ����� ������� ����� ������ �� 1-� ����� � ����� ����������� ���������� � �������� � ������ ����
	*@param $node root of subtree giving word
	*@return numbers of characters (array)
	*/
	function firstpos($node) {
		if($node->type==NODE) {
			switch($node->subtype) {
				case NODE_ALT:
					$result = array_merge($this->firstpos($node->firop), $this->firstpos($node->secop));
					break;
				case NODE_CONC:
					$result = $this->firstpos($node->firop);
					if($node->firop->nullable) {
						$result = array_merge($result, $this->firstpos($node->secop));
					} else {
						$this->firstpos($node->secop);
					}
					break;
				case NODE_QUESTQUANT:
				case NODE_ITER:
					$result = $this->firstpos($node->firop);
					break;
				case NODE_ASSERTTF:
					$result = array($node->number);
					break;
			}
		} else {
			if($node->direction) {
				$result = array($node->number);
			} else {
				$result = array(-$node->number);
			}
		}
		$node->firstpos = $result;
		return $result;
	}
	/**
	*������� ���������� ������� ������� ����� ������ �� ��������� ����� � ����� �����������
	*���������� � �������� � ������ ����
	@param $node - root of subtree
	@return numbers of characters (array)
	*/
	function lastpos($node) {
		if($node->type==NODE) {
			switch($node->subtype) {
				case NODE_ALT:
					$result = array_merge($this->lastpos($node->firop), $this->lastpos($node->secop));
					break;
				case NODE_CONC:
					$result = $this->lastpos($node->secop);
					if($node->secop->nullable) {
						$result = array_merge($this->lastpos($node->firop), $result);
					} else {
						$this->lastpos($node->firop);
					}
					break;
				case NODE_ITER:
				case NODE_QUESTQUANT:
					$result = $this->lastpos($node->firop);
					break;
				case NODE_ASSERTTF:
					$result = array($node->number);
					break;
			}
		} else {
			if($node->direction) {
				$result = array($node->number);
			} else {
				$result = array(-$node->number);
			}
		}
		$node->lastpos = $result;
		return $result;
	}
	function followpos($node, &$fpmap) {
		if($node->type==NODE) {
			switch($node->subtype) {
				case NODE_CONC:
					$this->followpos($node->firop, $fpmap);
					$this->followpos($node->secop, $fpmap);
					foreach($node->firop->lastpos as $key) {#need fix
						$this->fp_push($fpmap[$key], $node->secop->firstpos);
					}
					break;
				case NODE_ITER:
					$this->followpos($node->firop, $fpmap);
					foreach($node->firop->lastpos as $key) {#need fix
						$this->fp_push($fpmap[$key], $node->firop->firstpos);
					}
					break;
				case NODE_ALT:
					$this->followpos($node->secop, $fpmap);
				case NODE_QUESTQUANT:
					$this->followpos($node->firop, $fpmap);
					break;
			}
		}
	}
	function buildfa() {//��������� ��������� ��� ����������� � ���� finiteautomates[0][0]
						//o�������� ��������� � ������ ��-��� ����� �������,finiteautomate[!=0] - asserts' fa
	
	}
	function compare($string, $assertnumber) {//if main regex then assertnumber is 0
		$result = new compare_result;
		return $result;
	}
	function fp_push(&$arr1, $arr2) {
		foreach($arr2 as $value) {
			if(!in_array($value, $arr1)) {
				$arr1[] = $value;
			}
		}
	}
	function find_asserts($node) {
		if($node->type==NODE) {
			switch($node->subtype) {
				case NODE_ASSERTTF:
					$this->roots[$node->number] = $node;
					break;
				case NODE_ALT:
				case NODE_CONC:
					$this->find_asserts($node->secop);
				case NODE_ITER:
				case NODE_QUESTQUANT:
					$this->find_asserts($node->firop);
					break;
			}
		}
	}
	function not_marked_state($built) {
		$notmarkedstate = false;
		$size = count($built);
		for($i = 0; $i<$size&&!$notmarkedstate; $i++) {
			if(!$built[$i]->marked) {
				$notmarkedstate = $built[$i];
			}
		}
		return $not_marked_state;
	}
	function is_include_characters($strin1, $string2) {
		$result = true;
		$size = strlen($string2);
		for($i = 0; $i<$size&&$result;$i++) {
			if(strpos($string1, $string2[$i])===false) {
				$result = false;
			}
		}
		return $result;
	}
	function followposU($number, $fpmap, $passages) {
		$str1 = $this->cconn[$number];//for this charclass will found equivalent numbers
		foreach($this->cconn as $num=>$cc) {//forming vector of equivalent numbers
			$str2 = $cc;
			if(is_include_characters($str1, $str2)&&array_key_exists($num)) {//if charclass 1 and 2 equivalenta and number exist in passages
				array_push($equnum, $num);
			}
		}
		foreach($equnum as $num) {//forming map of following numbers
			$this->fp_push($followU, $fpmap);
		}
		return $followU;
	}
}
?>