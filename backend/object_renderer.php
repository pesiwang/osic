<?php
date_default_timezone_set('Asia/Shanghai');
require_once __DIR__ . '/../common/object.php';
require_once __DIR__ . '/../common/router.php';
require_once __DIR__ . '/../common/render.php';
require_once __DIR__ . '/../common/util.php';
require_once __DIR__ . '/../3party/smarty/Smarty.class.php';

error_reporting(E_ALL & ~E_NOTICE);

function calc_object_size($object){
	$base_size = 0;
	foreach($object->getFields() as $name => $field){
		$base_size += strlen($name);
		switch($field->getType()){
			case ObjectField::TYPE_INTEGER:
				$base_size += 11;
				break;
			case ObjectField::TYPE_BIGINT:
				$base_size += 21;
				break;
			default:
				$base_size += $field->getLength();
				break;
		}
	}
	return $base_size * 2;
}

if($argc != 5){
	Util::error('usage: ' . $argv[0] . "  <template> <object_object> <current_router_object> <obsolete_router_object>\n");
	exit(-1);
}

$template = $argv[1];
$current_router_file = $argv[3];
$obsolete_router_file = $argv[4];
$object = unserialize(file_get_contents($argv[2]));
$current_router = (strlen($current_router_file) == 0) ? null : unserialize(file_get_contents($current_router_file));
$obsolete_router = (strlen($obsolete_router_file) == 0) ? null : unserialize(file_get_contents($obsolete_router_file));

if(!isset($object)){
	Util::error('bad object object');
	exit(-1);
}

if(!isset($current_router) && !isset($obsolete_router)){
	Util::error('at least one router(current|obsolete) must be provided');
	exit(-1);
}

$object_size = calc_object_size($object);

$smarty = new Smarty();
$smarty->template_dir = __DIR__;
$smarty->compile_dir = '/tmp';
$smarty->left_delimiter = '<!--{';
$smarty->right_delimiter = '}-->';
$smarty->caching = false;
$smarty->assign('object', $object);
$smarty->assign('current_router', $current_router);
$smarty->assign('obsolete_router', $obsolete_router);
$smarty->assign('object_size', $object_size);
$smarty->display($template);
