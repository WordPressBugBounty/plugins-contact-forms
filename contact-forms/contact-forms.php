<?php
/*
Plugin Name: WordPress Contact Forms by Cimatti
Description: Quickly create and publish forms in your WordPress powered website.
Version: 1.9.5
Plugin URI: https://www.cimatti.it/en/wordpress-plugins/contact-forms/
Author: Cimatti Consulting
Author URI: https://www.cimatti.it
Text Domain: contact-forms
Domain Path: /languages
Requires at least: 3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/*
WordPress Contact Forms by Cimatti
Copyright (c) 2011-2024 Andrea Cimatti

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.


The full copy of the GNU General Public License is available here: http://www.gnu.org/licenses/gpl.txt

*/

define('ACCUA_FORMS_DB_VERSION', '12');
define('ACCUA_FORMS_CSS_VERSION', '53');
define('ACCUA_FORMS_JS_VERSION', '9');
define('ACCUA_FORMS_FILE', __FILE__);
define('ACCUA_FORMS_DIR_URL', plugin_dir_url( ACCUA_FORMS_FILE ));
define('ACCUA_FORMS_DIR', dirname( ACCUA_FORMS_FILE ));

require_once('accua-form-api.php');
require_once('accua-forms.php');
require_once('accua-shortcode-button.php');

function accua_forms_dashboard_page_head(){
  $screen = get_current_screen();
  $screen->add_help_tab( array(
    'id'	=> 'accua_help_tab',
    'title'	=> __('Contact Forms Dashboard'),
    'content'	=> '<p>' . __( 'Marketing tools for WordPress', 'contact-forms') . '</p>',
  ) );

  //wp_enqueue_script('accua', plugins_url('/flot/jquery.flot.js', ACCUA_FORMS_FILE ), array( 'jquery' ));
  wp_enqueue_script('accua_chart_js', plugins_url('/chartjs/Chart.min.js', ACCUA_FORMS_FILE ), array( 'jquery' ), ACCUA_FORMS_JS_VERSION);
  //wp_enqueue_style('accua_flot',  plugins_url('/flot/layout.css', ACCUA_FORMS_FILE ));
  wp_enqueue_style('accua',  plugins_url('accua.css', ACCUA_FORMS_FILE ), array(), ACCUA_FORMS_CSS_VERSION);

    /* Tentativo x drag and drop
        $page_hook_id = fx_smb_setings_page_id();
        /* Load the JavaScript needed for the settings screen. * /
    add_action( 'admin_enqueue_scripts', 'fx_smb_enqueue_scripts' );
    add_action( "admin_footer-{$page_hook_id}", 'fx_smb_footer_scripts' );

    /* Set number of column available. * /
    add_filter( 'screen_layout_columns', 'fx_smb_screen_layout_column', 10, 2 );
    */
}

/* COSA CARINA PER AGGIUNGERE WIDGET NELLA DASHBOARD DI WP
todo: posso scegliere la sua posizione di default?
*/
add_action( 'wp_dashboard_setup', 'accua_contact_forms_dashboard_add_widgets' );
function accua_contact_forms_dashboard_add_widgets() {
    wp_add_dashboard_widget( 'accua_contact_forms_dashboard_widget_news', __( 'Contact Forms', 'contact-forms' ), 'accua_contact_forms_dashboard_widget_news_handler' );
}

