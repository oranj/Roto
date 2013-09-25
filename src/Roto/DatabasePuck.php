<?php

namespace Roto;

class DatabasePuck {
	private $database;
	private $table;
	private $data;

	public function __construct($table, $record, $database) {
		$this->table = $table;
		$this->record = $record;
		$this->database = $database;
	}

	public function __call($name, $arguments) {
		if (substr($name, -1) == 's') {
			$multiple = true;
			$table = substr($name, 0, -1);
		} else {
			$multiple = false;
			$table = $name;
		}
		$key = $table.'_id';

		if (! array_key_exists($key, $this->record)) {
			throw new Exception(sprintf("`%s` doesn't know any `%s`s", $this->table, $table));
		}
		$value = $this->record[$key];
		if ($multiple) {
			$pucks = $this->database->keypucks("SELECT * FROM `%s` WHERE `%s` = '%s'", $table, $key, $value);
		} else {
			$pucks = $this->database->puck("SELECT * FROM `%s` WHERE `%s` = '%s'", $table, $key, $value);
		}

		return $pucks;
	}

	public function __get($name) {
		if (array_key_exists($this->table.'_'.$name, $this->record)) {
			return $this->record[$this->table.'_'.$name];
		}
		if (array_key_exists($name, $this->record)) {
			return $this->record[$name];	
		}
		throw new Exception(sprintf("Unknown column: `%s`", $name));
	}

}
