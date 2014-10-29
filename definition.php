<?php
class TKey
{
	public $type;
	public $length;
	public $isFixedLength;
}

class TObjectField
{
	public $name;
	public $fullname;
	public $type;
	public $value;
}

class TObject
{
	public $key;
	public $fields = array();
}

class TSetElementField
{
	public $name;
	public $fullname;
	public $type;
	public $value;
	public $index;
}

class TSetElement
{
	public $key;
	public $fields = array();
}

class TSet
{
	public $key;
	public $capacity;
	public $element;
}

class TRouterMediaServerMemcache
{
	public $host;
	public $port;
	public $prefix;
}

class TRouterMediaServerRedis
{
	public $host;
	public $port;
	public $prefix;
}

class TRouterMediaServerMysql
{
	public $host;
	public $port;
	public $user;
	public $password;
	public $database;
	public $table;
}

class TRouterMedia
{
	public $name;
	public $servers = array();
	public $policy;
}

class TRouter
{
	public $cache;
	public $storage;
}
