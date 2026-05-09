<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<input
        type="text"
        placeholder=""
        value="<?php echo esc_attr($current_field); ?>"
        name="<?php echo esc_attr('fields' . $field_name . '[' . $field['key'] . ']'); ?>"
        class="text widefat rad4"
        style="width:200px;"/>

<a
        href="#help"
        class="wpallimport-help"
        title="<?php esc_attr_e('Use H:i:s format.', 'csv-xml-import-for-acf'); ?>"
        style="top:0;">?</a>