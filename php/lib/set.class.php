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

function set_loader($class) {
	if(0 == strncasecmp($class, 'osi\\Set_', 8)){
		$class = substr($class, 8);
		$class = str_replace('_', '/', $class);
		$class = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class));
		require_once __DIR__ . '/' . $class . '.set.php'; 
	}
	else if(0 == strncasecmp($class, 'osi\\SetElement_', 15)){
		$class = substr($class, 15);
		$class = str_replace('_', '/', $class);
		$class = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class));
		require_once __DIR__ . '/' . $class . '.set.php'; 
	}
}

spl_autoload_register('\osi\set_loader');
