<?php
namespace osi;
class SetStorage_<%$name|osic_name2class%>{
	static private $_serverParams = array(
<%foreach from=$router->storage->servers key=idx item=server%>
				<%$idx%> => <%$server|osic_expend_server_param%>,
<%/foreach%>
			);

	static private function _getServerIndexByPolicy($id){
		<%$router->storage->policy%>
	}

	static private function _getServerById($id){
		$serverParam = self::$_serverParams[self::_getServerIndexByPolicy($id)];
<%if strcasecmp($router->storage->name,'MEMCACHE')==0%>
		$memcache = new \Memcached($serverParam['host'] . $serverParam['port']);
		if(count($memcache->getServerList()) == 0){
			$memcache->addServer($serverParam['host'], $serverParam['port']);
			$memcache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
			$memcache->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_IGBINARY);
			$memcache->setOption(\Memcached::OPT_TCP_NODELAY, true);
		}
		return array($memcache, $serverParam);
<%else if strcasecmp($router->storage->name,'REDIS')==0%>
		$redis = new \Redis();
		$succ = $redis->pconnect($serverParam['host'], $serverParam['port']);
		if(!$succ)
			throw new \Exception('failed to connect to Redis server, host=' . $serverParam['host'] . ' port=' . $serverParam['port']);
		return array($redis, $serverParam);
<%else if strcasecmp($router->storage->name,'MYSQL')==0%>
		$dsn = 'mysql:host=' . $serverParam['host'] . ';port=' . $serverParam['port'];
		$pdo = new \PDO($dsn, $serverParam['user'], $serverParam['password'], array(\PDO::ATTR_PERSISTENT => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		return array($pdo, $serverParam);
<%/if%>
	}

<%if strcasecmp($router->storage->name,'MEMCACHE')==0%>
	static public function load($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		$data = $memcache->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);

			$elements = array();
			foreach($data as $elementId => $elementData){
				$element = new SetElement_<%$name|osic_name2class%>();
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
<%else if strcasecmp($router->storage->name,'REDIS')==0%>

	static public function load($id){
		list($redis, $serverParam) = self::_getServerById($id);
		$data = $redis->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);

			$elements = array();
			foreach($data as $elementId => $elementData){
				$element = new SetElement_<%$name|osic_name2class%>();
				$element->fromArray($elementData);
				$elements[$elementId] = $element;
			}
			return $elements;
		}
		return NULL;
	}

	static public function save($id, Array $elements){
		list($redis, $serverParam) = self::_getServerById($id);

		$data = array();
		foreach($elements as $elementId => $element)
			$data[$elementId] = $element->toArray();

		try{
			$redis->set($serverParam['prefix'] . $id, json_encode($data));
		}
		catch(\Exception $e){
			throw new \Exception('failed on SET command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']);
		}
	}
