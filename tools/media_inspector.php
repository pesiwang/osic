<?php
date_default_timezone_set('Asia/Shanghai');
require_once __DIR__ . '/../common/router.php';
require_once __DIR__ . '/../common/util.php';

error_reporting(E_ALL & ~E_NOTICE);

if($argc != 3){
	Util::error('usage: ' . $argv[0] . " <target:cache|dset|storage> <router_object>\n");
	exit(-1);
}

$target = $argv[1];
$router = unserialize(file_get_contents($argv[2]));

if(!isset($router)){
	Util::error('bad router object');
	exit(-1);
}

switch($target){
	case 'cache':
		if(!$router->hasCache())
			Util::error("no cache found\n");
		else
			Util::output($router->getCache()->getType());
		break;
	case 'dset':
		if(!$router->hasDset())
			Util::error("no dset found\n");
		else
			Util::output($router->getDset()->getType());
		break;
	case 'storage':
			if(!$router->hasStorage())
			Util::error("no storage found\n");
		else
			Util::output($router->getStorage()->getType());
		break;
	default:
		Util::error("bad target " . $target . "\n");
		break;
}
