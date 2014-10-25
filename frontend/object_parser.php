<?php
require __DIR__ . '/../common/util.php';
require __DIR__ . '/../common/object.php';
error_reporting(E_ALL & ~E_NOTICE);

if($argc != 3){
	Util::error('usage: ' . $argv[0] . "  <object_file> <object_name>\n");
	exit(-1);
}

function parseFields($xmls){
	$fields = array();
	foreach($xmls as $xml){
		if(!isset($xml))
			throw new Exception('xml error');
	
		$field = new ObjectField();
	
		if(!isset($xml['name']))
			throw new Exception('cannot find attribute [name] in FIELDS section');
		$name = $xml['name']->__toString();
	
		if(!isset($xml['type']))
			throw new Exception('cannot find attribute [type] in FIELDS section');
		$field->setType($xml['type']->__toString());

		if(isset($xml['length']))
			$field->setLength($xml['length']->__toString());
	
		$field->setValue($xml[0]->__toString());
		
		$fields[$name] = $field;
	}
	return $fields;
}

function parseKey($xml){
	if(!isset($xml))
		throw new Exception('xml error');
	
	$key = new ObjectField();
	
	if(!isset($xml['type']))
		throw new Exception('cannot find attribute [type] in KEY section');
	$key->setType($xml['type']->__toString());
	
	if(isset($xml['length']))
		$key->setLength($xml['length']->__toString());

	return $key;
}

try{
	$objectName = $argv[2];
	$objectFile = $argv[1];
	$xml = @simplexml_load_file($objectFile, null, LIBXML_NOCDATA);
	
	//step 1. parsing key field
	$object = new Object();
	$object->setName($objectName)
		->setKey(parseKey($xml->key));

	//step 2. parsing fields
	foreach(parseFields($xml->fields->children()) as $name => $field)
		$object->addField($name, $field);
	
	Util::output(serialize($object));
}
catch(Exception $e){
	Util::error("failed to parse object, reason:" . $e->getMessage() . "\n");
}
