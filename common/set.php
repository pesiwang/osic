<?php
class SetField{
	const INDEX_NONE	= 0x00;
	const INDEX_NUMERIC = 0x01;
	const INDEX_STRING	= 0x02;
	
	const TYPE_UNKNOWN = 0x00;
	const TYPE_STRING = 0x01;
	const TYPE_INTEGER = 0x02;
	const TYPE_BIGINT = 0x03;
	
	protected $_type;
	protected $_length;
	protected $_value;
	protected $_index;
	
	public function __construct(){
		$this->_type = self::TYPE_UNKNOWN;
		$this->_index = self::INDEX_NONE;
		$this->_length = 0;
		$this->_value = null;
	}
	
	public function getType(){
		return $this->_type;
	}
	
	public function setType($type){
		switch(strtoupper($type)){
			case 'STRING':
				$this->_type = self::TYPE_STRING;
				break;
			case 'INTEGER':
				$this->_type = self::TYPE_INTEGER;
				break;
			case 'BIGINT':
				$this->_type = self::TYPE_BIGINT;
				break;
			default:
				throw new Exception('unknown type[' . $type . ']');
				break;
		}
		return $this;
	}
	
	public function getIndex(){
		return $this->_index;
	}
	
	public function setIndex($index){
		$this->_index = $index;
		return $this;
	}
	
	public function getLength(){
		return $this->_length;
	}
	
	public function setLength($length){
		$this->_length = $length;
		return $this;
	}
	
	public function getValue(){
		return $this->_value;
	}
	
	public function setValue($value){
		$this->_value = $value;
		return $this;
	}
}

class Set{
	protected $_name;
	protected $_key;
	protected $_element;
	protected $_capacity;
	protected $_fields;
	
	public function __construct(){
		$this->_name = '';
		$this->_key = null;
		$this->_element = null;
		$this->_fields = array();
	}
	
	public function getName(){
		return $this->_name;
	}
	
	public function setName($name){
		$this->_name = $name;
		return $this;
	}
	
	public function getKey(){
		return $this->_key;
	}
	
	public function setKey(SetField $key){
		$this->_key = $key;
		return $this;
	}
	
	public function getElement(){
		return $this->_element;
	}
	
	public function setElement(SetField $element){
		$this->_element = $element;
		return $this;
	}
	
	public function getCapacity(){
		return $this->_capacity;
	}
	
	public function setCapacity($capacity){
		$this->_capacity = $capacity;
		return $this;
	}
	
	public function addField($name, SetField $field){
		$this->_fields[$name] = $field;
		return $this;
	}
	
	public function getFields(){
		return $this->_fields;
	}
}