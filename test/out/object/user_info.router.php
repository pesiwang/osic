<?php
namespace osi;
require_once __DIR__ . '/user_info.cache.php';
require_once __DIR__ . '/user_info.storage.php';

class ObjectRouter_UserInfo
{
	static public function get($id){
		$object = null;
		$object = ObjectCache_UserInfo::get($id);
		if(!isset($object)){
			$object = ObjectStorage_UserInfo::get($id);
			if(isset($object))
				ObjectCache_UserInfo::set($id, $object);
		}
		return $object;
	}

	static public function set($id, Object_UserInfo $object){
		ObjectCache_UserInfo::set($id, $object);
		ObjectStorage_UserInfo::set($id, $object);
	}

	static public function del($id){
		ObjectCache_UserInfo::del($id);
		ObjectStorage_UserInfo::del($id);
	}
}
