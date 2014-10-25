<?php
date_default_timezone_set('Asia/Shanghai');
require_once __DIR__ . '/../common/router.php';
require_once __DIR__ . '/../common/object.php';
require_once __DIR__ . '/../common/set.php';
require_once __DIR__ . '/../common/queue.php';
require_once __DIR__ . '/../common/index.php';
require_once __DIR__ . '/../common/util.php';

error_reporting(E_ALL & ~E_NOTICE);

if($argc != 3){
	Util::error('usage: ' . $argv[0] . " <object:object|set|queue|index|router> <object_file>\n");
	exit(-1);
}

$target = $argv[1];
$object = unserialize(file_get_contents($argv[2]));

switch(strtolower($target)){
	case 'object':
		is_a($object, "Object") ? Util::output('YES') : Util::output('NO');
		break;
	case 'set':
		is_a($object, "Set") ? Util::output('YES') : Util::output('NO');
		break;
	case 'queue':
		is_a($object, "Queue") ? Util::output('YES') : Util::output('NO');
		break;
	case 'index':
		is_a($object, "Index") ? Util::output('YES') : Util::output('NO');
		break;
	case 'router':
		is_a($object, "Router") ? Util::output('YES') : Util::output('NO');
		break;
	default:
		Util::output('NO');
		break;
}
