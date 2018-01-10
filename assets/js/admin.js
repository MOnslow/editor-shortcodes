jQuery(document).ready(function($) {

	// Clipboard
	var clipboard = new Clipboard('.clipboard');
	clipboard.on('success', function(e) {
		e.clearSelection();
		$('.tip').addClass('active');
		setTimeout(function(){
			$('.tip').removeClass('active');
		}, 2000);
	});

	// Show/Hide Placeholder Content
	 $('input[name="es_shortcode_type"]').change(function(){
		if(this.value === 'single') {
			$('.placeholder-content').hide();
		} else {
			$('.placeholder-content').show();
		}
	});
	$('input[name="es_shortcode_type"]').filter(':checked').change();

	// Autosize Textarea
	autosize($('.shortcodes-meta-box textarea'));


	let $output = $('#es_rendered_shortcode').val();

	// Update shortcode name
	let $shortcode_value = $('#es_shortcode').val();
	console.log($shortcode_value);
	$('body').on('focusout', '#es_shortcode', function(){
		let $new_shortcode_value = $(this).val();
		if ($shortcode_value != '') {
			$output = $output.replace($shortcode_value, $new_shortcode_value);
		} else {
			$output = $output.replace('[]', '['+$new_shortcode_value+']');
		}
		$('#es_rendered_shortcode').val($output);
	});
	$('body').on('focusin', '#es_shortcode', function(){
		$shortcode_value = $(this).val();
	});

	// Update shortcode content
	let $shortcode_content_value = $('#editor_shortcode_content').val();
	$('body').on('focusout', '#editor_shortcode_content', function(){
		let $new_shortcode_content_value = $(this).val();
		$output = $output.replace($shortcode_content_value, $new_shortcode_content_value);
		$('#es_rendered_shortcode').val($output);
	});
	$('body').on('focusin','#editor_shortcode_content', function(){
		$shortcode_content_value = $(this).val();
	});

	for (i = 0; i < 11; i++) {
		// Update attribute keys
		let $atts_key = $('[name="es_attributes['+i+'][key]"]').val();
		$('body').on('focusout', '[name="es_attributes['+i+'][key]"]', function(){
			let $new_atts_key = $(this).val();
			$output = $output.replace($atts_key, $new_atts_key);
			$('#es_rendered_shortcode').val($output);
		});
		$('body').on('focusin', '[name="es_attributes['+i+'][key]"]', function(){
			$atts_key = $(this).val();
		});

		// Update attribute values
		let $atts_value = $('[name="es_attributes['+i+'][value]"]').val();
		$('body').on('focusout', '[name="es_attributes['+i+'][value]"]', function(){
			let $new_atts_value = $(this).val();
			$output = $output.replace($atts_value, $new_atts_value);
			$('#es_rendered_shortcode').val($output);
		});
		$('body').on('focusin', '[name="es_attributes['+i+'][value]"]', function(){
			$atts_value = $(this).val();
		});
	}



});