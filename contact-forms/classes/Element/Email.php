<?php
class AccuaForm_Element_Email extends Element_Textbox {
  public function __construct($label, $name, array $properties = null) {
    parent::__construct($label,$name,$properties);
	  $this->attributes['type'] = 'email';
		$this->setValidation(new Validation_Email(
      str_replace('%element%', $label, __("Attention: '%element%' must contain an email address.", 'contact-forms'))
    ));
  }
}
