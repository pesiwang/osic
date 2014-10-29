<?php
namespace osi;
require_once __DIR__ . '/<%$name|osic_name2file%>.router.php';

class Object_<%$name|osic_name2class%> extends Object{
<%foreach from=$object->fields item=field%>
<%if strcasecmp($field->type, 'OBJECT')==0%>
	public $<%$field->name%> = null;
<%else%>
	public $<%$field->name%> = <%if strcasecmp($field->type, 'STRING') == 0%>'<%$field->value%>'<%else%><%$field->value%><%/if%>;
<%/if%>
<%/foreach%>

	public function __construct(){
<%foreach from=$object->fields item=field%>
<%if strcasecmp($field->type, 'OBJECT')==0%>
		$this-><%$field->name%> = new Object_<%$name|osic_name2class%><%$field->fullname|osic_name2class%>();
<%/if%>
<%/foreach%>
	}

	static public function get($id){
		$object = ObjectRouter_<%$name|osic_name2class%>::get($id);
<%if isset($obsolete_router)%>
		if(!isset($object)){
			$object = ObjectRouterObsolete_<%$name|osic_name2class%>::get($id);
			if(isset($object))
				ObjectRouter_<%$name|osic_name2class%>::set($id, $object);
		}
<%/if%>
		return $object;
	}

	static public function set($id, Object_<%$name|osic_name2class%> $object){
		ObjectRouter_<%$name|osic_name2class%>::set($id, $object);
	}
}
<%foreach from=$aux_objects key=aux_name item=aux_object%>

class Object_<%$name|osic_name2class%><%$aux_name|osic_name2class%> extends Object{
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
		$this-><%$field->name%> = new Object_<%$name|osic_name2class%><%$field->fullname|osic_name2class%>();
<%/if%>
<%/foreach%>
	}
}
<%/foreach%>
