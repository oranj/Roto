<?php

namespace Roto\Autoloader;

class Psr0 extends BaseAutoloader {

	protected $root;

	public function __construct($root) {
		$this->root = $root;
	}

	public function load($classPath) {

		$paths = explode("\\", trim($classPath, "\\"));
		$class = array_pop($paths);

		$path = $this->root . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $paths) . DIRECTORY_SEPARATOR . str_replace("_", DIRECTORY_SEPARATOR, $class).'.php';

		if (file_exists($path)) {
			require_once($path);
		}
	}

}