<?php
class AccuaForm_Validation_Captcha2 extends AccuaForm_Validation_Captcha2b {
	public function __construct($privateKey, $message = "") {
	  if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
	  	$error_type = E_USER_DEPRECATED;
	  } else {
	    $error_type = E_USER_NOTICE;
	  }
	  trigger_error("AccuaForm_Validation_Captcha2 is <strong>deprecated</strong> since contact-forms version 1.4.7! Use AccuaForm_Validation_Captcha2b instead.", $error_type);
		$this->privateKey = $privateKey;
		if(!empty($message))
			$this->message = $message;
	}
}
