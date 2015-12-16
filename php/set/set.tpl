<?php
namespace osi;
require_once __DIR__ . '/<%$name|osic_name2file%>.router.php';

class Set_<%$name|osic_name2class%>{
	const CAPACITY = <%$set->capacity%>;

	private $_elements	= NULL;

	public function __construct(array $elements = array()) {
		$this->_elements	= $elements;
	}
	
	public function fetch($elementId){
		if(!isset($this->_elements) || !isset($this->_elements[$elementId]))
			return NULL;

		return $this->_elements[$elementId];
	}

	public function put($elementId, SetElement_<%$name|osic_name2class%> $element){
		if(!isset($this->_elements) || !is_array($this->_elements))
			$this->_elements = array();

		$this->_elements[$elementId] = $element;
		if(count($this->_elements) > self::CAPACITY)
			throw new Exception('max capacity "<%$set->capacity%>" reached');
	}

	public function puts(Array $elements){
		$this->_elements += $elements;
		if(count($this->_elements) > self::CAPACITY)
			throw new Exception('max capacity "<%$set->capacity%>" reached');
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
<%foreach from=$set->element->fields item=field%>
<%if $field->index != NULL%>

	public function listBy<%$field->name|ucfirst%>($offset, $number){
		if(!isset($this->_elements) || !is_array($this->_elements))
			return array();
<%if strcasecmp($field->index, 'STRING') == 0%>
		uasort($this->_elements, create_function('$lhs, $rhs', 'return strcmp($lhs-><%$field->name%>, $rhs-><%$field->name%>);'));
<%else%>
		uasort($this->_elements, create_function('$lhs, $rhs', 'return $lhs-><%$field->name%> - $rhs-><%$field->name%>;'));
<%/if%>
		return array_slice($this->_elements, $offset, $number, true);
	}
<%/if%>
<%/foreach%>

	static public function load($id){
		$elements = SetRouter_<%$name|osic_name2class%>::load($id);
<%if isset($obsolete_router)%>
		if(!isset($elements)){
			$elements = SetRouterObsolete_<%$name|osic_name2class%>::load($id);
			if(isset($elements))
				SetRouter_<%$name|osic_name2class%>::save($id, $elements);
		}
<%/if%>
		if(!isset($elements)){
			return NULL;
		}
		return new Set_<%$name|osic_name2class%>($elements);
	}

	static public function save($id,  Set_<%$name|osic_name2class%> $set){
		SetRouter_<%$name|osic_name2class%>::save($id, $set->_elements);
	}

}

class SetElement_<%$name|osic_name2class%> extends SetElement{
<%foreach from=$set->element->fields item=field%>
<%if strcasecmp($field->type, 'OBJECT')==0%>
	public $<%$field->name%> = null;
<%else%>
	public $<%$field->name%> = <%if strcasecmp($field->type, 'STRING') == 0%>'<%$field->value%>'<%else%><%$field->value%><%/if%>;
<%/if%>
<%/foreach%>

	public function __construct(){
<%foreach from=$set->element->fields item=field%>
<%if strcasecmp($field->type, 'OBJECT')==0%>
		$this-><%$field->name%> = new SetElement_<%$name|osic_name2class%><%$field->fullname|osic_name2class%>();
<%/if%>
<%/foreach%>
	}
}
<%foreach from=$aux_objects key=aux_name item=aux_object%>

class SetElement_<%$name|osic_name2class%><%$aux_name|osic_name2class%> extends SetElement{
<%foreach from=$aux_object item=field%>
<%if strcasecmp($field->type, 'OBJECT')==0%>
	public $<%$field->name%> = null;
<%else%>
	public $<%$field->name%> = <%if strcasecmp($field->type, 'STRING') == 0%>'<%$field->value%>'<%else%><%$field->value%><%/if%>;
<%/if%>
<%/foreach%>

	public function __construct(){
<%foreach from=$aux_object item=field%>
<%if strcasecmp($field->type, 'OBJECT')==0%>
		$this-><%$field->name%> = new SetElement_<%$name|osic_name2class%><%$field->fullname|osic_name2class%>();
<%/if%>
<%/foreach%>
	}
}
<%/foreach%>
