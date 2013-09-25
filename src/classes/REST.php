<?php

class REST {
	public function __construct() {}

	public function tokenLogin() {

		if (! isset($_GET['token'])) {
			throw new APIException('missingToken', 'No token was provided');
		}

		$db = Service::get('db');
		$user = $db->row("SELECT u.*, t.* FROM token t JOIN user u ON t.user_id = u.user_id AND t.token_id = '%s' AND t.token_ip='%s'", $_GET['token'], USER_IP);

		if (! $user) {
			throw new APIException("invalidToken", "This token is invalid");
		} 

		$expires = strtotime($user['token_expires']);
		$created = strtotime($user['token_created']);

		if (time() > $expires || time() < $created) {
			throw new APIException("expiredToken", "This token is expired");
		}

		return $user;

	}


}