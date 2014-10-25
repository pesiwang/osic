<?php
require __DIR__ . '/../common/util.php';
require __DIR__ . '/../common/set.php';
error_reporting(E_ALL & ~E_NOTICE);

if($argc != 3){
	Util::error('usage: ' . $argv[0] . "  <set_file> <set_name>\n");
	exit(-1);
}

function parseFields($xmls){
	$fields = array();
	foreach($xmls as $xml){
		if(!isset($xml))
			throw new Exception('xml error');
	
		$field = new SetField();
	
		if(!isset($xml['name']))
			throw new Exception('cannot find attribute [name] in FIELDS section');
		$name = $xml['name']->__toString();
	
		if(!isset($xml['type']))
			throw new Exception('cannot find attribute [type] in FIELDS section');
		$field->setType($xml['type']->__toString());
	
		if(isset($xml['length']))
			$field->setLength($xml['length']->__toString());
			
		if(isset($xml['index']))
			$field->setIndex(($xml['index']->__toString() == 'NUMERIC') ? SetField::INDEX_NUMERIC : SetField::INDEX_STRING);

		$field->setValue($xml[0]->__toString());
		
		$fields[$name] = $field;
	}
	return $fields;
}

function parseKey($xml){
	if(!isset($xml))
		throw new Exception('xml error');
	
	$key = new SetField();
	
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
	
	$element = new SetField();
	
	if(!isset($xml['type']))
		throw new Exception('cannot find attribute [type] in KEY section');
	$element->setType($xml['type']->__toString());
	
	if(isset($xml['length']))
		$element->setLength($xml['length']->__toString());

	return $element;
}

function parseCapacity($xml){
	$capacity = intval($xml[0]->__toString());
	if($capacity <= 0)
		throw new Exception('bad capacity value');
	return $capacity;
}

try{
	$setName = $argv[2];
	$setFile = $argv[1];
	$xml = @simplexml_load_file($setFile, null, LIBXML_NOCDATA);
	
	//step 1. parsing key field & element field & capacity
	$set = new Set();
	$set->setName($setName)
		->setKey(parseKey($xml->key))
		->setElement(parseElement($xml->element))
		->setCapacity(parseCapacity($xml->capacity));

	//step 2. parsing fields
	foreach(parseFields($xml->fields->children()) as $name => $field){
		$set->addField($name, $field);
	}
	
	Util::output(serialize($set));
}
catch(Exception $e){
	Util::error("failed to parse set, reason:" . $e->getMessage() . "\n");
}
