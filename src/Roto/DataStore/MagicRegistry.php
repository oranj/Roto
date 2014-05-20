<?php

namespace Roto\DataStore;

class MagicRegistry extends Registry {

	public function __get($key) {
		return $this->get($key);
	}

	public function __set($key) {
		return $this->set($key);
	}

}