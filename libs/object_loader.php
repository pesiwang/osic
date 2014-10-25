<?php
class ObjectException extends Exception{
}

function object_loader_replace_callback($matches){
	return '/' . strtolower($matches[1]);
}

function object_loader($class) {
	if(0 == strncasecmp($class, 'Object_', 7)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "object_loader_replace_callback", substr($class, 7)) . '.object.php';
	}
}

spl_autoload_register('object_loader');
