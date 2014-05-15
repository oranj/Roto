<?php

namespace \Roto\Request;

class CurlRequest extends RequestInterface {

	const USER_AGENT_STRING = "Oranj cURL";

	protected $opts = array();
	protected $cookieVals = array();

	public function setOpt($curlOpt, $value) {
		$this->opts[$curlOpt] = $value;
	}

	public function cookie($key, $value) {
		$this->cookieVals[$key] = $value;
	}

	protected function httpHeaderIfNotSet($key, $value) {
		if (! isset($this->httpHeaders[$key])) {
			$this->httpHeaders[$key] = $value;
		}
	}

	protected function ifNotSet($curlOpt, $value) {
		if (! isset($this->opts[$curlOpt])) {
			$this->opts[$curlOpt] = $value;
		}
	}

	public function exec() {
		$url = $this->buildUrl();

		$handle = curl_init($url);

		$this->setOpt(CURLOPT_USERAGENT, self::USER_AGENT_STRING);
		$this->setOpt(CURLOPT_HEADER, true);
		$this->setOpt(CURLOPT_BINARYTRANSFER, true);
		$this->setOpt(CURLOPT_RETURNTRANSFER, true);

		if (! is_null($this->formData)) {
			if (is_null($this->formDataEncoding)) {
				$payload = http_build_query($this->formData);
			} else if ($this->formDataEncoding == 'json') {
				$payload = json_encode($this->formData);

				$this->httpHeaderIfNotSet('Accept', 'application/json');
				$this->httpHeaderIfNotSet('Content-type', 'application/json; charset=utf-8');
			}
			$this->ifNotSet(CURLOPT_CUSTOMREQUEST, "POST");
			$this->setOpt(CURLOPT_POSTFIELDS, $payload);

		}

		$cookies = array();
		foreach ($this->cookieVals as $key => $value) {
			$string = sprintf("%s=%s", $key, $value);
			$cookies[] = $string;
		}
		if ($cookies) {
			$this->setOpt(CURLOPT_COOKIE, join(';',$cookies));
		}
		if ($this->timeout) {
			$this->setOpt(CURLOPT_TIMEOUT, $this->timeout);
		}
		$this->ifNotSet(CURLOPT_HTTPHEADER, array());
		foreach ($this->httpHeaders as $key => $value) {
			if (is_null($value)) {
				$this->opts[CURLOPT_HTTPHEADER][] = $key;
			} else {
				$this->opts[CURLOPT_HTTPHEADER][] = sprintf("%s: %s", $key, $value);
			}
		}

		if ($this->method != 'GET') {
			$this->setOpt(CURLOPT_CUSTOMREQUEST, $this->method);
		}

		foreach ($this->opts as $opt => $value) {
			curl_setopt($handle, $opt, $value);
		}

		$response = curl_exec($handle);

		if ($response === false) {
			throw new \Exception(sprintf("cURL Error %d: %s", curl_errno($handle), curl_error($handle)));
		}

		$headerLength = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
		curl_close($handle);

		$header = trim(substr($response, 0, $headerLength));
		$headers = explode("\n", $header);

		$rawBody = substr($response, $headerLength);

		$cookies = array();
		$status = null;
		$type = '';
		foreach ($headers as $headerLine) {
			if (preg_match('/^Set-Cookie: (.+)$/', $headerLine, $matches)) {
				$cookieVals = explode(';', $matches[1]);
				foreach ($cookieVals as $line) {
					list($key, $value) = explode('=', $line);
					$cookies[$key] = $value;
				}
			} else if (preg_match('/Content\-Type: (.+);?/', $headerLine, $matches)) {
				$type = $matches[1];
			} else if (preg_match('/HTTP\/1\.1 ([0-9]{3})/', $headerLine, $matches)) {
				$status = $matches[1];
			}
		}

		if ($type == 'application/json') {
			$body = json_decode($rawBody, true);
		} else {
			$body = $rawBody;
		}

		return array(
			'status'  => $status,
			'type'    => $type,
			'cookies' => $cookies,
			'body'    => $body,
			'header'  => $header,
			'raw'     => $rawBody
		);

		return $body;
	}

}