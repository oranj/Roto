<?php

namespace Roto;

$root = dirname(dirname(__FILE__));

Service::register('CFG', function() use ($root) {
	return new Config(
		$root.'/config/config.ini'
	);
});

Service::register('DB', function() {
	$cfg = Service::CFG()->database;
	return new Database(
		$cfg['username'],
		$cfg['password'],
		$cfg['hostname'],
		$cfg['database']
	);
});

Service::register('View', function() {
	return View::getInstance();
});

Service::register('Router', function() use ($root) {
	$router = new Router(
		Service::View(),
		$root . '/www/',
		$root . '/layout/templates/',
		'_folder.php'
	);

	return $router;
});