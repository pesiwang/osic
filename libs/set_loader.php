<?php
class SetException extends Exception{
}

function set_loader_replace_callback($matches){
	return '/' . strtolower($matches[1]);
}

function set_loader($class) {
	if(0 == strncasecmp($class, 'Set_', 4)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "set_loader_replace_callback", substr($class, 4)) . '.set.php';
	}
	else if(0 == strncasecmp($class, 'SetElement_', 11)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "set_loader_replace_callback", substr($class, 11)) . '.set.php';
	}
}

spl_autoload_register('set_loader');
