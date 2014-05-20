<?php

namespace Roto\Autoloader;

abstract class BaseAutoloader {

	public function __invoke($classPath) {
		$this->load($classPath);
	}

	public function register() {
		spl_autoload_register($this);
	}

	public function unregister() {
		spl_autoload_unregister($this);
	}

	public abstract function load($path);

}