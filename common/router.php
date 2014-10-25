<?php
class Media{
	protected $_connParams = array();
	protected $_type = '';
	
	public function setType($type){
		$this->_type = $type;
		return $this;
	}
	
	public function getType(){
		return $this->_type;
	}
	
	public function addConnParams($idx, Array $connParam){
		foreach($connParam as $name => &$value)
			$value = preg_replace('/{([^}]+)}/e', 'sprintf("$1", ' . $idx . ')', $value);
		$this->_connParams[$idx] = $connParam;
	}
	
	public function getConnParams(){
		return $this->_connParams;
	}
}


class Router{	
	private $_name;
	
	private $_cache		= null;
	private $_dset		= null;
	private $_storage	= null;
	
	private $_cachePolicy	= null;
	private $_dsetPolicy	= null;
	private $_storagePolicy	= null;

	public function __construct(){
	}
	
	public function getName(){
		return $this->_name;
	}
	
	public function setName($name){
		$this->_name = $name;
		return $this;
	}
	
	public function hasCache(){
		return is_object($this->_cache);
	}
	
	public function getCache(){
		return $this->_cache;
	}
	
	public function setCache(Media $cache){
		$this->_cache = $cache;
		return $this;
	}
	
	public function hasDset(){
		return is_object($this->_dset);
	}
	
	public function getDset(){
		return $this->_dset;
	}
	
	public function setDset(Media $dset){
		$this->_dset = $dset;
		return $this;
	}
	
	public function hasStorage(){
		return is_object($this->_storage);
	}
	
	public function getStorage(){
		return $this->_storage;
	}
	
	public function setStorage(Media $storage){
		$this->_storage = $storage;
		return $this;
	}
	
	public function hasCachePolicy(){
		return is_string($this->_cachePolicy);
	}
	
	public function getCachePolicy(){
		return $this->_cachePolicy;
	}
	
	public function setCachePolicy($policy){
		$this->_cachePolicy = $policy;
		return $this;
	}
	
	public function hasDsetPolicy(){
		return is_string($this->_dsetPolicy);
	}
	
	public function getDsetPolicy(){
		return $this->_dsetPolicy;
	}
	
	public function setDsetPolicy($policy){
		$this->_dsetPolicy = $policy;
		return $this;
	}
	
	public function hasStoragePolicy(){
		return is_string($this->_storagePolicy);
	}
	
	public function getStoragePolicy(){
		return $this->_storagePolicy;
	}
	
	public function setStoragePolicy($policy){
		$this->_storagePolicy = $policy;
		return $this;
	}
}