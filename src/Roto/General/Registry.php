<?php

namespace Roto\General;

class Registry {

	private $lambdaRegistry = array();
	private $objectRegistry = array();

	protected function isRegistered($key) {
		return isset($this->lambdaRegistry[$key]) || isset($this->objectRegistry[$key]);
	}

	public function get($key) {
		if (! isset($this->objectRegistry[$key])) {
			$this->objectRegistry[$key] = $this->getNew($key);
		}

		return $this->objectRegistry[$key];
	}

	public function getMany($keys) {
		$out = array();
		foreach ($keys as $key) {
			$out[] = $this->get($key);
		}
		return $out;
	}

	public function getNew($key) {
		if (! isset($this->lambdaRegistry[$key])) {
			throw new \Exception("No value for `$key'");
		}
		return $this->lambdaRegistry[$key]();
	}

	public function set($key, $value) {
		if ($this->isRegistered($key)) {
			throw new Exception("Service `$key' is already registered");
		}
		if (is_callable($value)) {
			$this->lambdaRegistry[$key] = $value;
		} else {
			$this->objectRegistry[$key] = $value;
		}
	}

	public function setMany($obj) {
		foreach ($obj as $key => $value) {
			$this->set($key, $value);
		}
	}
}