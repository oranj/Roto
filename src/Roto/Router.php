<?php

namespace Roto;

class Router {

	private $folderFile;
	private $templateRoot;
	private $webRoot;
	private $template = null;

	private $view = null;

	private $traces = array();
	private $routing_data = array();
	private $routing_parameters = array();

	private $mapping = array();

	public function __construct($view, $webRoot, $templateRoot) {
		$this->view = $view;
		$this->webRoot = $webRoot;
		$this->templateRoot = $templateRoot;
	}

	public function view($view) {
		$this->traces []= array('view' => $view);
	}

	public function template($path) {
		$this->traces []= array('template' => $path);
	}

	private function findInTrace($key) {
		$value = null;
		foreach ($this->traces as $route) {
			if (isset($route[$key])) {
				$value = $route[$key];
			}
		}
		return $value;
	}


	private function renderView() {
		$viewPath = $this->webRoot . $this->findInTrace('view');
		if (file_exists($viewPath)) {
			echo $this->view->includeFile($viewPath);
			return true;
		}
		return false;
	}


	private function renderTemplate() {
		$fullPath = $this->templateRoot.$this->findInTrace('template');
		if (file_exists($fullPath) && ! is_dir($fullPath)) {
			$View = $this->view;
			include($fullPath);
		} else {
			echo $this->view->main();
		}
	}

	private function evaluateController() {
		$controllerPath = $this->webRoot . $this->findInTrace('controller');
		if (file_exists($controllerPath)) {
			if (is_dir($controllerPath)) {
				throw new \Exception("Invalid controller path $controllerPath");
			}
			$View = $this->view;
			require($controllerPath);
		}
	}

	public function map($regex, $data) {
		$this->mapping []= array('match' => $regex, 'data' => $data);
		return $this;
	}

	private function atSubstitute($string, $matches, $default_template = '{{%s}}') {
		while (strpos($string, '@') !== false) {
			if (preg_match("/@(\{(.*?)\}|\b(.+?)\b)/", $string, $var_matches)) {

				$search = $var_matches[0];

				if ($var_matches[2]) {
					$name = $var_matches[2];
				} else if ($var_matches[3]) {
					$name = $var_matches[3];
				}

				if (isset($matches[$name])) {
					$replace = $matches[$name];
				} else {
					$replace = sprintf($default_template, $name);
				}

				$string = str_replace($search, $replace, $string);
			} else {
				break;
			}
		}
		return $string;
	}

	public function parameters() {
		$out = array();
		foreach($this->traces as $route) {
			if (isset($route['params'])) {
				foreach ($route['params'] as $name => $value) {
					$out[$name] = $value;
				}
			}
		}
		return $out;
	}

	public function param($name) {
		$params = $this->parameters();
		if (isset($params[$name])) {
			return $params[$name];
		}
		return null;
	}

	public function routingData() {
		return $this->traces;
	}

	public function route($request) {

		$urlinfo = parse_url($request);
		$request = $urlinfo['path'];

		$controller = null;
		$view = null;
		$template = null;
		$this->routing_parameters = array();
		$request_trace = array($request);
		$is_final = false;

		foreach ($this->mapping as $map) {
			$route = array();

			if (preg_match($map['match'], $request, $matches)) {
				if (isset($map['data']['parameters'])) {
					$route['params'] = array();
					foreach ($map['data']['parameters'] as $key => $value) {
						if (is_callable($value)) {
							$route['params'][$key] = call_user_func($value, $matches);
						} else {
							$route['params'][$key] = $this->atSubstitute($value, $matches, '');
						}
					}
				}
				$route['match'] = $map['match'];
				foreach ($map['data'] as $type => $value) {
					switch ($type) {
						case 'final':
							$is_final = true;
							break;
						case 'request':
							if (is_callable($value)) {
								$route['request'] = call_user_func($value, $matches);
							} else if (is_string($value)) {
								$route['request'] = $this->atSubstitute($value, $matches);
							}
							$request = $route['request'];
							break;
						case 'controller':
							if (is_callable($value)) {
								$route['controller'] = call_user_func($value, $matches);
							} else if (is_string($value)) {
								$route['controller'] = $this->atSubstitute($value, $matches);
							}
							break;
						case 'template':
							if (is_callable($value)) {
								$route['template'] = call_user_func($value, $matches);
							} else if (is_string($value)) {
								$route['template'] = $this->atSubstitute($value, $matches);
							}
							break;
						case 'view':
							if (is_callable($value)) {
								$route['view'] = call_user_func($value, $matches);
							} else if (is_string($value)) {
								$route['view'] = $this->atSubstitute($value, $matches);
							}
							break;
					}
				}
				$this->traces []= $route;
				if ($is_final) {
					break;
				}
			}
		}

		$this->evaluateController();
		$this->renderView();
		$this->renderTemplate();

	}
}