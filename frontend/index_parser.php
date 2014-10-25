<?php
require __DIR__ . '/../common/util.php';
require __DIR__ . '/../common/index.php';
error_reporting(E_ALL & ~E_NOTICE);

if($argc != 3){
	Util::error('usage: ' . $argv[0] . "  <index_file> <index_name>\n");
	exit(-1);
}

function parseKey($xml){
	if(!isset($xml))
		throw new Exception('xml error');
	
	$key = new IndexField();
	
	if(!isset($xml['type']))
		throw new Exception('cannot find attribute [type] in KEY section');
	$key->setType($xml['type']->__toString());
	
	if(isset($xml['length']))
		$key->setLength($xml['length']->__toString());

	return $key;
}

function parseElement($xml){
	if(!isset($xml))
		throw new Exception('xml error');
	
	$element = new IndexField();
	
	if(!isset($xml['type']))
		throw new Exception('cannot find attribute [type] in KEY section');
	$element->setType($xml['type']->__toString());
	
	if(isset($xml['length']))
		$element->setLength($xml['length']->__toString());

	return $element;
}

try{
	$indexName = $argv[2];
	$indexFile = $argv[1];
	$xml = @simplexml_load_file($indexFile, null, LIBXML_NOCDATA);
	
	//step 1. parsing key field & element field & capacity
	$index = new Index();
	$index->setName($indexName)
		->setKey(parseKey($xml->key))
		->setElement(parseElement($xml->element));

	Util::output(serialize($index));
}
catch(Exception $e){
	Util::error("failed to parse index, reason:" . $e->getMessage() . "\n");
}
