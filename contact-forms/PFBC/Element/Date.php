<?php
class Element_Date extends Element_Textbox {
	protected $jQueryOptions;
	
	public function jQueryDocumentReady() {
		parent::jQueryDocumentReady();
		echo 'jQuery("#', $this->attributes["id"], '").datepicker(', $this->jQueryOptions(), ');';
	}

	public function render() {
		$this->validation[] = new Validation_Date;
		parent::render();
	}
}
