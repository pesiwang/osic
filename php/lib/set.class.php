<?php
namespace osi;

class SetElement
{
	public function toArray(){
		$arr = array();
		foreach($this as $k => $v){
			if(is_a($v, '\\osi\\SetElement'))
				$arr[$k] = $v->toArray();
			else
				$arr[$k] = $v;
		}
		return $arr;
	}

	public function fromArray($arr){
		foreach($this as $k => &$v){
			if(isset($arr[$k])){
				if(is_a($v, '\\osi\\SetElement'))
					$v->fromArray($arr[$k]);
				else
					$v = $arr[$k];
			}
		}
	}
}

function set_loader_replace_callback($matches){
	return '/' . strtolower($matches[1]);
}

function set_loader($class) {
	if(0 == strncasecmp($class, 'osi\\Set_', 8)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "\osi\set_loader_replace_callback", substr($class, 8)) . '.set.php';
	}
	else if(0 == strncasecmp($class, 'osi\\SetElement_', 15)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "\osi\set_loader_replace_callback", substr($class, 15)) . '.set.php';
	}
}

spl_autoload_register('\osi\set_loader');
