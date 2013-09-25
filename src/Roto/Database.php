<?php

class Database {

	private $handle = null;
	private $username;
	private $hostname;
	private $database;
	private $password;

	public $lastQry = null;


	public function __construct($username, $password, $hostname, $database) {
		$this->username = $username;
		$this->password = $password;
		$this->hostname = $hostname;
		$this->database = $database;
	}

	private function connection() {

		if (is_null($this->handle)) {
			$this->handle = mysql_connect($this->hostname, $this->username, $this->password);

			if (! $this->handle) {
				throw new Exception("Could not connect: ".mysql_error()." (".mysql_errno().")");
			}

			mysql_select_db($this->database, $this->handle);
		}

		return $this->handle;
	}

	public function value() {
		$results = $this->getResultObj(func_get_args());
		$row = mysql_fetch_row($results);
		return $row[0];
	}

	public function row() {
		$results = $this->getResultObj(func_get_args());
		return mysql_fetch_assoc($results);

	} 

	public function rows() {
		$out = array();
		$results = $this->getResultObj(func_get_args());
		while ($row = mysql_fetch_assoc($results)) {
			$out[] = $row;
		}
		return $out;
	}

	public function puck() {
		$results = $this->getResultObj(func_get_args());
		$row = mysql_fetch_assoc($results);
		$key = key($row);
		$table = preg_replace('/_id$/', '', $key);
		return new DatabasePuck($table, $row, $this);
	}

	public function pucks() {
		$results = $this->getResultObj(func_get_args());
		$out = array();
		$table = null;
		while ($row = mysql_fetch_assoc($results)) {
			if (is_null($table)) {
				$key = key($row);
				$table = preg_replace('/_id$/', '', $key);
			}
			$out []= new DatabasePuck($table, $row, $this);
		}
		return $out;
	}

	public function keypucks($table, $cond, $id = null) {
		$results = $this->getResultObj(func_get_args());
		$out = array();
		$table = null;
		while ($row = mysql_fetch_assoc($results)) {
			if (is_null($table)) {
				$key = key($row);
				$table = preg_replace('/_id$/', '', $key);
			}
			$out [reset($row)]= new DatabasePuck($table, $row, $this);
		}
		return $out;
	}

	public function keyrow() {
		$out = array();
		$results = $this->getResultObj(func_get_args());
		while ($row = mysql_fetch_assoc($results)) {
			$out[reset($row)] = $row;
		}
		return $out;
	}

	public function keyrows() {
		$out = array();
		$results = $this->getResultObj(func_get_args());
		while ($row = mysql_fetch_assoc($results)) {
			$out[reset($row)] []= $row;
		}
		return $out;
	}

	public function insert($table, $data) {
		$columns = array();
		$values = array();
		foreach ($data as $column => $value) {
			$columns []= addslashes($column);
			$values []= is_string($value) ? '"'.addslashes($value).'"' : $value;
		}
		$sql = "INSERT INTO `$table` (`".join('`,`',$columns).'`) VALUES ('.join(',', $values).')';

	#	drop($sql);
		$this->query($sql);

		return mysql_insert_id($this->connection());		
	}

	public function replace($table, $data) {
		$columns = array();
		$values = array();
		foreach ($data as $column => $value) {
			$columns []= addslashes($column);
			$values []= addslashes($value);
		}
		$sql = "REPLACE INTO `$table` (`".join('`,`',$columns)."`) VALUES (`".join('`,`', $values)."`)";
		$this->query($sql);

		return mysql_insert_id($this->connection());
	}

	public function update($table, $data, $match = array()) {
		$columns = array();
		$values = array();
		foreach ($data as $column => $value) {
			$set = '`'.addslashes($column).'`= "'.addslashes($value).'"';
			$sets []= $set;
		}
		$sql = "UPDATE `$table` SET ".join(', ',$sets);
		if (count($match) > 0) {	
			$sql .= ' WHERE '.$this->buildMatchString($match);
		}

		return $this->query($sql);
	}

	public function delete($table, $match) {
		$sql = "DELETE FROM `$table` WHERE ".$this->buildMatchString($match);
		return $this->query($sql);
	}

	public function time($timestamp = null) {
		if (is_null($timestamp)) {
			$timestamp = time();
		}
		return date('Y-m-d H:i:s', $timestamp);
	}

	private function buildQuery($arguments) {
		return call_user_func_array('sprintf', $arguments);
	}

	private function buildMatchString($match) {
		$conds = array();
		foreach($match as $column => $value) {
			$conds []= "`$column` = \"".addslashes($value)."\"";
		}
		return join(' AND ', $conds);
	}

	private function errorCheck() {
		if (mysql_error()) {
			throw new Exception("MYSQL Error: ".mysql_error()." (".mysql_errno().")");
		}
	}

	private function query($sql) {
		$resource = mysql_query($sql, $this->connection());
		$this->lastQry = $sql;
		$this->errorCheck();

		return $resource;
	}

	private function getResultObj($arguments) {
		return $this->query($this->buildQuery($arguments));
	}
}
