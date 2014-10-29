<?php
namespace osi;
class ObjectCache_<%$name|osic_name2class%>{
	static private $_serverParams = array(
<%foreach from=$router->cache->servers key=idx item=server%>
				<%$idx%> => <%$server|osic_expend_server_param%>,
<%/foreach%>
			);

	static private function _getServerIndexByPolicy($id){
		<%$router->cache->policy%>
	}

	static private function _getServerById($id){
		$serverParam = self::$_serverParams[self::_getServerIndexByPolicy($id)];
<%if strcasecmp($router->cache->name,'MEMCACHE')==0%>
		$memcache = new \Memcached($serverParam['host'] . $serverParam['port']);
		if(count($memcache->getServerList()) == 0){
			$memcache->addServer($serverParam['host'], $serverParam['port']);
			$memcache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
			$memcache->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_IGBINARY);
			$memcache->setOption(\Memcached::OPT_TCP_NODELAY, true);
		}
		return array($memcache, $serverParam);
<%else if strcasecmp($router->cache->name,'REDIS')==0%>
		$redis = new \Redis();
		$succ = $redis->pconnect($serverParam['host'], $serverParam['port']);
		if(!$succ)
			throw new \Exception('failed to connect to Redis server, host=' . $serverParam['host'] . ' port=' . $serverParam['port']);
		return array($redis, $serverParam);
<%/if%>
	}

<%if strcasecmp($router->cache->name,'MEMCACHE')==0%>
	static public function get($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		$object = null;
		$data = $memcache->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);
			$object = new Object_<%$name|osic_name2class%>();
			$object->fromArray($data);
			return $object;
		}
		else if($memcache->getResultCode() != \Memcached::RES_NOTFOUND)
			throw new \Exception('failed on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
		return NULL;
	}

	static public function set($id, Object_<%$name|osic_name2class%> $object){
		list($memcache, $serverParam) = self::_getServerById($id);
		if(!$memcache->set($serverParam['prefix'] . $id, json_encode($object->toArray())))
			throw new \Exception('failed on SET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
	}

	static public function del($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		if(!$memcache->delete($serverParam['prefix'] . $id))
			throw new \Exception('failed on DEL command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
	}
<%else if strcasecmp($router->cache->name,'REDIS')==0%>

	static public function get($id){
		list($redis, $serverParam) = self::_getServerById($id);
		$data = $redis->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);
			$object = new Object_<%$name|osic_name2class%>();
			$object->fromArray($data);
			return $object;
		}
		return NULL;
	}

	static public function set($id, Object_<%$name|osic_name2class%> $object){
		list($redis, $serverParam) = self::_getServerById($id);
		try{
			$redis->set($serverParam['prefix'] . $id, json_encode($object->toArray()));
		}
		catch(\Exception $e){
			throw new \Exception('failed on SET command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']);
		}
	}

	static public function del($id){
		list($redis, $serverParam) = self::_getServerById($id);
		try{
			$redis->delete($serverParam['prefix'] . $id);
		}
		catch(\Exception $e){
			throw new \Exception('failed on DEL command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']);
		}
	}
<%/if%>
}
<%if $obsolete_router != NULL%>

class ObjectCacheObsolete_<%$name|osic_name2class%>{
	static private $_serverParams = array(
<%foreach from=$obsolete_router->cache->servers key=idx item=server%>
				<%$idx%> => <%$server|osic_expend_server_param%>,
<%/foreach%>
			);
		
	static private function _getServerIndexByPolicy($id){
		<%$obsolete_router->cache->policy%>
	}
		
	static private function _getServerById($id){
		$serverParam = self::$_serverParams[self::_getServerIndexByPolicy($id)];
<%if strcasecmp($obsolete_router->cache->name,'MEMCACHE')==0%>
		$memcache = new \Memcached($serverParam['host'] . $serverParam['port']);
		if(count($memcache->getServerList()) == 0){
			$memcache->addServer($serverParam['host'], $serverParam['port']);
			$memcache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
			$memcache->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_IGBINARY);
			$memcache->setOption(\Memcached::OPT_TCP_NODELAY, true);
		}
		return array($memcache, $serverParam);
<%else if strcasecmp($obsolete_router->cache->name,'REDIS')==0%>
		$redis = new \Redis();
		$succ = $redis->pconnect($serverParam['host'], $serverParam['port']);
		if(!$succ)
			throw new \Exception('failed to connect to Redis server, host=' . $serverParam['host'] . ' port=' . $serverParam['port']);
		return array($redis, $serverParam);
<%/if%>
	}
		
<%if strcasecmp($obsolete_router->cache->name,'MEMCACHE')==0%>
	static public function get($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		$object = null;
		$data = $memcache->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);
			$object = new Object_<%$name|osic_name2class%>();
			$object->fromArray($data);
			return $object;
		}
		else if($memcache->getResultCode() != \Memcached::RES_NOTFOUND)
			throw new \Exception('failed on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
		return NULL;
	}
		
	static public function set($id, Object_<%$name|osic_name2class%> $object){
		list($memcache, $serverParam) = self::_getServerById($id);
		if(!$memcache->set($serverParam['prefix'] . $id, json_encode($object->toArray())))
			throw new \Exception('failed on SET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
	}
		
	static public function del($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		if(!$memcache->delete($serverParam['prefix'] . $id))
			throw new \Exception('failed on DEL command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $memcache->getResultMessage());
	}
<%else if strcasecmp($obsolete_router->cache->name,'REDIS')==0%>
			
	static public function get($id){
		list($redis, $serverParam) = self::_getServerById($id);
		$data = $redis->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);
			$object = new Object_<%$name|osic_name2class%>();
			$object->fromArray($data);
			return $object;
		}
		return NULL;
	}
		
	static public function set($id, Object_<%$name|osic_name2class%> $object){
		list($redis, $serverParam) = self::_getServerById($id);
		try{
			$redis->set($serverParam['prefix'] . $id, json_encode($object->toArray()));
		}
		catch(\Exception $e){
			throw new \Exception('failed on SET command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']);
		}
	}
		
	static public function del($id){
		list($redis, $serverParam) = self::_getServerById($id);
		try{
			$redis->delete($serverParam['prefix'] . $id);
		}
		catch(\Exception $e){
			throw new \Exception('failed on DEL command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']);
		}
	}
<%/if%>
}
<%/if%>
