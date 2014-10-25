<?php
error_reporting(0);
class Util{
	static public function output($msg){
		fwrite(STDOUT, $msg);
	}

	static public function error($msg){
		fwrite(STDERR, $msg);
	}
}