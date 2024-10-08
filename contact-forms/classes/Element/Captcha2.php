<?php
class AccuaForm_Element_Captcha2 extends Element {
	protected $privateKey = "";
	protected $publicKey = "";

	public function __construct($label = "", $unused = null, array $properties = null) {
	  if ($properties === null && is_array($unused)) {
	    $properties = $unused;
	  }
		parent::__construct($label, "recaptcha_response_field", $properties);
		$validator = new AccuaForm_Validation_Captcha2b(__("The reCATPCHA response provided was incorrect.  Please re-try.", 'contact-forms'));
		$validator->configure(array('privateKey'=>$this->privateKey));
		$this->setValidation($validator);
	}

	public function render() {
    $js_lang = _accua_forms_json_encode($this->form->getLanguage());
    $js_field_id = _accua_forms_json_encode($this->attributes["id"]);//$this->form->attributes['id'] per id del form
    $js_publicKey = _accua_forms_json_encode($this->publicKey);
    $js_path = _accua_forms_json_encode(ACCUA_FORMS_DIR_URL . 'accua-recaptcha2.js');

    $field_id = htmlspecialchars($this->attributes["id"], ENT_QUOTES);
	  echo <<<EOT
<script type="text/javascript">
<!--
if (typeof(accuaform_recaptcha2_ajax_loaded) == "undefined" || !accuaform_recaptcha2_ajax_loaded) {
  var accuaform_recaptcha2_ajax_loaded = true;
  var accuaform_recaptcha2_ajax_delaying = true;
  var accuaform_recaptcha2_ajax_loading = true ;
  var accuaform_recaptcha2_ajax_controller = jQuery({});
  var accuaform_recaptcha2_ajax_initialized = {};
  jQuery(".accuaforms-field-required, .pfbc-fieldwrap > *").one('change', function() {
    if(accuaform_recaptcha2_ajax_delaying){
      accuaform_recaptcha2_ajax_delaying = false;
      jQuery.getScript( $js_path , function(){
        jQuery.getScript( "https://www.recaptcha.net/recaptcha/api.js?onload=accua_forms_onload_recaptcha2&render=explicit&hl=" + $js_lang );
        accuaform_recaptcha2_ajax_loading = false;
        accuaform_recaptcha2_ajax_controller.trigger('loaded');
      });
    }
  });
}
if (!accuaform_recaptcha2_ajax_initialized[ $js_field_id ]) {
  accuaform_recaptcha2_ajax_initialized[ $js_field_id ] = true;
  jQuery(function(){
    if (accuaform_recaptcha2_ajax_loading) {
      accuaform_recaptcha2_ajax_controller.one('loaded', function(){
        accua_forms_show_recaptcha2( $js_field_id , { sitekey: $js_publicKey });
      })
    } else {
      accua_forms_show_recaptcha2( $js_field_id , { sitekey: $js_publicKey });
    }
  });
}
// -->
</script>
<div id="{$field_id}" class="accua_forms_recaptcha2_container" style="min-height: 78px !important;"></div>
EOT;
	}
}
