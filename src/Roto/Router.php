<?php

namespace Roto;

class Router {

	private $folderFile;
	private $templateRoot;
	private $webRoot;
	private $definedController = null;
	private $definedView = null;
	private $definedTemplate = null;
	private $template = null;
	private $templateMatches = array();
	private $view = null;

	private $routing_data = array();
	private $routing_parameters = array();

	private $mapping = array();

	public function __construct($view, $webRoot, $templateRoot) {
		$this->view = $view;
		$this->webRoot = $webRoot;
		$this->templateRoot = $templateRoot;
	}

	public function view($view) {
		$this->definedView = $view;
	}


	private function renderView($path) {
		if ($this->definedView) {
			$path = $this->definedView;
		}
		$viewPath = $this->webRoot . $path;
		if (file_exists($viewPath)) {
			echo $this->view->includeFile($viewPath);
			return true;
		}
		return false;
	}

	public function template($path) {
		$this->definedTemplate = $path;
	}

	private function renderTemplate($path) {
		if (! is_null($this->definedTemplate)) {
			$path = $this->definedTemplate;
		}
		$fullPath = $this->templateRoot.$path;
		if ($path && file_exists($fullPath) && ! is_dir($fullPath)) {
			$View = $this->view;
			include($fullPath);
		} else {
			echo $this->view->main();
		}
	}

	private function evaluateController($path) {
		if ($this->definedController) {
			$path = $this->definedController;
		}
		$controllerPath = $this->webRoot . $path;
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
		return $this->routing_parameters;
	}

	public function param($name) {
		if (isset($this->routing_parameters[$name])) {
			return $this->routing_parameters[$name];
		}
		return null;
	}

	public function routingData() {
		return $this->routing_data;
	}

	public function route($request) {

		$urlinfo = parse_url($request);
		$request = $urlinfo['path'];

		$controller = null;
		$view = null;
		$template = null;
		$this->routing_parameters = array();
		$request_trace = array($request);

		foreach ($this->mapping as $map) {
			if (preg_match($map['match'], $request, $matches)) {
				if (isset($map['data']['parameters'])) {
					foreach ($map['data']['parameters'] as $key => $value) {
						if (is_callable($value)) {
							$this->routing_parameters[$key] = call_user_func($value, $matches);
						} else {
							$this->routing_parameters[$key] = $this->atSubstitute($value, $matches, '');
						}
					}
				}
				foreach ($map['data'] as $type => $value) {
					switch ($type) {
						case 'request':
							if (is_callable($value)) {
								$request = call_user_func($value, $matches);
							} else if (is_string($value)) {
								$request = $this->atSubstitute($value, $matches);
							}
							$request_trace []= $request;
							break;
						case 'controller':
							if (is_callable($value)) {
								$controller = call_user_func($value, $matches);
							} else if (is_string($value)) {
								$controller = $this->atSubstitute($value, $matches);
							}
							break;
						case 'template':
							if (is_callable($value)) {
								$template = call_user_func($value, $matches);
							} else if (is_string($value)) {
								$template = $this->atSubstitute($value, $matches);
							}
							break;
						case 'view':
							if (is_callable($value)) {
								$view = call_user_func($value, $matches);
							} else if (is_string($value)) {
								$view = $this->atSubstitute($value, $matches);
							}
							break;
					}
				}
			}
		}

		$this->routing_data = array(
			'controller' => $controller,
			'view' => $view,
			'template' => $template,
			'request' => $request_trace,
			'parameters' => $this->routing_parameters
		);


		$this->evaluateController($controller);
		$this->renderView($view);
		$this->renderTemplate($template);

	}
}