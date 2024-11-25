<?php
class AccuaForm_Element_ColorPicker extends Element {
  /**
   * Status that changes to 1 if WordPress is ready to load javascripts, and changes to 2 when color picker scripts are loaded
   *
   * @var int
   */
  protected static $_cp_loaded_scripts = 0;
  /**
   * Status that changes to 1 if WordPress is ready to load styles, and changes to 2 when color picker styles are loaded
   *
   * @var int
   */
  protected static $_cp_loaded_styles = 0;
  /**
   * Status that changes to TRUE when a color picker field is rendered, meaning that scripts and styles are needed
   *
   * @var bool
   */
  protected static $_cp_required_script_styles = FALSE;

  public function __construct($label, $name, array $properties = null) {
    parent::__construct($label,$name,$properties);
    $this->setValidation(new AccuaForm_Validation_Color());
  }
  
  public function jQueryDocumentReady() {
    parent::jQueryDocumentReady();
    echo 'jQuery("#', $this->attributes["id"], '").colorPicker({opacity: false, renderCallback: function($elm, toggled){
      if ($elm.val() != "") {
        $elm.val("#"+this.color.colors.HEX);
      }
    }});';
  }

  /**
   * Loads scripts and styles for color picker only once, if needed, when WordPress is ready
   *
   * @return void
   */
  protected static function maybe_load_colorPicker_scripts_styles() {
    if (self::$_cp_required_script_styles) { // scripts and styles are needed
      if (self::$_cp_loaded_scripts == 1) { // WordPress is ready to load scripts
        self::$_cp_loaded_scripts = 2; // Change status to load them just once
        wp_enqueue_script('accua-jqColorPicker', plugins_url('/js/jqColorPicker.min.js', ACCUA_FORMS_FILE ), array( 'jquery' ), ACCUA_FORMS_JS_VERSION);
      }
      if (self::$_cp_loaded_styles == 1) { // WordPress is ready to load styles
        self::$_cp_loaded_styles = 2; // Change status to load them just once
        // wp_enqueue_style(...) here if needed
      }
    }
  }

  /**
   * Function called during action wp_print_scripts to change status of self::$_cp_loaded_scripts and load scripts if needed
   *
   * @return void
   */
  public static function maybe_load_colorPicker_scripts(){
    if (self::$_cp_loaded_scripts == 0) { // First time
      self::$_cp_loaded_scripts = 1; // When we are in this status, WordPress is ready to load scripts
      self::maybe_load_colorPicker_scripts_styles(); // Load scripts if they were previously set as needed
    }
  }

  /**
   * Function called during action wp_print_styles to change status of self::$_cp_loaded_styles and load styles if needed
   *
   * @return void
   */
  public static function maybe_load_colorPicker_styles(){
    if (self::$_cp_loaded_styles == 0) { // First time
      self::$_cp_loaded_styles = 1; // When we are in this status, WordPress is ready to load styles
      self::maybe_load_colorPicker_scripts_styles(); // Load styles if they were previously set as needed
    }
  }

  public function render() {
    self::$_cp_required_script_styles = TRUE; // Changing status meaning that script and styles are needed
    self::maybe_load_colorPicker_scripts_styles(); // Load script and styles once if WordPress is ready
    parent::render();
  }
}
