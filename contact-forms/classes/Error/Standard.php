<?php
class AccuaForm_Error_Standard extends PFBCError {
  protected $errorfound;
  protected $errorsfound;

	public function applyAjaxErrorResponse() {
		$id = $this->form->getId();
		//$errorfound = _accua_forms_json_encode($this->errorfound);
		//$errorsfound = _accua_forms_json_encode($this->errorsfound);
		echo <<<JS
var errorSize = response.errors.length;
var errorHTML = '\\x3Cdiv class="pfbc-error ui-state-error ui-corner-all"\\x3E\\x3Cul\\x3E';
for(e = 0; e < errorSize; ++e) {
  errorHTML += '\\x3Cli\\x3E' + response.errors[e] + '\\x3C/li\\x3E';
}
errorHTML += '\\x3C/ul\\x3E\\x3C/div\\x3E';
jQuery("#$id").append(errorHTML);
JS;

	}

  public function applyAjaxErrorResponseUsingShowErrorMessages() {
    echo <<<JS
var errorSize = response.errors.length;
var errorHTML = '';
for(e = 0; e < errorSize; ++e) {
  errorHTML += '\\x3Cli\\x3E' + response.errors[e] + '\\x3C/li\\x3E';
}
show_error_messages(errorHTML);
JS;

  }

  public function __construct(array $properties = null) {
    $this->errorfound = __('The following error was found:', 'contact-forms');
    $this->errorsfound = __('The following @errorsize errors were found:', 'contact-forms');
    $this->configure($properties);
  }

  public function setErrorsFound($text = '') {
    $this->errorsfound = $text;
  }

  public function setErrorFound($text = '') {
    $this->errorfound = $text;
  }

	private function parse($errors) {
		$list = array();
		if(!empty($errors)) {
			$keys = array_keys($errors);
			$keySize = sizeof($keys);
			for($k = 0; $k < $keySize; ++$k)
				$list = array_merge($list, $errors[$keys[$k]]);
		}
		return $list;
	}

    public function render() {
        $errors = $this->parse($this->form->getErrors());
        if(!empty($errors)) {
            $size = sizeof($errors);
            if($size == 1)
                $format = $this->errorfound;
            else
                $format = str_replace('@errorsize', $size, $this->errorsfound);

            echo '<div class="pfbc-error ui-state-error ui-corner-all">', $format, '<ul><li>', implode("</li><li>", $errors), "</li></ul></div>";
        }
    }
    
    public function getAjaxErrorResponse() {
      return $this->parse($this->form->getErrors());
    }

    public function renderAjaxErrorResponse() {
        $errors = $this->parse($this->form->getErrors());
        if(!empty($errors)) {
            header("Content-type: application/json");
            print _accua_forms_json_encode(array("errors" => $errors));
        }
    }
}
