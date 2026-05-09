<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="field field_type-<?php echo esc_attr($field['type']);?> field_key-<?php echo esc_attr($field['key']);?>">
    <p class="label">
        <label>
            <?php echo (in_array($field['type'], array('message', 'tab', 'accordion'))) ? esc_html($field['type']) : ((empty($field['label']) ? '' : esc_html($field['label'])));?>
        </label>
    </p>
    <div class="wpallimport-clear"></div>
    <div class="acf-input-wrap">