<?php
class AccuaForm_Validation_Captcha2b extends Validation {
	protected $message = "Error: The reCATPCHA response provided was incorrect.  Please re-try.";
	protected $privateKey;
	
	public function isValid($value) {
	  if (!isset($_POST['g-recaptcha-response'])) {
	    return false;
	  }

	  $response = stripslashes_deep($_POST['g-recaptcha-response']);

	  if ($response === '') {
	    return false;
	  }

	  $url = 'https://www.google.com/recaptcha/api/siteverify'
	      .'?secret='.urlencode($this->privateKey)
	      .'&response='.urlencode($response)
	      .'&remoteip='.urlencode($_SERVER["REMOTE_ADDR"]);

	  $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    $res = curl_exec($ch);
    curl_close($ch);

    if ($res !== false) {
      @ $res = json_decode($res, true);
  		if (!empty($res['success'])) {
  			return true;
  		}
    }

	  return false;
	}
}
