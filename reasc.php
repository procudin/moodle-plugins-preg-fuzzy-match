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


class node{
	/*
	enum type;
	enum subtype;
	pointer firop,secop,thirdop;
	bool nullable;
	int number;
	vector firstpos,lastpos,followpos;
	bool direction;
	bool greed;
	string chars;
	*/
	function name(){
		return 'node';
	}
}

class reasc{

	function name(){
		return 'reasc';
	}
	function numeration($node){
		return -1;
	}
	function nullable($node){
		return true;
	}
	function firstpos($node){
		$result = array(0,0,0);
		return $result;
	}
	function lastpos($node){
		$result = array(0,0,0);
		return $result;
	}
	function followpos($node, $fpmap){
		$result=array(
			array(0,0,0),
			array(0,0,0),
			array(0,0,0));
		return $result;
	}
}
?>