<%else if strcasecmp($router->storage->name,'MYSQL')==0%>

	static public function load($id){
		try{
			list($pdo, $serverParam) = self::_getServerById($id);
			$stmt = $pdo->prepare('SELECT data FROM ' . $serverParam['database'] . '.' . $serverParam['table'] . ' WHERE id=:id');
			$stmt->bindValue(':id', $id);
			$stmt->execute();
			if($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
				$data = json_decode($row['data'], true);
				if(!is_array($data))
					throw new \Exception('data corrupted on GET command to Mysql server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);
	
				$elements = array();
				foreach($data as $elementId => $elementData){
					$element = new SetElement_<%$name|osic_name2class%>();
					$element->fromArray($elementData);
					$elements[$elementId] = $element;
				}
				return $elements;
			}
		}
		catch(\Exception $e){
			throw new \Exception('failed on GET command to Mysql server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $e->getMessage());
		}
		return NULL;
	}

	static public function save($id, Array $elements){
		try{
			list($pdo, $serverParam) = self::_getServerById($id);
			$data = array();
			foreach($elements as $elementId => $element)
				$data[$elementId] = $element->toArray();

			$stmt = $pdo->prepare('REPLACE INTO ' . $serverParam['database'] . '.' . $serverParam['table'] . ' SET id=:id, data=:data');
			$stmt->bindValue(':id', $id);
			$stmt->bindValue(':data', json_encode($data));
			$stmt->execute();
		}
		catch(\Exception $e){
			throw new \Exception('failed on SET command to Mysql server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $e->getMessage());
		}
	}
<%/if%>
}
<%if $obsolete_router != NULL%>

class SetStorageObsolete_<%$name|osic_name2class%>{
	static private $_serverParams = array(
<%foreach from=$obsolete_router->storage->servers key=idx item=server%>
				<%$idx%> => <%$server|osic_expend_server_param%>,
<%/foreach%>
			);
		
	static private function _getServerIndexByPolicy($id){
		<%$obsolete_router->storage->policy%>
	}
		
	static private function _getServerById($id){
		$serverParam = self::$_serverParams[self::_getServerIndexByPolicy($id)];
<%if strcasecmp($obsolete_router->storage->name,'MEMCACHE')==0%>
		$memcache = new \Memcached($serverParam['host'] . $serverParam['port']);
		if(count($memcache->getServerList()) == 0){
			$memcache->addServer($serverParam['host'], $serverParam['port']);
			$memcache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
			$memcache->setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_IGBINARY);
			$memcache->setOption(\Memcached::OPT_TCP_NODELAY, true);
		}
		return array($memcache, $serverParam);
<%else if strcasecmp($obsolete_router->storage->name,'REDIS')==0%>
		$redis = new \Redis();
		$succ = $redis->pconnect($serverParam['host'], $serverParam['port']);
		if(!$succ)
			throw new \Exception('failed to connect to Redis server, host=' . $serverParam['host'] . ' port=' . $serverParam['port']);
		return array($redis, $serverParam);
<%else if strcasecmp($obsolete_router->storage->name,'MYSQL')==0%>
		$dsn = 'mysql:host=' . $serverParam['host'] . ';port=' . $serverParam['port'];
		$pdo = new \PDO($dsn, $serverParam['user'], $serverParam['password'], array(\PDO::ATTR_PERSISTENT => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		return array($pdo, $serverParam);
<%/if%>
	}
		
<%if strcasecmp($obsolete_router->storage->name,'MEMCACHE')==0%>
	static public function load($id){
		list($memcache, $serverParam) = self::_getServerById($id);
		$data = $memcache->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Memcache server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);

			$elements = array();
			foreach($data as $elementId => $elementData){
				$element = new SetElement_<%$name|osic_name2class%>();
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
<%else if strcasecmp($obsolete_router->storage->name,'REDIS')==0%>
			
	static public function load($id){
		list($redis, $serverParam) = self::_getServerById($id);
		$data = $redis->get($serverParam['prefix'] . $id);
		if(is_string($data)){
			$data = json_decode($data, true);
			if(!is_array($data))
				throw new \Exception('data corrupted on GET command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);

			$elements = array();
			foreach($data as $elementId => $elementData){
				$element = new SetElement_<%$name|osic_name2class%>();
				$element->fromArray($elementData);
				$elements[$elementId] = $element;
			}
			return $elements;
		}
		return NULL;
	}
		
	static public function save($id, Array $elements){
		list($redis, $serverParam) = self::_getServerById($id);
		$data = array();
		foreach($elements as $elementId => $element)
			$data[$elementId] = $element->toArray();
		try{
			$redis->set($serverParam['prefix'] . $id, json_encode($data));
		}
		catch(\Exception $e){
			throw new \Exception('failed on SET command to Redis server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']);
		}
	}
<%else if strcasecmp($obsolete_router->storage->name,'MYSQL')==0%>
	
	static public function load($id){
		try{
			list($pdo, $serverParam) = self::_getServerById($id);
			$stmt = $pdo->prepare('SELECT data FROM ' . $serverParam['database'] . '.' . $serverParam['table'] . ' WHERE id=:id');
			$stmt->bindValue(':id', $id);
			$stmt->execute();
			if($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
				$data = json_decode($row['data'], true);
				if(!is_array($data))
					throw new \Exception('data corrupted on GET command to Mysql server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port'] . ', id=' . $id);

				$elements = array();
				foreach($data as $elementId => $elementData){
					$element = new SetElement_<%$name|osic_name2class%>();
					$element->fromArray($elementData);
					$elements[$elementId] = $element;
				}
				return $elements;
			}
		}
		catch(\Exception $e){
			throw new \Exception('failed on GET command to Mysql server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $e->getMessage());
		}
		return NULL;
	}
		
	static public function save($id, Array $elements){
		try{
			list($pdo, $serverParam) = self::_getServerById($id);
			$data = array();
			foreach($elements as $elementId => $element)
				$data[$elementId] = $element->toArray();

			$stmt = $pdo->prepare('REPLACE INTO ' . $serverParam['database'] . '.' . $serverParam['table'] . ' SET id=:id, data=:data');
			$stmt->bindValue(':id', $id);
			$stmt->bindValue(':data', json_encode($data));
			$stmt->execute();
		}
		catch(\Exception $e){
			throw new \Exception('failed on SET command to Mysql server, host = ' . $serverParam['host'] . ' port = ' . $serverParam['port']. ' underlying msg:' . $e->getMessage());
		}
	}
<%/if%>
}
<%/if%>
