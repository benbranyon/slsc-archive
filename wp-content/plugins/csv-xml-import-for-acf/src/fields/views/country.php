<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<input
        type="text"
        placeholder=""
        value="<?php echo esc_attr($current_field); ?>"
        name="<?php echo esc_attr('fields' . $field_name . '[' . $field['key'] . ']'); ?>"
        class="text widefat rad4"/>