<?php

namespace Roto\General;

use \Roto\DataStore\MagicRegistry;

class ConfigLoader {

	protected $iniFiles = array();

	public function __construct() {
		$this->iniFiles = array_reverse(func_get_args());
	}

	protected function load(MagicRegistry $registry) {

		foreach ($this->iniFiles as $file) {
			$data = parse_ini_file($file, true);

			foreach ($data as $section => $value) {
				if (is_array($value)) {
					if (! $registry->exists($section)) {
						$registry->set($section, $value);
					} else {
						//$sectionData = $registry->get($section);
						foreach ($value as $k => $v) {
							$registry->$section[$k] = $v;
							//$sectionData[$k] = $v;
						}
						//.$registry->set($section, $sectionData);
					}
				} else {
					$registry->set($section, $value);
				}
			}
		}
	}

}