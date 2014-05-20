<?php

namespace Roto\Controller;

abstract class Controller {

	protected $di;

	public function __construct($di) {
		$this->di = $di;
	}

	public function request($action = 'index', $args = array()) {
		$methodName = strtolower($action).'Action';
		if (method_exists($this, $methodName)) {

			$output = call_user_func_array(array($this, $methodName), $args);
			if (! is_array($output)) {
				throw new \Exception("Result of action $action must return an array");
			}

			return $output;
		} else {
			throw new \Exception("No action defined");
		}
	}

}