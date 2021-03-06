<?php

namespace Roto\DataStore;

class Registry extends BaseDataStore {

	protected function exists($key) {
		return array_key_exists($key, $this->data);
	}

	public function get($key) {
		if (! $this->exists($key)) {
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