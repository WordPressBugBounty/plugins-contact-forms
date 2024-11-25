<?php
abstract class AccuaForm_OptionElement extends OptionElement {
	protected $options;
	
	public function __sleep() {
	  return array("attributes", "label", "validation", "options");
	}

	public function getOptions() {
    return $this->options;
  }
}
