<?php
function accua_form_Load($class) {
  if (substr($class,0,10) === 'AccuaForm_') {
    $class = substr($class, 10);
    $file = ACCUA_FORMS_DIR . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . str_replace("_", DIRECTORY_SEPARATOR, $class) . ".php";
    if(is_file($file)) {
      include_once $file;
    }
  }
}
spl_autoload_register("accua_form_Load");

require_once('PFBC/Form.php');
require_once('AccuaForm.php');

define('ACCUA_FORM_API_PLUGIN_TEXTDOMAIN_PATH', dirname( plugin_basename( __FILE__ ) ) . '/languages/');

add_action( 'wp_print_scripts', 'accua_form_init_scripts');
function accua_form_init_scripts(){
  wp_enqueue_script('jquery');
  AccuaForm_Element_ColorPicker::maybe_load_colorPicker_scripts();
}

add_action('wp_enqueue_scripts', 'accua_form_enqueue_styles');
add_action('admin_enqueue_scripts', 'accua_form_enqueue_styles');
add_action('login_enqueue_scripts', 'accua_form_enqueue_styles');
add_action('wp_print_styles', 'accua_form_enqueue_styles');
function accua_form_enqueue_styles(){
  static $first = TRUE;
  if ($first) {
    AccuaForm_Element_ColorPicker::maybe_load_colorPicker_styles();
    wp_enqueue_style('accua-forms-api-base', plugins_url('accua-form-api.css', ACCUA_FORMS_FILE), array(), ACCUA_FORMS_CSS_VERSION);
    $first = FALSE;
  }
}

add_action('plugins_loaded', 'accua_form_init');
function accua_form_init(){
  load_plugin_textdomain( 'contact-forms', false, ACCUA_FORM_API_PLUGIN_TEXTDOMAIN_PATH);
  if (!defined('DOING_CRON')) {
    AccuaForm::isValid();
  }
}

add_action('wp_ajax_accua_form_submit', 'accua_form_ajax_submit_handler');
add_action('wp_ajax_nopriv_accua_form_submit', 'accua_form_ajax_submit_handler');
function accua_form_ajax_submit_handler(){
  header('Content-Type: text/html; charset='.get_option('blog_charset'));
  header('Cache-Control: no-transform');

  echo '<html><head></head><body><pre id="accua-form-ajax-response">';

  print _accua_forms_json_encode(AccuaForm::ajaxSubmit());

  echo '</pre><div id="accua-form-ajax-response-loaded"></div>';

  echo <<<EOJS
<script type="text/javascript">
<!--
try {
  window.parent.postMessage(document.getElementById("accua-form-ajax-response").innerHTML, '*');
} catch (err) {
}
// -->
</script>
EOJS;

  echo '</body></html>';
  die('');
}
