<?php

namespace Roto\General;

class Registry {

	private $data = array();

	protected function isRegistered($key) {
		return isset($this->data[$key]);
	}

	public function get($key) {
		if (! $this->isRegistered($key)) {
			throw new \Exception("No value for `$key'");
		}

		return $this->data[$key];
	}

	public function getMany($keys) {
		$out = array();
		foreach ($keys as $key) {
			$out[] = $this->get($key);
		}
		return $out;
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function setMany($obj) {
		foreach ($obj as $key => $value) {
			$this->set($key, $value);
		}
	}
}