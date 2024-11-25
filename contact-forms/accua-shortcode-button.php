<?php
// registers the buttons for use
function register_accua_fancy_buttons($buttons) {
	// inserts a separator between existing buttons and our new one
	// "accua_fancy_button" is the ID of our button
	array_push($buttons, "|", "accua_fancy_button");
	return $buttons;
}

// filters the tinyMCE buttons and adds our custom buttons
function accua_shortcode_buttons() {
	// Don't bother doing this stuff if the current user lacks permissions
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
		return;
	 
	// Add only in Rich Editor mode
	if ( get_user_option('rich_editing') == 'true') {
		// filter the tinyMCE buttons and add our own
		add_filter("mce_external_plugins", "add_accua_tinymce_plugin");
		add_filter('mce_buttons', 'register_accua_fancy_buttons');
	}
}
// init process for button control
add_action('init', 'accua_shortcode_buttons');

// add the button to the tinyMCE bar
function add_accua_tinymce_plugin($plugin_array) {
	$plugin_array['accua_fancy_button'] = plugins_url('accua-shortcode-button.js?v='.ACCUA_FORMS_JS_VERSION, ACCUA_FORMS_FILE);
	return $plugin_array;
}

add_action('wp_ajax_accua_shortcode_button_popup', 'accua_shortcode_buttons_popup');
function accua_shortcode_buttons_popup() {
  require_once('accua-shortcode-button-popup.php');
  die('');
}
