<?php
date_default_timezone_set('Asia/Shanghai');
require_once __DIR__ . '/../common/router.php';
require_once __DIR__ . '/../common/util.php';

error_reporting(E_ALL & ~E_NOTICE);

if($argc != 2){
	Util::error('usage: ' . $argv[0] . " <router_object>\n");
	exit(-1);
}

$router = unserialize(file_get_contents($argv[1]));

if(!isset($router)){
	Util::error('bad router object');
	exit(-1);
}

$components = array();

if($router->hasCache())
	$components[] = 'cache';
if($router->hasDset())
	$components[] = 'dset';
if($router->hasStorage())
	$components[] = 'storage';

for($idx = 0; $idx < count($components); ++$idx){
	Util::output($components[$idx]);
	if(($idx + 1) < count($components))
		Util::output("\n");
}
