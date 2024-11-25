<?php
class AccuaForm_Element_Date extends Element_Textbox {
  protected $minDate = null;
  protected $maxDate = null;
  public function __construct($label, $name, array $properties = null) {
    parent::__construct($label,$name,$properties);
    $this->attributes['type'] = 'date';
    $this->attributes['pattern'] = '\d{4}-\d{2}-\d{2}';
    $validator = new AccuaForm_Validation_Date(
      str_replace('%element%', $label, __("Attention: '%element%' must contain a valid date.", 'contact-forms'))
    );
    $validator->configure(array('minDate' => $this->minDate, 'maxDate'=> $this->maxDate));
    $this->setValidation($validator);
  }
}
