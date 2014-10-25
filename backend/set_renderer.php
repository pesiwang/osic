<?php
date_default_timezone_set('Asia/Shanghai');
require_once __DIR__ . '/../common/set.php';
require_once __DIR__ . '/../common/router.php';
require_once __DIR__ . '/../common/render.php';
require_once __DIR__ . '/../common/util.php';
require_once __DIR__ . '/../3party/smarty/Smarty.class.php';

error_reporting(E_ALL & ~E_NOTICE);

function calc_set_size($set){
	$base_size = 0;
	$elementLength = 0;
	switch($set->getElement()->getType()){
		case SetField::TYPE_INTEGER:
			$elementLength = 11;
			break;
		case SetField::TYPE_BIGINT:
			$elementLength = 21;
			break;
		default:
			$elementLength = $set->getElement()->getLength();
			break;
	}
	
	foreach($set->getFields() as $name => $field){
		$base_size += $elementLength + strlen($name);
		switch($field->getType()){
			case SetField::TYPE_INTEGER:
				$base_size += 11;
				break;
			case SetField::TYPE_BIGINT:
				$base_size += 21;
				break;
			default:
				$base_size += $field->getLength();
				break;
		}
	}
	return $base_size * 2 * $set->getCapacity();
}

if($argc != 5){
	Util::error('usage: ' . $argv[0] . "  <template> <set_object> <current_router_object> <obsolete_router_object>\n");
	exit(-1);
}

$template = $argv[1];
$current_router_file = $argv[3];
$obsolete_router_file = $argv[4];
$set = unserialize(file_get_contents($argv[2]));
$current_router = (strlen($current_router_file) == 0) ? null : unserialize(file_get_contents($current_router_file));
$obsolete_router = (strlen($obsolete_router_file) == 0) ? null : unserialize(file_get_contents($obsolete_router_file));

if(!isset($set)){
	Util::error('bad set object');
	exit(-1);
}

if(!isset($current_router) && !isset($obsolete_router)){
	Util::error('at least one router(current|obsolete) must be provided');
	exit(-1);
}

$set_size = calc_set_size($set);

$smarty = new Smarty();
$smarty->template_dir = __DIR__;
$smarty->compile_dir = '/tmp';
$smarty->left_delimiter = '<!--{';
$smarty->right_delimiter = '}-->';
$smarty->caching = false;
$smarty->assign('set', $set);
$smarty->assign('current_router', $current_router);
$smarty->assign('obsolete_router', $obsolete_router);
$smarty->assign('set_size', $set_size);
$smarty->display($template);