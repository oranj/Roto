<?php

namespace Roto;

class Config {

	private $data = null;
	private $iniFiles = array();

	private function loadConfig() {
		if (is_null($this->data)) {
			$numInis = count($this->iniFiles);
			$this->data = parse_ini_file($this->iniFiles[$numInis - 1], true);
			for ($i = $numInis - 2; $i >= 0; $i--) {
				$overrideData = parse_ini_file($this->iniFiles[$i], true);
				foreach ($overrideData as $section => $values) {
					if (is_array($values)) {
						if (! array_key_exists($section, $this->data)) {
							$this->data[$section] = array();
						}
						foreach ($values as $key => $value) {
							$this->data[$section][$key] = $value;
						}
					} else {
						$this->data[$section] = $value;
					}
				}
			}
		}
	}

	public function __construct() {	
		$this->iniFiles = func_get_args();
	}

	public function __get($name) {
		$this->loadConfig();
		if (! array_key_exists($name, $this->data)) {
			throw new Exception("Invalid database key `$name'");
		}
		return $this->data[$name];
	}


}