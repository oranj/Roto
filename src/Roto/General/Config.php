<?php

namespace Roto\General;

class Config {

	private $data = null;
	private $iniFiles = array();
	private $serverReplace = array();

	private function loadConfig() {
		if (is_null($this->data)) {
			$numInis = count($this->iniFiles);
			$this->data = array();//parse_ini_file($this->iniFiles[$numInis - 1], true);
			for ($i = $numInis - 1; $i >= 0; $i--) {
				$overrideData = parse_ini_file($this->iniFiles[$i], true);
				foreach ($overrideData as $section => $values) {
					if (is_array($values)) {
						if (! array_key_exists($section, $this->data)) {
							$this->data[$section] = array();
						}
						foreach ($values as $key => $value) {
							$this->data[$section][$key] = $this->substitute($value, $_SERVER);
						}
					} else {
						$this->data[$section] = $this->substitute($value, $_SERVER);
					}
				}
			}
		}
	}

	private function substitute($string, $matches = null, $default_template = '{{%s}}') {
		while (preg_match("/\{\[(.*?)\]\}/", $string, $var_matches)) {
			if (is_null($matches)) {
				throw new \Exception("Cannot use at substitution without configuring a map");
			}

			$search = $var_matches[0];
			$name = $var_matches[1];

			if (isset($matches[$name])) {
				$replace = $matches[$name];
			} else {
				$replace = sprintf($default_template, $name);
			}

			$string = str_replace($search, $replace, $string);

		}
		return $string;
	}

	public function __construct() {
		$args = func_get_args();
		$last_arg = $args[count($args) - 1];
		if (is_array($last_arg)) {
			$this->iniFiles = array_slice($args, 0, -1);
			$this->serverReplace = $last_arg;
		} else {
			$this->iniFiles = $args;
			$this->serverReplace = null;
		}
	}

	public function __get($name) {
		$this->loadConfig();
		if (! array_key_exists($name, $this->data)) {
			throw new \Exception("Invalid database key `$name'");
		}
		return $this->data[$name];
	}


}