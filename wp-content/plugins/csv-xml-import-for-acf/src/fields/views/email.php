<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<input
    type="text"
    placeholder=""
    value="<?php echo esc_attr( $current_field );?>"
    name="fields<?php echo esc_attr($field_name);?>[<?php echo esc_attr($field['key']);?>]"
    class="text widefat rad4"/>