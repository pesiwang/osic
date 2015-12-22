<?php
namespace osi;
class SetStorage_User_Items{
	static private $_serverParams = array(
				0 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_00'),
				1 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_01'),
				2 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_02'),
				3 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_03'),
				4 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_04'),
				5 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_05'),
				6 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_06'),
				7 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_07'),
				8 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_08'),
				9 => array('host' => 'localhost', 'port' => 3306, 'user' => 'root', 'password' => '', 'database' => 'db_example_user', 'table' => 'ti_09'),
			);

	static private function _getServerIndexByPolicy($id){
		return 0;
	}

	static private function _getServerById($id){
		$serverParam = self::$_serverParams[self::_getServerIndexByPolicy($id)];
		$dsn = 'mysql:host=' . $serverParam['host'] . ';port=' . $serverParam['port'];
		$pdo = new \PDO($dsn, $serverParam['user'], $serverParam['password'], array(\PDO::ATTR_PERSISTENT => true, \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		return array($pdo, $serverParam);
	}


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
					$element = new SetElement_User_Items();
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
}
