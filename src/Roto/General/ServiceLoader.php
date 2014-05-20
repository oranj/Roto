<?php

namespace Roto\General;


class ServiceLoader {

	protected $registry;

	public function __construct(\Roto\DataStore\ServiceRegistry $registry) {

		$this->registry = $registry;

	}

	public function load($glob) {

		$serviceFiles = glob($glob);

		foreach ($serviceFiles as $file) {
			if (is_file($file)) {
				$callback = require($file);
				$callback($this->registry);
			}
		}

	}

	public function getRegistry() {
		return $this->registry;
	}

}