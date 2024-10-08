<?php
if (!defined('ABSPATH')) {
  die;
}

wp_enqueue_script('jquery');
wp_enqueue_script('tiny_mce_popup', includes_url( 'js/tinymce/tiny_mce_popup.js' ));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>WordPress Contact Forms by Cimatti</title>
<?php
  wp_print_styles();
  wp_print_head_scripts();
?>
<style>
#button-dialog div {
  padding: 5px 0;
  height: 20px;
}

#button-dialog label {
  display: block;
  float: left;
  margin: 0 8px 0 0;
  width: 80px;
}

#button-dialog select,#button-dialog input {
  display: block;
  float: right;
  width: 100px;
  padding: 3px 5px;
}

#button-dialog select {
  width: 112px;
}

#button-dialog #insert {
  display: block;
  line-height: 24px;
  text-align: center;
  margin: 10px 0 0 0;
  float: right;
}

#accua-dialog div {
  padding: 0px 0 3px 0;
}

#opzioni_fancy label {
  display: block;
  float: left;
  width: 82px;
  color: #ccc;
}

#accua-dialog #select_form select,#accua-dialog #select_file select,#accua-dialog #select_azione select
  {
  font-size: 11px;
  padding: 4px;
  width: 234px;
}

#accua-dialog #select_form label,#accua-dialog #select_file label,#accua-dialog #select_azione label
  {
  display: block;
  float: left;
  font-size: 13px;
  line-height: 25px;
  margin-right: 10px;
  width: 50px;
}
</style>

<script type="text/javascript">
function insertAccuaFormButtonDialog() {
		AccuaFormButtonDialog.insert(AccuaFormButtonDialog.local_ed);
}

var AccuaFormButtonDialog = {
	local_ed : 'ed',
	init : function(ed) {
	  AccuaFormButtonDialog.local_ed = ed;
		tinyMCEPopup.resizeToInnerSize();
	},
	insert : function insertButton(ed) {
		var fid = jQuery('#accua-dialog select#accua-fid').val();
		var contenuto = jQuery('#accua-dialog textarea#accua-contenuto').val();

		if (fid=='no-sel') {
			alert( <?php print _accua_forms_json_encode(__("Please select a form", 'contact-forms')); ?> );
		} else {
			// Try and remove existing style / blockquote
			tinyMCEPopup.execCommand('mceRemoveNode', false, null);

			var output = '[accua-form fid="'+ fid +'"]';

			tinyMCEPopup.execCommand('mceReplaceContent', false, output);
			tinyMCEPopup.close();
		}
	}
};
tinyMCEPopup.onInit.add(AccuaFormButtonDialog.init, AccuaFormButtonDialog);

</script>

</head>
<body>
	<div id="accua-dialog">
		<form action="/" method="get" accept-charset="<?php echo get_option('blog_charset'); ?>">
			<div id="select_form">
			<?php $accua_form=get_option('accua_forms_saved_forms', array());  ?>
				<label for="accua-fid"><?php _e('Form') ?></label>
				<select name="accua-fid" id="accua-fid">
					<option value="no-sel"><?php _e('Select') ?></option>
			<?php 	foreach($accua_form as $fid => $form) { ?>
						<option value="<?php echo $fid; ?>"> <?php $title=empty($form['title']) ? $fid : $form['title']; echo htmlspecialchars($title); ?></option>
					<?php } ?>

				</select>
			</div> <br clear="all" />
			<div>
				<input type="button" id="insert" name="insert" value="<?php _e('Insert', 'contact-forms'); ?>" onclick="insertAccuaFormButtonDialog(); " />
			</div>
		</form>
	</div>
</body>
</html>