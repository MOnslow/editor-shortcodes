<?php
/*
Plugin Name: Editor Shortcodes
Plugin URI: https://www.logicdesign.co.uk
Description: Add shortcodes to the WordPress editor.
Version: 1.0
Author: Logic Design &amp; Consultancy Ltd
Author URI: https://www.logicdesign.co.uk
*/

/**
* Assets
*/
function editor_shortcodes_admin_assets() {
	wp_enqueue_style('admin-styles', plugin_dir_url(__FILE__).'assets/css/admin.css');

	wp_enqueue_script('clipboard', plugin_dir_url(__FILE__).'vendor/clipboard/clipboard.min.js', null, null, true);
	wp_enqueue_script('autosize', plugin_dir_url(__FILE__).'vendor/autosize/autosize.min.js', null, null, true);
	wp_enqueue_script('admin-scripts', plugin_dir_url(__FILE__).'assets/js/admin.js', null, null, true);
}
add_action('admin_enqueue_scripts', 'editor_shortcodes_admin_assets');

/**
* Register Post Type
*/
function editor_shortcodes_register_post_type() {
	register_post_type('editor_shortcodes', [
		'labels'  => [
			'name'               => 'Shortcodes',
			'singular_name'      => 'Shortcode',
			'menu_name'          => 'Shortcodes',
			'name_admin_bar'     => 'Shortcode',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Shortcode',
			'new_item'           => 'New Shortcode',
			'edit_item'          => 'Edit Shortcode',
			'view_item'          => 'View Shortcode',
			'all_items'          => 'All Shortcodes',
			'search_items'       => 'Search Shortcodes',
			'parent_item_colon'  => 'Parent Shortcodes:',
			'not_found'          => 'No Shortcodes Found.',
			'not_found_in_trash' => 'No Shortcodes in Trash.'
		],
		'public'        => true,
		'has_archive'   => false,
		'publicly_queryable'   => true,
		'rewrite'       => ['slug' => 'editor-shortcodes'],
		'show_in_menu'  => true,
		'menu_position' => 52,
		'menu_icon'     => 'dashicons-editor-code',
		'supports'      => ['title'],
	]);
}
add_action('init', 'editor_shortcodes_register_post_type');

