<?php

class Widget {

	public static $root = 'widgets/';
	private $input;
	private $path;

	public static function __callStatic($name, $args = array()) {
		$input = array();
		if (count($args) > 0) {
			$input = $args[0];
		}
		return new Widget(str_replace('_', '/', $name).'.php', $input);
	}

	public function __construct($path, $input = array()) {
		if (! file_exists(self::$root.$path)) {
			throw new Exception("Widget does not exist at path $path");
		}
		$this->path = $path;
		$this->input = $input;
	}

	public function render() {
		foreach ($this->input as $key => $value) {
			$this->{$key} = $value;
		}
		include(self::$root.$this->path);
		foreach ($this->input as $key => $value) {
			unset($this->{$key});
		}
	}

}