<?php


function see() {
	$args = func_get_args();
	$backtrace = debug_backtrace();
	ob_start();

	foreach ($backtrace as $i => $trace) {
		if (! in_array($trace['function'], array('see', 'drop', 'call_user_func_array'))) {
			break;
		}
	}

	echo "vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv\n\n";
	echo $backtrace[$i-1]['file'].':'.$backtrace[$i-1]['line']."\n\n";

	foreach ($args as $i => $arg) {
		printf('--------------------------- ARG %02d ---------------------------'.PHP_EOL.PHP_EOL, $i);
		print_r($arg);
		echo PHP_EOL.PHP_EOL;
	}
	echo '^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^'.PHP_EOL;
	$html = ob_get_clean();

	echo "<pre>".htmlentities($html)."</pre><!---\n\n\n$html\n\n\n-->\n";
}

function drop() {
	call_user_func_array('see', func_get_args());
	exit;
}

function json_success($data = array()) {
	echo json_encode(array(
		'status' => 'success',
		'data' => $data
	));
	exit;
}

function json_failure($errorId, $message) {
	echo json_encode(array(
		'status' => 'error',
		'error' => $errorId,
		'message' => $message
	));
	exit;
}