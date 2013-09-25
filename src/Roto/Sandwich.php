<?php

class Sandwich {

	private $docRoot = null;
	private $configFiles = array();
	private $appDirectories = array(
		'config' => null,
		'lib' => null,
		'func' => null,
		'layout' => null,
		'widget'=>null,
		'template'=>null,
		'www'=>null
	);
	private 

	public function __construct($router, $docRoot, $configFiles, $directoryStructure) {
		$this->docRoot = $docRoot;
		$this->configFiles = $configFiles;
		$this->directories = $directoryStructure;

		require_once($this->dir('func').'general.php');
	}	

	public function dir($key) {
		return $this->docRoot.$this->directories[strtolower(trim($key))];
	}

/*	public function __get($name) {
		if (substr($name, 0, 4) == 'DIR_') {
			return $this->dir(substr($name, 4));
		}
	}
*/
}