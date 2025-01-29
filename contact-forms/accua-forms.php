<?php
add_action('admin_menu', 'accua_forms_menu', -95);
function accua_forms_menu(){
  $dashboard_admin_page=add_menu_page('Wordpress Contact Forms by Cimatti', 'Contact Forms', 'manage_options', 'accua_forms', 'accua_forms_dashboard_page', ACCUA_FORMS_DIR_URL.'img/cimatti-icon-10.png', '90.90300');
  add_action('load-'.$dashboard_admin_page, 'accua_forms_dashboard_page_head');

  add_submenu_page('accua_forms', 'Wordpress Contact Forms by Cimatti', 'Dashboard', 'manage_options', "accua_forms", 'accua_forms_dashboard_page');

  $form_edit_page = add_submenu_page('accua_forms', 'Forms', 'Forms', 'manage_options', "accua_forms_list", 'accua_forms_list_page');
  add_action('admin_head-'.$form_edit_page, 'accua_forms_edit_page_head');
  add_action( 'admin_print_styles-'.$form_edit_page, 'accua_forms_edit_page_head_styles');
  add_action( 'admin_print_scripts-'.$form_edit_page, 'accua_forms_edit_page_head_scripts');

  $form_add_page = add_submenu_page('accua_forms', __('Add new form', 'contact-forms'), __('Add new', 'contact-forms'), 'manage_options', "accua_forms_add", 'accua_forms_add_page');
  add_action('admin_head-'.$form_add_page, 'accua_forms_edit_page_head');
  add_action( 'admin_print_styles-'.$form_add_page, 'accua_forms_edit_page_head_styles');
  add_action( 'admin_print_scripts-'.$form_add_page, 'accua_forms_edit_page_head_scripts');

  $form_submissions_page =  add_submenu_page('accua_forms', __('Forms submissions', 'contact-forms') , __('Submissions', 'contact-forms'), 'manage_options', "accua_forms_submissions_list", '__accua_forms_submissions_list_page');
  add_action('admin_head-'.$form_submissions_page, 'accua_forms_submissions_list_page_head');
  add_action( 'admin_print_styles-'.$form_submissions_page, 'accua_forms_edit_page_head_styles');

  $form_fields_page = add_submenu_page('accua_forms', __( 'Form fields', 'contact-forms'), __('Fields', 'contact-forms'), 'manage_options', "accua_forms_fields", 'accua_forms_fields_page');
  //add_action('admin_head-'.$form_fields_page, 'accua_forms_fields_page_head');
  add_action( 'admin_print_styles-'.$form_fields_page, 'accua_forms_edit_page_head_styles');

  $settings_page = add_submenu_page('accua_forms', __( 'Default Forms settings', 'contact-forms'), __('Settings', 'contact-forms'), 'manage_options', "accua_forms_settings", 'accua_forms_settings_page');
  add_action( 'admin_print_styles-'.$settings_page, 'accua_forms_edit_page_head_styles');
  add_action( 'admin_print_scripts-'.$settings_page, 'accua_forms_settings_page_head_scripts');

  wp_enqueue_script('jquery-form');
  wp_enqueue_script('jquery-color');
  wp_enqueue_script('jquery-ui-core');
  wp_enqueue_script('jquery-ui-tabs');
  wp_enqueue_script('jquery-ui-sortable');
  wp_enqueue_script('jquery-ui-draggable');
  wp_enqueue_script('jquery-ui-droppable');
  wp_enqueue_script('jquery-ui-selectable');
  wp_enqueue_script('jquery-ui-resizable');
  wp_enqueue_script('jquery-ui-dialog');
  wp_enqueue_style('wp-jquery-ui-dialog');
}

function accua_forms_report_page_head(){
  $column_list = array(
    'month' => __( 'Month', 'contact-forms'),
    'unique_submissions' => __( 'Unique submissions', 'contact-forms') ,
    'submissions' => __('Total submissions', 'contact-forms'),
  );
  global $hook_suffix;
  register_column_headers($hook_suffix, $column_list);

  //$baseurl = WP_PLUGIN_URL.'/'.substr(plugin_basename(__FILE__),0,-strlen(basename(__FILE__)));
  //echo '<link href="'.$baseurl.'/flot/layout.css" rel="stylesheet" type="text/css">';
  //echo '<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="'.$baseurl.'/flot/excanvas.min.js"></script><![endif]-->';
  //echo '<script language="javascript" type="text/javascript" src="'.$baseurl.'/flot/jquery.flot.js"></script>';

}


function accua_forms_report_page() {
?>
  <div id="accua_forms_report_page" class="accua_forms_admin_page wrap">
    <h2>Forms submissions report</h2>

<?php
  global $wpdb, $hook_suffix;
  $months = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

  $query = "SELECT YEAR(sub_date) AS `year`, MONTH(sub_date) AS `month`, COUNT(DISTINCT `email`) AS `unique_submissions`, COUNT(*) AS `submissions`
    FROM `{$wpdb->prefix}cformssubmissions`
    GROUP BY `year`, `month`
    ORDER BY `year` DESC, `month` DESC";

  $results = $wpdb->get_results($query);

  if ($results) {
?>
  <style type="text/css">
  .column-submissions, .column-unique_submissions {
    text-align: right !important;
  }
  </style>
  <div id="accua-form-report-graph" style="height:300px;"></div>
  <table class="widefat" id="stnl_review_reviewed">
    <thead>
      <tr><?php print_column_headers($hook_suffix); ?></tr>
    </thead>

    <tfoot>
      <tr><?php print_column_headers($hook_suffix, false); ?></tr>
    </tfoot>

    <tbody>
<?php
    $alternate = false;
    $hidden = get_hidden_columns($hook_suffix);
    $data = array(
      array( 'label' => __( 'Unique submissions', 'contact-forms'), 'data' => array()),
      array( 'label' => __( 'Total submissions', 'contact-forms'), 'data' => array()),
    );
    foreach ($results as $result){
      $month = $months[$result->month];
      echo "<tr class='iedit ".(($alternate = !$alternate)?'alternate':'')."'>\n";
      echo "<td class='column-month'".(in_array('month', $hidden)?" style='display:none;'":'').">$month {$result->year}</td>\n";
      echo "<td class='column-unique_submissions'".(in_array('unique_submissions', $hidden)?" style='display:none;'":'').">{$result->unique_submissions}</td>\n";
      echo "<td class='column-submissions'".(in_array('submissions', $hidden)?" style='display:none;'":'').">{$result->submissions}</td>\n";
      echo "</tr>\n";
      $time = mktime(0, 0, 0, $result->month, 1, $result->year) * 1000;
      $data[0]['data'][] = array($time, (int)$result->unique_submissions);
      $data[1]['data'][] = array($time, (int)$result->submissions);
    }
    /*
    $year = $results[0]->year;
    $month = $results[0]->month;
    while ($year <= $result->year || $month <= $result->month) {

      $month++;
      if ($month > 12) {
        $year++;
        $month = 1;
      }
    }
    */
?>
    </tbody>
  </table>
<script type="text/javascript">
jQuery(function($){
  var data = <?php print _accua_forms_json_encode($data); ?> ;
  var options = {
      xaxis: {
        //autoscaleMargin: 0.005,
        mode: "time",
        timeformat: "%b %y",
        minTickSize: [1, "month"]
      },
      legend: {
        position: "nw"
      }
  };
  $.plot($("#accua-form-report-graph"), data, options);
});
</script>
<?php
  }
?>

  </div>
<?php
}

function accua_forms_edit_page_head_styles() {
  //wp_admin_css( 'widgets' );
  wp_enqueue_style( 'wp-pointer' ); //for tooltips
  wp_enqueue_style( 'accua-forms-admin', plugins_url('accua-forms-admin.css', ACCUA_FORMS_FILE), array(), ACCUA_FORMS_CSS_VERSION); //
}

