<?php
class AccuaForm_Validation_Required extends Validation_Required {
  public function __construct($message = "") {
    if(empty($message)) {
      $this->message = __("Error: '%element%' is a required field.", 'contact-forms');
    } else {
      $this->message = $message;
    }
  }
}