/**
* Create Meta Box
*/
function editor_shortcodes_add_meta() {
	add_meta_box(
		'editor_shortcodes_postmeta',
		'Shortcode Details',
		'editor_shortcodes_postmeta',
		'editor_shortcodes',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'editor_shortcodes_add_meta' );

/**
* Meta Box Layout
*/
function editor_shortcodes_postmeta() {
	global $post;
	$meta = get_post_custom($post->ID);
	$attributes = get_post_meta($post->ID,'es_attributes',false);
	?>
		<div class="shortcodes-meta-box">

			<?php
				$output = '';
				$output .= '[';
				$output .= $meta['es_shortcode'][0];
				if (!empty($attributes)) {
					foreach ($attributes[0] as $attribute) {
						$output .= ' '.$attribute['key'].'="'.$attribute['value'].'"';
					}
				}
				$output .= ']';
				if ($meta['es_shortcode_type'][0] == 'multi'){
					if (!empty($meta['editor_shortcode_content'][0])) {
						$output .= '<br /><br />'.$meta['editor_shortcode_content'][0].'<br /><br />';
					} else {
						$output .= '<br /><br />Lorem ipsum dolor sit amet...<br /><br />';
					}
					$output .= '[/'.$meta['es_shortcode'][0].']';
				}
			?>
			<input name="es_rendered_shortcode" type="hidden" value="<?php echo htmlspecialchars($output); ?>" id="es_rendered_shortcode" />

			<?php if (!empty($meta['es_shortcode'][0])) : ?>
				<div class="es-shortcode-preview">
					<label>Shortcode Preview</label>
					<pre><span class="tip">Copied to clipboard</span><button title="Copy to clipboard" type="button" class="clipboard" data-clipboard-target="#es-shortcode-preview" data-tip="Copied to clipboard"><span class="dashicons dashicons-clipboard"></span></button><code><span id="es-shortcode-preview"><?php echo $output; ?></span></code></pre>
				</div>
			<?php endif; ?>

			<label for="es_shortcode">Shortcode <small>Lowercase, no spaces - use underscores.</small></label>
			<input type="text" name="es_shortcode" id="es_shortcode" class="update-field" value="<?php echo $meta['es_shortcode'][0]; ?>" required>

			<?php if (!$meta['es_shortcode'][0]) : ?>

				<p class="no-shortcode-message">Please save your shortcode for more options.</p>

			<?php else : ?>

				<label for="es_description">Description <small>For reference</small></label>
				<textarea name="es_description" id="es_description"><?php echo $meta['es_description'][0]; ?></textarea>

				<label>Type <small>Either a single line shortcode, or one that wraps content.</small></label>
				<div class="radio-group">
					<label for="es_shortcode_type_single"><input type="radio" name="es_shortcode_type" id="es_shortcode_type_single" value="single" checked <?php if ($meta['es_shortcode_type'][0] == 'single'){ echo ' checked="checked"'; } ?>>Standalone</label>
					<label for="es_shortcode_type_multi"><input type="radio" name="es_shortcode_type" id="es_shortcode_type_multi" value="multi" <?php if ($meta['es_shortcode_type'][0] == 'multi'){ echo ' checked="checked"'; } ?>>Wrapping</label>
				</div>

				<div class="placeholder-content">
					<label for="editor_shortcode_content">Placeholder Content</label>
					<input type="text" name="editor_shortcode_content" id="editor_shortcode_content" value="<?php echo $meta['editor_shortcode_content'][0]; ?>">
				</div>

				<label>Attributes <small>Allow for customisation of shortcodes.</small></label>
				<?php
					$c = 0;
					if ( count( $attributes ) > 0 ) {
						foreach( $attributes[0] as $attribute ) {
							if ( isset( $attribute['key'] ) || isset( $attribute['value'] ) ) {
								printf( '<div class="row"><div class="col-xs-5 col-lg-4"><label>Attribute <small>Lowercase, no spaces - use underscores.</small></label><input type="text" class="update-field" name="es_attributes[%1$s][key]" value="%2$s" /></div><div class="col-xs-5 col-lg-7"><label>Hint <small>Explain what values are expected</small></label><input type="text" name="es_attributes[%1$s][value]" value="%3$s" /></div><div class="col-xs-2 col-lg-1"><label>&nbsp;</label><span class="remove-attribute button button-secondary">%4$s</span></div></div>', $c, $attribute['key'], $attribute['value'], '<span class="dashicons dashicons-trash"></span>');
								$c = $c +1;
							}
						}
					}
				?>
				<div id="attribute-list"></div>
				<a class="add-attribute button button-primary button-hero" href="#">Add Attribute</a>
				<script>
					var $ =jQuery.noConflict();
					$(document).ready(function() {
						var count = <?php echo $c; ?>;
						$('.add-attribute').click(function() {
							count = count + 1;
							if (count < 11) {
								$('#attribute-list').append('<div class="row"><div class="col-xs-5 col-lg-4"><label>Attribute <small>Lowercase, no spaces - use underscores.</small></label><input type="text" class="update-field" name="es_attributes['+count+'][key]" value="" /></div><div class="col-xs-5 col-lg-7"><label>Hint <small>Explain what values are expected</small></label><input type="text" name="es_attributes['+count+'][value]" value="" /></div><div class="col-xs-2 col-lg-1"><label>&nbsp;</label><span class="remove-attribute button button-secondary"><span class="dashicons dashicons-trash"></span></span></div></div>' );
							} else {
								alert('Easy tiger, let\'s not add too many attributes!');
							}
							return false;
						});
						$('body').on('click', '.remove-attribute', function() {
							$(this).parents('.row').remove();
						});
					});
				</script>

				<div class="es-shortcode-render">
					<label for="es_rendered_html">Rendered HTML <small>What the shortcodde gets replaced with when rendered. Use variables as outlined below.</small></label>
					<?php
						if (!empty($attributes)) {
							echo '<div class="attribute-code-list">Available Attributes: ';
							foreach ($attributes[0] as $attribute) {
								echo '<code>{$'.$attribute['key'].'}</code>';
							}
							echo '</div>';
						}
						echo '<div class="placeholder-content attribute-code-list">For your placeholder content, use: ';
						echo '<code>{$editor_shortcode_content}</code>';
						echo '</div>';
					?>
					<textarea class="code" name="es_rendered_html" id="es_rendered_html"><?php echo $meta['es_rendered_html'][0]; ?></textarea>

				</div>

			<?php endif; ?>

		</div>
	<?php
}

/**
* Save Meta Box
*/
function editor_shortcodes_save_postmeta() {
	global $post;
	$keys = array(
		'es_shortcode',
		'es_description',
		'es_shortcode_type',
		'es_attributes',
		'es_rendered_html',
		'es_rendered_shortcode',
		'editor_shortcode_content',
	);
	foreach($keys as $key){
		if( isset($_POST[$key]) ) {
			update_post_meta($post->ID, $key , $_POST[$key]);
		}
	}

}
add_action( 'save_post', 'editor_shortcodes_save_postmeta' );

/**
* Define Admin Columns
*/
function editor_shortcodes_define_columns($columns){
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => 'Name',
		'es_description' => 'Description',
		'es_shortcode' => 'Shortcode',
	);
	return $columns;
}
add_filter('manage_editor_shortcodes_posts_columns', 'editor_shortcodes_define_columns', 0);

