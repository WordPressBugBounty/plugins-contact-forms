<?php
class AccuaForm_Element_FieldsetEnd extends Element {
  public function __construct($label = "", $name = "", $properties = array()) {
    parent::__construct($label, $name, $properties);
  }

  public function render() {
    echo '</fieldset>';
  }

}