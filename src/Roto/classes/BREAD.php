<?php

class BREAD {

	const REQUIRE_LOGIN = 1;

	private $callbacks = array(
		'BROWSE' => null,
		'READ' => null,
		'EDIT' => null,
		'ADD' => null,
		'DELETE' => null
	);

	private $resource;
	private static $input = null;
	private $loginCallback;
	private $loginCache;
	
	private static function captureInput() {
		if (is_null(self::$input)) {
			self::$input = file_get_contents('php://input');
		}
	}

	private function authLoginCallback() {
		if (is_null($this->loginCache)) {
			if (is_null($this->loginCallback)) {
				throw new Error("No login callback provided");
			}
			$callback = $this->loginCallback;
			$this->loginCache = $callback();
		}
		return $this->loginCache;
	}

	public function __construct($resource, $loginCallback = null) {
		$this->resource = $resource;
		$this->loginCallback = $loginCallback;
		self::captureInput();
	}

	private function setCallback($method, $args) {
		
		if (count($args) == 2) {
			$flags = $args[0];
			$callback = $args[1];
		} else {
			$flags = 0;
			$callback = $args[0];
		}
		$this->callbacks[$method] = array(
			'callback' => $callback,
			'flags' => $flags
		);

		return $this;
	}

	public function onAdd() {
		return $this->setCallback('ADD', func_get_args());
	}

	public function onBrowse() {
		return $this->setCallback('BROWSE', func_get_args());
	}

	public function onRead() {
		return $this->setCallback('READ', func_get_args());
	}

	public function onEdit() {
		return $this->setCallback('EDIT', func_get_args());
	}

	public function onDelete() {
		return $this->setCallback('DELETE', func_get_args());
	}

	public function isSpecified(&$id = false) {
		$urlInfo = parse_url($_SERVER['REQUEST_URI']);
		$resourcePath = $urlInfo['path'];
		if (is_dir(DIR_WWW.$resourcePath)) {
			return false;
		} else {
			$id = basename($resourcePath);
			return true;
		}
	}

	private function runCallback($method, $data, $identifier = null) {
		$callbackData = $this->callbacks[$method];
		if (! $callbackData) {
			throw new APIException("unsupportedAction", sprintf("Resource `%s' does not support method `%s'", $this->resource, $method));
		}

		$args = array($data);
		if (! is_null($identifier)) {
			$args []= $identifier;
		}

		if ($callbackData['flags'] & self::REQUIRE_LOGIN) {
			$args []= $this->authLoginCallback();
		}
		call_user_func_array($callbackData['callback'], $args);
	}

	public function dispatch() {
		$requestMethod = $_SERVER['REQUEST_METHOD'];

		$identifier = null;
		$data = json_decode(self::$input, true);
		$method = null;

		if ($this->isSpecified($identifier)) {
			switch($requestMethod) {
				case 'GET':
					$data = $_GET;
					$method = 'READ';
					break;
				case 'DELETE':
					$method = 'DELETE';
					break;
				case 'PUT':
					$method = 'EDIT';
					break;
			}
		} else {
			switch($requestMethod) {
				case 'GET':
					$method = 'BROWSE';
					$data = $_GET;
					break;
				case 'POST':
					$method = 'ADD';
					break;
			}
		}

		$this->runCallback($method, $data, $identifier);
	}

}