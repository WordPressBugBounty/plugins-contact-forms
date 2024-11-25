function accua_forms_show_recaptcha(key, id, opts) {
  Recaptcha.create(key, id, opts);
  jQuery('.accua_forms_show_recaptcha_button').show();
  jQuery('#'+id).siblings('.accua_forms_show_recaptcha_button').hide();
}

var accuaform_recaptcha_ajax_loaded = true;
