<?php
/**
 * @param $entry
 * @param $post
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function pmai_pmxi_reimport($entry, $post){
    global $acf;
    if ($acf and version_compare($acf->settings['version'], '5.0.0') >= 0) {

        if( !in_array($entry, ['taxonomies','shop_customer','import_users'])) {
            $groups = acf_get_field_groups( [ 'post_type' => $entry ] );
        }

        if( !apply_filters('pmai_only_show_acf_groups_for_target_post_type', false) || empty($groups) ) {
            $groups = acf_get_field_groups();
        }

        if ( ! empty($groups) ) {
            foreach ($groups as $group) {
                $fields = acf_get_fields($group);
                if (!empty($fields)) {
                    foreach ($fields as $key => $field) {
                        if ( ! empty($field['name']) ) {
                            $all_existing_acf[] = '[' . $field['name'] . '] ' . $field['label'];
                            // Include subfields.
                            if( isset($field['sub_fields']) && !in_array($field['type'], ['repeater', 'flexible_content']) && is_array($field['sub_fields']) && !empty($field['sub_fields'])){
                                foreach($field['sub_fields'] as $sub_field){
                                    if( ! empty($sub_field['name'])){
                                        $all_existing_acf[] = '[' . $field['name'] . '_' . $sub_field['name'] . '] ' . $sub_field['label'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        $acfs = get_posts(array('posts_per_page' => -1, 'post_type' => 'acf'));
        $all_existing_acf = array();
        if (!empty($acfs)) {
            foreach ($acfs as $key => $acf_entry) {
                foreach (get_post_meta($acf_entry->ID, '') as $cur_meta_key => $cur_meta_val) {
                    if (strpos($cur_meta_key, 'field_') !== 0) {
                        continue;
                    }
                    $field = (!empty($cur_meta_val[0])) ? unserialize($cur_meta_val[0]) : array();
                    $field_name = '[' . esc_html($field['name']) . '] ' . esc_html($field['label']);
                    if ( ! in_array($field_name, $all_existing_acf) ) $all_existing_acf[] = esc_html($field_name);
                    if ( ! empty($field['sub_fields']) ) {
                        foreach ($field['sub_fields'] as $key => $sub_field) {
                            $sub_field_name = esc_html($field_name) . '---[' . esc_html($sub_field['name']) . ']';
                            if ( ! in_array($sub_field_name, $all_existing_acf) ) $all_existing_acf[] = esc_html($sub_field_name);
                        }
                    }
                }
            }
        }
    }
    ?>
    <div class="input">
        <input type="hidden" name="acf_list" value="0" />
        <input type="hidden" name="is_update_acf" value="0" />
        <input type="checkbox" id="is_update_acf_<?php echo esc_html($entry); ?>" name="is_update_acf" value="1" <?php echo $post['is_update_acf'] ? 'checked="checked"': '' ?>  class="switcher"/>
        <label for="is_update_acf_<?php echo esc_html($entry); ?>"><?php echo esc_html__('Advanced Custom Fields', 'csv-xml-import-for-acf'); ?></label>
        <div class="switcher-target-is_update_acf_<?php echo esc_html($entry); ?>" style="padding-left:17px;">
            <div class="input">
                <input type="radio" id="update_acf_logic_full_update_<?php echo esc_html($entry); ?>" name="update_acf_logic" value="full_update" <?php echo ( "full_update" == $post['update_acf_logic'] ) ? 'checked="checked"': '' ?> class="switcher"/>
                <label for="update_acf_logic_full_update_<?php echo esc_html($entry); ?>"><?php echo esc_html__('Update all ACF fields', 'csv-xml-import-for-acf'); ?></label>
            </div>
            <div class="input">
                <input type="radio" id="update_acf_logic_mapped_<?php echo esc_html($entry); ?>" name="update_acf_logic" value="mapped" <?php echo ( "mapped" == $post['update_acf_logic'] ) ? 'checked="checked"': '' ?> class="switcher"/>
                <label for="update_acf_logic_mapped_<?php echo esc_html($entry); ?>"><?php echo esc_html__('Update only mapped ACF groups', 'csv-xml-import-for-acf'); ?></label>
            </div>
            <div class="input">
                <input type="radio" id="update_acf_logic_only_<?php echo esc_html($entry); ?>" name="update_acf_logic" value="only" <?php echo ( "only" == $post['update_acf_logic'] ) ? 'checked="checked"': '' ?> class="switcher"/>
                <label for="update_acf_logic_only_<?php echo esc_html($entry); ?>"><?php echo esc_html__('Update only these ACF fields, leave the rest alone', 'csv-xml-import-for-acf'); ?></label>
                <div class="switcher-target-update_acf_logic_only_<?php echo esc_html($entry); ?> pmxi_choosen" style="padding-left:17px;">
                    <span class="hidden choosen_values"><?php if (!empty($all_existing_acf)) echo esc_html(implode(',', $all_existing_acf));?></span>
                    <input class="choosen_input" value="<?php if (!empty($post['acf_list']) and "only" == $post['update_acf_logic']) echo esc_html(implode(',', $post['acf_list'])); ?>" type="hidden" name="acf_only_list"/>
                </div>
            </div>
            <div class="input">
                <input type="radio" id="update_acf_logic_all_except_<?php echo esc_html($entry); ?>" name="update_acf_logic" value="all_except" <?php echo ( "all_except" == $post['update_acf_logic'] ) ? 'checked="checked"': '' ?> class="switcher"/>
                <label for="update_acf_logic_all_except_<?php echo esc_html($entry); ?>"><?php echo esc_html__('Leave these ACF fields alone, update all other ACF fields', 'csv-xml-import-for-acf'); ?></label>
                <div class="switcher-target-update_acf_logic_all_except_<?php echo esc_html($entry); ?> pmxi_choosen" style="padding-left:17px;">
                    <span class="hidden choosen_values"><?php if (!empty($all_existing_acf)) echo esc_html(implode(',', $all_existing_acf));?></span>
                    <input class="choosen_input" value="<?php if (!empty($post['acf_list']) and "all_except" == $post['update_acf_logic']) echo esc_html(implode(',', $post['acf_list'])); ?>" type="hidden" name="acf_except_list"/>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>