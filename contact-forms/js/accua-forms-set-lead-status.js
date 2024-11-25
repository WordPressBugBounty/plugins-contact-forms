{
  let old_values = {};
  function accua_forms_set_lead_status(t, subid, nonce, initial_lead_status){
    if (!(subid in old_values)) {
      old_values[subid] = initial_lead_status;
    }
    t = jQuery(t);
    var progress = t.siblings(".accua-forms-select-lead-status-progress");
    progress.addClass("accua-forms-select-lead-status-throbber");
    progress.removeClass("accua-forms-select-lead-status-cancel accua-forms-select-lead-status-check");
    t.prop('disabled', true);
    var new_value = t.val();
    jQuery.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: 'accua-forms-set-lead-status',
        subid: subid,
        lead_status: new_value,
        _nonce_set_lead_status: nonce
      },
      success: function() {
        t.prop('disabled', false);
        old_values[subid] = new_value;
        progress.removeClass("accua-forms-select-lead-status-throbber");
        progress.addClass("accua-forms-select-lead-status-check");
      },
      error: function() {
        t.prop('disabled', false);
        t.val(old_values[subid]);
        progress.removeClass("accua-forms-select-lead-status-throbber");
        progress.addClass("accua-forms-select-lead-status-cancel");
      }
    });
  }
}
