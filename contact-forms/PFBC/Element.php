<?php
abstract class Element extends Base {
	private $errors = array();

	protected $attributes;
	protected $form;
	protected $label;
	protected $description;
	protected $validation = array();
	protected $preHTML;
	protected $postHTML;
	protected $width;

	public function __construct($label, $name, array $properties = null) {
		$configuration = array(
			"label" => $label,
			"name" => $name
		);

		/*Merge any properties provided with an associative array containing the label
		and name properties.*/
		if(is_array($properties))
			$configuration = array_merge($configuration, $properties);
		
		$this->configure($configuration);
	}

	/*When an element is serialized and stored in the session, this method prevents any non-essential
	information from being included.*/
	public function __sleep() {
		return array("attributes", "label", "validation");
	}

	/*If an element requires external stylesheets, this method is used to return an
	array of entries that will be applied before the form is rendered.*/
	public function getCSSFiles() {}

	public function getDescription() {
		return $this->description;
	}

	public function getErrors() {
		return $this->errors;
	}	

	public function getID() {
		if(!empty($this->attributes["id"]))
			return $this->attributes["id"];
		else
			return "";
	}

	/*If an element requires external javascript file, this method is used to return an
	array of entries that will be applied after the form is rendered.*/
	public function getJSFiles() {}

	public function getLabel() {
		return $this->label;
	}

	public function getName() {
		if(!empty($this->attributes["name"]))
			return $this->attributes["name"];
		else
			return "";
	}

	public function getPostHTML() {
		return $this->postHTML;
	}

	public function getPreHTML() {
		return $this->preHTML;
	}

	public function getWidth() {
		return $this->width;
	}

	/*This method provides a shortcut for checking if an element is required.*/
	public function isRequired() {
		if(!empty($this->validation)) {
			foreach($this->validation as $validation) {
				if($validation instanceof Validation_Required)
					return true;
			}
		}
		return false;
	}

	/*The isValid method ensures that the provided value satisfies each of the 
	element's validation rules.*/
	public function isValid($value) {
		$valid = true;
		if(!empty($this->validation)) {
			if(!empty($this->label)) {
				$element = $this->label;
				if(substr($element, -1) == ":")
					$element = substr($element, 0, -1);
			}   
			else
				$element = $this->attributes["name"];

			foreach($this->validation as $validation) {
				if(!$validation->isValid($value)) {
					/*In the error message, %element% will be replaced by the element's label (or 
					name if label is not provided).*/
					$this->errors[] = str_replace("%element%", $element, $validation->getMessage());
					$valid = false;
				}	
			}
		}
		return $valid;
	}

	/*If an element requires jQuery, this method is used to include a section of javascript
	that will be applied within the jQuery(document).ready(function() {}); section after the 
	form has been rendered.*/
	public function jQueryDocumentReady() {}

	/*Elements that have the jQueryOptions property included (Date, Sort, Checksort, and Color)
	can make use of this method to render out the element's appropriate jQuery options.*/
	public function jQueryOptions() {
		if(!empty($this->jQueryOptions)) {
            $options = "";
            foreach($this->jQueryOptions as $option => $value) {
                if(!empty($options))
                    $options .= ", ";
                $options .= $option . ': ';
				/*When javascript needs to be applied as a jQuery option's value, no quotes are needed.*/
                if(is_string($value) && substr($value, 0, 3) == "js:")
                    $options .= substr($value, 3);
                else
                    $options .= var_export($value, true);
            }
            echo "{ ", $options, " }";
        }
	}

	/*Many of the included elements make use of the <input> tag for display.  These include the Hidden, Textbox, 
	Password, Date, Color, Button, Email, and File element classes.  The project's other element classes will
	override this method with their own implementation.*/
	public function render() {
		echo '<input', $this->getAttributes(), '/>';
	}

	/*If an element requires inline stylesheet definitions, this method is used send them to the browser before
	the form is rendered.*/
	public function renderCSS() {}

	/*If an element requires javascript to be loaded, this method is used send them to the browser after
	the form is rendered.*/
	public function renderJS() {}

	public function setClass($class) {
		if(!empty($this->attributes["class"]))
			$this->attributes["class"] .= " " . $class;
		else
			$this->attributes["class"] = $class;
	}

	public function setForm(Form $form) {
		$this->form = $form;
	}

	public function setID($id) {
		$this->attributes["id"] = $id;
	}

	public function setValue($value) {
		$this->attributes["value"] = $value;
	}

	public function setWidth($width) {
		if(substr($width, -2) == "px")
			$width = substr($width, 0, -2);
		elseif(substr($width, -1) == "%")
			$width = substr($width, 0, -1);
		$this->width = $width;
	}

	/*This method provides a shortcut for applying the Required validation class to an element.*/
	public function setRequired($required) {
		if(!empty($required))
			$this->validation[] = new Validation_Required;
	}

	/*This method applies one or more validation rules to an element.  If can accept a single concrete 
	validation class or an array of entries.*/
	public function setValidation($validation) {
		/*If a single validation class is provided, an array is created in order to reuse the same logic.*/
		if(!is_array($validation))
			$validation = array($validation);
		foreach($validation as $object) {
			/*Ensures $object contains a existing concrete validation class.*/
			if($object instanceof Validation)
				$this->validation[] = $object;
		}	
	}
}
