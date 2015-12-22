<?php
namespace osi;
require_once __DIR__ . '/user_info.router.php';

class Object_UserInfo extends Object{
	public $name = '';
	public $hometown = 'meizhou';
	public $sex = 0;
	public $age = 18;
	public $statistics = null;

	public function __construct(){
		$this->statistics = new Object_UserInfoStatistics();
	}

	static public function get($id){
		$object = ObjectRouter_UserInfo::get($id);
		return $object;
	}

	static public function set($id, Object_UserInfo $object){
		ObjectRouter_UserInfo::set($id, $object);
	}

	static public function del($id){
		ObjectRouter_UserInfo::del($id);
	}
}

class Object_UserInfoStatistics extends Object{
	public $loginTimes = 0;
	public $level = 0;

	public function __construct(){
	}
}
