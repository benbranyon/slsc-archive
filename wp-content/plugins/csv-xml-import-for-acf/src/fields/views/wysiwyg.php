<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<textarea
        name="<?php echo esc_attr('fields' . $field_name . '[' . $field['key'] . ']'); ?>"
        class="widefat rad4"><?php echo esc_html($current_field);?>
</textarea>
