<?php
namespace osi;
class SetCache_User_Items{
	static private $_serverParams = array(
				0 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				1 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				2 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				3 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				4 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				5 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				6 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				7 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				8 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
				9 => array('host' => 'localhost', 'port' => 11211, 'prefix' => 'example_user_'),
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

	static public function load($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		$data = $memcache->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);

			$elements = array();
			foreach($data as $elementId => $elementData){
				$element = new SetElement_User_Items();
				$element->fromArray($elementData);
				$elements[$elementId] = $element;
			}
			return $elements;
		}
		else if($memcache->getResultCode() != \Memcached::RES_NOTFOUND)
			throw new \Exception('failed on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
		return NULL;
	}

	static public function save($id, Array $elements){
		list($memcache, $serverParam) = self::_getServerById($id);

		$data = array();
		foreach($elements as $elementId => $element)
			$data[$elementId] = $element->toArray();

		if(!$memcache->set($serverParam['prefix'] . $id, json_encode($data)))
			throw new \Exception('failed on SET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
	}
}
