<?php

namespace Roto\View;

class LambdaView extends View {

	protected $lambda;
	public function __construct($lambda) {
		$this->lambda = $lambda;
	}

	public function render() {
		ob_start();
		call_user_func($this->lambda, $this);
		$out = ob_get_clean();
		return $out;
	}

}