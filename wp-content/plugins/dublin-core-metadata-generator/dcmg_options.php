<?php
add_action( 'admin_menu', 'dublincore_metadata_generator_add_admin_menu' );
add_action( 'admin_init', 'dublincore_metadata_generator_settings_init' );
add_action( 'admin_enqueue_scripts', 'dublincoremetadatagenerator_image_enqueue' );

function dublincoremetadatagenerator_image_enqueue() {
	global $pagenow;
	$plugin_dir_uri = plugin_dir_url( __FILE__ );
    if($pagenow == 'options-general.php') :
        wp_enqueue_media();
        wp_register_script( 'meta-box-image',  $plugin_dir_uri . 'js/dcmg-box-image.js', array( 'jquery' ) );
        wp_localize_script( 'meta-box-image', 'meta_image',
            array(
                'title' => __( 'Choose your image', 'dublincoremetadatagenerator' ),
                'button' => __( 'Use this image', 'dublincoremetadatagenerator' ),
            )
        );
        wp_enqueue_script( 'meta-box-image' );
    endif;
}

//Register settings

function dublincore_metadata_generator_add_admin_menu(  ) { 
	add_options_page( 'Dublin Core Metadata Generator', 'Dublin Core Metadata Generator', 'manage_options', 'dublincore_metadata_generator', 'dublincore_metadata_generator_options_page' );
}

function dublincore_metadata_generator_settings_init(  ) { 
	register_setting( 'dcmg_settings_page', 'dublincore_metadata_generator_settings' );
	add_settings_section(
		'dcmg_settings_page_section', 
		__( 'Select the metadata to display in your pages', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_settings_section_callback', 
		'dcmg_settings_page'
	);

	add_settings_field( 
		'dublincore_metadata_generator_dublincore', 
		__( 'Dublin Core Metadata (DC)', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_dublincore_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);
	
	add_settings_field( 
		'dublincore_metadata_generator_cleanmetas', 
		__( 'Remove useless meta tags', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_cleanmetas_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);

	add_settings_field( 
		'dublincore_metadata_generator_cleanyoast', 
		__( 'Remove Yoast SEO comments', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_cleanyoast_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);

	add_settings_field( 
		'dublincore_metadata_generator_opengraph', 
		__( 'Open Graph Metadata (OG)', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_opengraph_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);

	add_settings_field( 
		'dublincore_metadata_generator_html_opengraph', 
		__( 'Open Graph Schema', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_html_opengraph_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);

	add_settings_field( 
		'dublincore_metadata_generator_default_image', 
		__( 'Default image (used by OG and Social Media)', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_default_image_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);

	add_settings_field( 
		'dublincore_metadata_generator_fb_admins', 
		__( 'ID for FB: admins', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_fb_admins_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);

	add_settings_field( 
		'dublincore_metadata_generator_description', 
		__( 'Meta tag Description', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_description_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);

	add_settings_field( 
		'dublincore_metadata_generator_keywords', 
		__( 'Meta tag Keywords', 'dublincoremetadatagenerator' ), 
		'dublincore_metadata_generator_keywords_render', 
		'dcmg_settings_page', 
		'dcmg_settings_page_section' 
	);

}

function dublincore_metadata_generator_dublincore_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
?>
	<input name='dublincore_metadata_generator_settings[dublincore_metadata_generator_dublincore]' value='0' type='hidden'>
	<input type='checkbox' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_dublincore]' <?php checked( $options['dublincore_metadata_generator_dublincore'], 1 ); ?> value='1'>
	<?php
}

function dublincore_metadata_generator_opengraph_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
?>
	<input name='dublincore_metadata_generator_settings[dublincore_metadata_generator_opengraph]' value='0' type='hidden'>
	<input type='checkbox' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_opengraph]' <?php checked( $options['dublincore_metadata_generator_opengraph'], 1 ); ?> value='1'>
<?php
}

function dublincore_metadata_generator_html_opengraph_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
?>
	<input name='dublincore_metadata_generator_settings[dublincore_metadata_generator_html_opengraph]' value='0' type='hidden'>
	<input type='checkbox' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_html_opengraph]' <?php checked( $options['dublincore_metadata_generator_html_opengraph'], 1 ); ?> value='1'>
	<p><em><?php _e('Automatically adds the Open Graph schema code in the <html> tag. Recommended if the OG metadata are enabled.', 'dublincoremetadatagenerator'); ?> </em></p>
<?php
}

function dublincore_metadata_generator_default_image_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
		if ( empty ( $options['dublincore_metadata_generator_default_image'] ) ) : $default = ''; 
			else : $default = $options['dublincore_metadata_generator_default_image'];
		endif;
	?>
	<input type='text' id='dcmg-img' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_default_image]' value='<?php echo $default; ?>'>
	<input type='button' id='dcmg-image-button' value='<?php _e( 'Choose your image', 'dublincoremetadatagenerator' )?>' />
	<p><em><?php _e('For optimal rendering, this image should be at least 800 pixels wide.', 'dublincoremetadatagenerator'); ?> </em></p>	
		<?php if( !empty( $default ) ): ?>			
				<div id="img-dcmg">
					 <img src="<?php echo $default; ?>" style="width:30%;height:auto;margin-top:10px;" id="imgtop" />
						<p id="remove_default_image" style="margin-top:-6px!important;">
						<a title="<?php _e('Remove', 'dublincoremetadatagenerator'); ?>" href="javascript:;" id="remove-footer-thumbnail">
						<?php _e('Remove image', 'dublincoremetadatagenerator'); ?></a>
						</p>			
				</div>
		<?php else :
		endif; 
}

function dublincore_metadata_generator_fb_admins_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
		if ( empty ( $options['dublincore_metadata_generator_fb_admins'] ) ) : $fbadm = ''; 
		else : $fbadm = $options['dublincore_metadata_generator_fb_admins'];
		endif;
?>
	<input type='text' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_fb_admins]' value='<?php echo $fbadm; ?>'>
	<p><em><?php _e('Get insights of your pages. You must be logged in to your Facebook account to get your fb:admins ID.', 'dublincoremetadatagenerator'); ?> </em></p>
	<?php
}

