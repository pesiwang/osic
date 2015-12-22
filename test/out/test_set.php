<?php

require_once __DIR__ . '/set/set.class.php';

$e = \osi\Set_User_Items::fetch("aaa", 0);
var_dump($e);
$e = new \osi\SetElement_User_Items();
$e->name	= 'sword';
$e->level	= 10;

\osi\Set_User_Items::put("aaa", 0, $e);
$e = \osi\Set_User_Items::fetch("aaa", 0);
var_dump($e);