function accua_forms_edit_page_head_scripts() {
  //wp_enqueue_script('admin-widgets');
  /*wp_enqueue_script('jquery-ui-sortable');
  wp_enqueue_script('jquery-ui-draggable');
  wp_enqueue_script('jquery-ui-droppable');*/
  wp_enqueue_script( 'wp-pointer' ); //for tooltips
  wp_enqueue_script('accua-jqColorPicker', plugins_url('/js/jqColorPicker.min.js', ACCUA_FORMS_FILE ), array( 'jquery' ), ACCUA_FORMS_JS_VERSION);

  wp_enqueue_script( 'accua-form-fields', plugins_url( 'form-fields.js' , ACCUA_FORMS_FILE ), array( 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable' ), ACCUA_FORMS_JS_VERSION);
  wp_enqueue_script( 'accua-form-settings', plugins_url('form-settings.js', ACCUA_FORMS_FILE), array('jquery'), ACCUA_FORMS_JS_VERSION);
}

function accua_forms_settings_page_head_scripts() {
  wp_enqueue_script('accua-jqColorPicker', plugins_url('/js/jqColorPicker.min.js', ACCUA_FORMS_FILE ), array( 'jquery' ), ACCUA_FORMS_JS_VERSION);
}

function accua_forms_edit_page_head() {
if (isset($_POST['accua-form-edit-action']) || (!isset($_GET['fid']))) {
  _accua_forms_form_edit_action();
  require_once('accua-forms-list-page.php');
  accua_forms_list_page_table(true);
}

?>
    <style type="text/css">
      .container > div {
        padding: 0px;
        margin-bottom: 6px;
      }
      .widget-liquid-right .widget, #wp_inactive_widgets .widget, .widget-liquid-right .sidebar-description, .widget-placeholder {
        width: 95%;
      }
      .column-submissions {
        text-align: right !important;
      }
    </style>
<?php
}

add_action( 'wp_ajax_accua-form-fields-order' , 'accua_forms_form_fields_order');
function accua_forms_form_fields_order() {
  if (!current_user_can('manage_options')){
    wp_die( -1, 403 );
  }
  check_ajax_referer('edit_form', '_nonce_edit_form');

  $post = stripslashes_deep($_POST);
  //update_option('accua_forms_form_fields_order_post', $post);

  if (empty($post['sidebars'])) {
    die('-1');
  }

  $forms_data = get_option('accua_forms_saved_forms', array());

  foreach ($post['sidebars'] as $fid => $order) {
    if (strpos($fid, 'cimatti-accua-fields-form-area-') !== 0){
      die('-1');
    }
    $fid = substr($fid, 31);

    if (empty($forms_data[$fid]['fields'])) {
      die('-1');
    }

    $old_fields = $forms_data[$fid]['fields'];
    unset($forms_data[$fid]['fields']);
    $new_fields = array();

    $order = explode(',', $order);

    foreach ($order as $i) {
      $i = preg_replace('/^(new-)?widget-\\d+_/', '', $i);
      if (isset($old_fields[$i])) {
        $new_fields[$i] = $old_fields[$i];
        unset($old_fields[$i]);
      }
    }

    if($old_fields){
      $new_fields += $old_fields;
    }
    $forms_data[$fid]['fields'] = $new_fields;
  }

  update_option('accua_forms_saved_forms', $forms_data);

  die('1');
}

add_action( 'wp_ajax_accua-save-form-field', 'accua_forms_save_form_field');
/* azione dove vengono salvati i campi dei un form */
function accua_forms_save_form_field() {
  if (!current_user_can('manage_options')){
    wp_die( -1, 403 );
  }
  check_ajax_referer('edit_form', '_nonce_edit_form');

  $post = stripslashes_deep($_POST);

  //update_option('accua_forms_save_form_field_post', $post);

  $forms_data = get_option('accua_forms_saved_forms', array());
  $fid = $post['form-id'];
  if (accua_forms_validate_form_id($fid) !== '') {
    die('-1');
  }
  if (empty($forms_data[$fid]['fields'])) {
    $forms_data[$fid]['fields'] = array();
  }

  $avail_fields = get_option('accua_forms_avail_fields', array());
  @ $wid = (string) $post['widget-id'];
  if (isset($avail_fields[$wid])) {
    $check_ref = $wid;
  } else {
    if (preg_match('/^(__html|__fieldset-begin|__fieldset-end)-\d+$/', $wid, $matches)) {
      $check_ref = $matches[1];
    } else {
      die('-1');
    }
  }
  if (empty($post['delete_widget'])) {
    //$istance_id = $post['multi_number'];
    @ $ref = $post['id_base'];
    if ($ref !== $check_ref) {
      die('-1');
    }
    $required = !empty($post["form-field-{$wid}-required"]);
    $widget_number = empty($post['multi_number']) ? (empty($post['widget_number']) ? '' : (int)$post['widget_number']) : (int)$post['multi_number'];

    if (isset($forms_data[$fid]['fields'][$wid])) {
      $old_istance_data = $forms_data[$fid]['fields'][$wid];
    } else {
      $old_istance_data = array();
    }

    $forms_data[$fid]['fields'][$wid] = array (
      'version' => 2,
      'istance_id' => $wid,
      'widget_number' => $widget_number,
      'ref' => $ref,
      'required' => $required,
    );

    if (!empty($post["form-field-{$wid}-override-label"])) {
      @ $label = (string) $post["form-field-{$wid}-label"];
      if (!current_user_can('unfiltered_html')) {
        $label = wp_kses($label, 'post');
      }
      $forms_data[$fid]['fields'][$wid]['label'] = $label;
    }

    $is_file = false;
    $is_date = false;
    if (isset($avail_fields[$wid]['type'])) {
      if ($avail_fields[$wid]['type'] == 'file') {
        $is_file = true;
      } else if ($avail_fields[$wid]['type'] == 'date') {
        $is_date = true;
      }
    }

    if (!empty($post["form-field-{$wid}-override-default-value"])) {
      @ $default_value = (string) $post["form-field-{$wid}-default-value"];
      if ($is_date) {
        $default_value = accua_forms_filter_date($default_value);
      } else if (!current_user_can('unfiltered_html')) {
        //This is filtered in any case because field type can change
        $default_value = wp_kses($default_value, 'post');
      }
      $forms_data[$fid]['fields'][$wid]['default_value'] = $default_value;
    }

    if (!empty($post["form-field-{$wid}-override-allowed-values"])) {
      @ $allowed_values = (string) $post["form-field-{$wid}-allowed-values"];
      if ($is_file){
        $forms_data[$fid]['fields'][$wid]['allowed_extensions'] = accua_forms_filter_extensions($allowed_values);
      } else {
        $forms_data[$fid]['fields'][$wid]['allowed_values'] = $allowed_values;
      }
    }
    if (!empty($post["form-field-{$wid}-override-datemin-values"])) {
      @ $mindate_values = (string) $post["form-field-{$wid}-min-of-date"];
      $forms_data[$fid]['fields'][$wid]['min_date'] = accua_forms_filter_date($mindate_values);
    }
    if (!empty($post["form-field-{$wid}-override-datemax-values"])) {
      @ $maxdate_values = (string) $post["form-field-{$wid}-max-of-date"];
      $forms_data[$fid]['fields'][$wid]['max_date'] = accua_forms_filter_date($maxdate_values);
    }
  } else {
    unset($forms_data[$fid]['fields'][$wid]);
  }

  update_option('accua_forms_saved_forms', $forms_data);

  die('1');
}

function accua_forms_filter_text($text) {
  if ( is_object( $text ) || is_array( $text ) ) {
    return '';
  }

  $text = (string) $text;
  $text = wp_check_invalid_utf8( $text );
  $text = preg_replace( '/[ \t\n\r\0\x0B]+/', ' ', $text );
  $text = trim( $text );

  return $text;
}

function accua_forms_filter_email($email) {
  $parts = explode('<', $email);
  if (isset($parts[1])) {
    $email = accua_forms_filter_text(trim($parts[1], "<> \t\n\r\0\x0B"));
    $display_name = accua_forms_filter_text(trim($parts[0]));
    return "$display_name <$email>";
  } else {
    return accua_forms_filter_text($email);
  }
}

function accua_forms_filter_emails($emails) {
  $split_emails = preg_split("/\s*[,;]\s*/", $emails);
  $emails = array();
  foreach ($split_emails as $email) {
    $emails[] = accua_forms_filter_email($email);
  }
  return implode(', ', $emails);
}

function accua_forms_filter_extensions($extensions) {
  $cleaned_extensions = array();
  $mimes = get_allowed_mime_types();
  $extensions = explode("\n", $extensions);
  foreach ($extensions as $extension) {
    $extension = trim($extension);
    if ($extension !== '') {
      foreach ( $mimes as $ext_preg => $mime_match ) {
        $ext_preg = '!^' . $ext_preg . '$!i';
        if ( preg_match( $ext_preg, $extension ) ) {
          $cleaned_extensions[] = $extension;
          break;
        }
      }
    }
  }
  return implode("\n", $cleaned_extensions);
}

function accua_forms_filter_settings($form_settings) {
  foreach($form_settings as $k => $v) {
    switch ($k) {
      case 'success_message_no_message':
      case 'error_message_no_message':
      case 'admin_emails_message_no_message':
      case 'confirmation_emails_message_no_message':
      case 'use_ajax':
        //boolean
        $form_settings[$k] = (bool) $v;
      break;
      case 'layout':
        $form_settings[$k] = ($v === 'toplabel') ? 'toplabel' : 'sidebyside';
      break;
      case 'emails_from':
        // single email
        $form_settings[$k] = accua_forms_filter_email($v);
      break;
      case 'admin_emails_to':
      case 'emails_bcc':
        //emails list
        $form_settings[$k] = accua_forms_filter_email($v);
      break;
      case 'success_message':
      case 'error_message':
      case 'admin_emails_message':
      case 'confirmation_emails_message':
        //HTML
        if (!current_user_can('unfiltered_html')) {
          $form_settings[$k] = wp_kses($v, 'post');
        }
      break;
      //case 'title':
      //case 'emails_from_name':
      //case 'admin_emails_subject':
      //case 'confirmation_emails_subject':
      //case 'style_*':
      default:
        // text field
        $form_settings[$k] = accua_forms_filter_text($v);
    }
  }
  return $form_settings;
}

add_action( 'wp_ajax_accua-save-form-settings', 'accua_forms_save_form_settings');
function accua_forms_save_form_settings() {
  if (!current_user_can('manage_options')){
    wp_die( -1, 403 );
  }
  check_ajax_referer('edit_form', '_nonce_edit_form');

  $post = stripslashes_deep($_POST);

  $forms_data = get_option('accua_forms_saved_forms', array());
  $fid = $post['form-id'];
  if (accua_forms_validate_form_id($fid) !== '') {
    die('-1');
  }

  $settings = array(
    'title',
    'success_message',
    'success_message_no_message',
    'error_message',
    'error_message_no_message',
    'emails_from_name',
    'emails_from',
    'admin_emails_to',
    'emails_bcc',
    'admin_emails_subject',
    'admin_emails_message',
    'admin_emails_message_no_message',
    'confirmation_emails_subject',
    'confirmation_emails_message',
    'confirmation_emails_message_no_message',
    //'use_ajax',

    'layout',
    'style_margin',
    'style_border_color',
    'style_border_width',
    'style_border_radius',
    'style_background_color',
    'style_padding',
    'style_color',
    'style_font_size',
    'style_field_spacing',
    'style_field_border_color',
    'style_field_border_width',
    'style_field_border_radius',
    'style_field_background_color',
    'style_field_padding',
    'style_field_color',
    'style_submit_border_color',
    'style_submit_border_width',
    'style_submit_border_radius',
    'style_submit_background_color',
    'style_submit_padding',
    'style_submit_color',
    'style_submit_font_size',
  );

  // print_r($post);

  $new_form_settings = array();
  foreach($settings as $i) {
    if (isset($post[$i])) {
      $new_form_settings[$i] = $post[$i];
    }
    unset($forms_data[$fid][$i]);
  }

  $forms_data[$fid] += accua_forms_filter_settings($new_form_settings);

  $forms_data[$fid]['use_ajax'] = !empty($post['use_ajax']);

  update_option('accua_forms_saved_forms', $forms_data);

  //print_r($forms_data[$fid]);

  die('');
}

function accua_forms_field_settings_form_counter() {
  static $i = 0;
  $i++;
  return $i;
}

/* impostazioni dei campi */
function accua_forms_field_text_settings_form($fid, $field_data=array(), $istance_data=array()){
  static $html_multi_number = 0;
  $i = accua_forms_field_settings_form_counter();

  $hidden = '';
  if (!is_array($istance_data)) {
    if ($istance_data === 'hidden') {
      $hidden = 'style="display:none"';
    }
    $istance_data = array();
    $empty_istance = true;
  } else {
    $empty_istance = empty($istance_data);
  }

  if (!is_array($field_data)){
    $field_data = array();
  }

  $field_data += array(
    'version' => '1',
    'id' => '__html',
    'name' => __( 'Custom HTML content', 'contact-forms'),
    'type' => 'html',
    'description' => __('Use this special field to inject raw HTML in the form. You can use this multiple times.', 'contact-forms'),
    'default_value' => '',
    'allowed_values' => '',
    'allowed_extensions' => '',
  );

  $override_label = isset($istance_data['label']) ? 'checked="checked"' : '';
  $override_default_value = isset($istance_data['default_value']) ? 'checked="checked"' : '';
  $override_allowed_values = isset($istance_data['allowed_values']) ? 'checked="checked"' : '';
  $override_allowed_extensions = '';

  if ($field_data['type'] == 'file') {
    if (isset($istance_data['version']) && $istance_data['version'] >= 2) {
      if (isset($istance_data['allowed_extensions'])) {
        $override_allowed_extensions = 'checked="checked"';
      }
    } else {
      if (isset($istance_data['allowed_values'])) {
        $istance_data['allowed_extensions'] = $istance_data['allowed_values'];
        $override_allowed_extensions = 'checked="checked"';
      }
    }

    if ($field_data['version'] < 2) {
      $field_data['allowed_extensions'] = $field_data['allowed_values'];
    }

    if ($field_data['allowed_extensions'] === '') {
      $file_data = get_option('accua_forms_default_file_field_data',array());
      if (isset($file_data['valid_extensions'])){
        $field_data['allowed_extensions'] = $file_data['valid_extensions'];
      }
    }
  }

  if ($field_data['type'] == 'date') {
  	$override_mindate_values = isset($istance_data['min_date']) ? 'checked="checked"' : '';
	$override_maxdate_values = isset($istance_data['max_date']) ? 'checked="checked"' : '';
    //$override_default_value = isset($istance_data['default_date_value']) ? 'checked="checked"' : '';

    $istance_data += array(
	  'min_date' => $field_data['min_date'],
	  'max_date' => $field_data['max_date'],
	  'default_value' => $field_data['default_date_value'],
	);
  }

  $istance_data += array(
    //'version' => 1,
    'istance_id' => $field_data['id'],
    'widget_number' => '',
    'ref' => $field_data['id'],
    'label' => $field_data['name'],
    'default_value' => $field_data['default_value'],
    'allowed_values' => $field_data['allowed_values'],
    'allowed_extensions' => $field_data['allowed_extensions'],
    'required' => false,
  );

  foreach ($istance_data as $key => $value) {
    $istance_data[$key] = htmlspecialchars($istance_data[$key], ENT_QUOTES);
  }

  foreach ($field_data as $key => $value) {
    $field_data[$key] = htmlspecialchars($field_data[$key], ENT_QUOTES);
  }

  $multi_number = '';
  $add_new = '';

  if (in_array($istance_data['ref'], array('__html','__fieldset-begin','__fieldset-end'))) {
    $forceoverride_field = true;
    if ($empty_istance) {
      $add_new = 'multi';
      $istance_data['istance_id'] .= '-__i__';
      $istance_data['widget_number'] = 1;
      $multi_number = 1 + $html_multi_number;
    } else {
      if ($html_multi_number < $istance_data['widget_number']) {
        $html_multi_number = $istance_data['widget_number'];
      }
    }
  } else {
    $forceoverride_field = false;
    $add_new = $empty_istance ? 'single' : '';
  }

  $fid = htmlspecialchars($fid, ENT_QUOTES);
  $testi_eot = array (
    'label' => __( 'Label', 'contact-forms'),
    'override' => __( 'override', 'contact-forms'),
    'default_value' => __( 'Default value', 'contact-forms'),
    'default_values' => __( 'Default value(s)', 'contact-forms'),
    'desc_def' => __( 'For multiple default values, use | as separator.', 'contact-forms'),
    'allowed_values' => __( 'Allowed values', 'contact-forms'),
    'desc_all' => __( 'The possible values this field can contain. Enter one value per line, in the format key|label. The key is the value that will be stored in the database. The label is optional, and the key will be used as the label if no label is specified.', 'contact-forms'),
    'allowed_extensions' => __( 'Allowed extensions', 'contact-forms'),
    'desc_all_ext' => __( 'Accepted file extensions. One per line, without dots.', 'contact-forms'),
    'required' => __( 'Required', 'contact-forms'),
    'custom_HTML_content' => __( 'Custom HTML content', 'contact-forms'),
    'remove' => __( 'Remove', 'contact-forms'),
    'close' => __( 'Close', 'contact-forms'),
    'save' => __( 'Save', 'contact-forms'),
    'min-of-date' => __( 'Min date', 'contact-forms'),
    'max-of-date' => __( 'Max date', 'contact-forms')
  );

  if ($forceoverride_field) {
    $override_begin = '';
    $override_type = 'hidden';
    $override_end = '';
  } else {
    $override_begin = "({$testi_eot['override']}: ";
    $override_type = 'checkbox';
    $override_end = ')';
  }

  $content = <<<EOT
    <p><label for="widget-{$istance_data['istance_id']}-label">{$testi_eot['label']}:</label>
    {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-label" value="1" {$override_label} />{$override_end}<br>
    <input type="text" value="{$istance_data['label']}" name="form-field-{$istance_data['istance_id']}-label" id="widget-{$istance_data['istance_id']}-label" class="widefat"></p>
EOT;

  $default_value = <<<EOT
    <p><label for="widget-{$istance_data['istance_id']}-default-value">{$testi_eot['default_value']}:</label>
    {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-default-value" value="1" {$override_default_value} />{$override_end}<br>
    <input type="text" value="{$istance_data['default_value']}" name="form-field-{$istance_data['istance_id']}-default-value" id="widget-{$istance_data['istance_id']}-default-value" class="widefat"></p>
EOT;

  $default_values = <<<EOT
    <p><label for="widget-{$istance_data['istance_id']}-default-value">{$testi_eot['default_value']}:</label>
    {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-default-value" value="1" {$override_default_value} />{$override_end}<br>
    <input type="text" value="{$istance_data['default_value']}" name="form-field-{$istance_data['istance_id']}-default-value" id="widget-{$istance_data['istance_id']}-default-value" class="widefat"><br />
    </p>
EOT;

  $allowed_values = <<<EOT
    <p><label for="widget-{$istance_data['istance_id']}-allowed-values">{$testi_eot['allowed_values']}:</label>
    {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-allowed-values" value="1" {$override_allowed_values} />{$override_end}<br>
    <textarea rows="6" cols="50" name="form-field-{$istance_data['istance_id']}-allowed-values" id="widget-{$istance_data['istance_id']}-allowed-values" class="widefat">{$istance_data['allowed_values']}</textarea><br />
    {$testi_eot['desc_all']}</p>
EOT;

  $allowed_ext = <<<EOT
    <p><label for="widget-{$istance_data['istance_id']}-allowed-values">{$testi_eot['allowed_extensions']}:</label>
    {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-allowed-values" value="1" {$override_allowed_extensions} />{$override_end}<br>
    <textarea rows="6" cols="50" name="form-field-{$istance_data['istance_id']}-allowed-values" id="widget-{$istance_data['istance_id']}-allowed-values" class="widefat">{$istance_data['allowed_extensions']}</textarea><br />
    {$testi_eot['desc_all_ext']}</p>
EOT;

  $required_checked = empty($istance_data['required']) ? '' : 'checked="checked"';
  $required = <<<EOT
    <p><label for="widget-{$istance_data['istance_id']}-required">{$testi_eot['required']}:</label>
    <input type="checkbox" value="1" {$required_checked} name="form-field-{$istance_data['istance_id']}-required" id="widget-{$istance_data['istance_id']}-required"></p>
EOT;

  if ($field_data['type'] == 'date'){
    $default_date_value = <<<EOT
      <p><label for="widget-{$istance_data['istance_id']}-default-value">{$testi_eot['default_value']}:</label>
      {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-default-value" value="1" {$override_default_value} />{$override_end}<br>
      <input type="date" value="{$istance_data['default_value']}" name="form-field-{$istance_data['istance_id']}-default-value" id="widget-{$istance_data['istance_id']}-default-value" class="widefat"></p>
EOT;
    $min_date = <<<EOT
      <p><label for="widget-{$istance_data['istance_id']}-min-of-date">{$testi_eot['min-of-date']}:</label>
      {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-datemin-values" value="1" {$override_mindate_values} />{$override_end}<br>
      <input type="date" value="{$istance_data['min_date']}" name="form-field-{$istance_data['istance_id']}-min-of-date" id="widget-{$istance_data['istance_id']}-min-of-date"></p>
EOT;
    $max_date = <<<EOT
      <p><label for="widget-{$istance_data['istance_id']}-max-of-date">{$testi_eot['max-of-date']}:</label>
      {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-datemax-values" value="1" {$override_maxdate_values} />{$override_end}<br>
      <input type="date" value="{$istance_data['max_date']}" name="form-field-{$istance_data['istance_id']}-max-of-date" id="widget-{$istance_data['istance_id']}-max-of-date"></p>
EOT;
  }

  switch ($field_data['type']) {
    case 'textarea':
      $content .= <<<EOT
    <p><label for="widget-{$istance_data['istance_id']}-default-value">{$testi_eot['default_value']}:</label>
      {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-default-value" value="1" {$override_default_value} />{$override_end}<br>
      <textarea rows="6" cols="50" name="form-field-{$istance_data['istance_id']}-default-value" id="widget-{$istance_data['istance_id']}-default-value" class="widefat">{$istance_data['default_value']}</textarea></p>
    $required
EOT;
    break;
    case 'hidden':
      $content = $default_value;
    break;
    case 'checkbox':
      $content .= $default_value . $required;
    break;
    case 'select':
    case 'radio':
    case 'post-select':
      $content .= $default_value . $allowed_values . $required;
    break;
    case 'multiselect':
    case 'multicheckbox':
    case 'post-multicheckbox':
      $content .= $default_values . $allowed_values . $required;
    break;
    case 'file':
      $content .= $allowed_ext . $required;
    case 'submit':
    case 'fieldset-begin':
      //just the label
    break;
    case 'fieldset-end':
      //Nothing!
      $content = '';
    break;
    case 'html':
      $content = <<<EOT
    <p><label for="widget-{$istance_data['istance_id']}-default-value">{$testi_eot['custom_HTML_content']}</label>
      {$override_begin}<input type="{$override_type}" name="form-field-{$istance_data['istance_id']}-override-default-value" value="1" {$override_default_value} />{$override_end}<br>
      <textarea rows="6" cols="50" name="form-field-{$istance_data['istance_id']}-default-value" id="widget-{$istance_data['istance_id']}-default-value" class="widefat">{$istance_data['default_value']}</textarea></p>
EOT;
    break;
    case 'date':
      $content .= $default_date_value . $min_date . $max_date . $required;
    break;
    case 'email':
    case 'autoreply_email':
    case 'textfield':
    case 'colorpicker':
    case 'datepicker':
    case 'dateselect':
    default:
      $content .= $default_value . $required;
    break;
  }
  $adminurl = admin_url();

  return <<<EOT
<div class="widget ui-draggable" id="widget-{$i}_{$istance_data['istance_id']}" $hidden>  <div class="widget-top">
  <div class="widget-title-action">
    <a href="#available-widgets" class="widget-action hide-if-no-js"></a>
  </div>
  <div class="widget-title"><h4>{$field_data['name']}<span class="in-widget-title"></span></h4></div>
  </div>

  <div class="widget-inside">
  <form method="post" action="">
  <div class="widget-content">
    $content
  </div>
  <input type="hidden" value="{$fid}" name="form-id">
  <input type="hidden" value="{$istance_data['istance_id']}" class="widget-id" name="widget-id">
  <input type="hidden" value="{$field_data['id']}" class="id_base" name="id_base">
  <input type="hidden" value="250" class="widget-width" name="widget-width">
  <input type="hidden" value="200" class="widget-height" name="widget-height">
  <input type="hidden" value="{$istance_data['widget_number']}" class="widget_number" name="widget_number">
  <input type="hidden" value="{$multi_number}" class="multi_number" name="multi_number">
  <input type="hidden" value="{$add_new}" class="add_new" name="add_new">

  <div class="widget-control-actions">
    <div class="alignleft">
    <a href="#remove" class="widget-control-remove delete">{$testi_eot['remove']}</a> |
    <a href="#close" class="widget-control-close">{$testi_eot['close']}</a>
    </div>
   <div  class="alignright">
      <img alt="" title="" class="ajax-feedback" src="{$adminurl}images/wpspin_light.gif">
      <input type="submit" value="{$testi_eot['save']}" class="button-primary widget-control-save" id="widget-{$istance_data['istance_id']}-savewidget" name="savewidget">
    </div>
    <br class="clear">
  </div>
  </form>
  </div>

  <!--<div class="widget-description">
    {$field_data['description']}
  </div>-->
</div>

EOT;
}

function accua_forms_add_page($message='') {
  $forms_data = get_option('accua_forms_saved_forms', array());
  $trash_data = get_option('accua_forms_trash_forms', array());
  if (!empty($_GET['fid'])) {
    $fid = htmlspecialchars(stripslashes($_GET['fid']), ENT_QUOTES);
  } else {
    if ($message === '') {
      $fid = 1 + ((int) get_option('accua_forms_lastid', 0));
      while (isset($forms_data[$fid]) || isset($trash_data[$fid])) {
        $fid++;
      }
      update_option('accua_forms_lastid', $fid);
      $message = _accua_forms_test_clonefrom($fid);
      if ($message === '') {
        return accua_forms_edit_page($fid);
      }
    } else {
      $fid = '';
    }
  }
  if (!empty($_GET['clonefrom'])) {
    check_admin_referer('clone_posts');
    $clonefrom = stripslashes($_GET['clonefrom']);
  } else {
    $clonefrom = '';
  }
?>

<div id="accua_forms_add_page" class="accua_forms_admin_page wrap">
<h2><?php _e( 'Create a form', 'contact-forms'); ?> </h2>
<?php if ($message !== '') {
  echo "<div style='border:1px solid; padding: 10px;'>$message</div>";
} ?>
<form action="admin.php" method="GET">
<?php wp_nonce_field('edit_posts', '_wpnonce', false, true) ?>
<input type="hidden" name="page" value="accua_forms_list" />
<p>Form id: <input type="text" name="fid" value="<?php echo $fid; ?>" /></p>
<?php
  if ($forms_data) {
    echo '<p><select name="clonefrom">
            <option value="">'.__( 'Empty form', 'contact-forms').'</option>
            <optgroup label="'.__( 'Clone form:', 'contact-forms').'">';
    foreach ($forms_data as $i => $formdata) {
      $sel = ($i == $clonefrom) ? " selected='selected'" : '';
      $i = htmlspecialchars($i, ENT_QUOTES);
      if (isset($formdata['title']) && ('' !== trim($formdata['title']))) {
        $formtitle = htmlspecialchars($formdata['title']);
      } else {
        $formtitle = $i;
      }
      echo "<option value='$i'$sel>$formtitle</option>\n";
    }
    echo '</optgroup></select></p>';
  }
?>
<p><input type="submit" value="<?php _e( 'Create', 'contact-forms'); ?>" /></p>
</form>
</div>
<?php
}

function _accua_forms_test_clonefrom($fid){
  $error = '';
  if (isset($_GET['clonefrom'])&&$_GET['clonefrom']!=='') {
    $clonefrom = stripslashes($_GET['clonefrom']);
    $forms_data = get_option('accua_forms_saved_forms', array());
    if (isset($forms_data[$fid])){
      $error .= "<p>".__( 'Form already exists', 'contact-forms')."</p>";
    } else if (empty($forms_data[$clonefrom])) {
      $error .= "<p>".__( 'Source form doesn\'t exists.', 'contact-forms')."</p>";
    } else {
      $forms_data[$fid] = $forms_data[$clonefrom];
      if (!isset($forms_data[$fid]['title'])) {
        $forms_data[$fid]['title'] = $clonefrom ." ".__( 'clone', 'contact-forms');
      } else {
        $forms_data[$fid]['title'] .= " ". __( 'clone', 'contact-forms');
      }
      update_option('accua_forms_saved_forms', $forms_data);
    }
  }
  return $error;
}

function _accua_forms_form_edit_action() {
  static $message = null;
  if ($message === null) {
    $message = '';
    if (isset($_POST['accua-form-edit-action'])){
      $post = stripslashes_deep($_POST);
      switch ($post['accua-form-edit-action']) {
        case 'delete':
          $fid = $post['form-id'];
          check_admin_referer('contact-forms-delete_'.$fid);
          $forms_data = get_option('accua_forms_saved_forms', array());
          unset($forms_data[$fid]);
          update_option('accua_forms_saved_forms', $forms_data);
          $fid = htmlspecialchars($fid);
          $message .= sprintf( __( 'Form "%s" deleted','contact-forms' ), $fid );
        break;
      }
    }
  }
  return $message;
}

function accua_forms_validate_form_id($fid) {
  $error = '';
  if (!preg_match('/^[a-z0-9_-]+$/i', $fid)) {
    $error .= "<p>".__( 'Only letters, numbers, hyphen and underscores allowed in form identificative name', 'contact-forms')."</p>";
  }
  if (substr($fid,0,2) == '__') {
    $error .= "<p>".__( 'The identificative name can\'t start with two underscores (__)', 'contact-forms')."</p>";
  }
  if (strlen($fid) > 70) {
    $error .= "<p>".__( 'You cannot use more than 70 characters for the identificative name', 'contact-forms')."</p>";
  }
  return $error;
}

function accua_forms_list_page() {
  $message = '';
  if (isset($_POST['accua-form-edit-action'])){
    $message = _accua_forms_form_edit_action();
  } else if (isset($_GET['fid'])) {
    check_admin_referer('edit_posts');
    $fid = stripslashes($_GET['fid']);
    $error = accua_forms_validate_form_id($fid);
    if ($error === '' && (isset($_GET['clonefrom'])&&$_GET['clonefrom']!=='')) {
      $error .= _accua_forms_test_clonefrom($fid);
    }
    if ($error === '') {
      return accua_forms_edit_page($fid);
    } else {
      return accua_forms_add_page($error);
    }
  }
?>
<div id="accua_forms_list_page" class="accua_forms_admin_page wrap">
<?php if ($message !== '') {
  echo "<div style='border:1px solid; padding: 10px;'>$message</div>";
} ?>
<h2><img src="<?php echo ACCUA_FORMS_DIR_URL.'img/cimatti-icon-20.png'; ?>"/> <?php _e( 'Contact Forms', 'contact-forms'); ?>
  <a class="add-new-h2" href="<?php echo get_admin_url(); ?>admin.php?page=accua_forms_add"><?php _e('Add New','contact-forms'); ?></a>
</h2>
<div ><?php
  echo strtr(__( 'Use the turquoise blue %img_c button in the TinyMCE editor to include the forms in posts, pages or other content types (shortcode and php functions also available)', 'contact-forms'),
   array('%img_c'=>'<img alt="C" src="' . plugins_url('img/cimatti-icon-16.png', ACCUA_FORMS_FILE ) . '" />')
  );
?></div>
<?php
accua_forms_list_page_table();
?>
</div>
<?php
}

function accua_forms_edit_page($fid) {
  wp_enqueue_script('jquery-ui-tabs','','','',true);
  wp_enqueue_script('contact_forms_tabs', plugins_url('accua_tabs.js', ACCUA_FORMS_FILE ), array( 'jquery' ), ACCUA_FORMS_JS_VERSION);

  if (!class_exists('AccuaFormsHelp')) {
      require_once('accua-forms-help.php');
    }
  $accuaHelp = AccuaFormsHelp::getInstance();
  /*
  $avail_fields = array(
    'first_name' => array (
      'id' => "first_name",
      'name' => "First Name",
      'type' => "textfield",
      'description' => 'This is the first name',
    ),
    'last_name' => array (
      'id' => "last_name",
      'name' => "Last Name",
      'type' => "textfield",
      'description' => 'This is the last name',
    ),
    'email' => array (
      'id' => "email",
      'name' => "Email",
      'type' => "email",
      'description' => 'This is the email',
    ),
  );
  */

  $avail_fields = get_option('accua_forms_avail_fields', array());
  $default_form_data = get_option('accua_forms_default_form_data',array());



  $form_data = _accua_forms_get_form_data($fid, true, !empty($_GET['restore']));
  $form_overrided_data = $form_data['_overrided'];

  $fid_esc = htmlspecialchars($fid, ENT_QUOTES);

  $adminurl = admin_url();

  global $wp_version;
  if (version_compare($wp_version, '4') >= 0) {
?>
<style>
#widgets-right .accua-form-widget-scroll-wrapper .widget.ui-draggable {
        height: auto !important;
}
</style>
<?php
  }

?>
<div id="accua_forms_edit_page" class="accua_forms_admin_page wrap">
<h2><img src="<?php echo ACCUA_FORMS_DIR_URL.'img/cimatti-icon-20.png'; ?>"/> <?php  _e('Contact Forms - Edit Form', 'contact-forms'); ?></h2>
<div class="accua_form_save_settings_status"></div>
<?php wp_nonce_field('edit_form', '_nonce_edit_form'); ?>
<div id="titlediv"><br />
  <label id="title-prompt-text" class="screen-reader-text" for="title"><?php  _e( 'Enter title here', 'contact-forms'); ?></label>
  <input id="title" type="text" autocomplete="off" value="<?php echo htmlspecialchars($form_data['title'], ENT_QUOTES) ?>" size="30" name="post_title">
  <script type="text/javascript">
   jQuery(function($){
       if ( jQuery('#titlediv #title').val() == '' )
         jQuery('#title-prompt-text').removeClass('screen-reader-text');

       jQuery('#titlediv #title').focus(function() {
           jQuery('#title-prompt-text').addClass('screen-reader-text');
       });
       jQuery('#titlediv #title').blur(function() {
         if ( jQuery('#titlediv #title').val() == '' )
            jQuery('#title-prompt-text').removeClass('screen-reader-text');
       });
   });
  </script>
</div>
    <div id="accua_tabs">
      <div id="save_settings_top" class="accua_forms_save_settings_top">
        <form id="delete_form" action="admin.php?page=accua_forms_list" method="POST" onsubmit="return confirm(<?php print htmlspecialchars(_accua_forms_json_encode(__('Do you really want to delete this form?', 'contact-forms')), ENT_QUOTES); ?>);">
          <input type="hidden" name="accua-form-edit-action" value="delete" />
          <input type="hidden" name="form-id" value="<?php echo $fid_esc; ?>" />
          <input type="submit" value="<?php _e( 'Delete this form', 'contact-forms'); ?>" />
          <?php wp_nonce_field( 'contact-forms-delete_'.$fid ); ?>
        </form>
        <?php /*<input class="button button-primary button-large accua_form_save_settings_button" id="accua_form_save_settings" type="button" value="<?php echo htmlspecialchars(__( 'Save settings', 'contact-forms'), ENT_QUOTES); ?>" /> */ ?>
      </div>
      <ul id="ul_accua_tabs">
        <li class="tabs"><a href="#accua_tab_fields"><?php _e( 'Fields', 'contact-forms'); ?></a></li>
        <li class="tabs"><a href="#accua_tab_messages"><?php _e( 'Messages', 'contact-forms'); ?></a></li>
        <?php /*<li class="tabs"><a href="#accua_tab_preview"><?php _e( 'Preview/Test', 'contact-forms'); ?></a></li> */ ?>
      </ul>
    <div id="accua_tab_fields" class="content_tab">
		<div style="width:50%; float:left;background: #f4f4f4;">
			<div style="padding: 20px;">
			<input class="button button-primary button-large accua_form_save_settings_button" id="accua_form_save_settings_inside" type="button" value="<?php echo htmlspecialchars(__( 'Save', 'contact-forms'), ENT_QUOTES); ?>" />
		      <div id="accua_tabs2">
				  <ul>
				    <li class="tabs"><a href="#accua_tab_fields2"><?php _e( 'Fields', 'contact-forms'); ?></a></li>
				    <li class="tabs"><a href="#accua_tab_customise"><?php _e( 'Appearance', 'contact-forms'); ?></a></li>
				  </ul>
				  <div id="accua_tab_fields2">
				    <h2><?php _e( 'Drag & Drop Form Fields', 'contact-forms'); ?></h2>
				    <?php /*<a href="admin.php?page=accua_forms_fields" target="_blank"><strong><?php _e( 'Create new fields here', 'contact-forms'); ?></strong></a></p> */ ?>
				    <div style="width:30%; float:left;">
					<!-- Begin available fields -->

						<div class="widget-liquid-left" style="margin-right:0">
						    <!-- <div id="widgets-left"> -->
						    <div id="widgets-left" style="margin-right:5px;">
						      <div id="available-widgets" class="widgets-holder-wrap">
						        <div class="widget-holder">
						        <div id="widget-list">
						        <!-- begin fields list -->

						        <?php

						          //This block must be executed before the output of available fields so accua_forms_field_text_settings_form() can initialize $html_multi_number for further html and fieldset fields
						          $form_fields_html = '';
                                  foreach ($form_data['fields'] as $field) {
                                    if (empty($avail_fields[$field['ref']])) {
                                      $ref = array();
                                      if (!empty($field['ref'])) {
                                        if ($field['ref'] == '__fieldset-begin') {
                                          $ref = array(
                                              'id' => '__fieldset-begin',
                                              'name' => __('Fieldset begin', 'contact-forms'),
                                              'type' => 'fieldset-begin',
                                              'description' => '',
                                          );
                                        } else if ($field['ref'] == '__fieldset-end') {
                                          $ref = array(
                                              'id' => '__fieldset-end',
                                              'name' => __('Fieldset end', 'contact-forms'),
                                              'type' => 'fieldset-end',
                                              'description' => '',
                                          );
                                        }
                                      }
                                    } else {
                                      $ref = $avail_fields[$field['ref']];
                                    }

                                    //print_r($ref);
                                    $form_fields_html .= accua_forms_field_text_settings_form($fid, $ref, $field);
                                  }


						          foreach ($avail_fields as $avail_field) {
						            $hidden = (empty($form_data['fields'][$avail_field['id']])) ? false : 'hidden';
						            echo accua_forms_field_text_settings_form($fid, $avail_field, $hidden);
						          }
						          //Custom HTML field
						          echo accua_forms_field_text_settings_form($fid);
						          //Fieldset begin
						          echo accua_forms_field_text_settings_form($fid, array(
						              'id' => '__fieldset-begin',
						              'name' => __( 'Fieldset begin', 'contact-forms'),
						              'type' => 'fieldset-begin',
						              'description' => __('You can use this field multiple times.', 'contact-forms'),
						              'default_value' => '',
						              'allowed_values' => '',
						          ));
						          //Fieldset end
						          echo accua_forms_field_text_settings_form($fid, array(
						            'id' => '__fieldset-end',
						            'name' => __( 'Fieldset end', 'contact-forms'),
						            'type' => 'fieldset-end',
						            'description' => __('You can use this field multiple times.', 'contact-forms'),
						            'default_value' => '',
						            'allowed_values' => '',
						          ));
						        ?>

						        <!-- end fields list -->
						        </div>

						        <br class='clear' />
						        </div>
						        <br class="clear" />
						      </div>

						    </div>
						</div>
					<!-- End available fields -->
					</div>

				    <div style="width:70%; float:left;" class="container">
				    <!--
				      <h3>Form Fields</h3>
				      <div id="form_fields_container">
				      </div>
				      -->

				      <div class="widget-liquid-right" style="width:100%">
				        <div id="widgets-right" style="width:100%">
				          <div class="widgets-holder-wrap dashed">
				            <div class="sidebar-name">
				            <div class="sidebar-name-arrow"><br></div>
				            <h3><?php _e( 'Drop fields here', 'contact-forms'); ?> <span><img alt="" title="" class="ajax-feedback" src="<?php echo $adminurl;?>images/wpspin_light.gif"></span></h3>
				            </div>
				            <div class="widgets-sortables ui-sortable" id="cimatti-accua-fields-form-area-<?php echo $fid_esc ?>">
				                <?php echo $form_fields_html; ?>
				            </div>
				          </div>
				        </div>
				      </div>

				    </div>
				  </div>
				  <div id="accua_tab_customise">
				  <div style="width: 48%; float:left;">
				    <h3><?php _e( 'General', 'contact-forms'); ?></h3>
				    <p id="accua_form_use_ajax"><input class="accua_form_value" type="checkbox" value="1" <?php if (!empty($form_data['use_ajax'])) {echo 'checked="checked" ';} ?>/><?php _e('Do not reload the page on form submission', 'contact-forms'); ?></p>

					<p id="accua_form_layout"><?php _e( 'Labels', 'contact-forms'); ?> <select name="layout" class="accua_form_value">
					  <option value="" <?php if (isset($form_overrided_data['layout'])) { echo 'selected="selected"'; } ?>>default (<?php if($default_form_data['layout']=='sidebyside') _e( 'Labels on the left of the fields', 'contact-forms'); else _e( 'Labels on top of the fields', 'contact-forms'); ?>)</option><option value="sidebyside" <?php if ((isset($form_overrided_data['layout'])) && ($form_data['layout'] == 'sidebyside')) { echo 'selected="selected"'; } ?>><?php _e( 'Labels on the left of the fields', 'contact-forms'); ?></option><option value="toplabel" <?php if ((isset($form_overrided_data['layout'])) && ($form_data['layout'] == 'toplabel')) { echo 'selected="selected"'; } ?>><?php _e( 'Labels on top of the fields', 'contact-forms'); ?></option></select>
					</p>

					<div id="accua_form_style_margin" class="label_input" class="label_container">
					  <input name="accua_form_style_margin" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_margin'])) {echo 'checked="checked" ';} ?>/><strong><?php _e( 'Margin', 'contact-forms'); ?></strong>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_margin']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_margin'], ENT_QUOTES) ?>" />

					</div>

					<div class="label_input">
						<div id="accua_form_style_border_color" class="label_container">
						  <input name="accua_form_style_border_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_border_color'])) {echo 'checked="checked" ';} ?>/><strong><?php _e( 'Border color', 'contact-forms'); ?></strong>
						  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_border_color']); ?></div>
						  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_border_color'], ENT_QUOTES) ?>" />
						</div>
						<div id="accua_form_style_border_width" class="label_container">
						  <input name="accua_form_style_border_width" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_border_width'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Width', 'contact-forms'); ?>
						  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_border_width']); ?></div>
						  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_border_width'], ENT_QUOTES) ?>" />
						</div>
						<div id="accua_form_style_border_radius" class="label_container">
						  <input name="accua_form_style_border_radius" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_border_radius'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Radius', 'contact-forms'); ?>
						  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_border_radius']); ?></div>
						  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_border_radius'], ENT_QUOTES) ?>" />
						</div>
					</div>

					<div class="label_input">
						<div id="accua_form_style_background_color" class="label_container">
							<input name="accua_form_style_background_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_background_color'])) {echo 'checked="checked" ';} ?>/><strong><?php _e( 'Background', 'contact-forms'); ?></strong>
						  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_background_color']); ?></div>
						  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_background_color'], ENT_QUOTES) ?>" />
						</div>

						<div id="accua_form_style_padding" class="label_container">
						  <input name="accua_form_style_padding" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_padding'])) {echo 'checked="checked" ';} ?>/><strong><?php _e( 'Padding', 'contact-forms'); ?></strong>
						  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_padding']); ?></div>
						  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_padding'], ENT_QUOTES) ?>" />
						</div>
					</div>
					<div class="label_input">
						<div id="accua_form_style_color" class="label_container">
						  <input name="accua_form_style_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_color'])) {echo 'checked="checked" ';} ?>/><strong><?php _e( 'Font', 'contact-forms'); ?></strong>
						  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_color']); ?></div>
						  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_color'], ENT_QUOTES) ?>" />
						</div>

						<div id="accua_form_style_font_size" class="label_container">
							<input name="accua_form_style_font_size" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_font_size'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Size', 'contact-forms'); ?>
						  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_font_size']); ?></div>
						  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_font_size'], ENT_QUOTES) ?>" />
						</div>
					</div>
					</div>
				    <div style="width: 48%; float:left;">

					<h3><?php _e( 'Fields', 'contact-forms'); ?></h3>

					<div id="accua_form_style_field_spacing" class="label_input">
					  <input name="accua_form_style_field_spacing" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_field_spacing'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Spacing', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_field_spacing']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_spacing'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_field_border_color" class="label_input">
					  <input name="accua_form_style_field_border_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_field_border_color'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Border color', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_field_border_color']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_border_color'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_field_border_width" class="label_input">
					  <input name="accua_form_style_field_border_width" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_field_border_width'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Border width', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_field_border_width']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_border_width'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_field_border_radius" class="label_input">
					  <input name="accua_form_style_field_border_radius" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_field_border_radius'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Rounded corner radius', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_field_border_radius']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_border_radius'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_field_background_color" class="label_input">
					  <input name="accua_form_style_field_background_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_field_background_color'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Background color', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_field_background_color']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_background_color'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_field_padding" class="label_input">
					  <input name="accua_form_style_field_padding" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_field_padding'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Padding', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_field_padding']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_padding'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_field_color" class="label_input">
					  <input name="accua_form_style_field_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_field_color'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Text color', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_field_color']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_color'], ENT_QUOTES) ?>" />
					</div>


					<h3><?php _e( 'Submit button', 'contact-forms'); ?></h3>

					<div id="accua_form_style_submit_border_color" class="label_input">
					  <input name="accua_form_style_submit_border_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_submit_border_color'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Border color', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_submit_border_color']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_border_color'], ENT_QUOTES) ?>" />
					  <?php _e( 'Customize', 'contact-forms'); ?>
					</div>

					<div id="accua_form_style_submit_border_width" class="label_input">
					  <input name="accua_form_style_submit_border_width" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_submit_border_width'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Border width', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_submit_border_width']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_border_width'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_submit_border_radius" class="label_input">
					  <input name="accua_form_style_submit_border_radius" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_submit_border_radius'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Rounded corner radius', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_submit_border_radius']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_border_radius'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_submit_background_color" class="label_input">
					  <input name="accua_form_style_submit_background_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_submit_background_color'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Background color', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_submit_background_color']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_background_color'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_submit_padding" class="label_input">
					  <input name="accua_form_style_submit_padding" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_submit_padding'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Padding', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_submit_padding']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_padding'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_submit_color" class="label_input">
					  <input name="accua_form_style_submit_color" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_submit_color'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Text color', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_submit_color']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_color'], ENT_QUOTES) ?>" />
					</div>

					<div id="accua_form_style_submit_font_size" class="label_input">
					  <input name="accua_form_style_submit_font_size" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['style_submit_font_size'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Font size', 'contact-forms'); ?>
					  <div class="default_value"><?php echo htmlspecialchars($default_form_data['style_submit_font_size']); ?></div>
					  <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_font_size'], ENT_QUOTES) ?>" />
					</div>

					<br clear="all"/>
				  </div>
				  </div>
				</div>



			</div>
	</div>
    <div style="width:50%; float:right;">
	    <div style="padding: 15px;">
	        <h2><?php _e('Preview', 'contact-forms'); ?>
	        <?php echo $accuaHelp->add_pointer('form_edit_preview'); ?>
	        </h2>
	        <div id="accua_form_preview_area_wrapper">
			<?php
			echo "<script>
			  function resizeIframe(obj) {
			  	altezza = obj.contentWindow.document.documentElement.scrollHeight + 200;
			    obj.style.height = altezza + 'px';
			  }
			</script>";

			?>
	        <iframe id="accua_form_preview_area" src="admin-ajax.php?action=accua_forms_preview&fid=<?php echo htmlspecialchars($fid,ENT_QUOTES);?>"  frameborder="0" scrolling="no" onload="resizeIframe(this)" ></iframe>
	        <?php //todo: posso usare lo stile del sito? font ecc ?>
	        </div>

	    </div>
    </div>

    <div style="clear:both;">&nbsp;</div>

    <?php /* * / ?>
    <pre>
    accua_forms_form_fields_order_post: <?php echo htmlspecialchars(print_r(get_option('accua_forms_form_fields_order_post'), true)); ?>

    accua_forms_save_form_field_post: <?php echo htmlspecialchars(print_r(get_option('accua_forms_save_form_field_post'), true)); ?>

    accua_forms_saved_form_data: <?php echo htmlspecialchars(print_r($form_data, true)); ?>

    </pre>
    <?php /* */ ?>
</div>
 <div id="accua_tab_messages" class="content_tab">
 <?php
  $settings_editor = array(
    'teeny' => true,
    'editor_class' => 'accua_form_value',
    'tinymce' => array(
        'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,'));
  ?>

  <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e('1. On-screen success message', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <div id="accua_form_success_message">
            <input class="accua_form_check_override"  name="accua_form_success_message" type="radio" value="0" <?php if (!isset($form_overrided_data['success_message'])) {echo ' checked ';} ?>> <?php _e( 'Use the default message', 'contact-forms'); ?>
            <input class="accua_form_check_override"  name="accua_form_success_message" type="radio" value="1" <?php if (isset($form_overrided_data['success_message']) && !isset($form_overrided_data['success_message_no_message'])) {echo ' checked ';} ?>/> <?php _e( 'Customize', 'contact-forms'); ?>
            <input class="accua_form_check_override"  name="accua_form_success_message" type="radio" value="-1" <?php if (isset($form_overrided_data['success_message_no_message'])) {echo ' checked ';} ?>/> <?php _e( 'Don\'t show any messages', 'contact-forms'); ?><br />
            <div class="defalut_message">
              <?php _e( 'Default Success message', 'contact-forms'); ?>
              <div class="defalut_content_message"><?php echo wpautop($default_form_data['success_message']); ?></div>
            </div>
            <?php  wp_editor( $form_data['success_message'] , 'accua_form_success_message_textarea' , $settings_editor);   ?>
            <!--  <textarea class="accua_form_value" style="width:95%"; cols="80" rows="8"><?php echo htmlspecialchars($form_data['success_message'], ENT_QUOTES) ?></textarea> -->
          </div>
      </div>
    </div>
   </div>

   <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e('2. On-screen error message', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <div id="accua_form_error_message">
          <input class="accua_form_check_override"  name="accua_form_error_message" type="radio" value="0" <?php if (!isset($form_overrided_data['error_message'])) {echo ' checked ';} ?>> <?php _e( 'Use the default message', 'contact-forms'); ?>
          <input class="accua_form_check_override"  name="accua_form_error_message" type="radio" value="1" <?php if (isset($form_overrided_data['error_message']) && !isset($form_overrided_data['error_message_no_message'])) {echo ' checked ';} ?>/> <?php _e( 'Customize', 'contact-forms'); ?>
          <input class="accua_form_check_override"  name="accua_form_error_message" type="radio" value="-1" <?php if (isset($form_overrided_data['error_message_no_message'])) {echo ' checked ';} ?>/> <?php _e( 'Don\'t show any messages', 'contact-forms'); ?><br />
          <div class="defalut_message">
            <?php _e( 'Default error message', 'contact-forms'); ?> <br />
            <div  class="defalut_content_message" ><?php echo wpautop($default_form_data['error_message']); ?></div>
          </div>
          <?php  wp_editor( $form_data['error_message'] , 'accua_form_error_message_textarea' , $settings_editor); ?>
          </div>
        </div>
      </div>
   </div>
   <br clear="all"/>
   <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e('3. Email  to notify administrator', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <div id="accua_form_admin_emails_to" class="label_input">
            <label><?php _e('To', 'contact-forms'); ?></label>
            <div class="default_value"><?php echo htmlspecialchars($default_form_data['admin_emails_to']); ?></div>
            <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['admin_emails_to'], ENT_QUOTES) ?>" />
            <input name="accua_form_admin_emails_to" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['admin_emails_to'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Customize', 'contact-forms'); ?>
          </div>
          <div id="accua_form_emails_bcc" class="label_input">
            <label><?php _e('Bcc', 'contact-forms'); ?></label>
            <div class="default_value"><?php echo htmlspecialchars($default_form_data['emails_bcc']); ?></div>
            <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['emails_bcc'], ENT_QUOTES) ?>" />
            <input name ="accua_form_emails_bcc" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['emails_bcc'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Customize', 'contact-forms'); ?>
          </div>

          <div id="accua_form_admin_emails_subject" class="label_input">
            <label><?php _e( 'Subject', 'contact-forms'); ?></label>
            <div class="default_value"><?php echo htmlspecialchars($default_form_data['admin_emails_subject']); ?></div>
            <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['admin_emails_subject'], ENT_QUOTES) ?>" />
            <input name="accua_form_admin_emails_subject" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['admin_emails_subject'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Customize', 'contact-forms'); ?>
          </div>

          <div id="accua_form_admin_emails_message">
            <input class="accua_form_check_override"  name="accua_form_admin_emails_message" type="radio" value="0" <?php if (!isset($form_overrided_data['admin_emails_message'])) {echo ' checked ';} ?>> <?php _e( 'Use the default message', 'contact-forms'); ?>
            <input class="accua_form_check_override"  name="accua_form_admin_emails_message" type="radio" value="1" <?php if (isset($form_overrided_data['admin_emails_message']) && !isset($form_overrided_data['admin_emails_message_no_message'])) {echo ' checked ';} ?>/> <?php _e( 'Customize', 'contact-forms'); ?>
            <input class="accua_form_check_override"  name="accua_form_admin_emails_message" type="radio" value="-1" <?php if (isset($form_overrided_data['admin_emails_message_no_message'])) {echo ' checked ';} ?>/> <?php _e( 'Don\'t show any messages', 'contact-forms'); ?><br />
            <div class="defalut_message">
              <?php _e( 'Default message', 'contact-forms'); ?>
              <div class="defalut_content_message"><?php echo wpautop($default_form_data['admin_emails_message']); ?></div>
            </div>
            <?php  wp_editor( $form_data['admin_emails_message'] , 'accua_form_admin_emails_message_textarea' , $settings_editor);   ?>
          </div>

         </div>
      </div>
   </div>

   <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e('4. Email confirmation to the person who completed the form', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <div id="accua_form_emails_from_name" class="label_input">
            <label><?php _e( 'From name', 'contact-forms'); ?></label>
            <div class="default_value"><?php echo htmlspecialchars($default_form_data['emails_from_name']); ?></div>
            <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['emails_from_name'], ENT_QUOTES) ?>" />
            <input name="accua_form_emails_from_name" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['emails_from_name'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Customize', 'contact-forms'); ?>
          </div>
          <div id="accua_form_emails_from" class="label_input">
            <label><?php _e( 'From email', 'contact-forms'); ?></label>
            <div class="default_value"><?php echo htmlspecialchars($default_form_data['emails_from']); ?></div>
            <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['emails_from'], ENT_QUOTES) ?>" />
            <input name="accua_form_emails_from" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['emails_from'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Customize', 'contact-forms'); ?>
          </div>
          <div id="accua_form_confirmation_emails_subject" class="label_input">
            <label><?php _e( 'Subject', 'contact-forms'); ?></label>
            <div class="default_value"><?php echo htmlspecialchars($default_form_data['confirmation_emails_subject']); ?></div>
            <input class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['confirmation_emails_subject'], ENT_QUOTES) ?>" />
            <input name="accua_form_confirmation_emails_subject" class="accua_form_check_override" type="checkbox" value="1" <?php if (isset($form_overrided_data['confirmation_emails_subject'])) {echo 'checked="checked" ';} ?>/><?php _e( 'Customize', 'contact-forms'); ?>
          </div>

           <div id="accua_form_confirmation_emails_message">
            <input class="accua_form_check_override"  name="accua_form_confirmation_emails_message" type="radio" value="0" <?php if (!isset($form_overrided_data['confirmation_emails_message'])) {echo ' checked ';} ?>> <?php _e( 'Use the default message', 'contact-forms'); ?>
            <input class="accua_form_check_override"  name="accua_form_confirmation_emails_message" type="radio" value="1" <?php if (isset($form_overrided_data['confirmation_emails_message']) && !isset($form_overrided_data['confirmation_emails_message_no_message'])) {echo ' checked ';} ?>/> <?php _e( 'Customize', 'contact-forms'); ?>
            <input class="accua_form_check_override"  name="accua_form_confirmation_emails_message" type="radio" value="-1" <?php if (isset($form_overrided_data['confirmation_emails_message_no_message'])) {echo ' checked ';} ?>/> <?php _e( 'Don\'t show any messages', 'contact-forms'); ?><br />
            <div class="defalut_message">
              <?php _e( 'Default message', 'contact-forms'); ?>
              <div class="defalut_content_message"><?php echo wpautop($default_form_data['confirmation_emails_message']); ?></div>
            </div>
            <?php  wp_editor( $form_data['confirmation_emails_message'] , 'accua_form_confirmation_emails_message_textarea' , $settings_editor);   ?>
          </div>
         </div>
      </div>
   </div>
   <br clear="all"/>

<?php accua_forms_print_tokens(); ?>

<input type="hidden" id="accua_form_save_settings_id" value="<?php echo $fid_esc; ?>" />
</div>
<?php /*
<div id="accua_tab_preview" class="content_tab">
</div> */?>
<p></p>
<input  class="button button-primary button-large accua_form_save_settings_button" id="accua_form_save_settings_2" type="button" value="<?php _e( 'Save settings', 'contact-forms'); ?>" /> <span class="accua_form_save_settings_status"></span>

</div>
<script>
jQuery('input[type=radio]').change(function() {
    var name = jQuery(this).attr('name');
    if (jQuery(this).val() == '1') {
      jQuery('#'+name+' .defalut_message').hide();
      jQuery('#'+name+' .wp-editor-wrap').show();
    }
    else {
      if(jQuery(this).val() != '-1')
        jQuery('#'+name+' .defalut_message').show();
      else
        jQuery('#'+name+' .defalut_message').hide();
      jQuery('#'+name+' .wp-editor-wrap').hide();
    }
});

jQuery('input[type=checkbox]').click(function() {
  var name = jQuery(this).attr('name');
  if (!this.checked) {
    jQuery('#'+name+' .default_value').show();
    jQuery('#'+name+' .accua_form_value, #'+name+' .cp-color-picker').hide();
  }
  else {
    jQuery('#'+name+' .default_value').hide();
    jQuery('#'+name+' .accua_form_value, #'+name+' .cp-color-picker').show();
    //cp-color-picker
  }
});

jQuery(".token_link").click(function() {
  jQuery("#dialog_token").dialog("open");
  return false;
});

//inizializzazione
jQuery(document).ready(function($){
  $('#accua_form_preview_area_wrapper').resizable({handles: 's'});
  $('#accua_form_preview_area').css({
      'width': '100%',
      'height': '100%'
    });
  $.each(
      ['success_message','error_message','admin_emails_message','confirmation_emails_message'],
      function(i,key){
        var value = $('#accua_form_'+key+' .accua_form_check_override:checked').val();
        if(value!=undefined && value!=0) {
          if(value!=-1)
            jQuery('#accua_form_'+key+' .wp-editor-wrap').show();
          else
            jQuery('#accua_form_'+key+' .wp-editor-wrap').hide();
          jQuery('#accua_form_'+key+' .defalut_message').hide();

        }
        else {
            jQuery('#accua_form_'+key+' .wp-editor-wrap').hide();
            jQuery('#accua_form_'+key+' .defalut_message').show();
        }
       }
  );
  $.each(
      ['emails_from_name','emails_from','admin_emails_to','emails_bcc','admin_emails_subject','confirmation_emails_subject','style_margin','style_border_color','style_border_width','style_border_radius','style_background_color','style_padding','style_color','style_font_size','style_field_spacing','style_field_border_color','style_field_border_width','style_field_border_radius','style_field_background_color','style_field_padding','style_field_color','style_submit_border_color','style_submit_border_width','style_submit_border_radius','style_submit_background_color','style_submit_padding','style_submit_color','style_submit_font_size'],
      function(i,key){
        if(!$('#accua_form_'+key+' .accua_form_check_override').is(':checked')) {
          jQuery('#accua_form_'+key+' .default_value').show();
          jQuery('#accua_form_'+key+' .accua_form_value, #accua_form_'+key+' .wp-picker-container').hide();
        }
        else {
          jQuery('#accua_form_'+key+' .default_value').hide();
          jQuery('#accua_form_'+key+' .accua_form_value, #accua_form_'+key+' .wp-picker-container').show();
        }
  });
  $("#dialog_token").dialog({ dialogClass:'wp-dialog' ,autoOpen : false, modal : true, show : "blind", hide : "blind"});

  $('#accua_token a').appendTo('.wp-media-buttons');

});

jQuery(document).ready(function($){

	var originalWidth = $(document).width();
	if(originalWidth <= 883) { //iphone
	  $(".metabox-holder").width('98%');
	}

	$(window).resize(function (e) {
		var newWidth = $(document).width();
		if(newWidth <= 883) {
			if (originalWidth > 883) {
				$(".metabox-holder").width('98%');
			}
		} else if (originalWidth<=883) {
		  $(".metabox-holder").width('47%');
		}
   originalWidth = newWidth;
   });
});

</script>

<?php
  $accuaHelp->finished();
}

function accua_forms_fields_page_head() {
/*
  $baseurl = WP_PLUGIN_URL.'/'.substr(plugin_basename(__FILE__),0,-strlen(basename(__FILE__)));
?>
    <script type="text/javascript" src="<?php echo $baseurl.'/qtip/jquery.qtip-1.0.0-rc3.js'; ?>"></script>
    <script type="text/javascript">
      var avail_fields = {
        first_name: {
          id: "first_name",
          name: "First Name",
          type: "textfield"
        },
        last_name: {
          id: "last_name",
          name: "Last Name",
          type: "textfield"
        },
        email: {
          id: "email",
          name: "Email",
          type: "email"
        }
      };

      jQuery(function($){
        $avail = $('#available_fields_container');
        for(var id in avail_fields) {
          var field = avail_fields[id];
          var el = $('<div></div>').text(field.name);
          el.prepend('<input type="checkbox" />');
          var content = $('<div><span>Id: </span></div>');
          content.append($('<input type="text" />').val(field.id));
          content.append($('<br /><span>Name: </span>'));
          content.append($('<input type="text" />').val(field.name));
          content.append($('<br /><span>Type: </span>'));
          content.append($('<select><option value="textfield">Textfield</option><option value="textarea">Textarea</option><option value="email">Email</option></select>').val(field.type));
          el.qtip({
            content: {
              text: content
            },
            position: {
              target: 'mouse',
              corner: {
                target: 'bottomRight',
                tooltip: 'topLeft'
              },
              adjust: {
                mouse: false
              }
            },
            show: {
              when: {
                event: 'mouseover'
              },
              solo: true
            },
            hide: {
              when: {
                event: 'unfocus'
              }
            }
          });
          $avail.append(el);
        }
      });
    </script>
    <style type="text/css">
      .container > div {
        padding: 0px;
        margin-bottom: 6px;
      }
    </style>
<?php
*/
}

function accua_forms_fields_get_types() {
  return array(
    'textfield' => __( 'Text Field', 'contact-forms'),
    'textarea' => __( 'Text Area', 'contact-forms'),
    'email' =>  __( 'Email', 'contact-forms'),
    'autoreply_email' => __( 'Autoreply Email', 'contact-forms'),
    'checkbox' => __( 'Checkbox','contact-forms'),
    'select' => __('Select',  'contact-forms'),
    'radio' => __( 'Radio buttons', 'contact-forms'),
    'multiselect' => __( 'Multiple selections area', 'contact-forms'),
    'multicheckbox' => __( 'Multiple checkboxes', 'contact-forms'),
    'post-select' => __( 'Post select', 'contact-forms'),
    'post-multicheckbox' => __( 'Multiple post checkboxes', 'contact-forms'),
    'colorpicker' => __( 'Color picker', 'contact-forms'),
    'hidden' => __('Hidden value',  'contact-forms'),
    'file' => __('File upload',  'contact-forms'),
    'submit' => __( 'Submit button', 'contact-forms'),
    'html' => __( 'Custom HTML', 'contact-forms'),
    'captcha' => __( 'Captcha', 'contact-forms'),
    'password' => 'Password',
    'password-and-confirm' => __( 'Password and password confirmation','contact-forms'),
    'date' => __( 'Date','contact-forms'),
  );
}

function accua_forms_filter_date($value){
  if (($value !== '') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
    try {
      $date = new DateTime($value);
      if ($date) {
        return $value;
      }
    } catch (Exception $e) {
    }
  }
  return '';
}

function accua_forms_fields_filter_values($post, $old_data = array()) {
  //TODO: funzione di validazione dei field
  $data = array(
    'version' => 2,
    'id' => $post['form-field-id'],
    'name' => $post['form-field-name'],
    'type' => $post['form-field-type'],
    'description' => $post['form-field-description'],
    'default_value' => $post['form-field-default-value'],
    'default_date_value' => $post['form-field-default-date-value'],
    'allowed_values' => $post['form-field-allowed-values'],
    'allowed_extensions' => '',
    'min_date' => $post['form-field-min-of-date'],
    'max_date' => $post['form-field-max-of-date'],
  );
  $valid = true;
  $message = '';

  $types = accua_forms_fields_get_types();
  if (!isset($types[$data['type']])) {
    $message .= "<p>".__('Invalid type', 'contact-forms')."</p>";
    $valid = false;
    $data['type'] = 'textfield';
  }

  if (!current_user_can('unfiltered_html')) {
    $filter_fields = array('name', 'description', 'default_value', 'allowed_values');
    foreach ($filter_fields as $k) {
      $data[$k] = wp_kses($data[$k], 'post');
    }
  }

  if ($data['type'] == 'file') {
    $data['allowed_extensions'] = accua_forms_filter_extensions($data['allowed_values']);
  }

  $dates = array(
    'default_date_value' => __('Invalid default date', 'contact-forms'),
    'min_date' => __('Invalid min date', 'contact-forms'),
    'max_date' => __('Invalid max date', 'contact-forms'),
  );
  foreach ($dates as $k => $errormsg) {
    if ($data[$k] !== '') {
      $data[$k] = accua_forms_filter_date($data[$k]);
      if ($data[$k] === '') {
        $message .= "<p>".$errormsg."</p>";
        $valid = false;
      }
    }
  }

  return array(
    'data' => $data,
    'valid' => $valid,
    'message' => $message,
  );
}

function accua_forms_fields_page() {
/*
?>
<div class="wrap"><h2>Edit Form Fields</h2>
  <div id="available_fields_container" class="container"></div>
</div>
<?php
*/
  $message = '';
  $taxonomy = '';
  $post_type = '';
  
  /*
  $avail_fields = array(
    'first_name' => array (
      'id' => "first_name",
      'name' => "First Name",
      'type' => "textfield",
      'description' => 'This is the first name',
    ),
    'last_name' => array (
      'id' => "last_name",
      'name' => "Last Name",
      'type' => "textfield",
      'description' => 'This is the last name',
    ),
    'email' => array (
      'id' => "email",
      'name' => "Email",
      'type' => "email",
      'description' => 'This is the email',
    ),
  );
  */

  $avail_fields = get_option('accua_forms_avail_fields', array());

  $default_form_values = array(
    'version' => 1,
    'id' => '',
    'name' => '',
    'type' => 'textfield',
    'description' => '',
    'default_value' => '',
    'default_date_value' => '',
    'allowed_values' => '',
    'allowed_extensions' => '',
    'min_date' => '',
    'max_date' => '',
  );

  $editing = false;
  $adding = true;

  if (!empty($_POST['action'])) {
    check_admin_referer('edit_form_field', '_wpnonce_edit_form_field');
    $post = stripslashes_deep($_POST) + $default_form_values;
    switch($post['action']) {
      case 'edit-form-field':
        if (empty($avail_fields[$post['form-field-id']])) {
          $message .= 'Field "'.htmlspecialchars(sanitize_text_field($post['form-field-id'])).'" doesn\'t exists';
        } else {
          if (empty($post['delete-field'])) {
            $filtered_data = accua_forms_fields_filter_values($post, $avail_fields[$post['form-field-id']]);
            $avail_fields[$post['form-field-id']] = $filtered_data['data'];
            $message .= sprintf( __( 'Field "%s" updated', 'contact-forms'), htmlspecialchars($post['form-field-id']) );
            do_action('accua_forms_field_updated', $avail_fields[$post['form-field-id']]);
          } else {
            $deleting_field = $avail_fields[$post['form-field-id']];
            unset ($avail_fields[$post['form-field-id']]);
            $message .= sprintf( __( 'Field "%s" deleted', 'contact-forms'), htmlspecialchars($post['form-field-id']) );
            do_action('accua_forms_field_deleted', $deleting_field);
          }
          update_option('accua_forms_avail_fields', $avail_fields);
        }
      break;
      case 'add-form-field':
        $fill_form_fields = true;
        $valid = true;
        if (empty($post['form-field-id']) || !preg_match('/^[a-z0-9_-]+$/i', $post['form-field-id'])) {
          $message .= "<p>".__( 'Only letters, numbers, hyphen and underscores allowed in field identificative slug', 'contact-forms')."</p>";
          $valid = false;
        }
        if(substr($post['form-field-id'], 0, 2) == '__') {
          $message .= "<p>".__( 'The field identificative slug can\'t start with two underscores (__)', 'contact-forms')."</p>";
          $valid = false;
        }
        if (!empty($avail_fields[$post['form-field-id']])) {
          $message .= sprintf( __( '<p>A field with identificative slug "%s" already exists</p> Field "%s" deleted', 'contact-forms'), htmlspecialchars($post['form-field-id']) );
          $valid = false;
        }
        if (strlen($post['form-field-id']) > 70) {
          $message .= "<p>".__( 'The identificative slug cannot be longer than 70 characters', 'contact-forms')."</p>";
          $valid = false;
        }
        $filtered_data = accua_forms_fields_filter_values($post);
        $message .= $filtered_data['message'];
        $valid = $valid && $filtered_data['valid'];
        if ($valid) {
          $fill_form_fields = false;
          $avail_fields[$post['form-field-id']] = $filtered_data['data'];
          update_option('accua_forms_avail_fields', $avail_fields);
          $message .= sprintf( __( 'Field "%s" created', 'contact-forms'), htmlspecialchars($post['form-field-id']) );
          do_action('accua_forms_field_added', $avail_fields[$post['form-field-id']]);
        }
        if ($fill_form_fields) {
          $editing = true;
          $default_form_values = $filtered_data['data'];
        }
      break;
    }
  } else if (!empty($_GET['edit-fid'])) {
    $fid = stripslashes($_GET['edit-fid']);
    if (empty($avail_fields[$fid])) {
      $message .= sprintf( __( 'Field "%s" doesn\'t exists', 'contact-forms'), htmlspecialchars($fid) );
    } else {
      $adding = false;
      $editing = true;
      $default_form_values = $avail_fields[$fid] + $default_form_values;
    }
  }
  if ($default_form_values['version'] >= 2 && $default_form_values['type'] == 'file') {
    //Show allowed_extensions value in allowed_values field
    $default_form_values['allowed_values'] = $default_form_values['allowed_extensions'];
  }

?>
<div id="accua_forms_fields_page" class="accua_forms_admin_page wrap nosubsub">
<h2><img src="<?php echo ACCUA_FORMS_DIR_URL.'img/cimatti-icon-20.png'; ?>"/> <?php _e('Contact Forms - Fields', 'contact-forms'); ?></h2>
<?php /* screen_icon(); ?>
<h2><?php echo esc_html( $title );
if ( !empty($_REQUEST['s']) )
  printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_html( stripslashes($_REQUEST['s']) ) ); ?>
</h2>

<?php if ( isset($_REQUEST['message']) && ( $msg = (int) $_REQUEST['message'] ) ) : ?>
<div id="message" class="updated"><p><?php echo $messages[$msg]; ?></p></div>
<?php $_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
endif; */ ?>
<div id="ajax-response"><?php echo $message?></div>

<?php /*
<form class="search-form" action="" method="get">
<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); ?>" />
<input type="hidden" name="post_type" value="<?php echo esc_attr($post_type); ?>" />

<?php $wp_list_table->search_box( $tax->labels->search_items, 'tag' ); ?>

</form>
*/ ?>

<br class="clear" />

<div id="col-container">

<div id="col-right">
<div class="col-wrap">
<?php if (!$editing) { ?>
<form id="posts-filter" action="" method="post">
<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); /* TODO: Is this needed? */ ?>" />
<input type="hidden" name="post_type" value="<?php echo esc_attr($post_type); /* TODO: Is this needed? */ ?>" />

<?php /* $wp_list_table->display(); */ ?>

<table cellspacing="0" class="wp-list-table widefat fixed tags">
  <thead>
  <tr>
    <th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox" /></th>
    <th style="" class="manage-column column-name" id="name" scope="col"><?php _e( 'Label', 'contact-forms'); ?></th>
    <th style="" class="manage-column column-description" id="description" scope="col"><?php _e( 'Description', 'contact-forms'); ?></th>
    <th style="" class="manage-column column-slug" id="slug" scope="col"><?php _e( 'Slug', 'contact-forms'); ?></th>
    <th style="" class="manage-column column-type" id="type" scope="col"><?php _e( 'Type', 'contact-forms'); ?></th>
  </tr>
  </thead>

  <tfoot>
  <tr>
    <th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox" /></th>
    <th style="" class="manage-column column-name" scope="col"><?php _e( 'Label', 'contact-forms'); ?></th>
    <th style="" class="manage-column column-description" scope="col"><?php _e( 'Description', 'contact-forms'); ?></th>
    <th style="" class="manage-column column-slug" scope="col"><?php _e( 'Slug', 'contact-forms'); ?></th>
    <th style="" class="manage-column column-type" scope="col"><?php _e( 'Type', 'contact-forms'); ?></th>
  </tr>
  </tfoot>

  <tbody class="list:tag" id="the-list">
<?php
  foreach ($avail_fields as $id => $field) {
    foreach (array('id', 'name', 'type', 'description') as $i) {
      $field[$i] = htmlspecialchars($field[$i], ENT_QUOTES);
      $field[$i] = sanitize_text_field($field[$i]);
    }
    echo <<<END_OF_ROW
    <tr id="field-{$field['id']}">
      <th class="check-column" scope="row"><input type="checkbox" /></th>
      <td class="name column-name"><strong><a title="Edit “{$field['name']}”" href="admin.php?page=accua_forms_fields&amp;edit-fid={$field['id']}" class="row-title">{$field['name']}</a></strong><br><div class="row-actions"><span class="edit"><a href="admin.php?page=accua_forms_fields&amp;edit-fid={$field['id']}">Edit</a></span></div></td>
      <td class="description column-description">{$field['description']}</td>
      <td class="slug column-slug">{$field['id']}</td>
      <td class="type column-type">{$field['type']}</td>
    </tr>
END_OF_ROW;
  }
?>
  </tbody>
</table>

<br class="clear" />
</form>
<?php } ?>
<?php /* if ( 'category' == $taxonomy ) : ?>
<div class="form-wrap">
<p><?php printf(__('<strong>Note:</strong><br />Deleting a category does not delete the posts in that category. Instead, posts that were only assigned to the deleted category are set to the category <strong>%s</strong>.'), apply_filters('the_category', get_cat_name(get_option('default_category')))) ?></p>
<?php if ( current_user_can( 'import' ) ) : ?>
<p><?php printf(__('Categories can be selectively converted to tags using the <a href="%s">category to tag converter</a>.'), 'import.php') ?></p>
<?php endif; ?>
</div>
<?php elseif ( 'post_tag' == $taxonomy && current_user_can( 'import' ) ) : ?>
<div class="form-wrap">
<p><?php printf(__('Tags can be selectively converted to categories using the <a href="%s">tag to category converter</a>'), 'import.php') ;?>.</p>
</div>
<?php endif;
do_action('after-' . $taxonomy . '-table', $taxonomy);
*/ ?>

</div>
</div><!-- /col-right -->

<div id="col-left">
<div class="col-wrap">

<?php
/*
if ( !is_null( $tax->labels->popular_items ) ) {
  if ( current_user_can( $tax->cap->edit_terms ) )
    $tag_cloud = wp_tag_cloud( array( 'taxonomy' => $taxonomy, 'echo' => false, 'link' => 'edit' ) );
  else
    $tag_cloud = wp_tag_cloud( array( 'taxonomy' => $taxonomy, 'echo' => false ) );

  if ( $tag_cloud ) :
  ?>
<div class="tagcloud">
<h3><?php echo $tax->labels->popular_items; ?></h3>
<?php echo $tag_cloud; unset( $tag_cloud ); ?>
</div>
<?php
endif;
}
*/

/*
if ( current_user_can($tax->cap->edit_terms) ) {
  // Back compat hooks. Deprecated in preference to {$taxonomy}_pre_add_form
  if ( 'category' == $taxonomy )
    do_action('add_category_form_pre', (object)array('parent' => 0) );
  elseif ( 'link_category' == $taxonomy )
    do_action('add_link_category_form_pre', (object)array('parent' => 0) );
  else
    do_action('add_tag_form_pre', $taxonomy);

  do_action($taxonomy . '_pre_add_form', $taxonomy);
*/

$types = accua_forms_fields_get_types();
?>
<div class="form-wrap">
<h3><?php echo $adding? __( 'Add new field','contact-forms'): __( 'Edit field','contact-forms') ; ?></h3>
<form id="addtag" method="post" action="admin.php?page=accua_forms_fields" class="validate">
<input type="hidden" name="action" value="<?php echo $adding?'add':'edit'; ?>-form-field" />
<?php /*
<input type="hidden" name="screen" value="<?php echo esc_attr($current_screen->id); ?>" />
<input type="hidden" name="taxonomy" value="<?php echo esc_attr($taxonomy); ?>" />
<input type="hidden" name="post_type" value="<?php echo esc_attr($post_type); ?>" />
*/ ?>
<?php wp_nonce_field('edit_form_field', '_wpnonce_edit_form_field'); ?>

<div class="form-field form-required">
  <label for="tag-name"><?php _e( 'Field label', 'contact-forms'); ?></label>
  <input name="form-field-name" id="tag-name" type="text" value="<?php echo htmlspecialchars($default_form_values['name'], ENT_QUOTES) ?>" size="40" aria-required="true" />
  <p><?php _e('The name is how it appears on your site.', 'contact-forms'); ?></p>
</div>
<?php /* if ( ! global_terms_enabled() ) : */ ?>
<div class="form-field">
  <label for="tag-slug"><?php _e( 'Field slug (identificative)', 'contact-forms'); ?></label>
  <input name="form-field-id" id="tag-slug" type="text" value="<?php echo htmlspecialchars($default_form_values['id'], ENT_QUOTES) ?>" <?php if (!$adding) { echo 'disabled="disabled"'; } ?> size="40" />
  <?php if (!$adding) { echo '<input type="hidden" name="form-field-id" value="'.htmlspecialchars($default_form_values['id'], ENT_QUOTES).'" />'; } ?>
  <p><?php _e('The &#8220;slug&#8221; is the URL-friendly version of the name. It is used as an identificator, and is unchangeable. It is usually all lowercase and it must contains only letters, numbers, and underscores.', 'contact-forms'); ?></p>
</div>
<div class="form-field">
  <label for="parent"><?php _e( 'Field type', 'contact-forms'); ?></label>
  <select class="postform" id="parent" name="form-field-type">
  <?php /*
    <option value="textfield" class="level-0" <?php echo ($default_form_values['type'] == 'textfield')?'selected="selected"':'';?> >Text Field</option>
    <option value="textarea" class="level-0" <?php echo ($default_form_values['type'] == 'textarea')?'selected="selected"':'';?> >Text Area</option>
    <option value="email" class="level-0" <?php echo ($default_form_values['type'] == 'email')?'selected="selected"':'';?> >Email</option>
    <option value="checkbox" class="level-0" <?php echo ($default_form_values['type'] == 'checkbox')?'selected="selected"':'';?> >Checkbox</option>
    <option value="select" class="level-0" <?php echo ($default_form_values['type'] == 'select')?'selected="selected"':'';?> >Select</option>
  */
  foreach ($types as $typeid => $typename) {
    $selected = ($default_form_values['type'] == $typeid)?'selected="selected"':'';
    echo <<<EOT
<option value="{$typeid}" class="level-0" {$selected} >{$typename}</option>
EOT;
  }

  ?>
  </select>
</div>
<?php /* endif; // global_terms_enabled() */ ?>
<?php /* if ( is_taxonomy_hierarchical($taxonomy) ) : ?>
<div class="form-field">
  <label for="parent"><?php _ex('Parent', 'Taxonomy Parent'); ?></label>
  <?php wp_dropdown_categories(array('hide_empty' => 0, 'hide_if_empty' => false, 'taxonomy' => $taxonomy, 'name' => 'parent', 'orderby' => 'name', 'hierarchical' => true, 'show_option_none' => __('None'))); ?>
  <?php if ( 'category' == $taxonomy ) : // @todo: Generic text for hierarchical taxonomies ?>
    <p><?php _e('Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.'); ?></p>
  <?php endif; ?>
</div>
<?php endif; // is_taxonomy_hierarchical() */ ?>
<div class="form-field">
  <label for="tag-description"><?php _e( 'Field description', 'contact-forms'); ?></label>
  <textarea name="form-field-description" id="tag-description" rows="5" cols="40"><?php echo htmlspecialchars($default_form_values['description'], ENT_QUOTES) ?></textarea>
  <p><?php _e('The description is not prominent by default; however, some themes may show it.', 'contact-forms'); ?></p>
</div>

<div class="form-field">
   <label for="form-field-default-value"><?php _e( 'Default value(s)', 'contact-forms'); ?>:</label>
   <textarea name="form-field-default-value" id="form-field-default-value" rows="5" cols="40"><?php echo htmlspecialchars($default_form_values['default_value'], ENT_QUOTES) ?></textarea>
   <p><?php _e( 'For multiple default values in multiple select and multiple checkboxes, use | as separator.', 'contact-forms'); ?></p>
</div>

<div class="form-field">
   <label for="form-field-allowed-values"><?php _e( 'Allowed values', 'contact-forms'); ?>:</label>
    <textarea rows="5" cols="40" name="form-field-allowed-values" id=form-field-allowed-values"><?php echo htmlspecialchars($default_form_values['allowed_values'], ENT_QUOTES) ?></textarea>
    <p><?php _e( 'Options used in select, radio and multiple checkboxes. Enter one value per line, in the format key|label. The key is the value that will be stored in the database. The label is optional, and the key will be used as the label if no label is specified. For file fields, this indicates allowed extensions (one per line without dot)', 'contact-forms'); ?></p>
</div>

<div class="form-field">
<?php _e( 'Settings for date fields', 'contact-forms'); ?>
<div class="form-field">
   <label for="form-field-default-date-value"><?php _e( 'Default value', 'contact-forms'); ?>:</label>
   <input type="date" name="form-field-default-date-value" id="form-field-default-date-value" value="<?php echo htmlspecialchars($default_form_values['default_date_value'], ENT_QUOTES) ?>">
</div>


<label for="form-field-min-of-date"><?php _e( 'Min date', 'contact-forms'); ?>:</label>
<input type="date" id="form-field-min-of-date" name="form-field-min-of-date" value="<?php echo htmlspecialchars($default_form_values['min_date'], ENT_QUOTES) ?>">

<label for="form-field-max-of-date"><?php _e( 'Max date', 'contact-forms'); ?>:</label>
<input type="date" id="form-field-max-of-date" name="form-field-max-of-date" value="<?php echo htmlspecialchars($default_form_values['max_date'], ENT_QUOTES) ?>">

</div>


<?php
/*
if ( ! is_taxonomy_hierarchical($taxonomy) )
  do_action('add_tag_form_fields', $taxonomy);
do_action($taxonomy . '_add_form_fields', $taxonomy);
*/

if ($adding) {
  submit_button( __( 'Add new field', 'contact-forms'), 'button' );
} else {
  submit_button( __( 'Save changes', 'contact-forms'), 'button' );
  submit_button( __( 'Delete field', 'contact-forms'), 'button', 'delete-field');
}

/*
// Back compat hooks. Deprecated in preference to {$taxonomy}_add_form
if ( 'category' == $taxonomy )
  do_action('edit_category_form', (object)array('parent' => 0) );
elseif ( 'link_category' == $taxonomy )
  do_action('edit_link_category_form', (object)array('parent' => 0) );
else
  do_action('add_tag_form', $taxonomy);

do_action($taxonomy . '_add_form', $taxonomy);
*/
?>
</form></div>
<?php /* } */ ?>

</div>
</div><!-- /col-left -->

</div><!-- /col-container -->
</div><!-- /wrap -->
<?php
  /* echo '<pre>accua_forms_avail_fields:', htmlspecialchars(print_r($avail_fields, true)), '</pre>'; */
}

function accua_forms_settings_page() {
?>
<div id="accua_forms_settings_page" class="accua_forms_admin_page wrap">
<h2><img src="<?php echo ACCUA_FORMS_DIR_URL.'img/cimatti-icon-20.png'; ?>"/> <?php _e('Contact Forms - Default Settings', 'contact-forms'); ?></h2>
<?php
  $empty_form_data = array(
    'success_message' => '',
    'error_message' => '',
    'emails_from_name' => '',
    'emails_from' => '',
    'admin_emails_to' => '',
    'emails_bcc' => '',
    'admin_emails_subject' => '',
    'admin_emails_message' => '',
    'confirmation_emails_subject' => '',
    'confirmation_emails_message' => '',
    'layout' => 'sidebyside',
    'style_margin' => '',
    'style_border_color' => '',
    'style_border_width' => '',
    'style_border_radius' => '',
    'style_background_color' => '',
    'style_padding' => '',
    'style_color' => '',
    'style_font_size' => '',
    'style_field_spacing' => '',
    'style_field_border_color' => '',
    'style_field_border_width' => '',
    'style_field_border_radius' => '',
    'style_field_background_color' => '',
    'style_field_padding' => '',
    'style_field_color' => '',
    'style_submit_border_color' => '',
    'style_submit_border_width' => '',
    'style_submit_border_radius' => '',
    'style_submit_background_color' => '',
    'style_submit_padding' => '',
    'style_submit_color' => '',
    'style_submit_font_size' => '',
  );

  $empty_file_data = array(
    'valid_extensions' => '',
    'max_size' => '',
    'dest_path' => '',
  );

  $empty_captcha_data = array(
    'recaptcha_force_v1' => '',
    'recaptcha_public_key' => '',
    'recaptcha_private_key' => '',
  );

  $empty_analytics_data = array(
    'analytics_track_submit' => false,
    'analytics_track_fields' => false,
  );

  $empty_anonymize_ip_data = array(
    'anonymize_ip_bytes' => 0,
  );

  if($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['accua_form_save_form_settings'])) {
    check_admin_referer('accua_form_save_settings', '_nonce_accua_form_save_settings');
    $post = stripslashes_deep($_POST);
    $post += $empty_form_data;
    $post += $empty_file_data;
    $post += $empty_captcha_data;
    $post += $empty_analytics_data;
    $post += $empty_anonymize_ip_data;
    $form_data = array();
    $file_data = array();
    $captcha_data = array();
    $analytics_data = array();
    $anonymize_ip_data = array();
    foreach($empty_form_data as $key=>$val){
      $form_data[$key] = $post[$key];
    }
    $form_data = accua_forms_filter_settings($form_data);

    $file_data['valid_extensions'] = accua_forms_filter_extensions($post['valid_extensions']);
    $file_data['max_size'] = $post['max_size'];
    if (current_user_can('edit_files') || current_user_can('install_plugins')) {
      $file_data['dest_path'] = $post['dest_path'];
    } else {
      $old_file_data = get_option('accua_forms_default_file_field_data',array());
      $file_data['dest_path'] = isset($old_file_data['dest_path']) ? $old_file_data['dest_path'] : '';
    }

    $captcha_data['recaptcha_force_v1'] = (bool) $post['recaptcha_force_v1'];
    $captcha_data['recaptcha_public_key'] = sanitize_text_field($post['recaptcha_public_key']);
    $captcha_data['recaptcha_private_key'] = sanitize_text_field($post['recaptcha_private_key']);

    foreach($empty_analytics_data as $key=>$val){
      $analytics_data[$key] = (bool) $post[$key];
    }

    /* foreach($empty_anonymize_ip_data as $key=>$val){
      $anonymize_ip_data[$key] = $post[$key];
    } */
    $anonymize_ip_bytes = (int) $post['anonymize_ip_bytes'];
    if ($anonymize_ip_bytes < 0) {
      $anonymize_ip_bytes = 0;
    } else if ($anonymize_ip_bytes > 4) {
      $anonymize_ip_bytes = 4;
    }
    $anonymize_ip_data['anonymize_ip_bytes'] = $anonymize_ip_bytes;

    update_option('accua_forms_default_form_data', $form_data);
    update_option('accua_forms_default_file_field_data', $file_data);
    update_option('accua_forms_default_captcha_field_data', $captcha_data);
    update_option('accua_forms_default_analytics_data', $analytics_data);
    update_option('accua_forms_anonymize_ip_data', $anonymize_ip_data);

    if (!empty($post['delete_previous_ip_values'])) {
      global $wpdb;
      $wpdb->query("UPDATE `{$wpdb->prefix}accua_forms_submissions` SET afs_ip = ''");
    }
  } else {
    $form_data = get_option('accua_forms_default_form_data',array()) + $empty_form_data;
    $file_data = get_option('accua_forms_default_file_field_data',array()) + $empty_file_data;
    $captcha_data = get_option('accua_forms_default_captcha_field_data',array()) + $empty_captcha_data;
    $analytics_data = get_option('accua_forms_default_analytics_data',array()) + $empty_analytics_data;
    $anonymize_ip_data = get_option('accua_forms_anonymize_ip_data',array()) + $empty_anonymize_ip_data;
  }
  if ($captcha_data['recaptcha_force_v1']) {
    $captcha_data = $empty_captcha_data;
  }
?>
<form method="post">
<?php wp_nonce_field('accua_form_save_settings', '_nonce_accua_form_save_settings'); ?>
<input type="hidden" name="accua_form_save_form_settings" value="1" />
<?php /*
<p id="accua_form_layout"><?php _e( 'Layout', 'contact-forms'); ?>: <select name="layout" class="accua_form_value"><option value="sidebyside" <?php if ($form_data['layout'] == 'sidebyside') { echo 'selected="selected"'; } ?>>Labels on the left of the fields</option><option value="toplabel" <?php if ($form_data['layout'] == 'toplabel') { echo 'selected="selected"'; } ?>>Labels on top of the fields</option></select></p>
<p id="accua_form_success_message"><?php _e( 'Success message', 'contact-forms'); ?>:<br /><textarea name="success_message" class="accua_form_value" style="width:95%"; cols="80" rows="8"><?php echo htmlspecialchars($form_data['success_message'], ENT_QUOTES) ?></textarea></p>
<p id="accua_form_error_message"><?php _e( 'Error message', 'contact-forms'); ?>:<br /><textarea name="error_message" class="accua_form_value" style="width:95%"; cols="80" rows="8"><?php echo htmlspecialchars($form_data['error_message'], ENT_QUOTES) ?></textarea></p>
<p id="accua_form_emails_from"><?php _e( 'Emails from', 'contact-forms'); ?>: <input name="emails_from" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['emails_from'], ENT_QUOTES) ?>" /></p>
<p id="accua_form_admin_emails_to"><?php _e( 'Admin emails to', 'contact-forms'); ?>: <input name="admin_emails_to" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['admin_emails_to'], ENT_QUOTES) ?>" /></p>
<p id="accua_form_emails_bcc"><?php _e( 'Emails bcc', 'contact-forms'); ?>: <input name="emails_bcc" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['emails_bcc'], ENT_QUOTES) ?>" /></p>
<p id="accua_form_admin_emails_subject"><?php _e( 'Admin email subject', 'contact-forms'); ?>: <input name="admin_emails_subject" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['admin_emails_subject'], ENT_QUOTES) ?>" /></p>
<p id="accua_form_admin_emails_message"><?php _e( 'Admin email message', 'contact-forms'); ?>:<br /><textarea name="admin_emails_message" class="accua_form_value" style="width:95%"; cols="80" rows="8"><?php echo htmlspecialchars($form_data['admin_emails_message'], ENT_QUOTES) ?></textarea></p>
<p id="accua_form_confirmation_emails_subject"><?php _e( 'Confirmation email subject', 'contact-forms'); ?>: <input name="confirmation_emails_subject" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['confirmation_emails_subject'], ENT_QUOTES) ?>" /></p>
<p id="accua_form_confirmation_emails_message"><?php _e( 'Confirmation email message', 'contact-forms'); ?>:<br /><textarea name="confirmation_emails_message" class="accua_form_value" style="width:95%"; cols="80" rows="8"><?php echo htmlspecialchars($form_data['confirmation_emails_message'], ENT_QUOTES) ?></textarea></p>
*/ ?>
<div id="accua_tab_messages" class="content_tab">
 <?php
  $settings_editor = array(
    'teeny' => true,
    'editor_class' => 'accua_form_value',
    'tinymce' => array(
        'theme_advanced_buttons1' => 'bold,italic,underline,|,bullist,numlist,'));
  ?>
  <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e('1. On-screen success message', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <div id="accua_form_success_message">
            <?php  wp_editor( $form_data['success_message'] , 'success_message' , $settings_editor);   ?>
          </div>
      </div>
    </div>
   </div>

   <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e('2. On-screen error message', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <div id="accua_form_error_message">
           <?php  wp_editor( $form_data['error_message'] , 'error_message' , $settings_editor); ?>
          </div>
        </div>
      </div>
   </div>
   <br clear="all"/>
   <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e('3. Email  to notify administrator', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <div id="accua_form_admin_emails_to" class="label_input">
            <label><?php _e( 'To', 'contact-forms'); ?></label>
            <input name="admin_emails_to" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['admin_emails_to'], ENT_QUOTES) ?>" />
          </div>
          <br clear="all" />
          <div id="accua_form_emails_bcc" class="label_input">
            <label><?php _e( 'Bcc', 'contact-forms'); ?></label>
            <input name="emails_bcc" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['emails_bcc'], ENT_QUOTES) ?>" />
          </div>
          <br clear="all" />
          <div id="accua_form_admin_emails_subject" class="label_input">
            <label><?php _e( 'Subject', 'contact-forms'); ?></label>
            <input name="admin_emails_subject" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['admin_emails_subject'], ENT_QUOTES) ?>" />
          </div>
          <br clear="all" />
          <div id="accua_form_admin_emails_message">
            <?php  wp_editor( $form_data['admin_emails_message'] , 'admin_emails_message' , $settings_editor);   ?>
          </div>

         </div>
      </div>
   </div>

   <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e('4. Email confirmation to the person who completed the form', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <div id="accua_form_emails_from_name" class="label_input">
            <label><?php _e( 'From name', 'contact-forms'); ?></label>
            <input class="accua_form_value" name="emails_from_name" type="text" value="<?php echo htmlspecialchars($form_data['emails_from_name'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_emails_from" class="label_input">
            <label><?php _e( 'From email', 'contact-forms'); ?></label>
            <input class="accua_form_value" name="emails_from" type="text" value="<?php echo htmlspecialchars($form_data['emails_from'], ENT_QUOTES) ?>" />
          </div>
          <br clear="all" />
          <div id="accua_form_confirmation_emails_subject" class="label_input">
            <label><?php _e( 'Subject', 'contact-forms'); ?></label>
            <input class="accua_form_value" type="text" name="confirmation_emails_subject" value="<?php echo htmlspecialchars($form_data['confirmation_emails_subject'], ENT_QUOTES) ?>" />
          </div>
          <br clear="all" />
           <div id="accua_form_confirmation_emails_message">
              <?php  wp_editor( $form_data['confirmation_emails_message'] , 'confirmation_emails_message' , $settings_editor);   ?>
          </div>
         </div>
      </div>
   </div>
   <br clear="all"/>

   <div class="metabox-holder accua-forms-metabox-holder">
     <div class="postbox ">
       <h3 class="hndle"><span><?php _e( 'File upload default settings', 'contact-forms'); ?></span></h3>
       <div class="inside" id="dashboard_right_now">
         <div id="accua_form_valid_extensions"><?php _e( 'Valid extensions', 'contact-forms'); ?> <br /><textarea name="valid_extensions" class="accua_form_value" style="width:95%"; cols="80" rows="8"><?php echo htmlspecialchars($file_data['valid_extensions'], ENT_QUOTES) ?></textarea>
           <small><?php _e( 'List of valid extensions, without dot, one per line.', 'contact-forms'); ?></small>
         </div>
         <div id="accua_form_max_size"><?php _e( 'Maximum file size:', 'contact-forms'); ?> <input name="max_size" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($file_data['max_size'], ENT_QUOTES) ?>" /><br />
           <small><?php _e( 'You can use suffix K, M or G for kilobyte, megabyte or gigabyte.', 'contact-forms'); ?>
           <?php
             $server_max_size = AccuaForm_Element_File::file_upload_max_size();
             if ($server_max_size > 0) {
               _e( 'This value is limited by server upload limits of ', 'contact-forms');
               echo  AccuaForm_Element_File::format_size($server_max_size).". ";
               _e( 'If you need a greater limit you should ask to the server administrator.', 'contact-forms');
             }
           ?>
           </small>
         </div>
         <?php if (current_user_can('edit_files') || current_user_can('install_plugins')) { ?>
         <div id="accua_form_dest_path"><?php _e( 'Upload path', 'contact-forms');?> : <input name="dest_path" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($file_data['dest_path'], ENT_QUOTES) ?>" />
           <small><?php _e( 'If it stars with \'/\' an absolute path is used, otherwise a path relative to the WordPress installation directory. Default value is "wp-content/uploads/accua-forms"', 'contact-forms');?>.</small>
         </div>
         <?php } ?>
       </div>
     </div>

     <div class="postbox ">
       <h3 class="hndle"><span><?php _e( 'reCaptcha settings', 'contact-forms'); ?></span></h3>
       <div class="inside" id="dashboard_right_now">
         <p><?php _e( 'As reCAPTCHA v1 is discontinued, only reCAPTCHA v2 is supported', 'contact-forms');?></p>
         <p><?php echo strtr(__('Please register this site for reCAPTCHA v2 on %REGISTERURL%, then  enter the keys for this site in the following fields', 'contact-forms'), array('%REGISTERURL%' => '<a href="https://www.google.com/recaptcha" target="_blank">google.com/recaptcha</a>'));?></p>
         <div id="accua_form_recaptcha_public_key"><?php _e('Site key', 'contact-forms');?> : <input name="recaptcha_public_key" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($captcha_data['recaptcha_public_key'], ENT_QUOTES) ?>" />
         </div>
         <div id="accua_form_recaptcha_private_key"><?php _e('Secret key', 'contact-forms');?> : <input name="recaptcha_private_key" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($captcha_data['recaptcha_private_key'], ENT_QUOTES) ?>" />
         </div>
       </div>
     </div>

     <div class="postbox ">
       <h3 class="hndle"><span><?php _e( 'IP address tracking', 'contact-forms'); ?></span></h3>
       <div class="inside" id="dashboard_accua_form_anonymize_ip_addresses">
         <p>
           <?php _e("Select how many bytes of the visitor's IPs should be masked.", 'contact-forms');?>
         </p>
         <p>
           <input type="radio" name="anonymize_ip_bytes" id="anonymize_ip_bytes_0" value="0" <?php if($anonymize_ip_data['anonymize_ip_bytes'] == 0) { echo 'checked="checked"'; } ?> />
             <label for="anonymize_ip_bytes_0"><?php _e('No mask - e.g. 192.168.1.1', 'contact-forms');?></label><br />
         </p>
         <p>
           <input type="radio" name="anonymize_ip_bytes" id="anonymize_ip_bytes_1" value="1" <?php if($anonymize_ip_data['anonymize_ip_bytes'] == 1) { echo 'checked="checked"'; } ?> />
             <label for="anonymize_ip_bytes_1"><?php _e('1 byte - e.g. 192.168.1.xxx', 'contact-forms');?></label><br />
         </p>
         <p>
           <input type="radio" name="anonymize_ip_bytes" id="anonymize_ip_bytes_2" value="2" <?php if($anonymize_ip_data['anonymize_ip_bytes'] == 2) { echo 'checked="checked"'; } ?> />
             <label for="anonymize_ip_bytes_2"><?php _e('2 byte - e.g. 192.168.xxx.xxx', 'contact-forms');?></label><br />
         </p>
         <p>
           <input type="radio" name="anonymize_ip_bytes" id="anonymize_ip_bytes_3" value="3" <?php if($anonymize_ip_data['anonymize_ip_bytes'] == 3) { echo 'checked="checked"'; } ?> />
             <label for="anonymize_ip_bytes_3"><?php _e('3 byte - e.g. 192.xxx.xxx.xxx', 'contact-forms');?></label><br />
         </p>
         <p>
           <input type="radio" name="anonymize_ip_bytes" id="anonymize_ip_bytes_4" value="4" <?php if($anonymize_ip_data['anonymize_ip_bytes'] == 4) { echo 'checked="checked"'; } ?> />
             <label for="anonymize_ip_bytes_4"><?php _e('Fully mask IP address', 'contact-forms');?></label><br />
         </p>
         <h4><?php _e( 'Delete IP addresses', 'contact-forms'); ?></h4>
         <p>
           <input type="checkbox" id="delete_previous_ip_values" name="delete_previous_ip_values" class="accua_form_value" value="1" />
           <label for="delete_previous_ip_values"><?php _e('Delete all previous IP values', 'contact-forms');?></label><br />
         </p>
         <br clear="all">
       </div>
     </div>

     <div class="postbox ">
       <h3 class="hndle"><span><?php _e( 'Track actions with Google Analytics and/or Matomo', 'contact-forms'); ?></span></h3>
       <div class="inside" id="dashboard_right_now">
         <p>
           <input type="checkbox" id="accua_form_analytics_track_submit" name="analytics_track_submit" class="accua_form_value" value="1" <?php if($analytics_data['analytics_track_submit']) { echo 'checked="checked"'; } ?> />
           <label for="accua_form_analytics_track_submit"><?php _e('Track submissions', 'contact-forms');?></label>
         </p>
         <p>
           <input type="checkbox" id="accua_form_analytics_track_fields" name="analytics_track_fields" class="accua_form_value" value="1" <?php if($analytics_data['analytics_track_fields']) { echo 'checked="checked"'; } ?> />
           <label for="accua_form_analytics_track_fields"><?php _e('Track fields filled in', 'contact-forms');?></label>
         </p>
       </div>
     </div>

   </div>

   <div class="metabox-holder accua-forms-metabox-holder">
      <div class="postbox ">
        <h3 class="hndle"><span><?php _e( 'Layout &amp; Styling', 'contact-forms'); ?></span></h3>
        <div class="inside" id="dashboard_right_now">
          <p><?php _e( 'Customize the look and feel of your forms. Leave fields empty if you wish to use the native styles of your WordPress Theme.', 'contact-forms'); ?><p>
          <h4><?php _e( 'Forms', 'contact-forms'); ?></h4>
          <div id="accua_form_layout"> <?php _e( 'Layout', 'contact-forms'); ?>
            <select name="layout" class="accua_form_value"><option value="sidebyside" <?php if ($form_data['layout'] == 'sidebyside') { echo 'selected="selected"'; } ?>>Labels on the left of the fields</option><option value="toplabel" <?php if ($form_data['layout'] == 'toplabel') { echo 'selected="selected"'; } ?>>Labels on top of the fields</option></select>
          </div>
          <div id="accua_form_style_margin" class="label_input">
            <label><?php _e( 'Margin', 'contact-forms'); ?></label>
            <input name="style_margin" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_margin'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_border_color" class="label_input">
            <label><?php _e( 'Border color', 'contact-forms'); ?></label>
            <input name="style_border_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_border_color'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_border_width" class="label_input">
            <label><?php _e( 'Border width', 'contact-forms'); ?></label>
            <input name="style_border_width" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_border_width'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_border_radius" class="label_input">
            <label><?php _e( 'Rounded corner radius', 'contact-forms'); ?></label>
            <input name="style_border_radius" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_border_radius'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_background_color" class="label_input">
            <label><?php _e( 'Background color', 'contact-forms'); ?></label>
            <input name="style_background_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_background_color'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_padding" class="label_input">
            <label><?php _e( 'Padding', 'contact-forms'); ?></label>
            <input name="style_padding" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_padding'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_color" class="label_input">
            <label><?php _e( 'Text color', 'contact-forms'); ?></label>
            <input name="style_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_color'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_font_size" class="label_input">
            <label><?php _e( 'Font size', 'contact-forms'); ?></label>
            <input name="style_font_size" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_font_size'], ENT_QUOTES) ?>" />
          </div>

          <h4><?php _e( 'Fields', 'contact-forms'); ?></h4>
          <div id="accua_form_style_field_spacing" class="label_input">
            <label><?php _e( 'Spacing', 'contact-forms'); ?></label>
            <input name="style_field_spacing" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_spacing'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_field_border_color" class="label_input">
            <label><?php _e( 'Border color', 'contact-forms'); ?></label>
            <input name="style_field_border_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_border_color'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_field_border_width" class="label_input">
            <label><?php _e( 'Border width', 'contact-forms'); ?></label>
            <input name="style_field_border_width" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_border_width'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_field_border_radius" class="label_input">
            <label><?php _e( 'Rounded corner radius', 'contact-forms'); ?></label>
            <input name="style_field_border_radius" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_border_radius'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_field_background_color" class="label_input">
            <label><?php _e( 'Background color', 'contact-forms'); ?></label>
            <input name="style_field_background_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_background_color'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_field_padding" class="label_input">
            <label><?php _e( 'Padding', 'contact-forms'); ?></label>
            <input name="style_field_padding" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_padding'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_field_color" class="label_input">
            <label><?php _e( 'Text color', 'contact-forms'); ?></label>
            <input name="style_field_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_field_color'], ENT_QUOTES) ?>" />
          </div>

          <h4><?php _e( 'Submit button', 'contact-forms'); ?></h4>
          <div id="accua_form_style_submit_border_color" class="label_input">
            <label><?php _e( 'Border color', 'contact-forms'); ?></label>
            <input name="style_submit_border_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_border_color'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_submit_border_width" class="label_input">
            <label><?php _e( 'Border width', 'contact-forms'); ?></label>
            <input name="style_submit_border_width" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_border_width'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_submit_border_radius" class="label_input">
            <label><?php _e( 'Rounded corner radius', 'contact-forms'); ?></label>
            <input name="style_submit_border_radius" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_border_radius'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_submit_background_color" class="label_input">
            <label><?php _e( 'Background color', 'contact-forms'); ?></label>
            <input name="style_submit_background_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_background_color'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_submit_padding" class="label_input">
            <label><?php _e( 'Padding', 'contact-forms'); ?></label>
            <input name="style_submit_padding" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_padding'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_submit_color" class="label_input">
            <label><?php _e( 'Text color', 'contact-forms'); ?></label>
            <input name="style_submit_color" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_color'], ENT_QUOTES) ?>" />
          </div>
          <div id="accua_form_style_submit_font_size" class="label_input">
            <label><?php _e( 'Font size', 'contact-forms'); ?></label>
            <input name="style_submit_font_size" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($form_data['style_submit_font_size'], ENT_QUOTES) ?>" />
          </div>
          <br clear="all" />
      </div>
    </div>
   </div>


   <br clear="all"/>

</div>

<?php /*
<h3><?php _e( 'File upload default settings', 'contact-forms'); ?></h3>
<p id="accua_form_valid_extensions"><?php _e( 'Valid extensions', 'contact-forms'); ?> <br /><textarea name="valid_extensions" class="accua_form_value" style="width:95%"; cols="80" rows="8"><?php echo htmlspecialchars($file_data['valid_extensions'], ENT_QUOTES) ?></textarea>
  <small><?php _e( 'List of valid extensions, without dot, one per line.', 'contact-forms'); ?></small>
</p>
<p id="accua_form_max_size"><?php _e( 'Maximum file size:', 'contact-forms'); ?> <input name="max_size" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($file_data['max_size'], ENT_QUOTES) ?>" /><br />
  <small><?php _e( 'You can use suffix K, M or G for kilobyte, megabyte or gigabyte.', 'contact-forms'); ?>
<?php
    $server_max_size = AccuaForm_Element_File::file_upload_max_size();
    if ($server_max_size > 0) {
      _e( 'This value is limited by server upload limits of ', 'contact-forms');
      echo  AccuaForm_Element_File::format_size($server_max_size).". ";
      _e( 'If you need a greater limit you should ask to the server administrator.', 'contact-forms');
    }
?>
  </small></p>
<p id="accua_form_dest_path"><?php _e( 'Upload path', 'contact-forms');?> : <input name="dest_path" class="accua_form_value" type="text" value="<?php echo htmlspecialchars($file_data['dest_path'], ENT_QUOTES) ?>" />
  <small><?php _e( 'If it stars with \'/\' an absolute path is used, otherwise a path relative to the WordPress installation directory. Default value is "wp-content/uploads/accua-forms"', 'contact-forms');?>.</small>
</p> */ ?>
<p><input class="button button-primary button-large" id="accua_form_save_settings" type="submit" value="Save settings" /></p>

<?php accua_forms_print_tokens(); ?>

</form>
</div>

<script type='text/javascript'>
jQuery(function($) {
  $('#accua_form_style_border_color .accua_form_value').colorPicker();
  $('#accua_form_style_background_color .accua_form_value').colorPicker();
  $('#accua_form_style_color .accua_form_value').colorPicker();
  $('#accua_form_style_field_border_color .accua_form_value').colorPicker();
  $('#accua_form_style_field_background_color .accua_form_value').colorPicker();
  $('#accua_form_style_field_color .accua_form_value').colorPicker();
  $('#accua_form_style_submit_border_color .accua_form_value').colorPicker();
  $('#accua_form_style_submit_background_color .accua_form_value').colorPicker();
  $('#accua_form_style_submit_color .accua_form_value').colorPicker();
});
</script>

<?php
}

function _accua_forms_get_abs_dest_path($dest_path = '') {
  if ($dest_path === '') {
    return realpath(ABSPATH) . '/wp-content/uploads/accua-forms';
  } else if (substr($dest_path,0,1) === '/') {
    return $dest_path;
  } else {
    return realpath(ABSPATH) . '/' . $dest_path;
  }
}

function _accua_forms_get_form_data($fid = false, $return_empty = true, $restore_trash = false){
  $empty_form_data = array(
    'fields' => array(),
    'title' => '',
    'success_message' => '',
    'error_message' => '',
    'emails_from_name' => '',
    'emails_from' => '',
    'admin_emails_to' => '',
    'emails_bcc' => '',
    'admin_emails_subject' => '',
    'admin_emails_message' => '',
    'confirmation_emails_subject' => '',
    'confirmation_emails_message' => '',
    'use_ajax' => true,
    'layout' => 'sidebyside',
    'style_margin' => '',
    'style_border_color' => '',
    'style_border_width' => '',
    'style_border_radius' => '',
    'style_background_color' => '',
    'style_padding' => '',
    'style_color' => '',
    'style_font_size' => '',
    'style_field_spacing' => '',
    'style_field_border_color' => '',
    'style_field_border_width' => '',
    'style_field_border_radius' => '',
    'style_field_background_color' => '',
    'style_field_padding' => '',
    'style_field_color' => '',
    'style_submit_border_color' => '',
    'style_submit_border_width' => '',
    'style_submit_border_radius' => '',
    'style_submit_background_color' => '',
    'style_submit_padding' => '',
    'style_submit_color' => '',
    'style_submit_font_size' => '',
  );

  if ($fid === false) {
    return $empty_form_data;
  }

  $default_form_data = get_option('accua_forms_default_form_data',array());

  if ($fid === null) {
    return $default_form_data + $empty_form_data;
  }

  $forms_data = get_option('accua_forms_saved_forms', array());
  if ($restore_trash) {
    $trash_data = get_option('accua_forms_trash_forms', array());
    if (isset($trash_data[$fid])) {
      $forms_data[$fid] = $trash_data[$fid];
      update_option('accua_forms_saved_forms', $forms_data);
      unset($trash_data[$fid]);
      update_option('accua_forms_trash_forms', $trash_data);
    }
  }

  if (isset($forms_data[$fid])) {
    $form_data = array(
        '_overrided' => $forms_data[$fid]
      ) + $forms_data[$fid] + $default_form_data + $empty_form_data;
    /*
    if ($form_data['fields']) {
      foreach ($form_data['fields'] as $i => $istance_data) {
        // TODO: popuplate default fields data?
      }
    }
    */
    return $form_data;
  } else if ($return_empty) {
    return array(
        '_overrided' => array()
      ) + $default_form_data + $empty_form_data;
  } else {
    return null;
  }

}

function _accua_forms_style_parameters($params) {
  $ret = '';
  foreach ($params as $key => $value) {
    $value = trim($value);
    if ($value !== '') {
      if (is_numeric($value)) {
        $ret .= "{$key}:{$value}px;";
      } else {
        $ret .= "{$key}:{$value};";
      }
      if ($key == 'border-width') {
        $ret .= "border-style:solid;";
      }
    }
  }
  return $ret;
}

/*
 * generazione del form (e della preview)
 * */
add_action('accua_form_alter', 'accua_forms_form_generate', -999, 2);
function accua_forms_form_generate($baseid, $form) {
  if (substr($baseid, 0, 14) == '__accua-form__') {
    $fid = substr($baseid,14);
    $form_data = _accua_forms_get_form_data($fid, false);
    /*
    echo '<!-- fid = ';
    print_r($fid);
    echo "\n\nform_data = ";
    print_r($form_data);
    echo "\n-->";
    */
    if ($form_data) {
      $form_style = _accua_forms_style_parameters(array(
        'margin' => $form_data['style_margin'],
        'border-color' => $form_data['style_border_color'],
        'border-width' => $form_data['style_border_width'],
        'border-radius' => $form_data['style_border_radius'],
        'background' => $form_data['style_background_color'],
        'padding' => $form_data['style_padding'],
        'color' => $form_data['style_color'],
        'font-size' => $form_data['style_font_size'],
      ));

      $field_style = _accua_forms_style_parameters(array(
        'margin-bottom' => $form_data['style_field_spacing'],
        'border-color' => $form_data['style_field_border_color'],
        'border-width' => $form_data['style_field_border_width'],
        'border-radius' => $form_data['style_field_border_radius'],
        'background' => (trim($form_data['style_field_background_color']) === '')?'transparent':$form_data['style_field_background_color'],
        'padding' => $form_data['style_field_padding'],
        'color' => (trim($form_data['style_field_color']) === '')?$form_data['style_color']:$form_data['style_field_color'],
        'font-size' => $form_data['style_font_size'],
      ));
      $field_properties = array();
      if ($field_style !== '') {
        $field_properties['style'] = $field_style;
      }

      $submit_style = _accua_forms_style_parameters(array(
        'border-color' => $form_data['style_submit_border_color'],
        'border-width' => $form_data['style_submit_border_width'],
        'border-radius' => $form_data['style_submit_border_radius'],
        'background' => $form_data['style_submit_background_color'],
        'padding' => $form_data['style_submit_padding'],
        'color' => $form_data['style_submit_color'],
        'font-size' => $form_data['style_submit_font_size'],
      ));
      $submit_properties = array();
      if ($submit_style !== '') {
        $submit_properties['style'] = $submit_style;
      }

      if ($form_style !== '') {
        $form->configure(array('style' => $form_style));
      }

      if (!empty($form_data['use_ajax'])){
        $form->configure(array(
          "accua_ajax" => 1,
        ));
      }

      $add_submit = true;
      $fieldset_open = false;
      $avail_fields = get_option('accua_forms_avail_fields', array());

      foreach ($form_data['fields'] as $istance_data) {
        if (empty($avail_fields[$istance_data['ref']])) {
          $field_data = array();
          if (!empty($istance_data['ref'])) {
            if ($istance_data['ref'] == '__fieldset-begin') {
              $field_data = array(
                'id' => '__fieldset-begin',
                'name' => __('Fieldset begin', 'contact-forms'),
                'type' => 'fieldset-begin',
                'description' => '',
              );
            } else if ($istance_data['ref'] == '__fieldset-end') {
              $field_data = array(
                'id' => '__fieldset-end',
                'name' => __('Fieldset end', 'contact-forms'),
                'type' => 'fieldset-end',
                'description' => '',
              );
            }
          }
        } else {
          $field_data = $avail_fields[$istance_data['ref']];
        }

        $field_data += array(
          'version' => 1,
          'id' => '__html',
          'name' => __( 'Custom HTML content', 'contact-forms'),
          'type' => 'html',
          'description' => __('Use this special field to inject raw HTML in the form. You can use this multiple times.', 'contact-forms'),
          'default_value' => '',
          'allowed_values' => '',
          'allowed_extensions' => '',
        );

        $istance_data += array(
          'version' => 1,
        );

        if ($field_data['type'] == 'file') {
          if ($field_data['version'] < 2) {
            $field_data['allowed_extensions'] = $field_data['allowed_values'];
          }
          if ($istance_data['version'] < 2 && isset($istance_data['allowed_values'])) {
            $istance_data['allowed_extensions'] = $istance_data['allowed_values'];
          }
        }

        /* prendo i dati relativi ai campi di tipo date
        lo faccio prima, così default value in questo modo viene sovrascritto se un campo è di un tipo diverso

        NB: se i campi sono già sovrascritti, si trovano già in istance_data */
        if ($field_data['type'] == 'date'){
        	$istance_data += array(
              'default_value' => $field_data['default_date_value'],
	          'min_date' => $field_data['min_date'],
	          'max_date' => $field_data['max_date'],
	        );
        }

        $istance_data += array(
          'istance_id' => $field_data['id'],
          'widget_number' => '',
          'ref' => $field_data['id'],
          'label' => $field_data['name'],
          'default_value' => $field_data['default_value'], /* viene impostato il valore di defualt se non è un campo data */
          'allowed_values' => $field_data['allowed_values'],
          'allowed_extensions' => $field_data['allowed_extensions'],
        );

        $element = NULL;
        $element_conf = NULL;

        $allowed_val = trim($istance_data['allowed_values']);

        if ($field_data['type'] == 'post-multicheckbox' || $field_data['type'] == 'post-select') {
          $posts = accua_get_pages($allowed_val);
          $allowed_values = array();
          foreach ($posts as $p) {
            //$allowed_values[$p->ID] = apply_filters( 'the_title', $p->post_title, $p->ID );
            $allowed_values[$p->ID] = $p->post_title;
          }
        } else {
          if ($field_data['type'] == 'file') {
            $filedata = get_option('accua_forms_default_file_field_data',array());
            $filedata += array(
              'valid_extensions' => '',
              'max_filesize' => '',
              'dest_path' => '',
            );
            $allowed_val = accua_forms_filter_extensions($istance_data['allowed_extensions']);
            if ($allowed_val == '') {
              $allowed_val = accua_forms_filter_extensions($filedata['valid_extensions']);
            }
          }

          $allowed_val = explode("\n",$allowed_val);
          $allowed_values = array();
          foreach ($allowed_val as $val) {
            $val = explode('|',$val,2);
            $val[0] = trim($val[0]);
            /*
            if (empty($val[0])) {
              continue;
            }
            */
            if ((!isset($val[1])) || (trim($val[1])==='')) {
              if ($val[0] === '') {
                continue;
              } else {
                $val[1] = $val[0];
              }
            }
            $allowed_values[$val[0]] = $val[1];
          }
        }

        switch ($field_data['type']) {
          case 'textarea':
            $element = new Element_Textarea($istance_data['label'], $istance_data['istance_id'], $field_properties+array('cols' => '50', 'value'=>$istance_data['default_value']));
          break;
          case 'hidden':
            $element = new Element_Hidden($istance_data['istance_id'], $istance_data['default_value']);
          break;
          case 'checkbox':
            $lab = $istance_data['label'];
            if (!empty($istance_data['required'])) {
              $lab .= ' <strong>*</strong>';
            }
            if ($allowed_values) {
              reset($allowed_values);
              $val = (string) key($allowed_values);
              $defval = trim($istance_data['default_value']);
            } else if ($istance_data['default_value'] == '1') {
              $defval = $val = '1';
            } else {
              $val = empty($istance_data['default_value'])?'1':$istance_data['default_value'];
              $defval = '';
            }
            $element = new AccuaForm_Element_Checkbox('', $istance_data['istance_id'], array($val => $lab), array('value' => $defval));
          break;
          case 'select':
          case 'post-select':
            if (!isset($allowed_values[''])) {
              $allowed_values = array('' => '') + $allowed_values;
            }
            $defval = trim($istance_data['default_value']);
            $element = new AccuaForm_Element_Select($istance_data['label'], $istance_data['istance_id'], $allowed_values, $field_properties+array('value'=>$defval));
          break;
          case 'radio':
            $defval = trim($istance_data['default_value']);
            $element = new AccuaForm_Element_Radio($istance_data['label'], $istance_data['istance_id'], $allowed_values, array('value'=>$defval));
          break;
          case 'multiselect':
            $defval = explode('|', $istance_data['default_value']);
            foreach ($defval as $k => $v) {
              $defval[$k] = trim($v);
            }
            $element = new AccuaForm_Element_Select($istance_data['label'], $istance_data['istance_id'], $allowed_values, $field_properties+array('multiple' => true, 'value'=>$defval));
          break;
          case 'multicheckbox':
          case 'post-multicheckbox':
            $defval = explode('|', $istance_data['default_value']);
            foreach ($defval as $k => $v) {
              $defval[$k] = trim($v);
            }
            $element = new AccuaForm_Element_Checkbox($istance_data['label'], $istance_data['istance_id'], $allowed_values, array('value'=>$defval));
          break;
          case 'file':
            $fdata = array();

            if ($allowed_values) {
              $fdata['validExtensions'] = array_keys($allowed_values);
            }

            if (!empty($filedata['max_size'])){
              $fdata['maxSize'] = $filedata['max_size'];
            }

            $fdata['destPath'] = _accua_forms_get_abs_dest_path($filedata['dest_path']);

            $element = new AccuaForm_Element_File($istance_data['label'], $istance_data['istance_id'], $field_properties+$fdata);
          break;
          case 'html':
            $element = new Element_HTML($istance_data['default_value']);
          break;
          case 'email':
          case 'autoreply_email':
            $element = new AccuaForm_Element_Email($istance_data['label'], $istance_data['istance_id'], $field_properties+array('value'=>$istance_data['default_value']));
            $element->setValidation(new Validation_Email(
              str_replace('%element%', $istance_data['label'], __("Attention: '%element%' must contain an email address.", 'contact-forms'))
              ));
            //"Errore: '{$istance_data['label']}' deve contenere un indirizzo email valido."
          break;
          case 'colorpicker':
            $element = new AccuaForm_Element_ColorPicker($istance_data['label'], $istance_data['istance_id'], $field_properties+array('value'=>$istance_data['default_value']));
          break;
          case 'fieldset-begin':
            if ($fieldset_open) {
              $form->addElement(new AccuaForm_Element_FieldsetEnd());
            } else {
              $fieldset_open = true;
            }
            $element = new AccuaForm_Element_FieldsetBegin($istance_data['label'], $istance_data['istance_id']);
          break;
          case 'fieldset-end':
            if ($fieldset_open) {
              $element = new AccuaForm_Element_FieldsetEnd();
              $fieldset_open = false;
            }
          break;
          case 'submit':
            $add_submit = false;
            $element = new Element_Button($istance_data['label'], 'submit', $submit_properties+array('name' => $istance_data['istance_id'], 'value' => $istance_data['default_value']));
          break;
          case 'captcha':
              $empty_captcha_data = array(
                'recaptcha_force_v1' => '',
                'recaptcha_public_key' => '',
                'recaptcha_private_key' => '',
              );
              $captcha_data = get_option('accua_forms_default_captcha_field_data',array()) + $empty_captcha_data;
              $captcha_properties = array("description" => "");
              $captcha_use_v1 = true;
              if (($captcha_data['recaptcha_public_key'] !== '') && ($captcha_data['recaptcha_private_key'] !== '')) {
                $captcha_properties['privateKey'] = $captcha_data['recaptcha_private_key'];
                $captcha_properties['publicKey'] = $captcha_data['recaptcha_public_key'];
                $captcha_use_v1 = $captcha_data['recaptcha_force_v1'];
              }
              if ($captcha_use_v1) {
                $element = new Element_HTML("\n\n<!-- ReCaptcha 1 is discontinued, please go to Contact Forms settings page and set reCaptcha v2 keys -->\n\n");
              } else {
                $element = new AccuaForm_Element_Captcha2 ($istance_data['label'], '', $captcha_properties);
              }
          break;
		      case 'password':
            $element = new Element_Password($istance_data['label'], $istance_data['istance_id'], $field_properties+array('value'=>$istance_data['default_value']));
			    break;
		      case 'password-and-confirm':
			      $id_2 = "___{$istance_data['istance_id']}___confirmpass";
            $element = new Element_Password(__("Password", 'contact-forms'), $istance_data['istance_id'], $field_properties+array('value'=>$istance_data['default_value']));
			      $element_conf = new Element_Password(__("Confirm password", 'contact-forms'), $id_2, $field_properties+array('value'=>$istance_data['default_value']));
			      $element_conf_validator = new AccuaForm_Validation_Password();
			      $element_conf_validator->configure(array('otherPasswordFieldName'=>$istance_data['istance_id']));
            $element_conf->setValidation($element_conf_validator);
          break;
          case 'date':
            $element = new AccuaForm_Element_Date($istance_data['label'], $istance_data['istance_id'], $field_properties+array('value'=>$istance_data['default_value'], 'minDate'=>$istance_data['min_date'], 'maxDate'=>$istance_data['max_date']));
          break;
          //case 'textfield':
          default:
            $element = new Element_Textbox($istance_data['label'], $istance_data['istance_id'], $field_properties+array('value'=>$istance_data['default_value']));
          break;
        }
        if ($element) {
          if (!empty($istance_data['required'])) {
            $element->setClass('accuaforms-field-required');
            if($field_data['type'] == 'captcha' && empty($captcha_use_v1)) {
              //nothing
            } else if($field_data['type'] == 'password-and-confirm') {
              $element->setValidation(new Validation_Required(
                str_replace('%element%', $istance_data['label'], __("Attention: Passwords are required fields.", 'contact-forms'))
              ));
            } else {
              $element->setValidation(new Validation_Required(
                str_replace('%element%', $istance_data['label'], __("Attention: '%element%' is a required field.", 'contact-forms'))
              ));
            }
          }

          if ($elementName = $element->getName()) {
            $element->setClass('accuaform-fieldname-'.$elementName);
          }

          if ($field_data['type']) {
            $element->setClass('accuaform-fieldtype-'.$field_data['type']);
          }

          $form->addElement($element);
          if($element_conf!=NULL) {
            $form->addElement($element_conf);
            $element_conf=NULL;
          }
        }
      }
      if ($fieldset_open) {
        $form->addElement(new AccuaForm_Element_FieldsetEnd());
        $fieldset_open = false;
      }
      if ($add_submit) {
        $form->addElement(new Element_Button(__('Submit', 'contact-forms'), 'submit', $submit_properties));
      }
    }

  }
}

function accua_forms_aggregate_submitted_data(&$replace_map, $params = array()) {
  if (empty($params['txt']) && empty($params['html']) && empty($params['json']) && empty($params['email'])) {
    $params += array(
      'txt' => true,
      'html' => true,
      'json' => true,
      'email' => true,
    );
  }

  if (!empty($params['txt'])) {
    $replace_map['__submitted_txt'] = implode("\n",$replace_map['__submitted_txt_raw']);
  }
  if (!empty($params['html'])) {
    $replace_map['__submitted_html'] = implode('</td></tr><tr><td>',$replace_map['__submitted_html_raw']);
  }
  if (!empty($params['json'])) {
    $replace_map['__submitted_json'] = _accua_forms_json_encode($replace_map['__submitted_json_raw']);
  }
  if (!empty($params['email'])) {
    $replace_map['__autoreply_email'] = implode('; ', $replace_map['__autoreply_email_raw']);
  }

}

add_filter('accua_form_validate', 'accua_forms_validation_handler', 10, 4);
function accua_forms_validation_handler($valid, $submittedID, $submittedData, $form){
  if (substr($submittedID, 0, 14) == '__accua-form__') {
    $fid = substr($submittedID,14);
    $form_data = _accua_forms_get_form_data($fid, false);
    if ($form_data) {
      return apply_filters('accua_forms_validation', $valid, $fid, $submittedData, $form);
    }
  }
  return $valid;
}

function accua_forms_anonymize_ip($ip) {
  $ip = (string) $ip;
  $anonymize_ip_data = get_option('accua_forms_anonymize_ip_data',array());
  if (empty($anonymize_ip_data['anonymize_ip_bytes'])) {
    return $ip;
  }
  switch ($anonymize_ip_data['anonymize_ip_bytes']) {
    case 1:
      $ip = preg_replace('/\.[^.]+$/', '.xxx', $ip);
    break;
    case 2:
      $ip = preg_replace('/\.[^.]+\.[^.]+$/', '.xxx.xxx', $ip);
    break;
    case 3:
      $ip = preg_replace('/\.[^.]+\.[^.]+\.[^.]+$/', '.xxx.xxx.xxx', $ip);
    break;
    case 4:
      $ip = '';
  }
  return $ip;
}

add_action('accua_form_submit', 'accua_forms_form_submission_handler', -10, 3);
function accua_forms_form_submission_handler($submittedID, $submittedData, $form) {
  if (empty($GLOBALS['wp_rewrite'])) {
    $GLOBALS['wp_rewrite'] = new WP_Rewrite();
  }

  if (!class_exists('AccuaConditionalReplacer')){
    require_once('AccuaConditionalReplacer.php');
  }

  if (substr($submittedID, 0, 14) == '__accua-form__') {
    $fid = substr($submittedID,14);
    $form_data = _accua_forms_get_form_data($fid, false);
    if ($form_data) {
      global $wpdb;

      $time = time();

      $afs_stats = _accua_forms_json_encode(array(
        'user_agent' => $form->stats['user_agent'],
        'platform' => $form->stats['platform'],
        'tentatives' => $form->stats['tentatives'],
        'submit_method' => $form->stats['submit_method'],
      ));

      $anonymized_ip = accua_forms_anonymize_ip($form->stats['ip']);

      $insert_ret = $wpdb->insert(
        $wpdb->prefix . 'accua_forms_submissions',
        array (
          'afs_form_id' => (string) $fid,
          'afs_post_id' => (string) $form->stats['pid'],
          'afs_ip' => $anonymized_ip,
          'afs_uri' => (string) $form->stats['uri'],
          'afs_referrer' => (string) $form->stats['referrer'],
          'afs_lang' => (string) $form->stats['lang'],
          'afs_created' => (string) gmdate('Y-m-d H:i:s', $form->stats['created']),
          'afs_submitted' => (string) gmdate('Y-m-d H:i:s', $time),
          'afs_stats' => (string) $afs_stats,
        )
      );

      if ($insert_ret) {
        $submission_id = $form->stats['submission_id'] = $wpdb->insert_id;
      } else {
        $submission_id = $form->stats['submission_id'] = 0;
        error_log("[WordPress Contact Forms] unable to save submitted form data");
      }

      $review_submission_url = admin_url('admin.php').'?page=accua_forms_submissions_list&sid='.$submission_id;

      $replace_map = array(
        '__fid' => $fid,
        '__subid' => $submission_id,
        '__pid' => $form->stats['pid'],
        '__ip' => $form->stats['ip'],
        '__anonymized_ip' => $anonymized_ip,
        '__uri' => $form->stats['uri'],
        '__url' => $form->stats['url'],
        '__referrer' => $form->stats['referrer'],
        '__lang' => $form->stats['lang'],
        '__locale' => $form->stats['locale'],
        '__created' => $form->stats['created'],
        '__created_day' => date('l j F Y', $form->stats['created']),
        '__created_day_month_year' => date('j F Y', $form->stats['created']),
        '__created_hour' => date('G:i', $form->stats['created']),
        '__submitted' => $time,
        '__submitted_day' => date('l j F Y', $time),
        '__submitted_day_month_year' => date('j F Y', $time),
        '__submitted_hour' => date('G:i', $time),
        '__confirmation_emails_message' => $form_data['confirmation_emails_message'],
        '__user_agent' => $form->stats['user_agent'],
        '__platform' => $form->stats['platform'],
        '__tentatives' => $form->stats['tentatives'],
        '__submit_method' => $form->stats['submit_method'],
        '__review_submission_url' => $review_submission_url,
      );

      $avail_fields = get_option('accua_forms_avail_fields', array());

      $replace_map['__autoreply_email_raw'] = array();
      $replace_map['__submitted_txt_raw'] = array();
      $replace_map['__submitted_html_raw'] = array();
      $replace_map['__submitted_json_raw'] = array();

      $_field_data = array();
      $_istance_data = array();
      $field_data = array();

      foreach ($submittedData as $istance_id => $value) {
        if (empty($form_data['fields'][$istance_id])) {
          continue;
        }
        $istance_data = $form_data['fields'][$istance_id];

        if (empty($avail_fields[$istance_data['ref']])) {
          $fieldset_data = array();
          if (!empty($istance_data['ref'])) {
            if ($istance_data['ref'] == '__fieldset-begin') {
              $field_data = array(
                'id' => '__fieldset-begin',
                'name' => __('Fieldset begin', 'contact-forms'),
                'type' => 'fieldset-begin',
                'description' => '',
              );
            } else if ($istance_data['ref'] == '__fieldset-end') {
              $field_data = array(
                'id' => '__fieldset-end',
                'name' => __('Fieldset end', 'contact-forms'),
                'type' => 'fieldset-end',
                'description' => '',
              );
            }
          }
        } else {
          $field_data = $avail_fields[$istance_data['ref']];
        }

        $field_data += array(
          'id' => '__html',
          'name' => __( 'Custom HTML content', 'contact-forms'),
          'type' => 'html',
          'description' => __( 'Use this special field to inject raw HTML in the form. You can use this multiple times.', 'contact-forms'),
          'default_value' => '',
          'allowed_values' => '',
        );

        /*
        echo "<!-- istance_data: "
          , print_r($istance_data, true)
          , "\nfield_data: "
          , print_r($field_data, true)
          , "\nvalue: "
          , print_r($value, true)
          , "\n-->\n";
        */

        $istance_data += array(
          'istance_id' => $field_data['id'],
          'widget_number' => '',
          'ref' => $field_data['id'],
          'label' => $field_data['name'],
          'default_value' => $field_data['default_value'],
          'allowed_values' => $field_data['allowed_values'],
        );

        $type = $field_data['type'];
        $file_download_url = '';

        switch ($field_data['type']) {
          case 'checkbox':
          case 'submit':
            if (empty($value)) {
              $replace_map[$istance_data['istance_id']] = $value = '';
            } else {
              if (empty($istance_data['default_value'])) {
                $replace_map[$istance_data['istance_id']] = 'Checked';
                $value = '1';
              } else {
                $replace_map[$istance_data['istance_id']] = $value = $istance_data['default_value'];
              }
            }
          break;
          case 'multiselect':
          case 'multicheckbox':
          case 'select':
          case 'radio':
            $el = $form->getElementByName($istance_id);
	          $opts = $el->getOptions();
	          if (!is_array($value)) {
	            $value = array($value);
	          }
	          $label = array();
	          foreach ($value as $val) {
	            if (isset($opts[$val])) {
	              $label[] = $opts[$val];
	            }
	          }
	          $replace_map['__label_'.$istance_data['istance_id']] = implode(", ", $label);
	          $replace_map[$istance_data['istance_id']] = implode(', ',$value);
            $value = implode('|',$value);
          break;
          case 'post-multicheckbox':
          	if (is_array($value) && $value) {
	            $el = $form->getElementByName($istance_id);
	            $opts = $el->getOptions();
	            $value2 = array();
	            $titles = array();
	            $ids = array();
	            $urls = array();
	            foreach ($value as $val) {
								if (isset($opts[$val])) {
								  $titles[] = $opts[$val];
								  $ids[] = $value;
								  $urls[] = get_permalink($value);
	              	$value2[] = $val . ': ' . trim(preg_replace('/[\s\n\r]+/', ' ', $opts[$val]));
	              }
	            }
	            $replace_map['__label_'.$istance_data['istance_id']] = $replace_map['__post_title_'.$istance_data['istance_id']] = implode("\n", $titles);
              $replace_map['__post_id_'.$istance_data['istance_id']] = implode("\n", $ids);
              $replace_map['__post_url_'.$istance_data['istance_id']] = implode("\n", $urls);
	            $replace_map[$istance_data['istance_id']] = $value = implode("\n", $value2);
            } else {
              $replace_map['__label_'.$istance_data['istance_id']] = $replace_map['__post_title_'.$istance_data['istance_id']] = '';
              $replace_map['__post_id_'.$istance_data['istance_id']] = '';
              $replace_map['__post_url_'.$istance_data['istance_id']] = '';
              $replace_map[$istance_data['istance_id']] = $value = '';
            }
          break;
          case 'post-select':
            if ($value !== '') {
              $el = $form->getElementByName($istance_id);
              $opts = $el->getOptions();
              if (isset($opts[$value])) {
                $replace_map['__label_'.$istance_data['istance_id']] = $replace_map['__post_title_'.$istance_data['istance_id']] = $opts[$value];
                $replace_map['__post_id_'.$istance_data['istance_id']] = $value;
                $replace_map['__post_url_'.$istance_data['istance_id']] = get_permalink($value);
                $value = $value . ': ' . trim(preg_replace('/[\s\n\r]+/', ' ', $opts[$value]));
              } else {
                $replace_map['__label_'.$istance_data['istance_id']] = $replace_map['__post_title_'.$istance_data['istance_id']] = '';
                $replace_map['__post_id_'.$istance_data['istance_id']] = '';
                $replace_map['__post_url_'.$istance_data['istance_id']] = '';
                $value = '';
              }
            }
            $replace_map[$istance_data['istance_id']] = $value;
          break;
          case 'autoreply_email':
            if ($value !== '') {
              $replace_map['__autoreply_email_raw'][] = $value;
            }
            $replace_map[$istance_data['istance_id']] = $value;
          break;
          case 'file':
            //TODO: move temp file to "{$submission_id}_{$field_data['id']}_{$file['name']}"; value is $file['name']
            $buildid = $submittedData['_AccuaForm_buildID'];
            $file = $form->getFile($istance_id);
            if ($value !== null && $value !== '' && $file) {
              if ($form->renameFile($istance_id, "{$submission_id}_{$field_data['id']}_{$file['name']}")) {
                $urlfield = rawurlencode($istance_data['istance_id']);
                $urlfile = rawurlencode($value);
                $token = accua_forms_generate_download_token($submission_id);
                $file_download_url = admin_url('admin-ajax.php') . "?action=accua_forms_download_submitted_file&subid={$submission_id}&field={$urlfield}&file={$urlfile}&nonce=" . wp_create_nonce('accua_forms_download_nonce')."&token={$token}";
              }
            }
            $replace_map[$istance_data['istance_id']] = $value;
            $replace_map['__download_'.$istance_data['istance_id']] = $file_download_url;
          break;
          case 'password':
          case 'password-and-confirm':
            $value = trim($value);
            $replace_map[$istance_data['istance_id']] = $value;
            $type = 'hashed-password';
            if ($value !== '') {
              $value = wp_hash_password($value);
            }
            $replace_map['__hashed_'.$istance_data['istance_id']] = $value;
          break;
          default:
            $replace_map[$istance_data['istance_id']] = $value;
        }

        switch ($field_data['type']) {
          case 'file':
            $replace_map['__submitted_txt_raw'][$istance_data['istance_id']] = "{$istance_data['istance_id']}\t$value\t$file_download_url";
            $replace_map['__submitted_json_raw'][$istance_data['istance_id']] = "$value\t$file_download_url";
            $replace_map['__submitted_html_raw'][$istance_data['istance_id']] = "<strong>{$istance_data['istance_id']}</strong></td><td class='valori_submitted'><a href='".htmlspecialchars($file_download_url,ENT_QUOTES)."'>".htmlspecialchars($value)."</a>";
          break;

          case 'email':
          case 'autoreply_email':
            $replace_map['__submitted_txt_raw'][$istance_data['istance_id']] = "{$istance_data['istance_id']}\t$value";
            $replace_map['__submitted_json_raw'][$istance_data['istance_id']] = $value;
            $replace_map['__submitted_html_raw'][$istance_data['istance_id']] = "<strong>{$istance_data['istance_id']}</strong></td><td class='valori_submitted'><a href='mailto:".htmlspecialchars($value,ENT_QUOTES)."'>".htmlspecialchars($value)."</a>";
            break;
          case 'submit':
          break;
          case 'colorpicker':
            $replace_map['__submitted_txt_raw'][$istance_data['istance_id']] = "{$istance_data['istance_id']}\t$value";
            $replace_map['__submitted_json_raw'][$istance_data['istance_id']] = $value;
            if ($value === '') {
              $value_html = '';
            } else {
              $value_esc = htmlspecialchars($value, ENT_QUOTES);
              $value_html = "<span style='color: $value_esc'><font color='$value_esc'>&#9608;</font></span> $value_esc";
            }
            $replace_map['__submitted_html_raw'][$istance_data['istance_id']] = "<strong>{$istance_data['istance_id']}</strong></td><td class='valori_submitted'>$value_html";
          break;
          case 'password':
          case 'password-and-confirm':
          break;
          default:
            $replace_map['__submitted_txt_raw'][$istance_data['istance_id']] = "{$istance_data['istance_id']}\t$value";
            $replace_map['__submitted_json_raw'][$istance_data['istance_id']] = $value;
            $replace_map['__submitted_html_raw'][$istance_data['istance_id']] = "<strong>{$istance_data['istance_id']}</strong></td><td class='valori_submitted'>".htmlspecialchars($value);
        }

        if ($submission_id) {
          $wpdb->insert(
            $wpdb->prefix . 'accua_forms_submissions_values',
            array (
              'afsv_sub_id' => $submission_id,
              'afsv_field_id' => $istance_data['istance_id'],
              'afsv_type' => $type,
              'afsv_value' => $value,
            ),
            array('%d','%s','%s','%s')
          );
        }

        $_field_data[$istance_data['istance_id']] = $field_data;
        $_istance_data[$istance_data['istance_id']] = $istance_data;

      }

      $replace_map['__autoreply'] = (bool) ($replace_map['__autoreply_email_raw']
          && $form_data['confirmation_emails_subject']
          && $form_data['confirmation_emails_message']);

      accua_forms_aggregate_submitted_data($replace_map);

      //Older undocumented filter, maintained for backward compatibility. In fact filter accua_forms_form_submission_handler is called before accua_forms_submission, but for the rest they are the same
      $replace_map = apply_filters('accua_forms_form_submission_handler', $replace_map, $fid, $submittedData, $form, $_field_data, $_istance_data);

      //Newer filter, with an easier name
      $replace_map = apply_filters('accua_forms_submission', $replace_map, $fid, $submittedData, $form, $_field_data, $_istance_data);

      $submitted_html = '<table><tr><td>' . $replace_map['__submitted_html'] . '</td></tr></table>';
      $confirmation_emails_message = $replace_map['__confirmation_emails_message'];
      unset($replace_map['__submitted_html'], $replace_map['__confirmation_emails_message'], $replace_map['__submitted_txt_raw'], $replace_map['__submitted_html_raw'], $replace_map['__submitted_json_raw'], $replace_map['__autoreply_email_raw']);

      $replace_map_html = array();
      foreach($replace_map as $key => $value) {
        $replace_map_html["!$key"] = wp_kses($value, 'post');
        $replace_map_html[$key] = htmlspecialchars($value, ENT_QUOTES);
      }

      $replace_map['__submitted_html'] = $replace_map_html['__submitted_html'] = $replace_map_html['!__submitted_html'] = $submitted_html;
      $replacer_html = new AccuaConditionalReplacer($replace_map_html);

      $confirmation_emails_message = $replace_map['__confirmation_emails_message'] = $replacer_html->doReplace($confirmation_emails_message);
      $replacer_html->appendPattern(array('__confirmation_emails_message' => $confirmation_emails_message, '!__confirmation_emails_message' => $confirmation_emails_message));
      $replacer = new AccuaConditionalReplacer($replace_map);

      $form_data_replaced = array();

      $settings = array(
        'emails_from_name',
        'emails_from',
        'admin_emails_to',
        'emails_bcc',
        'admin_emails_subject',
        'confirmation_emails_subject',
      );

      foreach($settings as $i) {
        $form_data_replaced[$i] = $replacer->doReplace($form_data[$i]);
      }

      $settings_html = array(
        'success_message',
        'admin_emails_message',
      );

      foreach($settings_html as $i) {
        $form_data_replaced[$i] = $replacer_html->doReplace($form_data[$i]);
      }

      AccuaForm::appendSubmittedMessages(wpautop($form_data_replaced['success_message']));

      $header = array("Content-Type: text/html; charset=".get_option('blog_charset'));

      $emails_from = trim($form_data_replaced['emails_from']);
      if (strpos($emails_from, '@') !== false) {
        if ((strpos($emails_from, '<') === false) && is_email($emails_from)) {
          $emails_from_name = trim($form_data_replaced['emails_from_name']);
          if ($emails_from_name !== '') {
            $emails_from = "=?" . get_bloginfo('charset') . "?B?" . base64_encode($emails_from_name) . "?= <$emails_from>";
          }
        }
        $header[] = 'From: '.$emails_from;
      }

      if ($form_data_replaced['emails_bcc']) {
        $header[] = 'Bcc: '.$form_data_replaced['emails_bcc'];
      }

      if ($form_data_replaced['admin_emails_to']
          && $form_data_replaced['admin_emails_subject']) {
        /*
        $admin_tos = explode(',', strtr($form_data_replaced['admin_emails_to'], "\n\t\r;", ',,,,'));
        foreach ($admin_tos as $admin_to) {
          $mail1 = wp_mail(trim($admin_to), $form_data_replaced['admin_emails_subject'], $form_data_replaced['admin_emails_message'], $header);
        }
        */
        $mail1 = wp_mail($form_data_replaced['admin_emails_to'], $form_data_replaced['admin_emails_subject'],'<html><head></head><body style="background:#f9f8f8;font-size: 12px;font-family: "Lucida Sans","Lucida Grande", Verdana, Arial, Sans-Serif;"">'.wpautop($form_data_replaced['admin_emails_message']).'</body></html>', $header);
      }

      if ($replace_map['__autoreply'] && $replace_map['__autoreply_email']
          && $form_data_replaced['confirmation_emails_subject']
          && $confirmation_emails_message) {
        $mail2 = wp_mail($replace_map['__autoreply_email'], $form_data_replaced['confirmation_emails_subject'], '<html><head></head><body>'.wpautop($confirmation_emails_message).'</body></html>', $header);
      }

      /*
      echo "<!-- replace_map: "
         , print_r($replace_map, true)
         , "\nreplace_map: "
         , print_r($replace_map, true)
         , "\nform_data_replaced: "
         , print_r($form_data_replaced, true)
         , "\nautoreply_email: "
         , print_r($autoreply_email, true)
         , "\nmail1: "
         , print_r($mail1, true)
         , "\nmail2: "
         , print_r($mail2, true)
         ,"\n-->";
      */
    }
  }
}

function accua_forms_get_submission_data($subid, $options = array()){
  //TODO: Completa e usa per sostituire i campi
  global $wpdb;
  $subid = (int) $subid;
  $options += array(
    'extra' => true,
    'file_format' => 'name',
  );
  $ret = array();
  if ($options['extra']) {
    $query1 = "SELECT *
      FROM `{$wpdb->prefix}accua_forms_submissions`
      WHERE afs_id = $subid";
    $data = $wpdb->get_row($query1);
    if (!empty($data)) {
      $created = $data->afs_created;
      $created[10] = 'T';
      $created.='.00+00:00';
      $created = strtotime($created);
      $submitted = $data->afs_submitted;
      $submitted[10] = 'T';
      $submitted.='.00+00:00';
      $submitted = strtotime($submitted);
      if ($data->afs_stats) {
        $stats = json_decode($data->afs_stats, true);
        if (!$stats) {
          $stats = array();
        }
      } else {
        $stats = array();
      }
      $stats += array(
        'user_agent' => '',
        'platform' => '',
        'tentatives' => '',
        'submit_method' => '',
      );
      $ret += array(
        '__fid' => $data->afs_form_id,
        '__subid' => $subid,
        '__pid' => $data->afs_post_id,
        '__ip' => $data->afs_ip,
        '__anonymized_ip' => accua_forms_anonymize_ip($data->afs_ip),
        '__uri' => $data->afs_uri,
        '__referrer' => $data->afs_referrer,
        '__lang' => $data->afs_lang,
        '__created' => $created,
        '__created_day' => date('l j F Y', $created),
        '__created_hour' => date('G:i', $created),
        '__submitted' => $submitted,
        '__submitted_day' => date('l j F Y', $submitted),
        '__submitted_hour' => date('G:i', $submitted),
        '__user_agent' => $stats['user_agent'],
        '__platform' => $stats['platform'],
        '__tentatives' => $stats['tentatives'],
        '__submit_method' => $stats['submit_method'],
      );
    }
  }

  $query2 = "SELECT *
        FROM `{$wpdb->prefix}accua_forms_submissions_values`
        WHERE afsv_sub_id = $subid";

  $data2 = $wpdb->get_results($query2, OBJECT);

  foreach ($data2 as $row) {
    switch ($row->afsv_type) {
      case 'file' :
        if ($options['file_format'] == 'url' || $options['file_format'] == 'link') {
          $fieldid = rawurlencode($row->afsv_field_id);
          $filename = rawurlencode($row->afsv_value);
          $url = admin_url('admin-ajax.php') . "?action=accua_forms_download_submitted_file&subid={$row->afsv_sub_id}&field={$fieldid}&file={$filename}&nonce=" . wp_create_nonce('accua_forms_download_nonce');
          if ($options['file_format'] == 'link'){
            $url = htmlspecialchars($url,ENT_QUOTES);
            $filename = htmlspecialchars($row->afsv_value,ENT_QUOTES);
            $fielddata = "<a href='{$url}' target='_blank'>{$filename}</a>";
          } else {
            $fielddata = $url;
          }
        } else { // $options['file_format'] == 'name'
          $fielddata = $row->afsv_value;
        }
      break;
      default:
        $fielddata = $row->afsv_value;
    }
    $ret[$row->afsv_field_id] = $fielddata;
  }
  return $ret;
}

add_shortcode( 'accua-form', 'accua_forms_shortcode_handler' );
function accua_forms_shortcode_handler($atts, $content = '', $code = '') {
  if (empty($atts['fid'])) {
    return '';
  }

  $fid = $atts['fid'];
  $form_data = _accua_forms_get_form_data($fid, false);

  if (! $form_data) {
    return '';
  }

  $fid = '__accua-form__'.$fid;

  $out = '';

  if (AccuaForm::getSubmittedID() == $fid) {
    /* return "<pre>Form submitted.\n\nData: " . print_r(AccuaForm::getSubmittedData(), true) . '</pre>'; */
    $messages = AccuaForm::getSubmittedMessages();
    if ($messages) {
      $out .= '<div id="_response_messages_'.$fid.'" class="accua-form-messages">'.$messages.'</div>';
    }
    if (AccuaForm::isValid()) {
      return $out;
    }
    $form = AccuaForm::getSubmittedForm();
  } else {
    $analytics_data = get_option('accua_forms_default_analytics_data',array());
    $params = array(
      'layout' => $form_data['layout'],
      'title' => $form_data['title'],
      'track_submit' => !empty($analytics_data['analytics_track_submit']),
      'track_fields' => !empty($analytics_data['analytics_track_fields']),
    );
    $form = AccuaForm::create($fid, $params);
  }

  $out .= $form->render(true);

  $doing_ajax = function_exists('wp_doing_ajax') ? wp_doing_ajax() : (defined( 'DOING_AJAX' ) && DOING_AJAX);
  if ($doing_ajax && ($_REQUEST['action'] === 'wpseo_filter_shortcodes')) {
    $strip_regexp = '/(<iframe[^>]*>(.*?)<\/iframe>|<script[^>]*>(.*?)<\/script>|<input([^>]*)type="hidden"[^>]*>)/is';
    $out = preg_replace($strip_regexp, '', $out);
  }

  return $out;

}

function accua_forms_include($fid, $atts=array(), $content = '', $code = '') {
  $atts['fid'] = $fid;
  echo accua_forms_shortcode_handler($atts, $content, $code);
}

function __accua_forms_submissions_list_page(){
    if(isset($_GET['sid'])) {
        accua_forms_single_submission();
      } else {
        accua_forms_submissions_list_page();
      }
}
function accua_forms_submissions_list_page_head(){
    if(isset($_GET['sid'])) {
        require_once('accua-forms-single-submission.php');
        accua_forms_single_submission(true);
      } else {
        require_once('accua-forms-submissions-page.php');
        accua_forms_submissions_list_page(true);
      }

}

/* Generiamo token di sicurezza per poter accedere anche da anonimo - email */
function accua_forms_generate_download_token($subid) {
    $token = wp_generate_password(32, false); // Token casuale di 32 caratteri
    update_post_meta($subid, '_accua_download_token', $token); // Salva il token associato all'invio
    return $token;
}

/**
 * Gestisce il download di un file inviato tramite un modulo.
 *
 * Questa funzione viene eseguita tramite una richiesta AJAX e permette agli utenti di scaricare
 * un file precedentemente caricato con un modulo. Controlla i parametri della richiesta per verificare
 * la presenza di un file associato a un determinato ID di invio e campo del modulo.
 *
 * - Se il parametro "html" è presente, genera una pagina HTML con un link di reindirizzamento automatico.
 * - Recupera le informazioni del file dal database per verificarne l'esistenza.
 * - Se il file esiste e può essere letto, restituisce il contenuto con gli appropriati header HTTP.
 * - Se il file non viene trovato, restituisce un errore 404.
 *
 * Sicurezza:
 * - Nonce
 * - Utilizza `stripslashes_deep` per sanificare i dati in ingresso.
 * - Protegge il database utilizzando `wpdb->prepare` per prevenire SQL Injection.
 * - Determina il tipo MIME del file per un download sicuro.
 * - Aggiunto token di verifica per utenti
 */

add_action('wp_ajax_accua_forms_download_submitted_file', 'accua_forms_download_submitted_file');
add_action('wp_ajax_nopriv_accua_forms_download_submitted_file', 'accua_forms_download_submitted_file');
function accua_forms_download_submitted_file(){
  $get = stripslashes_deep($_GET);
  $token_valid = false;
  if (isset($get['token']) && isset($get['subid'])) {
    $subid = rawurlencode($get['subid']);
    $saved_token = get_post_meta($subid, '_accua_download_token', true);
    if ($get['token'] === $saved_token) {
        $token_valid = true;
    }
  }
  if(!$token_valid){ // token presente nell'email di riepilogo dei form - x utenti non loggati
      // Check if the user has the necessary capabilities
      if (!is_user_logged_in() || !current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'contact-forms'));
      }
      if (!isset($get['nonce']) || !check_ajax_referer('accua_forms_download_nonce', 'nonce', false)) {
        wp_die(__('Invalid security token.', 'contact-forms'));
      }
  }
  if (isset($get['subid'],$get['field'],$get['file'])) {
    if (!empty($get['html'])) {
      header("Content-type: text/html");
      //$subid = rawurlencode($get['subid']);
      $fieldid = rawurlencode($get['field']);
      $filename = rawurlencode($get['file']);
      $url = admin_url('admin-ajax.php') . "?action=accua_forms_download_submitted_file&subid={$subid}&field={$fieldid}&file={$filename}&nonce=" . wp_create_nonce('accua_forms_download_nonce');
      $url = htmlspecialchars($url,ENT_QUOTES);
      $filename = htmlspecialchars($get['file'],ENT_QUOTES);
      die("<html><head><title>{$filename}</title><meta http-equiv='refresh' content='0;URL={$url}'></head><body><a href='{$url}'>{$filename}</a></body></html>");
    }
    global $wpdb;
    $subid = (int) $get['subid'];
    $field = $get['field'];
    $file = $get['file'];
    $query = "SELECT *
          FROM `{$wpdb->prefix}accua_forms_submissions_values`
          WHERE afsv_sub_id = %d
            AND afsv_field_id = %s
            AND afsv_value = %s
      ";
    $query = $wpdb->prepare($query, $subid, $field, $file);
    $subval = $wpdb->get_results($query, OBJECT);
    if ($subval) {
      $file_data = get_option('accua_forms_default_file_field_data',array()) + array('dest_path' => '');
      $dest_path = _accua_forms_get_abs_dest_path($file_data['dest_path']);
      $filename = "{$dest_path}/{$subid}_{$field}_{$file}";
      if (is_file($filename) && is_readable($filename)){
        if (function_exists('finfo_open')){
          @ $finfo = finfo_open(FILEINFO_MIME);
          if ($finfo) {
            @ $filetype = finfo_file($finfo, $filename);
            @ finfo_close($finfo);
          }
        }
        if (empty($filetype) && function_exists('mime_content_type')){
          @ $filetype = mime_content_type($filename);
        }
        if (empty($filetype)) {
          $filetype = "application/octet-stream";
        }
        header("Content-type: $filetype");
        header("Content-length: ".filesize($filename));
        if (empty($_GET['view'])) {
          header("Content-disposition: attachment; filename=\"$file\"");
        }
        readfile($filename);
        die('');
      }
    }
  }
  header("HTTP/1.0 404 Not Found");
  //header("Status: 404 Not Found");
  die('File not found');
}

function accua_forms_buildConditionalReplacer($map = array()) {
  if (!class_exists('AccuaConditionalReplacer')){
    require_once('AccuaConditionalReplacer.php');
  }
  return new AccuaConditionalReplacer($map);
}


add_action('wp_ajax_accua_forms_preview', 'accua_forms_preview');
function accua_forms_preview() {
  if (!current_user_can('manage_options')){
    die ('');
  }

  echo '<html><head>
  <style>
    body {font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;}
  </style>';
  wp_print_styles();
  wp_print_head_scripts();
  echo '</head><body>';
  echo accua_forms_shortcode_handler(array('fid'=>$_REQUEST['fid']));
  wp_print_footer_scripts();
  echo '</body></html>';
  die('');
}

//salvataggio file excel
add_action('wp_ajax_accua_forms_submission_page_save_excel', 'accua_forms_submission_page_save_excel');
//add_action('wp_ajax_nopriv_accua_forms_submission_page_save_excel', 'accua_forms_submission_page_save_excel');

function accua_forms_submission_page_save_excel() {
  if (!current_user_can('manage_options')){
    header("HTTP/1.0 401 Access Denied");
    //header("Status: 401 Access Denied");
    die('You are not authorized to access this page.');
  }

  require_once('accua-forms-submissions-page.php');
  $listTable = new Accua_Forms_Submissions_List_Table();
  $listTable->export_xls = true;
  $listTable->prepare_items(true);
  $show_col = explode( ',', $_GET['accua_show_field']);
  $show_col = array_diff($show_col, array('singlesub'));
  header("Content-disposition: attachment; filename=downloads-report.xls");
  header("Content-type: application/vnd.ms-excel");
  accua_forms_submission_page_save_excel_general($listTable,$show_col);
  die('');
}



function accua_forms_submission_page_save_excel_general(Accua_Forms_Submissions_List_Table $listTable,array $show_col,array $options=array()) {
  global $wpdb;

  $content_type = 'text/html; charset=' . get_option('blog_charset');

  //creo il file excel
  ?><html xmlns:o="urn:schemas-microsoft-com:office:office"
  xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="http://www.w3.org/TR/REC-html40">
  <head>
  <meta http-equiv=Content-Type content="<?php echo $content_type; ?>" />
  <meta name=ProgId content=Excel.Sheet />
  <style>
  <!--
  td {vertical-align:top;}
  .head_row {font-weight:bold;}
  .column-date { mso-number-format:"Short Date"; }
  .datetime_cell { mso-number-format:"yyyy\\-mm\\-dd\\ hh\:mm\:ss"; }
  -->
  </style>
  <!--[if gte mso 9]><xml>
  <x:ExcelWorkbook>
  <x:ExcelWorksheets>
  <x:ExcelWorksheet>
  <x:WorksheetOptions>
  <x:FreezePanes/>
  <x:FilterOn/>
  <x:SplitHorizontal>1</x:SplitHorizontal>
  <x:TopRowBottomPane>1</x:TopRowBottomPane>
  <x:ActivePane>2</x:ActivePane>
  <x:Panes>
  <x:Pane>
  <x:Number>3</x:Number>
  </x:Pane>
  <x:Pane>
  <x:Number>2</x:Number>
  </x:Pane>
  </x:Panes>
  </x:WorksheetOptions>
  </x:ExcelWorksheet>
  </x:ExcelWorksheets>
  </x:ExcelWorkbook>
  </xml><![endif]-->
  </head>
  <body>
  <table x:str border=1 >
  <tr class='head-row'>
  <?php
  $cols = $listTable->get_columns();
  foreach($cols as $col_key=>$col_value) {
    if(in_array($col_key, $show_col)) { ?>
      <td x:autofilter="all"><?php echo $col_value; ?></td>
    <?php }
  } ?>
  </tr>

  <?php
  $lead_statuses = accua_forms_get_lead_statuses();

  foreach($listTable->items as $id_submission=>$single_submission) {
     @ set_time_limit(10);
     echo "<tr>";
     foreach($cols as $col_key=>$col_value) {
      if(in_array($col_key, $show_col)) {
        echo "<td class='.$col_key.'>";
        if ($col_key == 'lead_status') {
          if (isset($lead_statuses[$single_submission['lead_status']])) {
            echo htmlspecialchars($lead_statuses[$single_submission['lead_status']]);
          }
        } else if(isset($single_submission[$col_key])) {
              if ( method_exists( $listTable, 'column_' . $col_key ) ) {
                echo call_user_func( array( &$listTable, 'column_' . $col_key ), $single_submission );
              }
              else {
                echo $listTable->column_default( $single_submission, $col_key );
              }
         }
         echo "</td>";
      }

    }
   echo "</tr>";
  }

  ?>
  </table>
  </body>
  </html>
  <?php
}

function accua_forms_print_tokens() {
  $avail_fields = get_option('accua_forms_avail_fields', array());
  $tokens = '';
  foreach($avail_fields as $key=>$value) {
    $tokens .= $value['name'] . ": {" . $key . "}\n";
    switch ($value['type']) {
      case 'file':
        $tokens .= $value['name'] . " (download link): {__download_" . $key . "}\n";
      break;
      case 'multiselect':
      case 'multicheckbox':
        $tokens .= $value['name'] . " (labels): {__label_" . $key . "}\n";
      break;
      case 'select':
      case 'radio':
        $tokens .= $value['name'] . " (label): {__label_" . $key . "}\n";
      break;
      case 'post-multicheckbox':
        $tokens .= $value['name'] . " (posts titles): {__label_" . $key . "}\n";
        $tokens .= $value['name'] . " (posts ids): {__post_id_" . $key . "}\n";
        $tokens .= $value['name'] . " (posts urls): {__post_url_" . $key . "}\n";
      break;
      case 'post-select':
        $tokens .= $value['name'] . " (post title): {__label_" . $key . "}\n";
        $tokens .= $value['name'] . " (post id): {__post_id_" . $key . "}\n";
        $tokens .= $value['name'] . " (post url): {__post_url_" . $key . "}\n";
      break;
    }
  }

  echo <<<EOT
<div class="accua_forms_token_list">
<h2>Tokens</h2>
<em>In HTML text, use {!token_name} to insert unfiltered token value</em>
<h3>Fields</h3>
<em>These tokens are available only if the field is added to the form</em>
<pre>$tokens</pre>
<h3>Generic tokens</h3>
<pre>{__fid}
{__subid}
{__pid}
{__ip}
{__anonymized_ip}
{__uri}
{__url}
{__referrer}
{__lang}
{__locale}
{__created}
{__created_day}
{__created_day_month_year}
{__created_hour}
{__submitted}
{__submitted_day}
{__submitted_day_month_year}
{__submitted_hour}
{__user_agent}
{__platform}
{__tentatives}
{__submit_method}
{__submitted_txt}
{__submitted_html}
{__submitted_json}
{__autoreply}
{__autoreply_email}
{__confirmation_emails_message}
{__review_submission_url}</pre>
</div>
EOT;
  do_action('accua_forms_print_tokens');
}

function accua_get_pages($args = '') {
  global $wpdb;

  $defaults = array(
      'child_of' => 0,
      'sort_order' => 'ASC',
      'sort_column' => 'post_title',
      'hierarchical' => 1,
      'exclude' => array(),
      'include' => array(),
      'meta_key' => '',
      'meta_value' => '',
      'meta_value_lt' => '',
      'meta_value_gt' => '',
      'meta_value_le' => '',
      'meta_value_ge' => '',
      'meta_value_like' => '',
      'meta_value_format' => 'string',
      'authors' => '',
      'parent' => -1,
      'exclude_tree' => '',
      'number' => '',
      'offset' => 0,
      'post_type' => 'page',
      'post_status' => 'publish',
  );

  $r = wp_parse_args( $args, $defaults );
  $child_of = (int) $r['child_of'];
  $sort_order = $r['sort_order'];
  $sort_column = $r['sort_column'];
  $hierarchical = $r['hierarchical'];
  $exclude = $r['exclude'];
  $include = $r['include'];
  $meta_key = $r['meta_key'];
  $meta_value = $r['meta_value'];
  $meta_value_lt = $r['meta_value_lt'];
  $meta_value_gt = $r['meta_value_gt'];
  $meta_value_le = $r['meta_value_le'];
  $meta_value_ge = $r['meta_value_ge'];
  $meta_value_like = $r['meta_value_like'];
  $meta_value_format = $r['meta_value_format'];
  $authors = $r['authors'];
  $parent = $r['parent'];
  $exclude_tree = $r['exclude_tree'];
  $number = (int) $r['number'];
  $offset = (int) $r['offset'];
  $post_type = $r['post_type'];
  $post_status = $r['post_status'];

  /*
   // Make sure the post type is hierarchical
  $hierarchical_post_types = get_post_types( array( 'hierarchical' => true ) );
  if ( !in_array( $post_type, $hierarchical_post_types ) )
    return false;
  */

  // Make sure we have a valid post type
  if ( !is_array( $post_type ) )
    $post_type = explode( ',', $post_type );
  if ( array_diff( $post_type, get_post_types() ) )
    return false;

  // Make sure we have a valid post status
  if ( !is_array( $post_status ) )
    $post_status = explode( ',', $post_status );
  if ( array_diff( $post_status, get_post_stati() ) )
    return false;

  /*
   $cache = array();
  $key = md5( serialize( compact(array_keys($defaults)) ) );
  if ( $cache = wp_cache_get( 'get_pages', 'posts' ) ) {
  if ( is_array($cache) && isset( $cache[ $key ] ) ) {
  $pages = apply_filters('get_pages', $cache[ $key ], $r );
  return $pages;
  }
  }

  if ( !is_array($cache) )
    $cache = array();
  */

  $inclusions = '';
  if ( !empty($include) ) {
    $child_of = 0; //ignore child_of, parent, exclude, meta_key, and meta_value params if using include
    $parent = -1;
    $exclude = '';
    $meta_key = '';
    $meta_value = '';
    $meta_value_lt = '';
    $meta_value_gt = '';
    $meta_value_le = '';
    $meta_value_ge = '';
    $meta_value_like = '';
    $hierarchical = false;
    $incpages = wp_parse_id_list( $include );
    if ( ! empty( $incpages ) ) {
      foreach ( $incpages as $incpage ) {
        if (empty($inclusions))
          $inclusions = $wpdb->prepare(' AND ( ID = %d ', $incpage);
        else
          $inclusions .= $wpdb->prepare(' OR ID = %d ', $incpage);
      }
    }
  }
  if (!empty($inclusions))
    $inclusions .= ')';

  $exclusions = '';
  if ( !empty($exclude) ) {
    $expages = wp_parse_id_list( $exclude );
    if ( ! empty( $expages ) ) {
      foreach ( $expages as $expage ) {
        if (empty($exclusions))
          $exclusions = $wpdb->prepare(' AND ( ID <> %d ', $expage);
        else
          $exclusions .= $wpdb->prepare(' AND ID <> %d ', $expage);
      }
    }
  }
  if (!empty($exclusions))
    $exclusions .= ')';

  $author_query = '';
  if (!empty($authors)) {
    $post_authors = preg_split('/[\s,]+/',$authors);

    if ( ! empty( $post_authors ) ) {
      foreach ( $post_authors as $post_author ) {
        //Do we have an author id or an author login?
        if ( 0 == intval($post_author) ) {
          $post_author = get_user_by('login', $post_author);
          if ( empty($post_author) )
            continue;
          if ( empty($post_author->ID) )
            continue;
          $post_author = $post_author->ID;
        }

        if ( '' == $author_query )
          $author_query = $wpdb->prepare(' post_author = %d ', $post_author);
        else
          $author_query .= $wpdb->prepare(' OR post_author = %d ', $post_author);
      }
      if ( '' != $author_query )
        $author_query = " AND ($author_query)";
    }
  }

  $allowed_keys = array('author', 'post_author', 'date', 'post_date', 'title', 'post_title', 'name', 'post_name', 'modified',
    'post_modified', 'modified_gmt', 'post_modified_gmt', 'menu_order', 'parent', 'post_parent',
    'ID', 'rand', 'comment_count');

  $join = '';
  $where = "$exclusions $inclusions ";
  if ( ! ( empty( $meta_key ) && empty( $meta_value )
        && empty( $meta_value_lt ) && empty( $meta_value_gt )
        && empty( $meta_value_le ) && empty( $meta_value_ge )
        && empty( $meta_value_like ) ) ) {
    $join = " LEFT JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )";
    $allowed_keys[] = 'meta_key';
    $allowed_keys[] = 'meta_value';

    // meta_key and meta_value might be slashed
    $meta_key = stripslashes($meta_key);
    $meta_value = stripslashes($meta_value);
    $meta_value_lt = stripslashes($meta_value_lt);
    $meta_value_gt = stripslashes($meta_value_gt);
    $meta_value_le = stripslashes($meta_value_le);
    $meta_value_ge = stripslashes($meta_value_ge);
    $meta_value_like = stripslashes($meta_value_like);

    if ( ! empty( $meta_key ) ) {
      $where .= $wpdb->prepare(" AND $wpdb->postmeta.meta_key = %s", $meta_key);
    }

    $meta_value_field = "$wpdb->postmeta.meta_value";
    $meta_value_timestamp = false;
    switch($meta_value_format) {
      case 'timestamp':
        $meta_value_field = "TIMESTAMP( $meta_value_field )";
        $meta_value_param = "FROM_UNIXTIME( %s )";
        $meta_value_timestamp = true;
      break;
      case 'int':
        $meta_value_param = "%d";
      break;
      case 'float':
        $meta_value_param = "%f";
      break;
      //case 'string':
      default:
        $meta_value_param = "%s";
    }

    if ( ! empty( $meta_value ) ) {
      if ($meta_value_timestamp) {
        $meta_value = strtotime($meta_value);
      }
      $where .= $wpdb->prepare(" AND $meta_value_field = $meta_value_param", $meta_value);
    }
    if ( ! empty( $meta_value_lt ) ) {
      if ($meta_value_timestamp) {
        $meta_value_lt = strtotime($meta_value_lt);
      }
      $where .= $wpdb->prepare(" AND $meta_value_field < $meta_value_param", $meta_value_lt);
    }
    if ( ! empty( $meta_value_gt ) ) {
      if ($meta_value_timestamp) {
        $meta_value_gt = strtotime($meta_value_gt);
      }
      $where .= $wpdb->prepare(" AND $meta_value_field > $meta_value_param", $meta_value_gt);
    }
    if ( ! empty( $meta_value_le ) ) {
      if ($meta_value_timestamp) {
        $meta_value_le = strtotime($meta_value_le);
      }
      $where .= $wpdb->prepare(" AND $meta_value_field <= $meta_value_param", $meta_value_le);
    }
    if ( ! empty( $meta_value_ge ) ) {
      if ($meta_value_timestamp) {
        $meta_value_ge = strtotime($meta_value_ge);
      }
      $where .= $wpdb->prepare(" AND $meta_value_field >= $meta_value_param", $meta_value_ge);
    }
    if ( ! empty( $meta_value_like ) ) {
      if ($meta_value_timestamp) {
        $meta_value_like = strtotime($meta_value_like);
      }
      $where .= $wpdb->prepare(" AND $meta_value_field like $meta_value_param", $meta_value_like);
    }
  }

  if ( $parent >= 0 )
    $where .= $wpdb->prepare(' AND post_parent = %d ', $parent);


  if ( 1 == count ( $post_type ) ) {
    $where_post_type = $wpdb->prepare( "post_type = %s", array_shift( $post_type ) );
  } else {
    $post_type = implode( "', '", $post_type );
    $where_post_type = "post_type IN ('$post_type')";
  }

  if ( 1 == count( $post_status ) ) {
    $where_post_type .= $wpdb->prepare( " AND post_status = %s", array_shift( $post_status ) );
  } else {
    $post_status = implode( "', '", $post_status );
    $where_post_type .= " AND post_status IN ('$post_status')";
  }

  $orderby_array = array();
  foreach ( explode( ',', $sort_column ) as $orderby ) {
    $orderby = trim( $orderby );
    if ( !in_array( $orderby, $allowed_keys ) )
      continue;

    switch ( $orderby ) {
      case 'menu_order':
        break;
      case 'ID':
        $orderby = "$wpdb->posts.ID";
        break;
      case 'rand':
        $orderby = 'RAND()';
        break;
      case 'comment_count':
        $orderby = "$wpdb->posts.comment_count";
        break;
      case 'meta_key':
      case 'meta_value':
        $orderby = "$wpdb->postmeta.$orderby";
        break;
      default:
        if ( 0 === strpos( $orderby, 'post_' ) )
          $orderby = "$wpdb->posts." . $orderby;
        else
          $orderby = "$wpdb->posts.post_" . $orderby;
    }

    $orderby_array[] = $orderby;

  }
  $sort_column = ! empty( $orderby_array ) ? implode( ',', $orderby_array ) : "$wpdb->posts.post_title";

  $sort_order = strtoupper( $sort_order );
  if ( '' !== $sort_order && !in_array( $sort_order, array( 'ASC', 'DESC' ) ) )
    $sort_order = 'ASC';

  $query = "SELECT * FROM $wpdb->posts $join WHERE ($where_post_type) $where ";
  $query .= $author_query;
  $query .= " ORDER BY " . $sort_column . " " . $sort_order ;

  if ( !empty($number) && !empty($offset) ) {
      $query .= $wpdb->prepare(' LIMIT %d, %d', $offset, $number);
  }

  //echo "<!-- accua_forms_query:\n$query\n-->";

  $pages = $wpdb->get_results($query);

  if ( empty($pages) ) {
    $pages = apply_filters('get_pages', array(), $r);
    return $pages;
  }

  // Sanitize before caching so it'll only get done once
  $num_pages = count($pages);
  for ($i = 0; $i < $num_pages; $i++) {
    $pages[$i] = sanitize_post($pages[$i], 'raw');
  }

  /*
   // Update cache.
  update_post_cache( $pages );
  */

  if ( $child_of || $hierarchical )
    $pages = get_page_children($child_of, $pages);

  if ( !empty($exclude_tree) ) {
    $exclude = (int) $exclude_tree;
    $children = get_page_children($exclude, $pages);
    $excludes = array();
    foreach ( $children as $child )
      $excludes[] = $child->ID;
    $excludes[] = $exclude;
    $num_pages = count($pages);
    for ( $i = 0; $i < $num_pages; $i++ ) {
      if ( in_array($pages[$i]->ID, $excludes) )
        unset($pages[$i]);
    }
  }

  $pages = apply_filters('get_pages', $pages, $r);

  return $pages;
}

function accua_forms_trash_submission($id_sub){
  global $wpdb;
  return $wpdb->query($wpdb->prepare("UPDATE `{$wpdb->prefix}accua_forms_submissions` SET afs_status = -1 WHERE afs_id = %d", $id_sub)) !== FALSE;
}

function accua_forms_get_lead_statuses() {
  static $statuses = NULL;
  if ($statuses === NULL) {
    $statuses = array(
      0 => __('Undefined', 'contact-forms'),
      -1 => __('Spam', 'contact-forms'),
      1 => __('Job Candidate', 'contact-forms'),
      2 => __('Lead', 'contact-forms'),
      3 => __('Prospect', 'contact-forms'),
      4 => __('Opportunity', 'contact-forms'),
      5 => __('Customer', 'contact-forms'),
      6 => __('Supplier', 'contact-forms'),
      7 => __('Other', 'contact-forms'),
    );
  }
  return $statuses;
}

function accua_forms_select_lead_status($subid, $original_lead_status) {
  static $accuaHelp = NULL;
  if ($accuaHelp === NULL) {
    if (!class_exists('AccuaFormsHelp')) {
      require_once('accua-forms-help.php');
    }
    $accuaHelp = AccuaFormsHelp::getInstance();
  }
  $nonce = htmlspecialchars(_accua_forms_json_encode(wp_create_nonce( "set_lead_status_$subid" )),ENT_QUOTES);
  $ret = "<select onchange=\"accua_forms_set_lead_status(this, $subid, $nonce, $original_lead_status)\">";
  $statuses = accua_forms_get_lead_statuses();
  foreach ($statuses as $k => $l) {
    $selected = ($k == $original_lead_status) ? ' selected="selected" ' : '';
    $ret .= "<option value=\"$k\"$selected>" . htmlspecialchars($l) . "</option>";
  }
  $ret .= "</select>" . $accuaHelp->add_pointer('contact_forms_lead_statuses') . "<span class='accua-forms-select-lead-status-progress'></span>";
  return $ret;
}

add_action( 'wp_ajax_accua-forms-set-lead-status' , 'accua_forms_set_lead_status');
function accua_forms_set_lead_status() {
  if (!current_user_can('manage_options')){
    wp_die(0, 403);
  }
  $post = $_POST + array(
    'subid' => 0,
    'lead_status' => 0,
  );
  $subid = (int) $post['subid'];

  if ($subid) {
    check_ajax_referer("set_lead_status_$subid", '_nonce_set_lead_status');
    $lead_status = (int) $post['lead_status'];
    $statuses = accua_forms_get_lead_statuses();
    if (isset($statuses[$lead_status])) {
      global $wpdb;
      $ret = $wpdb->update("{$wpdb->prefix}accua_forms_submissions",
        array('afs_lead_status' => $lead_status),
        array('afs_id' => $subid),
        '%d', '%d'
      );
      if ($ret !== FALSE) {
        wp_die(1, 200);
      }
    }
  }
  wp_die(0, 500);
}
