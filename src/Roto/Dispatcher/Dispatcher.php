<?php

namespace Roto\Dispatcher;

class Dispatcher {

	protected $controller;
	protected $action = 'index';
	protected $actionArgs = array();
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

	public function setAction($action, $args) {
		$this->action = $action;
		$this->actionArgs = $args;
	}

	public function getView() {
		if (! ($this->controller instanceof \Roto\Controller\Controller)) {
			throw new \Exception("No controller defined yet");
		}
		return $this->controller->request($this->action, $this->actionArgs);
	}

	public function execute() {
		$view = $this->getView();
		print($view->render());
	}

}