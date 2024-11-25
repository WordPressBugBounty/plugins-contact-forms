<?php
class AccuaForm_View_Standard extends View {
	public function render() {
		echo '<form', $this->form->getAttributes(), '>';
		$this->form->getError()->render();

		$elements = $this->form->getElements();
		$elementCount = 0;
		$inButtonGroup = false;
		
		foreach($elements as $element) {
			if($element instanceof Element_Hidden || $element instanceof Element_HTMLExternal) {
        $element->render();
		  } elseif($element instanceof Element_Button) {
		    if (!$inButtonGroup) {
		      echo '<div class="pfbc-element pfbc-buttons">';
		      $inButtonGroup = true;
		    }
        $element->render();
      } else {
        if ($inButtonGroup) {
          echo '</div>';
          $inButtonGroup = false;
        }
        if ($element instanceof AccuaForm_Element_FieldsetBegin || $element instanceof AccuaForm_Element_FieldsetEnd) {
          $element->render();
        } else {
	  			echo '<div class="pfbc-element-', $elementCount, ' pfbc-element">', $element->getPreHTML();
		  		$this->renderLabel($element);
			  	if ($element instanceof Element_HTML) {
				    $element->render();
				  } else {
				    echo '<div class="pfbc-fieldwrap">';
  			    $element->render();
	  		    echo '</div>';
		  		}
				  echo $element->getPostHTML(), '<div class="pfbc-elementbottom"></div></div>';
				  ++$elementCount;
        }
			}
		}
		
		if ($inButtonGroup) {
		  echo '</div>';
		  $inButtonGroup = false;
		}

		echo '</form>';
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
    
	public function renderCSS() {
		$id = $this->form->getId();
		$width = $this->form->getWidth();
		$widthSuffix = $this->form->getWidthSuffix();
		$this->form->renderCSS();
/*
		parent::renderCSS();
		echo <<<CSS
#$id { width: $width{$widthSuffix}; }
#$id .pfbc-element { margin-bottom: .6em }
#$id .pfbc-label { margin-bottom: .2em; }
#$id .pfbc-label label { display: block; }
#$id .pfbc-textbox, #$id .pfbc-textarea, #$id .pfbc-select { width: 98% !important; display:block; border: 1px solid #ccc; padding:2px; }
#$id .pfbc-fieldwrap { width: $width{$widthSuffix}; display: inline-block; }
#$id .pfbc-buttons { text-align: right; }
CSS;
		
		$elements = $this->form->getElements();
		$elementCount = 0;
		foreach ($elements as $element) {
		  $elementWidth = $element->getWidth();
		  if(!$element instanceof Element_Hidden && !$element instanceof Element_HTMLExternal && !$element instanceof Element_HTMLExternal) {
		    if(!empty($elementWidth)) {
		      echo '#', $id, ' .pfbc-element-', $elementCount, ' { width: ', $elementWidth, $widthSuffix, '; }';
		      echo '#', $id, ' .pfbc-element-', $elementCount, ' .pfbc-textbox, #', $id, ' .pfbc-element-', $elementCount, ' .pfbc-textarea, #', $id, ' .pfbc-element-', $elementCount, ' .pfbc-select { width: ', $elementWidth, $widthSuffix, '; }';
		    }
		    $elementCount++;
		  }
		} */
		
	}
}
