(function() {
	tinymce.create('tinymce.plugins.buttonPlugin', {
		init : function(ed, url) {
			var post_id = jQuery('input#post_ID').val();
			// Register commands
			ed.addCommand('mcebutton', function() {
				ed.windowManager.open({
					file : ajaxurl + '?action=accua_shortcode_button_popup&post='+ post_id, // file that contains HTML for our modal window
					width : 450,
					height : 200,
					inline : 1
				}, {
					plugin_url : url
				});
			});
			 
			// Register buttons
			ed.addButton('accua_fancy_button', {title : 'WordPress Contact Forms by Cimatti', cmd : 'mcebutton', image: url + '/img/cimatti-icon-20.png' });
		},
		 
		getInfo : function() {
			return {
				longname : 'WordPress Contact Forms by Cimatti',
				author : 'Cimatti',
				authorurl : 'http://www.cimatti.it',
				infourl : 'http://www.cimatti.it/wordpress/contact-forms/',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});
	 
	// Register plugin
	// first parameter is the button ID and must match ID elsewhere
	// second parameter must match the first parameter of the tinymce.create() function above
	tinymce.PluginManager.add('accua_fancy_button', tinymce.plugins.buttonPlugin);

})();