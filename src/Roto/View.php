<?php

namespace Roto;

class View {

	const MAIN_REGION = '_main';

	private $regions = array();
	private static $instance = null;
	private $currentRegion = self::MAIN_REGION;
	private $data;

	public function __construct() {}

	private static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new View();
		}
		return self::$instance;
	}

	public function render($section_name = self::MAIN_REGION) {
		if (array_key_exists($section_name, $this->regions)) {
			foreach ($this->regions[$section_name] as $section) { 
				if (is_string($section)) {
					echo $section;
				} else if (gettype($section) == 'object' && get_class($section) == 'Roto\Widget') {
					$section->render();
				} else if (is_callable($section)) {
					$section();
				} 			
			}
		} else if ($section_name != self::MAIN_REGION) {
			throw new \Exception(
				"Unable to render, section {$section_name} not defined"
			);
		}
	}

	private function startRegion($name) {
		$this->endregion();
		$this->currentRegion = $name;
	}

	private function endRegion() {
		$this->region($this->currentRegion, trim(ob_get_clean()));
		ob_start();
	}

	public function includeFile($filename) {
		ob_start();
		include ($filename);
		$viewHtml = ob_get_clean();

		$this->region(self::MAIN_REGION, trim($viewHtml));
	}

	public function region($name, $sectionData = null) {
		if (! isset($this->regions[$name])) {
			$this->regions[$name] = array();
		}
		$this->regions[$name] []= $sectionData;
	}

	public function __call($name, $args) {
		if ($name == 'main') {
			$name = self::MAIN_REGION;
		}

		if (count($args) > 0) {
			$this->region($name, $args[0]);
		} else {
			$this->render($name);
		}		
	}

	public static function __callStatic($method, $args) {
		$inst = self::getInstance();
		return call_user_func_array(array($inst, $method), $args);
	}

	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function __isset($name) {
		return isset($this->data[$name]);
	}

	public function __get($name) {
		return $this->data[$name];
	}

}