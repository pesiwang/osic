<?php
date_default_timezone_set('Asia/Shanghai');
require_once __DIR__ . '/3party/smarty/Smarty.class.php';
require_once __DIR__ . '/definition.php';

class ObjectBuilder
{
	private $_object;
	private $_auxObjects = array();
	private $_router;
	private $_obsoleteRouter;

	public function compile($moduleName, $fileDef, $fileRouter, $fileObsoleteRouter, $tplFile){
		$xmlDef = @simplexml_load_file($fileDef, null, LIBXML_NOCDATA);
		if($xmlDef === FALSE)
			throw new Exception('bad xml file(' . $fileDef . ')');

		$xmlRouter = @simplexml_load_file($fileRouter, null, LIBXML_NOCDATA);
		if($xmlRouter === FALSE)
			throw new Exception('bad xml file(' . $fileRouter . ')');

		$xmlObsoleteRouter = FALSE;
		if($fileObsoleteRouter != NULL){
			$xmlObsoleteRouter = @simplexml_load_file($fileObsoleteRouter, null, LIBXML_NOCDATA);
			if($xmlObsoleteRouter === FALSE)
				throw new Exception('bad xml file(' . $fileObsoleteRouter . ')');
		}

		$this->_compileObject($xmlDef);
		$this->_compileRouter($xmlRouter);
		if($xmlObsoleteRouter !== FALSE)
			$this->_compileObsoleteRouter($xmlObsoleteRouter);

		$smarty = new \Smarty();
		$smarty->compile_dir = '/tmp';
		$smarty->left_delimiter = '<%';
		$smarty->right_delimiter = '%>';
		$smarty->caching = false;
		$smarty->assign('name', $moduleName);
		$smarty->assign('object', $this->_object);
		$smarty->assign('aux_objects', $this->_auxObjects);
		$smarty->assign('router', $this->_router);
		$smarty->assign('obsolete_router', $this->_obsoleteRouter);
		$smarty->display($tplFile);
	}

	private function _compileObject($xml){
		if(!$xml->key || !$xml->key->attributes()->type || !$xml->key->attributes()->length || !$xml->key->attributes()->isFixedLength)
			throw new Exception('bad [key] section');

		$this->_object = new TObject();
		$this->_object->key = new TKey();
		$this->_object->key->type = (string)($xml->key->attributes()->type);
		$this->_object->key->length = (string)($xml->key->attributes()->length);
		$this->_object->key->isFixedLength = (bool)($xml->key->attributes()->isFixedLength);

		$this->_object->fields = $this->_compileObjectField('', $xml->field);
	}

	private function _compileObjectField($prefix, $xml){
		$fields = array();
		foreach($xml as $item){
			if(!$item->attributes()->type || !$item->attributes()->name)
				throw new Exception('bad [field] section');

			$field = new TObjectField();
			$field->type = (string)($item->attributes()->type);
			$field->name = (string)($item->attributes()->name);
			$field->fullname = $prefix == '' ? $field->name : $prefix . '.' . $field->name;
			if(strcasecmp($field->type, 'OBJECT') == 0)
				$this->_auxObjects[$field->fullname] = $this->_compileObjectField($field->fullname, $item->field);
			else{
				$field->value = (string)($item);
				if(($field->value == '') && (strcasecmp($field->type, 'STRING') != 0))
					$field->value = 0;
			}
			$fields[] = $field;
		}
		return $fields;
	}

	private function _compileRouter($xml){
		$this->_router = new TRouter();
		if($xml->cache)
			$this->_router->cache = $this->_compileRouterMedia($xml->cache);
		if($xml->storage)
			$this->_router->storage = $this->_compileRouterMedia($xml->storage);
	}

	private function _compileObsoleteRouter($xml){
		$this->_obsoleteRouter = new TRouter();
		if($xml->cache)
			$this->_obsoleteRouter->cache = $this->_compileRouterMedia($xml->cache);
		if($xml->storage)
			$this->_obsoleteRouter->storage = $this->_compileRouterMedia($xml->storage);
	}

