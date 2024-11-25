<?php
class AccuaForm_Validation_Date extends Validation {
  protected $message = "Error: %element% must contain a valid date.";
  protected $minDate = null;
  protected $maxDate = null;

  public function isValid($value) {
    if ($value === '') {
      return true;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
      try {
        $date = new DateTime($value);
        if ($date) {
          if ($this->minDate && ($value < $this->minDate)) {
            return false;
          }
          if ($this->maxDate && ($value > $this->maxDate)) {
            return false;
          }
          return true;
        }
      } catch (Exception $e) {
      }
    }
    return false;
  }
}
