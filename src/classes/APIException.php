<?php

class APIException extends Exception {

	private $errorToken;
    public function __construct($errorToken, $message, $code = 0, Exception $previous = null) {

    	$this->errorToken = $errorToken;
	   
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    public function getErrorToken() {
    	return $this->errorToken;
    }
}