<?php
namespace osi;
require_once __DIR__ . '/items.cache.php';
require_once __DIR__ . '/items.storage.php';

class SetRouter_User_Items
{
	static public function load($id){
		$elements = NULL;
		$elements = SetCache_User_Items::load($id);
		if(!isset($elements)){
			$elements = SetStorage_User_Items::load($id);
			if(isset($elements))
				SetCache_User_Items::save($id, $elements);
		}
		return $elements;
	}

	static public function save($id, Array $elements){
		SetCache_User_Items::save($id, $elements);
		SetStorage_User_Items::save($id, $elements);
	}
}
