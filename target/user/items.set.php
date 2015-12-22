<?php
namespace osi;
require_once __DIR__ . '/items.router.php';

class Set_User_Items{
	const CAPACITY = 1000;

	private $_elements	= NULL;

	public function __construct(array $elements = array()) {
		$this->_elements	= $elements;
	}

	public function fetchAll() {
		if(!isset($this->_elements) || !is_array($this->_elements)) {
			return array();
		}
		
		return $this->_elements;
	}

	public function fetch($elementId){
		if(!isset($this->_elements) || !isset($this->_elements[$elementId]))
			return NULL;

		return $this->_elements[$elementId];
	}

	public function put($elementId, SetElement_User_Items $element){
		if(!isset($this->_elements) || !is_array($this->_elements))
			$this->_elements = array();

		$this->_elements[$elementId] = $element;
		if(count($this->_elements) > self::CAPACITY)
			throw new Exception('max capacity "1000" reached');
	}

	public function puts(Array $elements){
		$this->_elements += $elements;
		if(count($this->_elements) > self::CAPACITY)
			throw new Exception('max capacity "1000" reached');
	}

	public function erase($elementId){
		if(!isset($this->_elements) || !is_array($this->_elements) || !isset($this->_elements[$elementId]))
			return;

		unset($this->_elements[$elementId]);
	}

	public function clear(){
		$this->_elements	= array();
	}

	public function count(){
		if(!isset($this->_elements) || !is_array($this->_elements))
			return 0;
		return count($this->_elements);
	}

	public function listByNameAsc($offset, $number){
		if(!isset($this->_elements) || !is_array($this->_elements))
			return array();

		uasort($this->_elements, create_function('$lhs, $rhs', 'return strcmp($lhs->name, $rhs->name);'));
		return array_slice($this->_elements, $offset, $number, true);
	}

	public function listByNameDesc($offset, $number){
		if(!isset($this->_elements) || !is_array($this->_elements))
			return array();

		uasort($this->_elements, create_function('$lhs, $rhs', 'return strcmp($rhs->name, $lhs->name);'));
		return array_slice($this->_elements, $offset, $number, true);
	}

	static public function load($id){
		$elements = SetRouter_User_Items::load($id);
		if(!isset($elements)){
			return NULL;
		}
		return new Set_User_Items($elements);
	}

	static public function save($id,  Set_User_Items $set){
		SetRouter_User_Items::save($id, $set->_elements);
	}

}

class SetElement_User_Items extends SetElement{
	public $name = '';
	public $level = 100;
	public $statistics = null;

	public function __construct(){
		$this->statistics = new SetElement_User_ItemsStatistics();
	}
}

class SetElement_User_ItemsStatistics extends SetElement{
	public $useTimes = 0;
	public $remainNum = 0;

	public function __construct(){
	}
}
