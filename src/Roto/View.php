<?php

class View {

	const MAIN_SECTION = '_main';

	private $sections = array();
	private static $instance = null;
	private $currentRegion = self::MAIN_SECTION;

	public function __construct() {}

	private static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new View();
		}
		return self::$instance;
	}

	public function render($section = self::MAIN_SECTION) {
		if (array_key_exists($section, $this->sections)) {

			if (is_callable($this->sections[$section])) {
				$this->sections[$section]();
			} else {
				echo $this->sections[$section];
			}			
		} else if (! $section == self::MAIN_SECTION) {
			throw new Exception(
				"Unable to render, section {$section} not defined"
			);
		}
	}

	private function region($name) {
		$this->endregion();
		$this->currentRegion = $name;
	}

	private function endregion() {
		$this->section($this->currentRegion, trim(ob_get_clean()));
		ob_start();
	}

	public function includeFile($filename) {
		ob_start();
		include ($filename);
		$viewHtml = ob_get_clean();

		$this->section(self::MAIN_SECTION, trim($viewHtml));
	}

	public function section($name, $sectionData = null) {
		if (! isset($this->sections[$name])) {
			$this->sections[$name] = '';
		}
		$this->sections[$name] .= $sectionData;
	}

	public function __call($name, $args) {
		if ($name == 'main') {
			$name = self::MAIN_SECTION;
		}

		if (count($args) > 0) {
			$this->section($name, $args[0]);
		} else {
			$this->render($name);
		}		
	}

	public static function __callStatic($method, $args) {
		$inst = self::getInstance();
		return call_user_func_array(array($inst, $method), $args);
	}

}