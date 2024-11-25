<?php
class AccuaForm_View_SideBySide extends AccuaForm_View_Standard {
	protected $labelWidth;
	protected $labelRightAlign;
	protected $labelPaddingRight = 5;
	protected $labelPaddingTop;

	public function __construct($labelWidth, array $properties = null) {
		if(!empty($properties))
			$properties["labelWidth"] = $labelWidth;
		else
			$properties = array("labelWidth" => $labelWidth);

		parent::__construct($properties);
	}

	public function renderCSS() {
		$id = $this->form->getId();
		$width = $this->form->getWidth();
		$widthSuffix = $this->form->getWidthSuffix();
		$elementWidth = $width - $this->labelWidth - $this->labelPaddingRight;
		$this->form->renderCSS();
		/*
		View::renderCSS();
		echo <<<CSS
#$id { width: $width{$widthSuffix}; }
#$id .pfbc-element { margin-bottom: 0.6em; }
#$id .pfbc-elementbottom { clear: both; }
#$id .pfbc-label { width: {$this->labelWidth}$widthSuffix; float: left; padding-right: {$this->labelPaddingRight}$widthSuffix; }
#$id .pfbc-buttons { text-align: right; }
#$id .pfbc-textbox, #$id .pfbc-textarea, #$id .pfbc-select { width: 98% !important; display:block; border: 1px solid #ccc; padding:2px; }
#$id .pfbc-fieldwrap { width: $elementWidth{$widthSuffix}; display: inline-block; }
CSS;
	
		if(!empty($this->labelRightAlign))
			echo '#', $id, ' .pfbc-label { text-align: right; }';
		
		if(empty($this->labelPaddingTop) && !in_array("style", $this->form->getPrevent()))
			$this->labelPaddingTop = ".75em";

		if(!empty($this->labelPaddingTop)) {
			if(is_numeric($this->labelPaddingTop))
				$this->labelPaddingTop .= "px";
			echo '#', $id, ' .pfbc-label { padding-top: ', $this->labelPaddingTop, '; }';
		}
		
		$elements = $this->form->getElements();
		$elementSize = sizeof($elements);
		$elementCount = 0;
		foreach ($elements as $element) {
		  $elementWidth = $element->getWidth();
		  if(!$element instanceof Element_Hidden && !$element instanceof Element_HTMLExternal && !$element instanceof Element_HTMLExternal) {
		    if(!empty($elementWidth)) {
		      echo '#', $id, ' .pfbc-element-', $elementCount, ' { width: ', $elementWidth, $widthSuffix, '; }';
		      if($widthSuffix == "px") {
		        $elementWidth = $elementWidth - $this->labelWidth - $this->labelPaddingRight;
		        echo '#', $id, ' .pfbc-element-', $elementCount, ' .pfbc-textbox, #', $id, ' .pfbc-element-', $elementCount, ' .pfbc-textarea, #', $id, ' .pfbc-element-', $elementCount, ' .pfbc-select, #', $id, ' .pfbc-element-', $elementCount, ' .pfbc-right { width: ', $elementWidth, $widthSuffix, '; }';
		      }
		    }
		    $elementCount++;
		  }
		} */
	}
	
  /*This method encapsulates the various pieces that are included in an element's label.*/
  protected function renderLabel($element) {
        $label = $element->getLabel();
        $id = $element->getID();
        $description = $element->getDescription();
        if(!empty($label) || !empty($description)) {
            echo '<div class="pfbc-label">';
            if(!empty($label)) {
                echo '<label for="', $id, '">', $label;
                if($element->isRequired())
                    echo ' <strong>*</strong>';
                echo '</label>'; 
            }
            if(!empty($description))
                echo '<em>', $description, '</em>';
            echo '</div>';
        }
    }
	
}
