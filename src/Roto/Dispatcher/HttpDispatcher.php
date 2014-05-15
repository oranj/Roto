<?php

namespace Roto\Dispatcher;

use \Roto\View\View;

class HttpDispatcher extends Dispatcher {

	protected $layout;
	protected $layoutRoot;
	protected $headers = array();

	public function __construct($di, $layoutRoot) {
		$this->di = $di;
		$this->layoutRoot = $layoutRoot;
	}

	public function setLayout($layout) {
		if (is_string($layout)) {
			$this->layout = new View($this->layoutRoot.$layout, array());
		} else if ($layout instanceof View) {
			$this->layout = $layout;
		} else {
			throw new \Exception("Invalid layout");
		}
	}

	public function header($text) {
		$this->headers[] = $text;
	}

	public function sendHeaders() {
		foreach ($this->headers as $header) {
			header($header);
		}
	}

	public function getLayout() {
		return $this->layout;
	}

	public function execute() {
		$view = $this->controller->request($this->action);
		if (! ($view instanceof View)) {
			throw new \Exception("Action must return widget");
		}
		if (! is_null($this->layout)) {
			if (! ($this->layout instanceof View)) {
				throw new \Exception("Layout must be a widget");
			}
			$this->layout->view = $view;
			$out = $this->layout->render();
		} else {
			$out = $view->render();
		}
		$this->sendHeaders();
		print($out);
	}

}