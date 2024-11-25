<?php
class AccuaForm_Element_FieldsetBegin extends Element {
  public function __construct($legend, $name = "", $properties = array()) {
    $properties += array("legend" => $legend);
    parent::__construct("", $name, $properties);
  }
  
  public function render() {
    echo '<fieldset ', $this->getAttributes(array('legend')), ' ><legend>', $this->attributes["legend"], '</legend>';
  }
  
}