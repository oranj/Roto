<?php

namespace Roto\Dispatcher;

class Dispatcher {

	protected $controller;
	protected $action = 'index';
	protected $di;

	public function __construct($di) {
		$this->di = $di;
	}

	public function setController($controller) {
		if (is_string($controller)) {

			$psrPath = str_replace('/', '\\', trim($controller, '/'));

			if (! class_exists($psrPath, true)) {
				throw new \Exception("Invalid controller path: $psrPath");
			}

			$this->controller = new $psrPath($this->di);

		} else {

			$this->controller = $controller;
		}
	}

	public function setAction($action) {
		$this->action = $action;
	}

	public function execute() {
		$out = $this->controller->request($this->action);
		return $out();
	}

}