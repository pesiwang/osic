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

function object_loader($class) {
	if(0 == strncasecmp($class, 'osi\\Object_', 11)){
		$class = substr($class, 11);
		$class = str_replace('_', '/', $class);
		$class = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class));
		require_once __DIR__ . '/' . $class . '.object.php'; 
	}
}

spl_autoload_register('\osi\object_loader');
