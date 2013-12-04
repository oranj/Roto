<?php

namespace Roto;

class View {

	const MAIN_REGION = '_main';

	private $regions = array();
	private static $instance = null;
	private $currentRegion = self::MAIN_REGION;
	private $data;

	public function __construct() {}

	public function render($section_name = self::MAIN_REGION) {
		if (array_key_exists($section_name, $this->regions)) {
			$out = '';
			foreach ($this->regions[$section_name] as $section) {
				if (is_string($section)) {
					$out .= $section;
				} else if (gettype($section) == 'object' && get_class($section) == 'Roto\Widget') {
					$out .= $section->render();
				} else if (is_callable($section)) {
					$out .= $section();
				}
			}
			return $out;
		} else if ($section_name != self::MAIN_REGION) {
			return "<!-- [[$section_name]] -->";
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
			return $this->render($name);
		}
	}

	public function __set($name, $value) {
		$this->data[$name] = $value;
	}

	public function __isset($name) {
		return isset($this->data[$name]);
	}

	public function __get($name) {
		if (! isset($this->data[$name])) {
			return null;
		}
		return $this->data[$name];
	}

}