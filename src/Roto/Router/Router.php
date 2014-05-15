<?php

namespace Roto\Router;

class Router {

	private $routes = array();
	private $di;

	public function __construct($di) {
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
						if (ctype_alpha($c)) {
							$types[] = $c;
						} else {
							$types = array();
							$inType = false;
							$inName = false;
						}
					} else if (ctype_alpha($c)) {
						$buff .= $c;
					} else {
						if ($c == '^') {
							$types = array();
							$inType = true;
						} else {
							$i--;
							$inName = false;
							$regex .= '?P<'.$buff.'>';
							$buff = '';
						}
					}

					if (! $inName) {
						if (! $types) {
							throw new \Exception("Expected type flags");
						} else {
							$regex .= '[';
							foreach ($types as $type) {
								if ($type == 'a') {
									$regex .= 'a-z';
								} else if ($type == 'd') {
									$regex .= '0-9';
								}
							}
							$regex .= ']+';
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
			} else  {
				$regex .= preg_quote($c);
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
#			see($data['regex'], $path);
			if (preg_match($data['regex'], $path, $matches)) {
				$o = array();
				foreach ($matches as $key => $value) {
					if (! is_numeric($key)) {
						$o[$key]= $value;
					}
				}
				$controller = call_user_func($data['callback'], $o, $this);
				break;
			}
		}
		return false;
	}
}