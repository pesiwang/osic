<?php
namespace osi;

class Object
{
	public function toArray(){
		$arr = array();
		foreach($this as $k => $v){
			if(is_a($v, '\\osi\\Object'))
				$arr[$k] = $v->toArray();
			else
				$arr[$k] = $v;
		}
		return $arr;
	}

	public function fromArray($arr){
		foreach($this as $k => &$v){
			if(isset($arr[$k])){
				if(is_a($v, '\\osi\\Object'))
					$v->fromArray($arr[$k]);
				else
					$v = $arr[$k];
			}
		}
	}
}

function object_loader_replace_callback($matches){
	return '/' . strtolower($matches[1]);
}

function object_loader($class) {
	if(0 == strncasecmp($class, 'osi\\Object_', 11)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "\osi\object_loader_replace_callback", substr($class, 11)) . '.object.php';
	}
}

spl_autoload_register('\osi\object_loader');
