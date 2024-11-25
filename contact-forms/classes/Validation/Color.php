<?php
class AccuaForm_Validation_Color extends Validation {
  protected $message = "";
  
  public function __construct($message = "") {
    if(empty($message)) {
      $this->message = __("Attention: '%element%' must be a color in hexadecimal format like '#0080FF'.", 'contact-forms');
    } else {
      $this->message = $message;
    }
  }
  
  public function isValid($value) {
    if(is_string($value) && (($value === '') || preg_match('/^#[0-9a-f]{6}$/im', $value))) {
      return true;
    } else {
      return false;
    }
  }
}
