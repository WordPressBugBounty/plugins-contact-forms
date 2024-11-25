=== WordPress Contact Forms by Cimatti ===
Contributors: cimatti
Tags: contact, form, forms, contact form, contact forms, feedback, mail, email, ajax, attachment, curriculum, contact us, custom form, excel, form builder, web form, feedback, form manager, form to email, form to database, landing page, file upload, email form, customer request, spare parts, invitations, event forms, qtranslate, w3 total cache, drag and drop, form framework, form designer, form creator, php form builder
Requires at least: 3.5
Tested up to: 6.7.1
Stable tag: 1.9.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create and publish forms in your WordPress website with drag and drop. Contact forms, landing page forms, invitations, and more.

== Description ==
Forms are an essential component of any website. WordPress Contact Forms is the culmination of years of experience building and developing business websites of all types. Our plugin focuses on simplicity and power, it captures, stores and helps to classify contacts and leads according to their lead status. It's ideal for single-language and multilingual sites, simple blogs, or complex WordPress-powered Content Management Systems. If your website handles a considerable amount of contacts and you need to make diverse forms our plugin is an excellent choice.
= Select, Configure, and Embed Forms =
Choose the fields you require, customize on-screen messages and email responses, preview, test, and effortlessly embed forms into your posts, pages, or custom content types using shortcodes or the built-in TinyMCE button.
= Create Forms for Any Purpose =
Create as many forms as you need. Design landing pages, contact pages, invitations, job application forms with curriculum upload, customer request forms, spare part requests, and more. Utilize the "Clone" feature to avoid "reinventing the wheel" when creating new forms.
= Ready-to-Use Features =
WordPress Contact Forms has commonly used fields like First Name, Last Name, Address, Province, Country, Telephone, Email, Captcha, and default success messages and email notifications. Simply create a drag-and-drop form, save it, go to a post or page, and click the orange "C" icon in the WYSIWYG editor to insert a contact form into the post or page content.
= Easy to Use for Beginners =
These features make it effortless for first-time users, but the plugin's fast learning curve will soon entice you to explore its advanced features.
= Craft Superior Forms =
Don't leave anything to chance; fine-tune the entire form submission process.
= Create Reusable Fields =
Build custom fields to reuse multiple times, starting with 20 available field types, including Text Field, Text Area, Email, Autoreply Email, Checkbox, Checkbox Group, Radio Buttons, Select, Multiple Select, Hidden Value, File Upload, Captcha, Custom HTML, Password, and more.
= Customize Forms to Perfection =
Apply visual styles to your forms using options in the "Appearance" tab. Select colors, margins, button styles, and more.
= Custom Messages for Success =
Each form can have its own unique online success message. So, when a form is completed, you can provide access to a file download link, coupon information, or simply a message to confirm that the form was submitted correctly.
= Email Notifications for Administrators and Visitors =
Send email notifications to administrators, including tracking information like visitor IP, referrer, date and time of form submission, and other user properties (for a full list, see the Beginners' Guide). Send an email notification to the visitor as further confirmation that the form was filled in correctly. All email notifications can be tested in the Preview/Test Tab before publishing.

= Data Collection and Excel Export =
All submitted data is securely stored in your WordPress database. Contacts received can be easily categorized into lead status categories and spam and tests can be easily discarded. Add notes on each contact received to keep track. 

All contact data received can be filtered, searched, and exported to Excel at any time. You can export all the data or just the data you need. The Advanced Excel Export option allows you to export to a file with ready-to-use filtering options. 

WordPress Contact Forms also includes a tracking graph in its Dashboard that displays the performance of all or each of your website forms over time.

= Developer-Friendly API =
WordPress Contact Forms includes an API to assist developers in customizing and adding their own features. WordPress Filters are used to customize forms during generation, to check and validate submitted form values, to execute custom code using sent data, and to add custom tokens for messages. Read the documentation on our site for more information.
Powerful PHP Form Builder Class
WordPress Contact Forms utilizes a PHP form builder class to generate the forms, ensuring robust and efficient form creation and management.

== Installation ==
1. Upload `/contact-forms/` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Create one or more forms using the drag and drop interface.
4. Edit posts (or pages or custom types) and add the desired form using the "C" button in the visual editor (or using the shortcode.

== Frequently Asked Questions ==
You'll find the [FAQ on our website](http://www.cimatti.it/en/wordpress-plugins/contact-forms/faq/).

= Where do I report security bugs found in this plugin? =
Please report security bugs found in the source code of the
WordPress Contact Forms by Cimatti plugin through the [Patchstack
Vulnerability Disclosure Program](https://patchstack.com/database/vdp/contact-forms). The
Patchstack team will assist you with verification, CVE assignment, and
notify the developers of this plugin.

== Screenshots ==
1. Dashboard. Filter to show statisics from all forms or from individual forms.
2. Create forms easily with drag and drop.
3. Messages tab. Set up online success messages and email messages.
4. Preview tab. and test forms.
5. Choose styling options in Settings Page or in Preview tab.
6. Create your own fields for use in all forms.
7. List and manage forms. Edit, clone, see form submissions report, put in trash, copy shortcode or php code
8. Use screen options to show only the fields you need. Filter or search, then export.
9. Settings Page. Set up default options for all forms to avoid repetition. 

== Changelog ==
= 1.9.3 =
* Matomo support for tracking field filled in and form submissions as events
* Fixed CSRF vulnerability

= 1.9.2 =
* Reverted an incorrect fix in the previous version

= 1.9.1 =
* Hardening for potential SQL injection vulnerabilities

= 1.9.0 =
* Checks for unfiltered_html capability and supports limited admin permissions in WordPress multisite configurations
* Filters the list of allowed upload file extensions according get_allowed_mime_types(), and check the maximum upload size with wp_max_upload_size()
* Default date fix
* Other minor fixes

= 1.8.0 =
* Using tinyColorPicker on frontend
* Fixed XSS vulnerability

= 1.7.0 =
* Lead status
* Minor fixes

= 1.6.1 =
* Fixed trashed form restore
* Fixed CSRF vulnerability

= 1.6.0 =
* IP masking option

= 1.5.10 =
* Fixed plugin icon

= 1.5.9 =
* Fixed adding more custom HTML fields and fieldsets

= 1.5.8 =
* Fixed CSRF vulnerabilities
* Fixed some JavaScript errors due to WordPress filters
* Fixed many minor bugs

= 1.5.7 =
* tested compatibility with WordPress 6.2.0

= 1.5.6 =
* Fixed CSRF vulnerability

= 1.5.5 =
* Fixed XSS vulnerabilities

= 1.5.4 =
* Graphical changes to default messages
* Fixed some minor bugs

= 1.5.3 =
* Removed unused code
* Fixed some minor issues

= 1.5.2 =
* Fixed critical error when trying to access the dashboard

= 1.5.1 =
* Renewed UI
* Fixing some minor bugs

= 1.4.14 =
* Lazy load reCAPTCHA

= 1.4.13 =
* added html attributes to improve SEO

= 1.4.12 =
* Tested compatibility with PHP up to 8.0 and WordPress up to 5.8.1
* reCAPTCHA loaded from recaptcha.net trying to improve accessibility from countries banning google.com domain
* Fixed minor XSS vulnerability reported on wpscan.com by Felipe Restrepo Rodriguez and Sebastian Cruz Cardona. Form title was not sanitized in every place it was used in the admin interface, however this is mitigated by the fact that only admin users with manage_options capability can edit it.

= 1.4.11 =
* avoid "headers already sent" warnings during WordPress cron

= 1.4.10 =
* Tested compatibility with PHP up to 7.4
* After submission, the fragment #formSubmitSuccess-formID is added to the URL, so the page is scrolled to the top of the message, and it's easier to track the submission with tools like Google Analytics
* fragments #formSubmitInvalid-formID and #formSubmitError-formID are added in case of invalid submission or error
* improved loading of reCAPTCHA
* removed unused resources from PFBC library
* colorPicker styles and javascripts now loads only if it's used
* other small bugfixes

= 1.4.9 =
* improved compatibility with Google Tag Manager to track field filled in and form submission

= 1.4.8 =
* fixed compatibility issues with php 7.2
* option to track field filled in and form submission as events on Google Analytics
* workaround to open/download attachments in submissions exported and opened with Microsoft Excel

= 1.4.7 =
* fixed compatibility issue with WordPress 4.8 that made show/hide buttons for field settings in the form editor invisible
* fixed compatibility issues with php 7.0 and 7.1
* fixed strict standards errors

= 1.4.6 =
* restored compatibility with PHP < 5.3
* fixed some strict standards errors

= 1.4.5 =
* Scaled reCAPTCHA 2 area for devices with less than 400px screen width
* More informations about senders and receivers of the form emails in the forms list page

= 1.4.4 =
* Changed text domain of translatable strings to match the plugin slug
* Bulk action to delete permanently trashed submissions

= 1.4.3 =
* Fixed table index length issue that prevented saving submission values of new user of Contact Forms with recent WordPress versions
* Added internationalization info

= 1.4.2 =
* Fixed table definition error that prevented saving submission of new users of Contact Forms 1.4.0

= 1.4.1 =
* Fixed undefined variable in accua-form-api.php on line 29

= 1.4.0 =
* Filter and export by year and month
* Added actions accua_forms_field_added, accua_forms_field_updated and accua_forms_field_deleted

= 1.3.9 =
* better support for reCAPTCHA allowing to enter site keys and use version 2
* fixed visualization bug of reCAPTCHA 1 with new WordPress themes
* fixed bug that prevented editing fields on the page after a form submission

= 1.3.8 =
* fixed incompatibility with WordPress 4.4 that prevented submissions export

= 1.3.7 =
* fixed incompatibility with WordPress 4.4 that caused a PHP error in every page that includes a form
* new token for select, checkbox and radio labels
* changed database table to allow referrers and urls longer than 255 characters 

= 1.3.6 =
* Replaced deprecated user level '10' with capability 'manage_options'

= 1.3.5 =
* fixed incompatibility of form editor with WordPress 4.3 and Chrome
* adding rules to robots.txt to allow /wp-admin/js/ and /wp-admin/css/ for styles and scripts included from that folders

= 1.3.4 =
* Password fields now saves the hash value of the password using wp_hash_password
* Field to set the emails "From:" name
* fixed CAPTCHA field incompatibility with CloudFlare RocketLoader and possibly other JavaScript optimizer
* fixed glitch in the "Form fields" area on the "Edit form" page with latest versions of WordPress

= 1.3.3 =
* improved checkboxes, select and radio definition to allow pre-selected options
* fixed PHP 5.5 incompatibility issue. Now the plugin works with PHP from version 5.2 to 5.5
* fixed default value for email, colorpicker and password fields
* allowed removal of elements by a filter after the form generation

= 1.3.2 =
* fixed visualization of color picker field
* workaround to have multiple forms with recaptcha on the same page

= 1.3.1 =
* fixed validation of required fields with multiple values
* show recipient of admin email in form list
* changes in submissions list generation and export to allow usage by other plugins

= 1.3 =
* Spanish translation by Maria Ramos of [WebHostingHub](http://www.webhostinghub.com/)
* Color picker field
* Possibility to insert raw tokens in html messages
* Disabled HTML5 validation of email fields, using JavaScript validation

= 1.2.1 =
* Fixed installation and upgrade process issues introduced in 1.2
* Users who installed 1.2 as their first version reported that submissions where not saved. Upgrading to this version will fix this issue

= 1.2 =
* Fieldsets
* Submissions trash and restore
* Fixed counting of active and deleted forms
* Fixed submission bug in Internet Explorer 8 and previous versions

= 1.1 =
* Added interface to set basic form styles (borders, colors, padding)
* Fixed captcha validation
* Added submission graph by form
* Screenshots removed from the package
* Other minor fixes