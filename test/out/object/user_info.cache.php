<?php
namespace osi;
class ObjectCache_UserInfo{
	static private $_serverParams = array(
				0 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
			);

	static private function _getServerIndexByPolicy($id){
		return 0;
	}

	static private function _getServerById($id){
		$serverParam = self::$_serverParams[self::_getServerIndexByPolicy($id)];
		$memcache = new \Memcached($serverParam['host'] . $serverParam['port']);
		if(count($memcache->getServerList()) == 0){
			$memcache->addServer($serverParam['host'], $serverParam['port']);
			$memcache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
			$memcache->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_IGBINARY);
			$memcache->setOption(\Memcached::OPT_TCP_NODELAY, true);
		}
		return array($memcache, $serverParam);
	}

	static public function get($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		$object = null;
		$data = $memcache->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);
			$object = new Object_UserInfo();
			$object->fromArray($data);
			return $object;
		}
		else if($memcache->getResultCode() != \Memcached::RES_NOTFOUND)
			throw new \Exception('failed on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
		return NULL;
	}

	static public function set($id, Object_UserInfo $object){
		list($memcache, $serverParam) = self::_getServerById($id);
		if(!$memcache->set($serverParam['prefix'] . $id, json_encode($object->toArray())))
			throw new \Exception('failed on SET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
	}

	static public function del($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		if(!$memcache->delete($serverParam['prefix'] . $id))
			throw new \Exception('failed on DEL command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
	}
}
