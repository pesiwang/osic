<?php
class IndexException extends Exception{
}

function index_loader_replace_callback($matches){
	return '/' . strtolower($matches[1]);
}

function index_loader($class) {
	if(0 == strncasecmp($class, 'Index_', 6)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "index_loader_replace_callback", substr($class, 6)) . '.index.php';
	}
}

spl_autoload_register('index_loader');
