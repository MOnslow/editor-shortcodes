(function() {
	var myData = [];
	for(var i in php_vars.data) {
		myData.push({
            text: php_vars.data[i][0],
            value: php_vars.data[i][1]
        });
	}
	tinymce.PluginManager.add( 'shortcodes', function( editor, url ) {
		editor.addButton('shortcodes', {
			type: 'listbox',
			tooltip: 'See shortcode document for more',
			text: 'Insert Shortcode',
			icon: 'code',
			onselect: function (e) {
				editor.insertContent(this.value());
			},
			values: myData
		});
	});
})();