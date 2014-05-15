<?php

namespace Roto\Controller;

abstract class Controller {

	protected $di;

	public function __construct($di) {
		$this->di = $di;
	}

	public function request($action = 'index') {
		$methodName = strtolower($action).'Action';
		if (method_exists($this, $methodName)) {
			$output = call_user_func(array($this, $methodName));
			if (! is_callable($output)) {
				throw new \Exception("Result of action $action must return a callable method");
			}
			return $output;
		} else {
			throw new \Exception("No action defined");
		}
	}

}