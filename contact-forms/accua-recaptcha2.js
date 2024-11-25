var accuaform_recaptcha2_ajax_loaded = true;
var accuaform_recaptcha2_initialized = false;
var accuaform_recaptcha2_delayed = [];
var accuaform_recaptcha2_loaded = {};

function accua_forms_show_recaptcha2(id, opts) {
  if (accuaform_recaptcha2_initialized) {
    var grid = grecaptcha.render(id, opts);
    accuaform_recaptcha2_loaded[id] = grid; 
  } else {
    accuaform_recaptcha2_delayed.push({id: id, opts: opts});
  }
}

function accua_forms_reload_recaptcha2(id) {
  if ((typeof accuaform_recaptcha2_loaded[id]) != 'undefined') {
    grecaptcha.reset(accuaform_recaptcha2_loaded[id]);
  }
}

function accua_forms_onload_recaptcha2(){
  accuaform_recaptcha2_initialized = true;
  for (var i=0; i<accuaform_recaptcha2_delayed.length; i++){
    accua_forms_show_recaptcha2(accuaform_recaptcha2_delayed[i]['id'], accuaform_recaptcha2_delayed[i]['opts']);
  }
  accuaform_recaptcha2_delayed = [];
}
