<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<?php foreach ($errors as $msg): ?>
    <div class="error"><p><?php echo esc_html($msg); ?></p></div>
<?php endforeach; ?>