/**
* Display Admin Columns
*/
function editor_shortcodes_display_columns($column, $post_ID){
	global $post;
	switch ($column) {
		case 'es_description':
			echo htmlspecialchars($post->es_description);
		break;
		case 'es_shortcode':
			echo '<code>'.htmlspecialchars($post->es_rendered_shortcode).'</code>';
		break;
	}
}
add_action('manage_editor_shortcodes_posts_custom_column', 'editor_shortcodes_display_columns', 10, 2);

/**
* Get Shortcodes for WP Editor Dropdown
*/
function editor_shortcodes_fetch_list() {
	global $wpdb;
	$get_es_posts = $wpdb->get_results("
		SELECT p.post_title, pm.meta_value, pm.meta_key, pm.post_id
		FROM loki_postmeta pm
		JOIN loki_posts p ON pm.post_id = p.ID
		WHERE p.post_type = 'editor_shortcodes'
		AND p.post_status = 'publish'
		AND pm.meta_key LIKE 'es_rendered_shortcode'
	");

	$i = 0;
	$data = array();
	foreach ($get_es_posts as $es_meta_keys) {
		$i++;
		$data[$i][] = $es_meta_keys->post_title;
		$data[$i][] = $es_meta_keys->meta_value;
	}
	$dataToBePassed = array('data' => $data);
	wp_enqueue_script( 'tinymce_js', includes_url( 'js/tinymce/' ) . 'wp-tinymce.php', array( 'jquery' ), false, true );
	wp_enqueue_script('es-tinymce', plugin_dir_url(__FILE__).'assets/js/es_tinymce.js', null, null, true);
	wp_localize_script( 'es-tinymce', 'php_vars', $dataToBePassed );
}
add_action( 'plugins_loaded', 'editor_shortcodes_fetch_list' );

/**
* Get Shortcodes
*/
function editor_shortcodes_get_shortcodes() {
	global $wpdb;

	$get_es_posts = $wpdb->get_results("
		SELECT p.post_title, pm.meta_value, pm.meta_key, pm.post_id
		FROM loki_postmeta pm
		JOIN loki_posts p ON pm.post_id = p.ID
		WHERE p.post_type = 'editor_shortcodes'
		AND p.post_status = 'publish'
		AND pm.meta_key LIKE 'es_shortcode'
	");
	foreach ($get_es_posts as $es_meta_keys) {
		add_shortcode( $es_meta_keys->meta_value, 'editor_shortcodes_render' );
	}
}
add_action( 'the_post', 'editor_shortcodes_get_shortcodes' );

/**
* Add Shortcodes to TinyMCE
*/
function editor_shortcodes_tinymce_setup(){
	add_action( 'init', 'editor_shortcodes__tinymce_button' );
}
add_action( 'after_setup_theme', 'editor_shortcodes_tinymce_setup' );
function editor_shortcodes__tinymce_button() {
	if (!current_user_can('edit_posts') && ! current_user_can('edit_pages')) {
		return;
	}
	if (get_user_option('rich_editing') !== 'true') {
		return;
	}
	add_filter('mce_external_plugins', 'editor_shortcodes_add_buttons');
	add_filter('mce_buttons', 'editor_shortcodes_register_buttons');
}
function editor_shortcodes_add_buttons($plugin_array) {
	$plugin_array['shortcodes'] = plugin_dir_url(__FILE__).'assets/js/es_tinymce.js';
	return $plugin_array;
}
function editor_shortcodes_register_buttons($buttons) {
	array_push($buttons, 'shortcodes');
	return $buttons;
}

/**
* Render shortcode
*/
function editor_shortcodes_render($atts, $content = null, $variable) {
	$content = force_balance_tags(do_shortcode(force_balance_tags($content)));
	extract( shortcode_atts(
		array(
			'name' => $variable,
		), $atts )
	);
	$args = array (
		'post_type'     => 'editor_shortcodes',
		'post_status'   => 'publish',
		'meta_query'    => array(
			array(
				'key'   => 'es_shortcode',
				'value' => $name
			)
		)
	);

	$query = new WP_Query( $args );

	if ( $query->have_posts() && $name != '' ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$es_rendered_html = get_post_meta( get_the_ID() , 'es_rendered_html' , true);
			$es_rendered_html = str_replace('{$editor_shortcode_content}', $content, $es_rendered_html);
			if (!empty($atts)) {
				foreach($atts as $key => $value){
					$es_rendered_html = str_replace('{$'.$key.'}', $value, $es_rendered_html);
					$content = $es_rendered_html;
				}
			} else {
				$content = $es_rendered_html;
			}
		}
		return $content;
	} else {
		return '<p style="color: red" >Shortcode not found.</p>';
	}
	wp_reset_postdata();
}

/**
* Remove Empty Paragraphs
*/
function editor_shortcodes_empty_paragraph_fix( $content ) {
	global $wpdb;
	$get_es_posts = $wpdb->get_results("
		SELECT p.post_title, pm.meta_value, pm.meta_key, pm.post_id
		FROM loki_postmeta pm
		JOIN loki_posts p ON pm.post_id = p.ID
		WHERE p.post_type = 'editor_shortcodes'
		AND p.post_status = 'publish'
		AND pm.meta_key LIKE 'es_shortcode'
	");
	$shortcodes = array();
	foreach ($get_es_posts as $es_meta_keys) {
		$shortcodes[] = $es_meta_keys->meta_value;
	}
	foreach ( $shortcodes as $shortcode ) {
		$array = array (
			'<p>[' . $shortcode => '[' .$shortcode,
			'<p>[/' . $shortcode => '[/' .$shortcode,
			$shortcode . ']</p>' => $shortcode . ']',
			$shortcode . ']<br />' => $shortcode . ']'
		);
		$content = strtr( $content, $array );
	}
	return $content;
}
add_filter( 'the_content', 'editor_shortcodes_empty_paragraph_fix' );