function  accua_contact_forms_dashboard_widget_news_handler() {
    global $wpdb; ?>
    <table>
        <tr class="first">
            <?php $forms_data = get_option('accua_forms_saved_forms', array());
            $active_forms = count($forms_data); ?>
            <td class="first b"><?php echo $active_forms; ?></td><td class="t"><?php _e('Active forms', 'contact-forms'); ?></td>
        </tr>
        <tr class="first">
          <?php $pages = $wpdb->get_var("SELECT COUNT(DISTINCT afs_uri) FROM `{$wpdb->prefix}accua_forms_submissions`"); ?>
            <td class="first b"><?php echo $pages; ?></td><td class="t"><?php _e('Pages', 'contact-forms'); ?></td>
        </tr>
        <tr class="first">
            <?php $active_submissions = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}accua_forms_submissions` WHERE afs_status >= 0"); ?>
            <td class="first b"><?php echo $active_submissions; ?></td><td class="t"><?php _e('Submissions', 'contact-forms'); ?></td>
        </tr>
        <tr class="first">
            <?php $emails = $wpdb->get_var("SELECT COUNT(DISTINCT afsv_value) FROM `{$wpdb->prefix}accua_forms_submissions_values` WHERE afsv_type LIKE 'autoreply_email'"); ?>
            <td class="first b"><?php echo $emails; ?></td><td class="t"><?php _e('Distinct Emails', 'contact-forms'); ?></td>
        </tr>
    </table>
    <br />
    <span id="accua-forms-version"><a href="https://www.cimatti.it/en/wordpress-plugins/contact-forms/"><?php
        $plugin_data = get_plugin_data( ACCUA_FORMS_FILE );
        _e('Version', 'contact-forms'); echo " ".$plugin_data['Version']; ?></a> | <a href="admin.php?page=accua_forms" title="Contact Forms Dashboard"><?php _e('Dashboard', 'contact-forms'); ?></a>
    </span>

    <?php
}

function accua_forms_dashboard_page(){
    require_once ACCUA_FORMS_DIR.'/accua-forms-dashboard.php';
    return _accua_forms_dashboard_page();
}


register_activation_hook(ACCUA_FORMS_FILE, 'accua_forms_install');
function accua_forms_install(){
  load_plugin_textdomain( 'contact-forms', false, ACCUA_FORM_API_PLUGIN_TEXTDOMAIN_PATH);
  $modified = false;
  $keys = get_option('accua_form_api_keys', array());
  if (!isset($keys['hash'])) {
    $modified = true;
    $keys['hash'] = wp_generate_password(64,true,true);
  }
  if (!isset($keys['aes'])) {
    $modified = true;
    $keys['aes'] = wp_generate_password(64,true,true);
  }
  if ($modified) {
    update_option('accua_form_api_keys', $keys);
  }

  global $wpdb;

  require_once(ABSPATH.'wp-admin/includes/upgrade.php');

  $charset_collate = '';
  if ( ! empty($wpdb->charset) )
    $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
  if ( ! empty($wpdb->collate) )
    $charset_collate .= " COLLATE $wpdb->collate";

  $old_db_version = (int) get_option('accua_forms_db_version', 0);

  if ($old_db_version < 3) {
    $wpdb_suppress_errors_status = $wpdb->suppress_errors();
    $wpdb->query("ALTER TABLE `{$wpdb->prefix}accua_forms_submissions` DROP INDEX uri");
    $wpdb->query("ALTER TABLE `{$wpdb->prefix}accua_forms_submissions` DROP INDEX referrer");
    $wpdb->suppress_errors($wpdb_suppress_errors_status);
  }

  $sql = "CREATE TABLE `{$wpdb->prefix}accua_forms_submissions` (
  afs_id BIGINT(20) NOT NULL AUTO_INCREMENT,
  afs_form_id VARCHAR(77) NOT NULL DEFAULT '',
  afs_post_id BIGINT(20) NOT NULL DEFAULT 0,
  afs_ip VARCHAR(255) NOT NULL DEFAULT '',
  afs_uri TEXT NOT NULL DEFAULT '',
  afs_referrer TEXT NOT NULL DEFAULT '',
  afs_lang VARCHAR(60) NOT NULL DEFAULT '',
  afs_created TIMESTAMP NOT NULL DEFAULT 0,
  afs_submitted TIMESTAMP NOT NULL DEFAULT 0,
  afs_status TINYINT(1) NOT NULL DEFAULT 0,
  afs_stats TEXT NOT NULL DEFAULT '',
  afs_lead_status TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (afs_id),
  KEY form (afs_form_id, afs_status),
  KEY uri (afs_uri(190)),
  KEY pid (afs_post_id),
  KEY referrer (afs_referrer(190)),
  KEY status (afs_status, afs_id),
  KEY submitted (afs_submitted),
  KEY lead_status (afs_lead_status, afs_status)
  ) $charset_collate;";
  dbDelta($sql);

  $sql = "CREATE TABLE `{$wpdb->prefix}accua_forms_submissions_values` (
  afsv_sub_id BIGINT(20) NOT NULL,
  afsv_field_id VARCHAR(77) NOT NULL,
  afsv_type varchar(255) NOT NULL DEFAULT '',
  afsv_value TEXT NOT NULL DEFAULT '',
  PRIMARY KEY  (afsv_sub_id, afsv_field_id),
  KEY value_index (afsv_field_id(77), afsv_value(114))
  ) $charset_collate;";
  dbDelta($sql);

  /* creazione tabella per le note dell'utente
  sono relative a una compilazione
  possono esserci più note per ogni compilazione in date diverse

  */
	$sql = "CREATE TABLE `{$wpdb->prefix}accua_forms_submissions_notes` (
  afsn_sub_id BIGINT(20) NOT NULL,
  afsn_date TIMESTAMP NOT NULL DEFAULT 0,
  afsn_text  TEXT NOT NULL DEFAULT '',
  afsn_user  VARCHAR(100) NOT NULL DEFAULT '',
  PRIMARY KEY  (afsn_sub_id, afsn_date),
  KEY afsn_sub_id (afsn_sub_id)
  ) $charset_collate;";
	dbDelta($sql);


  $avail_fields = get_option('accua_forms_avail_fields', array());

  if (!is_array($avail_fields)) {
    $avail_fields = array();
  }

  if (empty($avail_fields) || ($old_db_version === 0)) {
    $avail_fields += array(
      'first_name' => array(
        'id' => 'first_name',
        'name' => 'First Name',
        'type' => 'textfield',
        'description' => '',
        'default_value' =>  '',
        'allowed_values' =>  '',
      ),
      'last_name' => array(
        'id' => 'last_name',
        'name' => 'Last Name',
        'type' => 'textfield',
        'description' => '',
        'default_value' =>  '',
        'allowed_values' =>  '',
      ),
      'email' => array(
        'id' => 'email',
        'name' => 'Email',
        'type' => 'autoreply_email',
        'description' => '',
        'default_value' =>  '',
        'allowed_values' =>  '',
      ),
      'address' => array(
        'id' => 'address',
        'name' => 'Address',
        'type' => 'textfield',
        'description' => '',
        'default_value' =>  '',
        'allowed_values' =>  '',
      ),
      'city' => array(
        'id' => 'city',
        'name' => 'City',
        'type' => 'textfield',
        'description' => '',
        'default_value' =>  '',
        'allowed_values' =>  '',
      ),
      'state_province' => array(
        'id' => 'state_province',
        'name' => 'State/Province',
        'type' => 'textfield',
        'description' => '',
        'default_value' =>  '',
        'allowed_values' =>  '',
      ),
      'country' => array(
        'id' => 'country',
        'name' => 'Country',
        'type' => 'select',
        'description' => '',
        'default_value' =>  '-',
        'allowed_values' =>  "-|Select...
Afghanistan
Åland Islands
Albania
Algeria
American Samoa
Andorra
Angola
Anguilla
Antarctica
Antigua And Barbuda
Argentina
Armenia
Aruba
Australia
Austria
Azerbaijan
Bahamas
Bahrain
Bangladesh
Barbados
Belarus
Belgium
Belize
Benin
Bermuda
Bhutan
Bolivia
Bosnia And Herzegovina
Botswana
Bouvet Island
Brazil
British Indian Ocean Territory
Brunei Darussalam
Bulgaria
Burkina Faso
Burundi
Cambodia
Cameroon
Canada
Cape Verde
Cayman Islands
Central African Republic
Chad
Chile
China
Christmas Island
Cocos (Keeling) Islands
Colombia
Comoros
Congo
Congo, The Democratic Republic Of The
Cook Islands
Costa Rica
Côte D'Ivoire
Croatia
Cuba
Cyprus
Czech Republic
Denmark
Djibouti
Dominica
Dominican Republic
Ecuador
Egypt
El Salvador
Equatorial Guinea
Eritrea
Estonia
Ethiopia
Falkland Islands (Malvinas)
Faroe Islands
Fiji
Finland
France
French Guiana
French Polynesia
French Southern Territories
Gabon
Gambia
Georgia
Germany
Ghana
Gibraltar
Greece
Greenland
Grenada
Guadeloupe
Guam
Guatemala
Guernsey
Guinea
Guinea-Bissau
Guyana
Haiti
Heard Island And Mcdonald Islands
Holy See (Vatican City State)
Honduras
Hong Kong
Hungary
Iceland
India
Indonesia
Iran, Islamic Republic Of
Iraq
Ireland
Isle Of Man
Israel
Italy
Jamaica
Japan
Jersey
Jordan
Kazakhstan
Kenya
Kiribati
Korea, Democratic People'S Republic Of
Korea, Republic Of
Kuwait
Kyrgyzstan
Lao People'S Democratic Republic
Latvia
Lebanon
Lesotho
Liberia
Libyan Arab Jamahiriya
Liechtenstein
Lithuania
Luxembourg
Macao
Macedonia, The Former Yugoslav Republic Of
Madagascar
Malawi
Malaysia
Maldives
Mali
Malta
Marshall Islands
Martinique
Mauritania
Mauritius
Mayotte
Mexico
Micronesia, Federated States Of
Moldova, Republic Of
Monaco
Mongolia
Montenegro
Montserrat
Morocco
Mozambique
Myanmar
Namibia
Nauru
Nepal
Netherlands
Netherlands Antilles
New Caledonia
New Zealand
Nicaragua
Niger
Nigeria
Niue
Norfolk Island
Northern Mariana Islands
Norway
Oman
Pakistan
Palau
Palestinian Territory, Occupied
Panama
Papua New Guinea
Paraguay
Peru
Philippines
Pitcairn
Poland
Portugal
Puerto Rico
Qatar
Réunion
Romania
Russian Federation
Rwanda
Saint Barthélemy
Saint Helena
Saint Kitts And Nevis
Saint Lucia
Saint Martin
Saint Pierre And Miquelon
Saint Vincent And The Grenadines
Samoa
San Marino
Sao Tome And Principe
Saudi Arabia
Senegal
Serbia
Seychelles
Sierra Leone
Singapore
Slovakia
Slovenia
Solomon Islands
Somalia
South Africa
South Georgia And The South Sandwich Islands
Spain
Sri Lanka
Sudan
Suriname
Svalbard And Jan Mayen
Swaziland
Sweden
Switzerland
Syrian Arab Republic
Taiwan, Province Of China
Tajikistan
Tanzania, United Republic Of
Thailand
Timor-Leste
Togo
Tokelau
Tonga
Trinidad And Tobago
Tunisia
Turkey
Turkmenistan
Turks And Caicos Islands
Tuvalu
Uganda
Ukraine
United Arab Emirates
United Kingdom
United States
United States Minor Outlying Islands
Uruguay
Uzbekistan
Vanuatu
Venezuela, Bolivarian Republic Of
Viet Nam
Virgin Islands, British
Virgin Islands, U.S.
Wallis And Futuna
Western Sahara
Yemen
Zambia
Zimbabwe",
      ),
      'message' => array(
        'id' => 'message',
        'name' => 'Message',
        'type' => 'textarea',
        'description' => '',
        'default_value' =>  '',
        'allowed_values' =>  '',
      ),
      'captcha' => array(
        'id' => 'captcha',
        'name' => 'Captcha',
        'type' => 'captcha',
        'description' => '',
        'default_value' =>  '',
        'allowed_values' =>  '',
      ),
    );
    update_option('accua_forms_avail_fields', $avail_fields);
  }

  $form_data = get_option('accua_forms_default_form_data',array());
  if (!is_array($form_data)) {
    $form_data = array();
  }

  $form_data += array(
    'success_message' => '<div style="padding: 0px 30px 10px; background-color: #fdfdfd; border: 1px solid #dfdfdf;">
<h2>Thank you {first_name} {last_name},</h2>
We have received your contact request. Check your inbox for the confirmation message.

<strong>Can\'t find the email?</strong>

It doesn\'t happen often but your mailbox could apply strict spam rules that block our email from reaching your inbox. Try checking your spam folder.

Also check that the email you entered in the form (<strong>{email}</strong>) is correct. If it is not exact you can submit the form again.

For other possible problems you may have encountered do not hesitate to contact us

</div>',
    'error_message' => '<div style="padding: 0px 30px 10px; background-color: #fdfdfd; border: 1px solid #dfdfdf;">
<h2>Oops! Something went wrong.</h2>
Internet is an awfully complex place and even though we take every precaution to make sure things run smoothly every once in a while things can go wrong that are not under our control.

Please try filling in the form again.

For other possible problems you may have encountered do not hesitate to contact us

</div>',
    'emails_from_name' => '',
    'emails_from' => '',
    'admin_emails_to' => get_option('admin_email', ''),
    'emails_bcc' => '',
    'admin_emails_subject' => 'A contact from your site',
    'admin_emails_message' => '<table style="font-family: \'Lucida Sans\',\'Lucida Grande\', Verdana, Arial, Sans-Serif !important; background: #fff; margin-top: 10px; border: 1px solid #DDDDDD; max-width: 700px;" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td>
<table style="max-width:700px;" border="0" cellspacing="0" cellpadding="0" align="center">
<tbody>
<tr>
<td>
<table style="padding: 10px; background-color: #fdfdfd; border: 1px solid #dfdfdf; max-width:700px;" border="0" cellspacing="0" cellpadding="0" align="center" bgcolor="#FFFFFF">
<tbody>
<tr valign="top">
<td style="max-width:22px;"></td>
<td style="font-family: \'Lucida Sans\',\'Lucida Grande\', Verdana, Arial, Sans-Serif !important; padding: 0px 30px 10px; max-width: 645px;">
<table>
<tbody>
<tr>
<td>On {__submitted_day_month_year} at {__submitted_hour} the following form was filled in.

<hr />

<strong>Page where the form was filled in</strong>
<a href="{__url}">{__url}</a></td>
</tr>
<tr><td>
<strong>Submission review page</strong>
<a href="{__review_submission_url}">{__review_submission_url}</a>
</td></tr>
<tr>
<td id="submitted_html">{__submitted_html}</td>
</tr>
<tr>
<td>[form_if {__referrer} [<strong>Referrer</strong> - where did the contact come from <em>before reaching the page</em>: {__referrer}]]</td>
</tr>
<tr>
<td><strong>Contact IP</strong> {__anonymized_ip}
<hr />
</td>
</tr>
<tr>
<td>[form_if {__autoreply} [The contact received in his email <a href="mailto:{email}">{email}</a> the following confirmation message:
<table>
<tbody>
<tr>
<td>{__confirmation_emails_message}</td>
</tr>
</tbody>
</table> ]]
</td>
</tr>
</tbody>
</table>
</td>
<td style="max-width:23px"></td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>',
    'confirmation_emails_subject' => 'Your message',
    'confirmation_emails_message' => '<div>

<h2>Thank you {first_name} {last_name},</h2>
Thank Your for your contact request.

We will contact you as soon as possible.

Sincerely,

The Website Team

<hr />
</div>',

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

  update_option('accua_forms_default_form_data', $form_data);
  update_option('accua_forms_db_version', ACCUA_FORMS_DB_VERSION);
}

add_action('plugins_loaded', 'accua_forms_check_db_version_and_update', 9);
function accua_forms_check_db_version_and_update() {
  $db_version = get_option('accua_forms_db_version', '');
  if (ACCUA_FORMS_DB_VERSION != $db_version) {
    accua_forms_install();
  }
}

add_filter( 'robots_txt', 'accua_forms_robots_txt', 10, 2);
function accua_forms_robots_txt($output, $public) {
  if ($public) {
    $site_url = parse_url( site_url() );
    $path = ( !empty( $site_url['path'] ) ) ? $site_url['path'] : '';
    $output .= "\nUser-agent: *\n";
    $output .= "Allow: $path/wp-admin/js/\n";
    $output .= "Allow: $path/wp-admin/css/\n";
  }
  return $output;
}

function _accua_forms_json_encode($item) {
  static $version = NULL;
  if ($version === NULL) {
    $version = version_compare(PHP_VERSION, '5.3.0', '>=');
  }
  if ($version) {
    return json_encode($item, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
  } else {
    return strtr(json_encode($item),
      array(
        '&' => '\\u0026',
        '<' => '\\u003C',
        '>' => '\\u003E',
      ));
  }
}


/* per ripulire eventuali token impostati nella versione 1.9.4 */
function accua_forms_cleanup_meta_tokens() {
  global $wpdb;
  $post_ids = $wpdb->get_col("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_accua_download_token'");

  if (!empty($post_ids)) {
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_accua_download_token'");
  }
}
register_activation_hook(__FILE__, 'accua_forms_cleanup_meta_tokens');