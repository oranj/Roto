<?php

namespace Roto;

class Router {

	private $folderFile;
	private $templateRoot;
	private $webRoot;
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

	private function renderView($path) {
		$viewPath = $this->webRoot.$path.'.php';
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
	
		foreach ($folderFiles as $folderFile) {
			if (file_exists($folderFile)) {
				require($folderFile);
			}
		}
	}

	private function evaluateController($path) {
		$controllerPath = preg_replace('/\.([a-z]+)$/', '.php', $this->webRoot.$path);
		if (file_exists($controllerPath)) {
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