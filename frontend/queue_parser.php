<?php
require __DIR__ . '/../common/util.php';
require __DIR__ . '/../common/queue.php';
error_reporting(E_ALL & ~E_NOTICE);

if($argc != 3){
	Util::error('usage: ' . $argv[0] . "  <queue_file> <queue_name>\n");
	exit(-1);
}

function parseFields($xmls){
	$fields = array();
	foreach($xmls as $xml){
		if(!isset($xml))
			throw new Exception('xml error');
	
		$field = new QueueField();
	
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
	
	$key = new QueueField();
	
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
	
	$element = new QueueField();
	
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
	$queueName = $argv[2];
	$queueFile = $argv[1];
	$xml = @simplexml_load_file($queueFile, null, LIBXML_NOCDATA);
	
	//step 1. parsing key field & element field & capacity
	$queue = new Queue();
	$queue->setName($queueName)
		->setKey(parseKey($xml->key))
		->setElement(parseElement($xml->element))
		->setCapacity(parseCapacity($xml->capacity));

	//step 2. parsing fields
	foreach(parseFields($xml->fields->children()) as $name => $field){
		$queue->addField($name, $field);
	}
	
	Util::output(serialize($queue));
}
catch(Exception $e){
	Util::error("failed to parse queue, reason:" . $e->getMessage() . "\n");
}
