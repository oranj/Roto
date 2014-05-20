<?php

namespace Roto\DataStore;

class ServiceRegistry extends Registry {

	private $services = array();
	private $factories = array();

	public function __construct() {
		parent::__construct();
	}

	protected function exists($key) {
		return parent::exists($key) || isset($this->services[$key]);
	}

	public function service($key, $lambda) {
		if ($this->exists($key)) {
			throw new \Exception("Service for `$key; is already set");
		}
		$this->services[$key] = $lambda;
	}

	public function factory($key, $lambda) {
		if (isset($this->factories[$key])) {
			throw new \Exception("Factory for `$key; is already set");
		}
		$this->factories[$key] = $lambda;
	}

	public function make($key) {
		$params = array_slice(func_get_args(), 1);

		if (! isset($this->factories[$key])) {
			throw new \Exception("No factory for `$key'");
		}
		return call_user_func_array($this->factories[$key], $params);
	}

	public function set($key, $value) {
		if ($this->exists($key)) {
			throw new \Exception("Value for `$key; is already set");
		}
		parent::set($key, $value);
	}

	public function get($key) {
		if (isset($this->services[$key])) {
			$cache = call_user_func($this->services[$key]);
			unset($this->services[$key]);
			parent::set($key, $cache);
		}

		return parent::get($key);

	}

}
