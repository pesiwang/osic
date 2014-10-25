<?php
function name2class($name){
	return ucfirst(preg_replace('/\.([a-z])/ei', "strtoupper('\\1')", $name));
}

function name2file($name){
	$subs = explode('.', $name);
	return strtolower($subs[count($subs) - 1]);
}

function fill_params(Array $array){
	foreach($array as $idx => &$connParam)
		foreach($connParam as $key => &$value)
			$value = preg_replace('/{([^}]+)}/e', 'sprintf("$1", ' . $idx . ')', $value);
	return $array;
}

function dump_array(Array $array){
	$str = 'array(';
	$first = true;
	foreach($array as $key => $val){
		if(!$first)
			$str .= ', ';
		else
			$first = false;

		if(!is_numeric($key))
			$str .= '\'' . $key . '\'';
		else
			$str .= $key;
		$str .= ' => ';
		
		if(is_array($val)){
			$str .= dump_array($val);
		}
		else
			$str .= '\'' . $val . '\'';
	}
	$str .= ')';
	return $str;
}
