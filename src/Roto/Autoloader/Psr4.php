<?php

namespace Roto\Autoloader;

class Psr4 extends BaseAutoLoader {

	protected $root;
	protected $nsPrefix;

	public function __construct($root, $nsPrefix) {
		$this->root = $root;
		$this->nsPrefix = trim($nsPrefix, "\\");
	}

	public function load($classPath) {

		$cleanPath = trim($classPath, "\\");
		$startPos = strpos($cleanPath, $this->nsPrefix);


		if ($startPos === 0) {

			$cleanPath = substr($cleanPath, strlen($this->nsPrefix));

			$paths = array_filter(explode("\\", trim($cleanPath)));

			$path = $this->root . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, $paths) . '.php';

			if (file_exists($path)) {
				require_once($path);
			}
		}
	}
}