function dublincore_metadata_generator_image_format_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
?>
	<select name='dublincore_metadata_generator_settings[dublincore_metadata_generator_image_format]'>
		<option value='1' <?php selected( $options['dublincore_metadata_generator_image_format'], 1 ); ?>>Summary</option>
		<option value='2' <?php selected( $options['dublincore_metadata_generator_image_format'], 2 ); ?>>Summary large image</option>
	</select>
	<p><em><?php _e('Summary (default) or Large Summary.', 'dublincoremetadatagenerator'); ?> </em></p>
<?php
}

function dublincore_metadata_generator_keywords_render(  ) { 
// the meta keywords are obsolete in 2020, but here they are in case you really want to use them
	$options = get_option( 'dublincore_metadata_generator_settings' );
		if ( empty ( $options['dublincore_metadata_generator_keywords'] ) ) : $keywords = ''; 
			else : $keywords = $options['dublincore_metadata_generator_keywords'];
		endif;
	?>
	<textarea style='width:66%;' type='text' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_keywords]'><?php echo $keywords; ?></textarea>
    <p><strong><?php _e('The meta keywords are obsolete in 2020, but here they are in case you really want to use them for Dublin Core.', 'dublincoremetadatagenerator'); ?></strong><br />
	<em><?php _e('Enter maximum 20 keywords, separated by a comma. These should be the top, most important keywords that you want your website to be optimized for. This is a global field, so in case you would like to have different individual keywords per page, it is recommended you leave this field empty, in which case each page/post will have its own keywords meta, autogenerated from the page/post title, tags and categories. It works for WooCommerce products as well.', 'dublincoremetadatagenerator'); ?> </em></p>
	<?php
}

function dublincore_metadata_generator_description_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
		if ( empty ( $options['dublincore_metadata_generator_description'] ) ) : $description = ''; 
			else : $description = $options['dublincore_metadata_generator_description'];
		endif;
	?>
	<textarea style='width:66%;' type='text' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_description]'><?php echo $description; ?></textarea>
	<p><em><?php _e('The description should contain less than 200 characters and ideally it should contain your SEO keywords displayed in a natural sentence and readable form. However, for optimal performance and customization, we advise you to use Yoast to set up descriptions. This is a global field, which means it will serve as the same description for all pages and posts.', 'dublincoremetadatagenerator'); ?> </em></p>
	<?php
}

function dublincore_metadata_generator_settings_section_callback(  ) { 
	echo __( 'There are two distinct types of metadata available: the <b>Dublin Core</b> and Open Graph, plus an additional set of description and keywords metadata. They are all optional; feel free to ignore them if you have already integrated such metadata to your website in a different way, for example through Yoast or other SEO plugins.', 'dublincoremetadatagenerator' );
}

function dublincore_metadata_generator_cleanmetas_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
?>
	<input name='dublincore_metadata_generator_settings[dublincore_metadata_generator_cleanmetas]' value='0' type='hidden'>
	<input type='checkbox' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_cleanmetas]' <?php checked( $options['dublincore_metadata_generator_cleanmetas'], 1 ); ?> value='1'>
	<p><em><?php _e('This option will clean up your code by eliminating all useless meta tags, <abbr title="Example: <meta name=”generator” content=”WordPress 5.4.1” />">generators</abbr> and advertising comments left by Wordpress, WooCommerce, Slider Revolution, LayerSlider, WPML and WPBakery Page Builder (Visual Composer) plugins in just one click.'); ?> </em></p>
	<?php
}

function dublincore_metadata_generator_cleanyoast_render(  ) { 
	$options = get_option( 'dublincore_metadata_generator_settings' );
?>
	<input name='dublincore_metadata_generator_settings[dublincore_metadata_generator_cleanyoast]' value='0' type='hidden'>
	<input type='checkbox' name='dublincore_metadata_generator_settings[dublincore_metadata_generator_cleanyoast]' <?php checked( $options['dublincore_metadata_generator_cleanyoast'], 1 ); ?> value='1'>
	<p><em><?php _e('This option removes the &lt;!-- This site is optimized with the Yoast SEO plugin --&gt; comments and any other advertising left by the Yoast SEO plugin in the public source code.'); ?> </em></p>
	<?php
}

function dublincore_metadata_generator_options_page(  ) { 
?>
	<form action='options.php' method='post'>
		<h1>Dublin Core Metadata Generator</h1>
		<?php
		settings_fields( 'dcmg_settings_page' );
		do_settings_sections( 'dcmg_settings_page' );
		submit_button();
		?>
	</form>
	<?php
}

//Edit link from plugin page

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'dcmg_add_plugin_action_links' );

function dcmg_add_plugin_action_links( $links ) {
	return array_merge(
		array(
			'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/tools.php?page=our_plugin_page">Settings</a>'
		),
		$links
	);
}
?>