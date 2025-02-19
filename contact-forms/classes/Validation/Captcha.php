<?php
class AccuaForm_Validation_Captcha extends Validation {
	protected $message = "Error: The reCATPCHA response provided was incorrect.  Please re-try.";
	protected $privateKey;

	public function isValid($value) {
		require_once(dirname(__FILE__) . "/../../PFBC/Resources/recaptchalib.php");
		$resp = recaptcha_check_answer ($this->privateKey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
		if($resp->is_valid)
			return true;
		else
			return false;
	}
}
