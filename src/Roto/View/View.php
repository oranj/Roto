<?php

namespace Roto\View;

class View extends \Roto\General\Registry {

	private $path;

	public function __construct($path) {
		if (! file_exists($path)) {
			throw new \Exception("View does not exist at path $path");
		}
		$this->path = $path;
	}

	public function __invoke() {
		return $this->render();
	}

	public function __get($key) {
		if (! $this->isRegistered($key)) {
			return "{{ $key }}";
		}
		return $this->get($key);
	}

	public function render() {
		ob_start();
		include($this->path);
		$out = ob_get_clean();
		return $out;
	}

}