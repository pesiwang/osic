<?php
require __DIR__ . '/../common/util.php';
require __DIR__ . '/../common/router.php';
error_reporting(E_ALL & ~E_NOTICE);

if($argc != 3){
	Util::error('usage: ' . $argv[0] . "  <router_file> <router_name>\n");
	exit(-1);
}

function parseMedia_Memcache($xml, $target){
	if($target != 'cache')
		throw new Exception('Memcache cannot be used as ' . $target);
	
	$media = new Media();
	$media->setType('memcache');

	foreach($xml->server as $server){
		if(!isset($server['range']))
			throw new Exception('missing [range] parameters in server definition');
		$range = $server['range']->__toString();
		
		if(!isset($server->host[0]) || !isset($server->port[0]) || !isset($server->prefix[0]))
			throw new Exception('incomplete connection parameter in memcache server definition');
		$connParam = array('host' => $server->host[0]->__toString(), 'port' => $server->port[0]->__toString(), 'prefix' => $server->prefix[0]->__toString());
		
		foreach(explode(',', $range) as $range){
			$subRange = explode('-', $range);
			$from = intval($subRange[0]);
			$to = (count($subRange) == 2) ? intval($subRange[1]) : $from;
			
			for($idx = $from; $idx <= $to; ++$idx)
				$media->addConnParams($idx, $connParam);
		}
	}
	return $media;
}

function parseMedia_Redis($xml, $target){
	if(($target != 'cache') && ($target != 'dset') && ($target != 'storage'))
		throw new Exception('Redis cannot be used as ' . $target);
	
	$media = new Media();
	$media->setType('redis');
	
	foreach($xml->server as $server){
		if(!isset($server['range']))
			throw new Exception('missing [range] parameters in server definition');
		$range = $server['range']->__toString();
		
		if($target == 'dset'){
			if(!isset($server->host[0]) || !isset($server->port[0]) || !isset($server->name[0]))
				throw new Exception('incomplete connection parameter in redis server definition');
			$connParam = array('host' => $server->host[0]->__toString(), 'port' => $server->port[0]->__toString(), 'name' => $server->name[0]->__toString());
		}
		else{
			if(!isset($server->host[0]) || !isset($server->port[0]) || !isset($server->prefix[0]))
				throw new Exception('incomplete connection parameter in redis server definition');
			$connParam = array('host' => $server->host[0]->__toString(), 'port' => $server->port[0]->__toString(), 'prefix' => $server->prefix[0]->__toString());
		}
		
		foreach(explode(',', $range) as $range){
			$subRange = explode('-', $range);
			$from = intval($subRange[0]);
			$to = (count($subRange) == 2) ? intval($subRange[1]) : $from;
			
			for($idx = $from; $idx <= $to; ++$idx)
				$media->addConnParams($idx, $connParam);
		}
	}
	return $media;
}

function parseMedia_Mysql($xml, $target){
	if($target != 'storage')
		throw new Exception('Mysql cannot be used as ' . $target);
	
	$media = new Media();
	$media->setType('mysql');
	
	foreach($xml->server as $server){
		if(!isset($server['range']))
			throw new Exception('missing [range] parameters in server definition');
		$range = $server['range']->__toString();
		
		if(!isset($server->host[0]) || !isset($server->port[0]) || !isset($server->user[0]) || !isset($server->pass[0]) || !isset($server->database[0]) || !isset($server->table[0]))
			throw new Exception('incomplete connection parameter in mysql server definition');
		$connParam = array('host' => $server->host[0]->__toString(), 'port' => $server->port[0]->__toString(), 'user' => $server->user[0]->__toString(), 'pass' => $server->pass[0]->__toString(), 'database' => $server->database[0]->__toString(), 'table' => $server->table[0]->__toString());
		
		foreach(explode(',', $range) as $range){
			$subRange = explode('-', $range);
			$from = intval($subRange[0]);
			$to = (count($subRange) == 2) ? intval($subRange[1]) : $from;
			
			for($idx = $from; $idx <= $to; ++$idx)
				$media->addConnParams($idx, $connParam);
		}
	}
	return $media;
}

function parseMedia_Mongo($xml, $target){
	if(($target != 'storage') && ($target != 'cache'))
		throw new Exception('Mongo cannot be used as ' . $target);
	
	$media = new Media();
	$media->setType('mongo');
	
	foreach($xml->server as $server){
		if(!isset($server['range']))
			throw new Exception('missing [range] parameters in server definition');
		$range = $server['range']->__toString();
		
		if(!isset($server->host[0]) || !isset($server->port[0]) || !isset($server->database[0]) || !isset($server->collection[0]))
			throw new Exception('incomplete connection parameter in mongo server definition');
		$connParam = array('host' => $server->host[0]->__toString(), 'port' => $server->port[0]->__toString(), 'database' => $server->database[0]->__toString(), 'collection' => $server->collection[0]->__toString());
		
		foreach(explode(',', $range) as $range){
			$subRange = explode('-', $range);
			$from = intval($subRange[0]);
			$to = (count($subRange) == 2) ? intval($subRange[1]) : $from;
			
			for($idx = $from; $idx <= $to; ++$idx)
				$media->addConnParams($idx, $connParam);
		}
	}
	return $media;
}

function parseMedia($xml, $target){
	if(!isset($xml) || !isset($xml['media']))
		return null;

	$media = strtoupper($xml['media']->__toString());
	switch($media){
		case 'MEMCACHE':
			return parseMedia_Memcache($xml, $target);
			break;
		case 'REDIS':
			return parseMedia_Redis($xml, $target);
			break;
		case 'MYSQL':
			return parseMedia_Mysql($xml, $target);
			break;
		case 'MONGO':
			return parseMedia_Mongo($xml, $target);
			break;
	}
	throw new Exception('unkown media [' . $media . ']');
	return null;
}

try{
	$routerName = $argv[2];
	$routerFile = $argv[1];
	$xml = @simplexml_load_file($routerFile, null, LIBXML_NOCDATA);
	
	//step 1. set name field
	$router = new Router();
	$router->setName($routerName);

	//step 2. parsing cache/dset/storage
	$media = parseMedia($xml->cache, 'cache');
	if(isset($media)) $router->setCache($media);
	
	$media = parseMedia($xml->dset, 'dset');
	if(isset($media)) $router->setDset($media);
	
	$media = parseMedia($xml->storage, 'storage');
	if(isset($media)) $router->setStorage($media);
	
	//step 3. parsing policy
	if($router->hasCache()){
		if(!isset($xml->cache->policy))
			throw new Exception('missing cache policy definition');
		$router->setCachePolicy($xml->cache->policy[0]->__toString());
	}
	if($router->hasDset()){
		if(!isset($xml->dset->policy))
			throw new Exception('missing dset policy definition');
		$router->setDsetPolicy($xml->dset->policy[0]->__toString());
	}
	if($router->hasStorage()){
		if(!isset($xml->storage->policy))
			throw new Exception('missing storage policy definition');
		$router->setStoragePolicy($xml->storage->policy[0]->__toString());
	}

	Util::output(serialize($router));
}
catch(Exception $e){
	Util::error("failed to parse router, reason:" . $e->getMessage() . "\n");
}