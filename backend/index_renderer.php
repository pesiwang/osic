<?php
date_default_timezone_set('Asia/Shanghai');
require_once __DIR__ . '/../common/index.php';
require_once __DIR__ . '/../common/router.php';
require_once __DIR__ . '/../common/render.php';
require_once __DIR__ . '/../common/util.php';
require_once __DIR__ . '/../3party/smarty/Smarty.class.php';

error_reporting(E_ALL & ~E_NOTICE);
if($argc != 4){
	Util::error('usage: ' . $argv[0] . "  <template> <index_object> <router_object>\n");
	exit(-1);
}

$template = $argv[1];
$router_file = $argv[3];
$index = unserialize(file_get_contents($argv[2]));
$router = unserialize(file_get_contents($router_file));

if(!isset($index)){
	Util::error('bad index object');
	exit(-1);
}

if(!isset($router)){
	Util::error('missing router must be provided');
	exit(-1);
}

$smarty = new Smarty();
$smarty->template_dir = __DIR__;
$smarty->compile_dir = '/tmp';
$smarty->left_delimiter = '<!--{';
$smarty->right_delimiter = '}-->';
$smarty->caching = false;
$smarty->assign('index', $index);
$smarty->assign('router', $router);
$smarty->display($template);
