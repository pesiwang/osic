<?php
namespace osi;
require_once __DIR__ . '/<%$name|osic_name2file%>.router.php';

class Set_<%$name|osic_name2class%>{
	const CAPACITY = <%$set->capacity%>;

	static public function fetch($id, $elementId){
		$elements = self::_loadElements($id);
		if(!isset($elements) || !isset($elements[$elementId]))
			return NULL;

		return $elements[$elementId];
	}

	static public function put($id, $elementId, SetElement_<%$name|osic_name2class%> $element){
		$elements = self::_loadElements($id);
		if(!isset($elements) || !is_array($elements))
			$elements = array();

		$elements[$elementId] = $element;
		if(count($elements) > <%$set->capacity%>)
			throw new Exception('max capacity "<%$set->capacity%>" reached');

		self::_saveElements($id, $elements);
	}

	static public function puts($id, Array $elements){
		$oldElements = self::_loadElements($id);
		if(!isset($oldElements) || !is_array($oldElements))
			$oldElements = array();

		$elements += $oldElements;
		if(count($elements) > <%$set->capacity%>)
			throw new Exception('max capacity "<%$set->capacity%>" reached');

		self::_saveElements($id, $elements);
	}

	static public function erase($id, $elementId){
		$elements = self::_loadElements($id);
		if(!isset($elements) || !is_array($elements) || !isset($elements[$elementId]))
			return;

		unset($elements[$elementId]);
		self::_saveElements($id, $elements);
	}

	static public function clear($id){
		self::_saveElements($id, array());
	}

	static public function count($id){
		$elements = self::_loadElements($id);
		if(!isset($elements) || !is_array($elements))
			return 0;
		return count($elements);
	}
<%foreach from=$set->element->fields item=field%>
<%if $field->index != NULL%>

	static public function listBy<%$field->name|ucfirst%>($id, $offset, $number){
		$elements = self::_loadElements($id);
		if(!isset($elements) || !is_array($elements))
			return array();
<%if strcasecmp($field->index, 'STRING') == 0%>
		uasort($elements, create_function('$lhs, $rhs', 'return strcmp($lhs-><%$field->name%>, $rhs-><%$field->name%>);'));
<%else%>
		uasort($elements, create_function('$lhs, $rhs', 'return $lhs-><%$field->name%> - $rhs-><%$field->name%>;'));
<%/if%>
		return array_slice($elements, $offset, $number, true);
	}
<%/if%>
<%/foreach%>

	static protected function _loadElements($id){
		$elements = SetRouter_<%$name|osic_name2class%>::load($id);
<%if isset($obsolete_router)%>
		if(!isset($elements)){
			$elements = SetRouterObsolete_<%$name|osic_name2class%>::load($id);
			if(isset($elements))
				SetRouter_<%$name|osic_name2class%>::save($id, $elements);
		}
<%/if%>
		return $elements;
	}

	static protected function _saveElements($id, Array $elements){
		SetRouter_<%$name|osic_name2class%>::save($id, $elements);
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
