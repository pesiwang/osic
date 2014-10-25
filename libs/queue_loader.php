<?php
class QueueException extends Exception{
}

function queue_loader_replace_callback($matches){
	return '/' . strtolower($matches[1]);
}

function queue_loader($class) {
	if(0 == strncasecmp($class, 'Queue_', 6)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "queue_loader_replace_callback", substr($class, 6)) . '.queue.php';
	}
	else if(0 == strncasecmp($class, 'QueueElement_', 13)){
		require_once __DIR__ . preg_replace_callback('/([A-Z])/', "queue_loader_replace_callback", substr($class, 13)) . '.queue.php';
	}
}

spl_autoload_register('queue_loader');
