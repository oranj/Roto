<?php

namespace \Roto\Request;

abstract class Request {

	protected $url;
	protected $query            = null;
	protected $formData         = null;
	protected $formDataEncoding = null;
	protected $timeout          = null;
	protected $method           = 'GET';
	protected $httpHeaders      = array();

	public function setUrl($url) {
		$this->url = $url;
	}

	public function setQuery($query) {
		$this->query = $query;
	}

	public function setFormData($formData, $encoding = null) {
		$this->formData = $formData;
		$this->formDataEncoding = $encoding;
	}

	public function setTimeout($seconds) {
		$this->timeout = $seconds;
	}

	public abstract function httpHeader($key, $value = null) {
		$this->httpHeaders[$key] = $value;
	}

	public abstract function setMethod($method) {
		$this->method = $method;
	}

	public abstract function exec();

	public static function get($url, $queryData) {
		$request = new self();

		$request->setMethod('GET');
		$request->setUrl($url);
		$request->setQuery($queryData);

		return $request->exec();
	}

	public static function post($url, $formData, $formEncoding = null) {
		$request = new self();

		$request->setMethod('POST');
		$request->setUrl($url);
		$request->setFormData($formData, $formEncoding);

		return $request->exec();
	}

	protected function buildUrl() {
		if (! $this->url) {
			throw new \Exception("No url provided");
		}
		$out = $this->url;
		if ($this->query) {
			$out .= '?'.http_build_query($this->query);
		}
		return $out;
	}

}