<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="wpallimport-collapsed closed pmai_options">
	<div class="wpallimport-content-section">
		<div class="wpallimport-collapsed-header">
			<h3><?php esc_html_e('Advanced Custom Fields Add-On','csv-xml-import-for-acf');?></h3>
		</div>
		<div class="wpallimport-collapsed-content" style="padding: 0;">
			<div class="wpallimport-collapsed-content-inner">
				<table class="form-table" style="max-width:none;">
					<tr>
						<td colspan="3">
							<?php if (!empty($groups)): ?>
								<p><strong><?php esc_html_e("Please choose your Field Groups.",'csv-xml-import-for-acf');?></strong></p>
								<ul>
									<?php 
									foreach ($groups as $key => $group) {
										$is_show_acf_group = apply_filters('wp_all_import_acf_is_show_group', true, $group);
										$id = $group['ID'];
										$name = '';

										if ( isset( $group['slug'] ) ) {
											if ( ! empty( $group['slug'] ) ) {
												$name = $group['slug'];
											}
										} elseif ( isset( $group['key'] ) ) {
											if ( ! empty( $group['key'] ) ) {
												$name = $group['key'];
											}
										} else {
											$name = $group['ID'];
										}
										?>
                                        <li>
                                            <input type="hidden" name="acf[<?php echo esc_attr($id);?>]" value="<?php echo $is_show_acf_group ? '0' : '1'?>"/>
                                            <?php if ($is_show_acf_group): ?>
                                                <input id="acf_<?php echo esc_attr($post_type . '_' . $id);?>" type="checkbox" name="acf[<?php echo esc_attr($name);?>]"
                                                       <?php if ( ! empty($post['acf'][ $id ]) || isset( $name ) && !empty($post['acf'][ $name ])): ?>checked="checked"<?php endif; ?>
                                                       value="1" rel="<?php echo esc_attr($id);?>" class="pmai_acf_group"/>
                                                <label for="acf_<?php echo esc_attr($post_type . '_' . $id); ?>">
                                                    <?php echo esc_html($group['title']); ?></label>
                                            <?php endif; ?>
                                        </li>
										<?php
									}
									?>
								</ul>
								<div class="acf_groups"></div>								
								<?php
							else:
								?>
								<p><strong><?php esc_html_e("Please create Field Groups.",'csv-xml-import-for-acf');?></strong></p>
								<?php	
							endif;
							?>					
						</td>
					</tr>
				</table>
			</div>
		</div>
	</div>
</div>