	private function _compileRouterMedia($xml){
		if(!$xml->attributes()->media)
			throw new Exception('attribute [media] not found in router');
		if(!$xml->policy)
			throw new Exception('section [policy] not found in router');

		$media = new TRouterMedia();
		$media->name = (string)($xml->attributes()->media);
		$media->policy = (string)($xml->policy);

		foreach($xml->server as $item){
			$ranges = $this->_compileRouterServerRange($item);

			if(strcasecmp($media->name, 'MEMCACHE') == 0){
				if(!$item->host || !$item->port || !$item->prefix)
					throw new Exception('incorrect MEMCACH server configuration');
				foreach($ranges as $idx){
					$server = new TRouterMediaServerMemcache();
					$server->host = sprintf((string)($item->host), $idx);
					$server->port = sprintf((string)($item->port), $idx);
					$server->prefix = sprintf((string)($item->prefix), $idx);
					$media->servers[$idx] = $server;
				}
			}
			else if(strcasecmp($media->name, 'REDIS') == 0){
				if(!$item->host || !$item->port || !$item->prefix)
					throw new Exception('incorrect REDIS server configuration');
				foreach($ranges as $idx){
					$server = new TRouterMediaServerRedis();
					$server->host = sprintf((string)($item->host), $idx);
					$server->port = sprintf((string)($item->port), $idx);
					$server->prefix = sprintf((string)($item->prefix), $idx);
					$media->servers[$idx] = $server;
				}
			}
			else if(strcasecmp($media->name, 'MYSQL') == 0){
				if(!$item->host || !$item->port || !$item->user || !$item->password || !$item->database || !$item->table)
					throw new Exception('incorrect MYSQL server configuration');
				foreach($ranges as $idx){
					$server = new TRouterMediaServerMysql();
					$server->host = sprintf((string)($item->host), $idx);
					$server->port = sprintf((string)($item->port), $idx);
					$server->user = sprintf((string)($item->user), $idx);
					$server->password = sprintf((string)($item->password), $idx);
					$server->database = sprintf((string)($item->database), $idx);
					$server->table = sprintf((string)($item->table), $idx);
					$media->servers[$idx] = $server;
				}
			}
			else{
				throw new Exception('unknown MEDIA in server configuration');
			}
		}
		return $media;
	}

	private function _compileRouterServerRange($xml){
		if(!$xml->attributes()->range)
			throw new Exception('no [range] attribute found in router');

		$ranges = array();
		if(preg_match('/^[0-9]+-[0-9]+$/i', (string)($xml->attributes()->range)) == 1){
			list($start, $end) = explode('-', (string)($xml->attributes()->range));
			for($idx = $start; $idx <= $end; ++$idx)
				$ranges[] = $idx;
		}
		else if(preg_match('/^[0-9]+(,[0-9]+)*$/i', (string)($xml->attributes()->range)) != 1){
			$ranges = explode(',', (string)($xml->attributes()->range));
		}
		else{
			throw new Exception('bad syntax in [range] attribute');
		}
		return $ranges;
	}
}

if($argc < 5)
	die("usage: php " . $argv[0] . " <module_name> <def_folder> <router_folder> <tpl_file>\n");

try{
	$moduleName = $argv[1];
	$folderDef = $argv[2];
	$folderRouter = $argv[3];
	$fileTpl = $argv[4];
	$folderModule = dirname(str_replace('.', '/', $moduleName));
	$fileDef = $folderDef . '/' . $folderModule . '/' . basename(str_replace('.', '/', $moduleName)) . '.object.xml';
	$fileRouter = $folderRouter. '/' . $folderModule . '/' . basename(str_replace('.', '/', $moduleName)) . '.current.xml';
	$fileObsoleteRouter = $folderRouter . '/' . $folderModule . '/' . basename(str_replace('.', '/', $moduleName)) . '.obsolete.xml';

	if(!file_exists($fileDef))
		throw new Exception('cannot locate def file ' . $fileDef);
	if(!file_exists($fileRouter))
		throw new Exception('cannot locate router file ' . $fileRouter);
	if(!file_exists($fileObsoleteRouter))
		$fileObsoleteRouter = null;

	$builder = new ObjectBuilder();
	$builder->compile($moduleName, $fileDef, $fileRouter, $fileObsoleteRouter, $fileTpl);
}
catch(Exception $e){
	die("compilation failed, reason:" . $e->getMessage() . "\n");
}
