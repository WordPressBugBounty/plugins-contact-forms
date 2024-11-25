<?php
class AccuaForm_Element_Captcha extends Element {
	protected $privateKey = "6LcazwoAAAAAAD-auqUl-4txAK3Ky5jc5N3OXN0_";
	protected $publicKey = "6LcazwoAAAAAADamFkwqj5KN1Gla7l4fpMMbdZfi";

	public function __construct($label = "", $unused = '', array $properties = null) {
	  if ($properties === null && is_array($unused)) {
	    $properties = $unused;
	  }
		parent::__construct($label, "recaptcha_response_field", $properties);
		$validator = new AccuaForm_Validation_Captcha(__("The reCATPCHA response provided was incorrect.  Please re-try.", 'contact-forms'));
		$validator->configure(array('privateKey'=>$this->privateKey));
		$this->setValidation($validator);
	}

	public function render() {
    $js_lang = _accua_forms_json_encode($this->form->getLanguage());
	  $js_field_id = _accua_forms_json_encode($this->attributes["id"]);
    $js_path = _accua_forms_json_encode(ACCUA_FORMS_DIR_URL . 'accua-recaptcha.js');
	  $js_publicKey = _accua_forms_json_encode($this->publicKey);

    $field_id = htmlspecialchars($this->attributes["id"], ENT_QUOTES);
    $publicKey = htmlspecialchars($this->publicKey, ENT_QUOTES);
    $button_text = htmlspecialchars(__('Show Captcha', 'contact-forms'), ENT_QUOTES);
    
	  echo <<<EOT
<script type="text/javascript">
<!--
if (typeof(accuaform_recaptcha_ajax_loaded) == "undefined" || !accuaform_recaptcha_ajax_loaded) {
  var accuaform_recaptcha_ajax_loaded = true;
  jQuery.getScript( "https://www.recaptcha.net/recaptcha/api/js/recaptcha_ajax.js" , function(){
    jQuery.getScript( $js_path , function(){
      jQuery(function(){
        accua_forms_show_recaptcha($js_publicKey, $js_field_id, {lang: $js_lang});
      });
    });
  });
}
// -->
</script>
<input type='button' value='$button_text' class='accua_forms_show_recaptcha_button' onclick='accua_forms_show_recaptcha($js_publicKey, $js_field_id, {lang: $js_lang})' />
<div id="{$field_id}"></div>
<noscript>
  		<iframe src="https://www.recaptcha.net/recaptcha/api/noscript?k={$publicKey}" height="300" width="500" frameborder="0"></iframe><br/>
  		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
</noscript>
EOT;
	}
}
