<?php

require_once __DIR__ . '/object/object.class.php';

$obj = \osi\Object_UserInfo::get("1111");
var_dump($obj);

$obj->name	= 'wolf';

\osi\Object_UserInfo::set("1111", $obj);
$obj = \osi\Object_UserInfo::get("1111");
var_dump($obj);

