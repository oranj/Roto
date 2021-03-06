<?php

namespace Roto\View;

class View extends \Roto\DataStore\Registry {

	private $path;

	public function __construct($path) {
		if (! file_exists($path)) {
			throw new \Exception("View does not exist at path $path");
		}
		$this->path = $path;
	}

	public function __get($key) {
		if (! $this->isRegistered($key)) {
			return "{{ $key }}";
		}
		return $this->get($key);
	}

	public function render($data) {
		ob_start();
		call_user_func(function() use ($data, $path) {
			include($path);
		});
		$out = ob_get_clean();
		return $out;
	}

}