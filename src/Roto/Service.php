<?php

namespace Roto;

class Service {
	
	private static $lambdaRegistry = array();
	private static  $cache = array();

	private static function isCached($key) {
		return isset(self::$cache[$key]);
	}

	public static function isRegistered($key) {
		return isset(self::$lambdaRegistry[$key]);
	}

	public static function __callStatic($name, $args) {
		return self::get($name);
	}

	public static function get($key) {
		if (! self::isRegistered($key)) {
			throw new Exception("Service `$key' not registered");
		}
		if (! self::isCached($key)) {
			$lambda = self::$lambdaRegistry[$key];
			self::$cache[$key] = $lambda();
		}
		return self::$cache[$key];

	}

	public static function register($key, $lambda, $override = false) {
		if (self::isRegistered($key) && ! $override) {
			throw new Exception("Service `$key' is already registered");
		}
		self::$lambdaRegistry[$key] = $lambda;
	} 
}