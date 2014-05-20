<?php

namespace Roto\Router;

class Router {

	private $routes = array();
	private $di;

	public function __construct($di) {//, $routeFactory, $routerParser) {
		$this->di = $di;
	}

	protected function generateRegex($path) {
		$regex = '/^';
		$inName = false;

		$inMatch = false;
		$inType = false;
		$inParens = false;
		$buff = '';
		for ($i = 0; $i < strlen($path); $i++) {



			$c = $path[$i];
			$cc = ($i + 1 < strlen($path)) ? $path[$i + 1] : false;

			if ($c == '(') {
				if ($inParens) {
					throw new \Exception("Already in parens");
				}
				$regex .= '(';
				$inParens = true;

			} else if ($inParens) {

				if ($inName) {
					if ($inType) {
						if (ctype_alpha($c) || $c == '*') {
							$types[] = $c;
						} else {
							$inType = false;
							$inName = false;
						}
					} else if (ctype_alpha($c)) {
						$buff .= $c;
					} else if ($c == '^') {
						$types = array();
						$inType = true;
					} else {
						$inName = false;
					}

					if (! $inName) {
						$i--;
						$regex .= '?P<'.$buff.'>';
						$buff = '';
						if (! $types) {
							throw new \Exception("Expected type flags");
						} else {
							$charMatchString = '';
							$isEverything = false;

							foreach ($types as $type) {
								if ($type == 'a') {
									$charMatchString .= 'a-z';
								} else if ($type == 'd') {
									$charMatchString .= '0-9';
								} else if ($type == '*') {
									$isEverything = true;
									break;
								}
							}

							if (! $isEverything) {
								$regex .= '['.$charMatchString.']+';
							} else {
								$regex .= '.+?';
							}

							$types = array('a');
						}
					}
				} else if ($c == ')') {
					if (! $inParens) {
						throw new \Exception("Not in paren");
					}
					$regex .= ')';
					if ($cc == '?') {
						$regex .= '?';
						$i++;
					}
					$inParens = false;

				} else if ($c == '^') {
					$types = array();
					$inType = true;
				} else if ($inType) {

				} else if ($c == ':') {
					$inName = true;
					$buff = '';
					$types = array('a');
				} else if ($c == '/') {
					$regex .= '\/';
				} else {
					$regex .= preg_quote($c);
				}
			} else {
				if ($c == '/') {
					$regex .= '\/';
				} else {
					$regex .= preg_quote($c);
				}
			}
		}
		$regex .= '$/i';
		return $regex;
	}

	public function map($name, $path, $callback) {
		$this->routes[$name] = array(
			'path' => $path,
			'regex' => $this->generateRegex($path),
			'callback' => $callback
		);

		return $this;
	}


	public function route($path) {
		$paths = parse_url($path);
		$path = ltrim($paths['path'], '/');
		$controller = null;
		foreach ($this->routes as $name => $data) {
			if (preg_match($data['regex'], $path, $matches)) {
				$o = array();
				foreach ($matches as $key => $value) {
					if (! is_numeric($key)) {
						$o[$key]= $value;
					}
				}
				$controller = call_user_func($data['callback'], $o, $this);
				return true;
				break;
			}
		}

		return false;
	}
}