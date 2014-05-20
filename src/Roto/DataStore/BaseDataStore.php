<?php

namespace Roto\DataStore;

abstract class BaseDataStore {

	protected $data;

	public function __construct(&$data = null) {
		if (is_null($data)) {
			$data = array();
		}
		$this->data =& $data;
	}

}