<?php
/** @noinspection Annotator */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<p>
    <?php
    if(!\pmai_acf_add_on\ACFService::is_field_supported_by_pro($field_name)) {
        echo esc_html__('This field type is not supported. E-mail support@wpallimport.com with the details of the custom ACF field you are trying to import to, as well as a link to download the plugin to install to add this field type to ACF, and we will investigate the possibility ot including support for it in the ACF add-on.', 'csv-xml-import-for-acf');
    } else {
    ?>
<div class="wpai-acf-free-edition-notice">
    <a href="<?php echo esc_url('https://www.wpallimport.com/?edd_action=add_to_cart&download_id=5839965&edd_options%5Bprice_id%5D=1&utm_source=acf-import-free&utm_medium=upgrade-notice&utm_campaign=pro-only-fields'); ?>" target="_blank" class="upgrade_link"><u>
            <?php
            $field_name = esc_html($field_name);
            $field_name = preg_replace('/(?<=\\w)(?=[A-Z])/', ' $1', $field_name);
            $field_name = str_replace(['Paypal', 'True False'], ['PayPal', 'True / False'], $field_name);

            printf(
                /* translators: %s refers to an ACF field type */
	            esc_html__('Upgrade to the Pro edition of the ACF Import Add-On to import %s fields.', 'csv-xml-import-for-acf'),
	            esc_html($field_name)
            ); ?>
        </u></a>
    <p><?php echo esc_html__('If you already own it, ensure it\'s installed and activated.', 'csv-xml-import-for-acf'); ?></p>
</div>
<?php
}
?>
</p>