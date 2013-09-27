<?php

namespace Roto;

class Router {

	private $folderFile;
	private $templateRoot;
	private $webRoot;
	private $definedController = null;
	private $definedView = null;
	private $template = null;
	private $templateMatches = array();
	private $view = null;

	public function __construct($view, $webRoot, $templateRoot, $folderFile = '_folder.php') {
		$this->view = $view;
		$this->webRoot = $webRoot;
		$this->templateRoot = $templateRoot;
		$this->folderFile = $folderFile;
	}

	public function template($template) {
		$this->template = $template;
	}

	public function controller($controller) {
		$this->definedController = $controller;
	}

	public function view($view) {
		$this->definedView = $view;
	}

	public function matchTemplate($regex, $template) {
		$this->templateMatches[$regex] = $template;
	}

	private function getFolderFiles($path) {
		$folders = array_filter(explode('/', $path));
		$folderPath = rtrim($this->webRoot.'/');
		$folderFiles = array();

		foreach ($folders as $folder) {
			$folderPath .= '/'.$folder;
			if (is_dir($folderPath)) {
				array_unshift($folderFiles, $folderPath.'/'.$this->folderFile);
			} else {
				break;
			}
		}

		return $folderFiles;
	}

	private function getController($path) {
		$controllerPath = $this->webRoot;

		if (isset($this->definedController)) {
			$controllerPath .= $this->definedController;
		} else {
			$controllerPath .= preg_replace('/\.([a-z]+)$/', '.php', $path);
		}

		return $controllerPath;
	}

	private function getTemplate($path) {
		if (! is_null($this->template)) {
			return $this->template;
		}
		foreach ($this->templateMatches as $regex => $template) {
			if (preg_match($regex, $path)) {
				return $template;
			}
		}
		return false;
	}

	private function getView($path) {
		$viewPath = $this->webRoot;
		if (isset($this->definedView)) {
			$viewPath .= $this->definedView;
		} else {
			$viewPath .= $path.'.php';
		}
		return $viewPath;
	}

	private function renderView($path) {
		$viewPath = $this->getView($path);
		if (file_exists($viewPath)) {
			$this->view->includeFile($viewPath);
			return true;
		}
		return false;
	}

	private function renderTemplate($path) {
		if ($template = $this->getTemplate($path)) {
			$View = $this->view;
			include($this->templateRoot.$template);
		} else {
			$this->view->main();
		}
	}

	private function evaluateFolderFiles($path) {
		$folderFiles = $this->getFolderFiles($path);
		$View = $this->view;
		foreach ($folderFiles as $folderFile) {
			if (file_exists($folderFile)) {
				require($folderFile);
			}
		}
	}

	private function evaluateController($path) {
		$controllerPath = $this->getController($path);
		if (file_exists($controllerPath)) {
			$View = $this->view;
			require($controllerPath);
		}
	}

	public function route($requestUri) {
		$pathInfo = parse_url($requestUri);
		$path = $pathInfo['path'];
		if ($path[strlen($path)-1] == '/') {
			$path .= 'index.html';
		}
		$path = ltrim($path, '/');
	
		$this->evaluateFolderFiles($path);
		$this->evaluateController($path);

		$this->renderView($path);
		$this->renderTemplate($path);
		
	}
}