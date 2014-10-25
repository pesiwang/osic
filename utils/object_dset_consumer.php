<?php
declare(ticks=1);
ini_set('memory_limit', '1024M');

class ObjectDsetConsumerConfig{
	private $_module = '';
	private $_heavyLoadLimit = 10000;
	private $_commitInterval = 30;
	private $_channel = 0;
	private $_directory = '';
	
	public function getModule(){
		return $this->_module;
	}
	
	public function setModule($module){
		$this->_module = $module;
		return $this;
	}
	
	public function getHeavyLoadLimit(){
		return $this->_heavyLoadLimit;
	}
	
	public function setHeavyLoadLimit($limit){
		$this->_heavyLoadLimit = $limit;
		return $this;
	}
	
	public function getCommitInterval(){
		return $this->_commitInterval;
	}
	
	public function setCommitInterval($interval){
		$this->_commitInterval = $interval;
		return $this;
	}
	
	public function getChannel(){
		return $this->_channel;
	}
	
	public function setChannel($channel){
		$this->_channel = $channel;
		return $this;
	}
	
	public function getDirectory(){
		return $this->_directory;
	}
	
	public function setDirectory($directory){
		$this->_directory = $directory;
		return $this;
	}
}

class ObjectDsetConsumer{
	private $_config = null;
	private $_halt = false;
	
	public function __construct(ObjectDsetConsumerConfig $config, $obsolete = false){
		$this->_config = $config;
		
		$consumerClassName = 'ObjectDset' . ($obsolete ? 'Obsolete' : 'Current') . '_' . ucfirst(preg_replace('/\.([a-z])/ei', "strtoupper('\\1')", $this->_config->getModule()));
		$routerClassName = 'ObjectRouter' . ($obsolete ? 'Obsolete' : 'Current') . '_' . ucfirst(preg_replace('/\.([a-z])/ei', "strtoupper('\\1')", $this->_config->getModule()));

		$consumerFileName = $this->_config->getDirectory() . '/' . strtolower(str_replace('.', '/', $this->_config->getModule())) . '.dset.' . (($obsolete ? 'obsolete' : 'current')) . '.php'; 
		$routerFileName = $this->_config->getDirectory() . '/' . strtolower(str_replace('.', '/', $this->_config->getModule())) . '.router.php';
		$objectFileName = $this->_config->getDirectory() . '/' . strtolower(str_replace('.', '/', $this->_config->getModule())) . '.object.php';
		
		if(!is_readable($consumerFileName) || !is_readable($routerFileName) || !is_readable($objectFileName))
            die('file missing on module[' . $config->getModule() . "]\n");

		require_once $this->_config->getDirectory() . '/object_loader.php';
        require_once $objectFileName;
        require_once $routerFileName;
        require_once $consumerFileName;
        if(!class_exists($consumerClassName) || !class_exists($routerClassName))
            die('class missing on module[' . $config->getModule() . "]\n");

        if(!pcntl_signal(SIGUSR1, array($this, '_onIncreaseCommitInterval')))
            die('cannot handle SIGUSR1 signal' . "\n");
        if(!pcntl_signal(SIGUSR2, array($this, '_onDecreaseCommitInterval')))
            die('cannot handle SIGUSR2 signal' . "\n");
        if(!pcntl_signal(SIGHUP, array($this, '_onHalt')))
            die('cannot handle SIGHUP signal' . "\n");

		//run till halt
		$consumer = new $consumerClassName($routerClassName::getDsetConnParam($this->_config->getChannel()));
		while(!$this->_halt){
			$idStats = $consumer->consume();
			
			$numFetched = count($idStats);
			echo $config->getModule(), ' commiting ', $numFetched, ' record(s)...';
			$ts = time();
			$numCommited = $this->_commit($routerClassName, $idStats);
			$te = time();
			$timeSpent = $te - $ts;
			echo "done(${numCommited}/${numFetched})(${timeSpent}s)\n";
			
			if($numFetched < $config->getHeavyLoadLimit())
				sleep($config->getCommitInterval());
		}
		echo "service closed.\n";
	}
	
	////////////////////////////////////////////
	private function _commit($routerClassName, $idStats){
		$cnt = 0;
		foreach($idStats as $id => $stat){
			if($stat == 'd'){
				try{
					$storage = $routerClassName::_getStorage($id);
					$storage->del($id);
					++$cnt;
				}
				catch(Exception $e){
				}
			}
			else{
				$cache = $routerClassName::_getCache($id);
				$storage = $routerClassName::_getStorage($id);
				$object = $cache->get($id);
				if(isset($object)){
					$storage->set($id, $object);
					++$cnt;
				}
			}
		}
		return $cnt;
	}
	
	public function _onIncreaseCommitInterval(){
        $this->_config->setCommitInterval($this->_config->getCommitInterval() + 1);
        echo "[SYS]commit interval set to ", $this->_config->getCommitInterval(), "\n";
    }

    public function _onDecreaseCommitInterval(){
        if($this->_config->getCommitInterval() > 0)
            $this->_config->setCommitInterval($this->_config->getCommitInterval() - 1);
        echo "[SYS]commit interval set to ", $this->_config->getCommitInterval(), "\n";
    }

    public function _onHalt($signo){
        $this->_halt = true;
    }
}


$options = getopt("m:d:c:i:l:o");
if(count($options) < 5){
	echo "usage:\n\t-m <module_name>\t\tspecify the module name to start dset consuming service\n\t-d <object_directory>\t\troot directory of compiled object files\n";
	echo "\t-c <channel>\t\tspecify channel id [0-n]\n\t-i <seconds_to_commit>\t\ttime value in seconds\n\t-l <busy_limit>\t\tdo not sleep if one time commits goes over this number\n";
	echo "\t-o use obsolete module\n";
	die();
}

$config = new ObjectDsetConsumerConfig();
$config->setModule($options['m'])
	->setDirectory($options['d'])
	->setChannel($options['c'])
	->setCommitInterval($options['i'])
	->setHeavyLoadLimit($options['l']);

new ObjectDsetConsumer($config, isset($options['o']));
