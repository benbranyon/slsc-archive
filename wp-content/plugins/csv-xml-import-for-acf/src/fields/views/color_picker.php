<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<input
        type="text"
        placeholder=""
        value="<?php echo esc_attr( $current_field );?>"
        name="<?php echo esc_attr('fields'.$field_name.'['.$field['key'].']');?>"
        class="text w95 widefat rad4"/>

<a
        href="#help"
        class="wpallimport-help"
        title="<?php esc_attr_e('Specify the hex code the color preceded with a # - e.g. #ea5f1a.', 'csv-xml-import-for-acf'); ?>"
        style="top:0;">?</a>