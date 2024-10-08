<?php
class AccuaForm_Validation_Password extends Validation {
  protected $otherPasswordFieldName = '';

	public function __construct($message = "") {
		if(empty($message)) {
			$this->message = __("Attention: Your passwords do not match.", 'contact-forms');
		} else {
			$this->message = $message;
		}
	}

	public function isValid($value) {
	  $valid = false;
	  if ($this->otherPasswordFieldName !== '') {
	    if (isset($_POST[$this->otherPasswordFieldName])) {
	      $otherValue = stripslashes((string) $_POST[$this->otherPasswordFieldName]);
	      $valid = $value === $otherValue;
	    }
	  }
		return $valid;
	}
}
