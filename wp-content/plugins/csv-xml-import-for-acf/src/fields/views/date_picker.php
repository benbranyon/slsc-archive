<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<input
        type="text"
        placeholder=""
        value="<?php echo esc_attr($current_field); ?>"
        name="<?php echo esc_attr('fields' . $field_name . '[' . $field['key'] . ']'); ?>"
        class="text datepicker widefat rad4"
        style="width:200px;"/>

<a
        href="#help"
        class="wpallimport-help"
        title="<?php esc_attr_e('Use any format supported by the PHP strtotime function.', 'csv-xml-import-for-acf'); ?>"
        style="top:0;">?</a>