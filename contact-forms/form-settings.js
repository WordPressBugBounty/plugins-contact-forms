jQuery(function($){
  $('#accua_form_style_border_color .accua_form_value').colorPicker();
  $('#accua_form_style_background_color .accua_form_value').colorPicker();
  $('#accua_form_style_color .accua_form_value').colorPicker();
  $('#accua_form_style_field_border_color .accua_form_value').colorPicker();
  $('#accua_form_style_field_background_color .accua_form_value').colorPicker();
  $('#accua_form_style_field_color .accua_form_value').colorPicker();
  $('#accua_form_style_submit_border_color .accua_form_value').colorPicker();
  $('#accua_form_style_submit_background_color .accua_form_value').colorPicker();
  $('#accua_form_style_submit_color .accua_form_value').colorPicker();

  function get_tinymce_content(name){
    if (jQuery("#"+name+" .wp-editor-wrap").hasClass("tmce-active")){
          return tinyMCE.get(name+'_textarea').getContent();
     } else
     {
          return jQuery("#" +name+ "_textarea").val();
     }
  }
  
  var save_button = $('.accua_form_save_settings_button');
  var save_status = $(".accua_form_save_settings_status");
  $('.accua_form_save_settings_button').click(function(){
    save_button.attr('disabled', 'disabled')
    save_status.empty();
    save_status.append("<span class='accua_form_api_throbbler'></span>");
    
    var form_id = $('#accua_form_save_settings_id').val();

    var data = {
        'action': 'accua-save-form-settings',
        'form-id': form_id,
        '_nonce_edit_form': $('#_nonce_edit_form').val(),
        'title': $('#title').val(),
        'use_ajax': ($('#accua_form_use_ajax .accua_form_value').is(':checked') ? 1 : 0)
    };
    
    var layout = $('#accua_form_layout .accua_form_value').val();
    if (layout == 'toplabel' || layout == 'sidebyside') {
      data.layout = layout;
    }
    
    $.each(
        ['success_message','error_message','emails_from_name','emails_from','admin_emails_to','emails_bcc','admin_emails_subject','admin_emails_message','confirmation_emails_subject','confirmation_emails_message','style_margin','style_border_color','style_border_width','style_border_radius','style_background_color','style_padding','style_color','style_font_size','style_field_spacing','style_field_border_color','style_field_border_width','style_field_border_radius','style_field_background_color','style_field_padding','style_field_color','style_submit_border_color','style_submit_border_width','style_submit_border_radius','style_submit_background_color','style_submit_padding','style_submit_color','style_submit_font_size'],
        function(i,key){
          var value = $('#accua_form_'+key+' .accua_form_check_override:checked').val();
          if(value!=undefined && value!=0) {
            if(value==-1)
              {
                data[key] ='';
                data[key+"_no_message"] = 1;
              }
            else {
              var element = $('#accua_form_'+key+' .accua_form_value');
              if(element.is('input')){
                data[key] = $('#accua_form_'+key+' .accua_form_value').val();
              }
              else if(element.is('textarea')) {
                data[key] = get_tinymce_content('accua_form_'+key);
              }
            }
          }
        }
    );
    
    /*
    $.post(
      ajaxurl,
      data,
      function(){},
      'json'
    )
    */
    
    if (accuaWidgets) {
      $('#widgets-right .button-primary.widget-control-save').click();
      accuaWidgets.saveOrder();
    }
    
    
    setTimeout(function(){
      $.ajax(ajaxurl, {
          'type': 'POST',
          'data': data,
          'success': function(){
            document.getElementById('accua_form_preview_area').src = 'admin-ajax.php?action=accua_forms_preview&fid=' + form_id;
            try {
              if (history.pushState && window.location.search.search('page=accua_forms_list') == -1) {
                history.pushState('', document.title, 'admin.php?page=accua_forms_list&fid=' + form_id);
                window.onpopstate = function(event) {
                  location.reload();
                }
              }
            } catch (e) {}
            
            var success_message = $("<span class='savedsettings'>Settings saved</span>");
            save_status.empty();
            save_status.append(success_message);
            var success_fadeout = function(){
              success_message.fadeOut(5000);
            }
            setTimeout(success_fadeout, 15000);
            save_button.removeAttr('disabled');
          },
          'error': function(){
            save_status.empty();
            save_status.append("<span class='error_savedsettings'>Error saving settings, please retry</span>");
            save_button.removeAttr('disabled');
          }
      }); }, 400); 
  });
});
