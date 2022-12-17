<?php
/**
 * Settings Page
 *
 * @package WP Modal Popup with Cookie Integration
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $wp_version;

$editor_config = array(
						'textarea_name' => 'wmpci_options[wmpci_popup_cnt]',
						'editor_class'	=> 'wmpci-popup-cnt',
						'textarea_rows'	=> 8
						);
$popup_designs = wmpci_popup_designs();
?>

<div class="wrap wmpci-settings">

<h2><?php _e( 'WP PopUp Settings', 'wmpci' ); ?></h2><br />

<?php
if( isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true' ) {
	echo '<div id="message" class="updated notice notice-success is-dismissible">
			<p>'.__("Your changes saved successfully.", "wmpci").'</p>
		  </div>';
}
?>

<form action="options.php" method="POST" id="wmpci-settings-form" class="wmpci-settings-form">
	
	<?php
	    settings_fields( 'wmpci_plugin_options' );
	    global $wmpci_options;
	?>

	
	<div id="wmpci-general-settings" class="post-box-container wmpci-general-settings">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<div id="general" class="postbox">

					<button class="handlediv button-link" type="button"><span class="toggle-indicator"></span></button>

						<!-- Settings box title -->
						<h3 class="hndle">
							<span><?php _e( 'General Settings', 'wp-modal-popup-with-cookie-integration' ); ?></span>
						</h3>
						
						<div class="inside">
						
						<table class="form-table wmpci-general-settings-tbl">
							<tbody>

								<tr>
									<th scope="row">
										<label for="wmpci-enable-popup"><?php _e('Enable Popup', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="checkbox" name="wmpci_options[enable_popup]" value="1" class="wmpci-enable-popup" id="wmpci-enable-popup" <?php checked( $wmpci_options['enable_popup'], 1 ); ?> /><br/>
										<span class="description"><?php _e('Check this box if you want to display popup on site.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="wmpci-mainheading"><?php _e('Main Heading', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="text" name="wmpci_options[wmpci_mainheading]" value="<?php echo wmpci_get_option('wmpci_mainheading'); ?>" class="wmpci-wmpci_mainheading large-text" id="wmpci-wmpci_mainheading" /><br/>
										<span class="description"><?php _e('Enter the main heading- eg. GET 10% OFF', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="wmpci-subheading"><?php _e('Sub Heading', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="text" name="wmpci_options[wmpci_subheading]" value="<?php echo wmpci_get_option('wmpci_subheading'); ?>" class="wmpci-wmpci_subheading large-text" id="wmpci-wmpci_subheading" /><br/>
										<span class="description"><?php _e('Enter the sub heading - eg.  Now purchase anything site-wide', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="wmpci-post-within"><?php _e('Popup Content', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<?php wp_editor( $wmpci_options['wmpci_popup_cnt'], 'wmpci-popup-cnt', $editor_config ); ?>
										<span class="description"><?php _e('Enter your popup content.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-popup-design"><?php _e('Popup Design', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<select name="wmpci_options[popup_design]" value="<?php echo $wmpci_options['popup_design']; ?>" class="wmpci-popup-design" id="wmpci-popup-design">
										<?php
										if( !empty($popup_designs) ) {
											foreach ($popup_designs as $design_key => $design_val) { ?>
												<option value="<?php echo $design_key; ?>" <?php selected( $wmpci_options['popup_design'], $design_key ); ?>><?php echo $design_val; ?></option>
										<?php }
										}
										?>
										</select><br/>
										<span class="description"><?php _e('Select design for popup.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-popup-delay"><?php _e('Popup Delay', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="number" min="0" name="wmpci_options[wmpci_popup_delay]" value="<?php echo $wmpci_options['wmpci_popup_delay']; ?>" class="wmpci-popup-delay" id="wmpci-popup-delay" /> <span><?php _e('Seconds', 'wmpci'); ?></span><br/>
										<span class="description"><?php _e('Enter no of second to open popup after page load.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-popup-disappear"><?php _e('Popup Disappear', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="number" min="0" name="wmpci_options[wmpci_popup_disappear]" value="<?php echo $wmpci_options['wmpci_popup_disappear']; ?>" class="wmpci-popup-disappear" id="wmpci-popup-disappear" /> <span><?php _e('Seconds', 'wmpci'); ?></span><br/>
										<span class="description"><?php _e('Enter no of second to hide popup.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-popup-expiry"><?php _e('Popup Expiry Time', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="number" min="0" name="wmpci_options[wmpci_popup_exp]" value="<?php echo $wmpci_options['wmpci_popup_exp']; ?>" class="wmpci-popup-expiry" id="wmpci-popup-expiry" /> <span><?php _e('Days', 'wmpci'); ?></span><br/>
										<span class="description"><?php _e('Enter expiry time when user click on close button. Upon expiry user will see popup again.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-hide-close-btn"><?php _e('Hide Close Button', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="checkbox" name="wmpci_options[hide_close_btn]" value="1" class="wmpci-hide-close-btn" id="wmpci-hide-close-btn" <?php checked( $wmpci_options['hide_close_btn'], 1 ); ?> /><br/>
										<span class="description"><?php _e('Check this box if you want to hide the close button of popup.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-esc-close"><?php _e('Close on Esc', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="checkbox" name="wmpci_options[close_on_esc]" value="1" class="wmpci-esc-close" id="wmpci-esc-close" <?php checked( $wmpci_options['close_on_esc'], 1 ); ?> /><br/>
										<span class="description"><?php _e('Check this box if you want to close the popup on esc key.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<td colspan="2" valign="top" scope="row">
										<input type="submit" id="wmpci-settings-submit" name="wmpci-settings-submit" class="button button-primary right" value="<?php _e('Save Changes','wp-modal-popup-with-cookie-integration');?>" />
									</td>
								</tr>
							</tbody>
						 </table>

					</div><!-- .inside -->
				</div><!-- #general -->
			</div><!-- .meta-box-sortables ui-sortable -->
		</div><!-- .metabox-holder -->
	</div><!-- #wmpci-general-settings -->

	<!-- Appearance Setting Box Starts -->
	<div id="wmpci-appearance-settings" class="post-box-container wmpci-appearance-settings">
		<div class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<div id="appearance" class="postbox">

					<button class="handlediv button-link" type="button"><span class="toggle-indicator"></span></button>

						<!-- Settings box title -->
						<h3 class="hndle">
							<span><?php _e( 'Appearance Settings', 'wp-modal-popup-with-cookie-integration' ); ?></span>
						</h3>
						
						<div class="inside">
						
						<table class="form-table wmpci-appearance-settings-tbl">
							<tbody>

								<tr>
									<th scope="row">
										<label for="wmpci-popup-height"><?php _e('Popup Height and Width', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="text" name="wmpci_options[popup_height]" value="<?php echo $wmpci_options['popup_height']; ?>" class="wmpci-popup-height" id="wmpci-popup-height" size="6" /> <label for="wmpci-popup-height"><?php _e('Height', 'wp-modal-popup-with-cookie-integration'); ?></label> &nbsp;&nbsp;
										<input type="text" name="wmpci_options[popup_width]" value="<?php echo $wmpci_options['popup_width']; ?>" class="wmpci-popup-width" id="wmpci-popup-width" size="6" /> <label for="wmpci-popup-width"><?php _e('Width', 'wp-modal-popup-with-cookie-integration'); ?></label> <br/>
										<span class="description"><?php _e('Enter custom height and width for popup. Leave empty to use default. (i.e 600px OR 60%)', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-bgcolor"><?php _e('Popup Background Color', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<?php if( $wp_version >= 3.5 ) { ?>
											<input type="text" value="<?php echo $wmpci_options['popup_bgcolor']; ?>" id="wmpci-popup-bgcolor" name="wmpci_options[popup_bgcolor]" class="wmpci-color-box" /><br/>
										<?php } else { ?>
											<div style='position:relative;'>
												<input type='text' value="<?php echo $wmpci_options['popup_bgcolor']; ?>" id="wmpci-color-box-farbtastic-inp" name="wmpci_options[popup_bgcolor]" class="wmpci-color-box-farbtastic-inp" data-default-color="" />
												<input type="button" class="wmpci-color-box-farbtastic button button-secondary" value="<?php _e('Select Color', 'wp-modal-popup-with-cookie-integration'); ?>" />
												<div class="colorpicker" style="background-color: #666; z-index:100; position:absolute; display:none;"></div>
											</div>
										<?php } ?>
											<span class="description"><?php _e('Select Popup background color.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="wmpci-fontcolor"><?php _e('Popup Fonts Color', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<?php if( $wp_version >= 3.5 ) { ?>
											<input type="text" value="<?php echo wmpci_get_option('popup_fontcolor'); ?>" id="wmpci-popup-fontcolor" name="wmpci_options[popup_fontcolor]" class="wmpci-color-box" /><br/>
										<?php } else { ?>
											<div style='position:relative;'>
												<input type='text' value="<?php echo wmpci_get_option($wmpci_options['popup_fontcolor']); ?>" id="wmpci-color-box-farbtastic-inp" name="wmpci_options[popup_fontcolor]" class="wmpci-color-box-farbtastic-inp" data-default-color="" />
												<input type="button" class="wmpci-color-box-farbtastic button button-secondary" value="<?php _e('Select Color', 'wp-modal-popup-with-cookie-integration'); ?>" />
												<div class="colorpicker" style="background-color: #666; z-index:100; position:absolute; display:none;"></div>
											</div>
										<?php } ?>
											<span class="description"><?php _e('Select Popup font color.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-popup-border-width"><?php _e('Popup Border Width', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="number" name="wmpci_options[popup_border_width]" value="<?php echo $wmpci_options['popup_border_width']; ?>" class="wmpci-popup-border-width" id="wmpci-popup-border-width" min="0" /> <label for="wmpci-popup-border-width"><?php _e('Px', 'wmpci'); ?></label> <br/>
										<span class="description"><?php _e('Enter width of popup border.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-popup-border-width"><?php _e('Popup Border Radius', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<input type="number" name="wmpci_options[popup_border_radius]" value="<?php echo $wmpci_options['popup_border_radius']; ?>" class="wmpci-popup-border-radius" id="wmpci-popup-border-radius" min="0" /> <label for="wmpci-popup-border-radius"><?php _e('Px', 'wmpci'); ?></label> <br/>
										<span class="description"><?php _e('Enter border radius of popup.', 'wmpci'); ?></span>
									</td>
								</tr>

								<tr>
									<th scope="row">
										<label for="wmpci-popup-delay"><?php _e('Popup Border Color', 'wp-modal-popup-with-cookie-integration'); ?>:</label>
									</th>
									<td>
										<?php if( $wp_version >= 3.5 ) { ?>
											<input type="text" value="<?php echo $wmpci_options['popup_border_color']; ?>" id="wmpci-popup-bgcolor" name="wmpci_options[popup_border_color]" class="wmpci-color-box" /><br/>
										<?php } else { ?>
											<div style='position:relative;'>
												<input type='text' value="<?php echo $wmpci_options['popup_border_color']; ?>" id="wmpci-color-box-farbtastic-inp" name="wmpci_options[popup_border_color]" class="wmpci-color-box-farbtastic-inp" data-default-color="" />
												<input type="button" class="wmpci-color-box-farbtastic button button-secondary" value="<?php _e('Select Color', 'wp-modal-popup-with-cookie-integration'); ?>" />
												<div class="colorpicker" style="background-color: #666; z-index:100; position:absolute; display:none;"></div>
											</div>
										<?php } ?>
											<span class="description"><?php _e('Select Popup border color.', 'wp-modal-popup-with-cookie-integration'); ?></span>
									</td>
								</tr>

								<tr>
									<td colspan="2" valign="top" scope="row">
										<input type="submit" id="wmpci-settings-submit" name="wmpci-settings-submit" class="button button-primary right" value="<?php _e('Save Changes','wp-modal-popup-with-cookie-integration');?>" />
									</td>
								</tr>
							</tbody>
						 </table>

					</div><!-- .inside -->
				</div><!-- #appearance -->
			</div><!-- .meta-box-sortables ui-sortable -->
		</div><!-- .metabox-holder -->
	</div><!-- #wmpci-appearance-settings -->
	<!-- Appearance Setting Box Ends -->

</form><!-- end .wmpci-settings-form -->
<!-- Appearance Setting Box Starts -->
</div><!-- end .wmpci-settings -->