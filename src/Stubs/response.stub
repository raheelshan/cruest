<?php

namespace App\Core;

 class Response {

	public $exception;
	public $valid;
	public $payload;
	public $errors;
	public $message;
	public $authorize;

	public function __construct($valid = false, $payload = null, $exception = null,$errors = null,$message=null,$authorize=true)
	{
		$this->exception = $exception;
		$this->valid = $valid;
		$this->payload = $payload;
		$this->errors = $errors;
		$this->message = $message;
		$this->authorize = $authorize;
	}
}