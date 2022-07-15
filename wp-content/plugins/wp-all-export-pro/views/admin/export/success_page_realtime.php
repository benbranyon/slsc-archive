<?php
if (!defined('ABSPATH')) {
    die();
}
?>
<?php
$cron_job_key = PMXE_Plugin::getInstance()->getOption('cron_job_key');
$urlToExport = site_url() . '/wp-load.php?security_token=' . substr(md5($cron_job_key . $update_previous->id), 0, 16) . '&export_id=' . $update_previous->id . '&action=get_data';
$uploads = wp_upload_dir();

$bundle_path = wp_all_export_get_absolute_path($update_previous->options['bundlepath']);

if (!empty($bundle_path)) {
    $bundle_url = site_url() . '/wp-load.php?security_token=' . substr(md5($cron_job_key . $update_previous->id), 0, 16) . '&export_id=' . $update_previous->id . '&action=get_bundle&t=zip';
}

$isImportAllowedSpecification = new \Wpae\App\Specification\IsImportAllowed();

$cpt = $update_previous->options['cpt'];
if (is_array($cpt)) {
    $cpt = $cpt[0];
}

$is_rapid_addon_export = true;

if (strpos($cpt, 'custom_') !== 0) {
    $is_rapid_addon_export = false;
}

if (current_user_can(PMXE_Plugin::$capabilities)) {
    ?>
    <div id="export_finished" style="padding-top: 10px;">
        <?php
        if ($isGoogleFeed) {
            ?>
            <h3><?php esc_html_e('WP All Export successfully exported your data!', 'wp_all_export_plugin'); ?></h3>
        <?php
        $cronJobKey = PMXE_Plugin::getInstance()->getOption('cron_job_key');
        include_once('google_merchants_success.php');
        } else {
        ?>

            <h2 style="color:#425f9a; font-size:24px; margin-bottom: 20px;">What's next?</h2>
            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                <p style="width: 630px;">
                    You must configure Zapier or a custom integration to process the generated export file.
                    Until then, WP All Export will only overwrite the same export file stored on this server each time a new record is processed.
                    The file will not be sent anywhere else until you configure or create a process to do so.
                </p>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery('.success-tabs .tab').click(function () {
                        jQuery('.success-tabs .tab').removeClass('selected');
                        jQuery(this).addClass('selected');
                        var rel = jQuery(this).attr('rel');
                        jQuery('.tab-content').removeClass('selected');
                        jQuery('.tab-content-container').find('#' + rel).addClass('selected');
                    });
                });
            </script>
            <ul class="success-tabs">
                <li rel="tab1-content" class="tab selected">
                        <?php esc_html_e("Zapier", 'wp_all_export_plugin'); ?>
                </li>
                <li rel="tab2-content" class="tab">
                    <?php esc_html_e("Custom Integration", 'wp_all_export_plugin'); ?>
                </li>
            </ul>
        <hr style="margin-top:0;"/>
            <div class="tab-content-container">
                <div class="tab-content normal-tab selected" id="tab1-content" style="height: 400px;">
                    <p>
                        <?php esc_html_e("Automatically send your data to over 500 apps with Zapier.", 'wp_all_export_plugin'); ?>
                        <br/>
                        <a href="https://zapier.com/zapbook/wp-all-export-pro/"
                           target="_blank"><?php esc_html_e("Click here to read more about WP All Export's Zapier Integration.", 'wp_all_export_plugin'); ?></a>
                    </p>
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/6tBacBmiHsQ" frameborder="0"
                            allowfullscreen></iframe>
                </div>

                <div class="tab-content normal-tab" id="tab2-content" style="height: 400px;">
                    <p>
                        The WP All Export API can be used to process the generated export files.
                    </p>
                    <p>
                        <a href="https://www.wpallimport.com/documentation/advanced/action-reference/#pmxe_after_export" target="_blank">Read more about the WP All Export API</a>
                    </p>

                </div>

            </div>
            <hr/>
            <h4 style="margin-bottom: 30px;">Download Export File</h4>
            <div class="input">
                <button class="button button-primary button-hero wpallexport-large-button download_data"
                        rel="<?php echo esc_url_raw(add_query_arg(array('page' => 'pmxe-admin-manage', 'action' => 'download', 'id' => $update_previous->id, '_wpnonce' => wp_create_nonce('_wpnonce-download_feed')), $this->baseUrl)); ?>"><?php echo strtoupper(wp_all_export_get_export_format($update_previous->options)); ?></button>
                <?php if (!empty($update_previous->options['split_large_exports'])): ?>
                    <button class="button button-primary button-hero wpallexport-large-button download_data"
                            rel="<?php echo esc_url_raw(add_query_arg(array('page' => 'pmxe-admin-manage', 'id' => $update_previous->id, 'action' => 'split_bundle', '_wpnonce' => wp_create_nonce('_wpnonce-download_split_bundle')), $this->baseUrl)); ?>"><?php printf(__('Split %ss', 'wp_all_export_plugin'), strtoupper(wp_all_export_get_export_format($update_previous->options))); ?></button>
                <?php endif; ?>
                <?php if (PMXE_Export_Record::is_bundle_supported($update_previous->options)): ?>
                    <button class="button button-primary button-hero wpallexport-large-button download_data"
                            rel="<?php echo esc_url_raw(add_query_arg(array('page' => 'pmxe-admin-manage', 'id' => $update_previous->id, 'action' => 'bundle', '_wpnonce' => wp_create_nonce('_wpnonce-download_bundle')), $this->baseUrl)); ?>"><?php esc_html_e('Bundle', 'wp_all_export_plugin'); ?></button>
                <?php endif; ?>
            </div>
        <hr/>
            <?php
        }
        ?>
    </div>
    <?php
} else {
    ?>
    <div id="export_finished">
        <p>
            <?php if (XmlExportEngine::$exportOptions['export_to'] == XmlExportEngine::EXPORT_TYPE_XML && XmlExportEngine::$exportOptions['xml_template_type'] == XmlExportEngine::EXPORT_TYPE_GOOLE_MERCHANTS) { ?>
                <button class="button button-primary button-hero wpallexport-large-button download_data"
                        rel="<?php echo esc_url_raw(esc_url(add_query_arg(array('page' => 'pmxe-admin-manage', 'action' => 'download', 'id' => $update_previous->id, '_wpnonce' => wp_create_nonce('_wpnonce-download_feed')), $this->baseUrl))); ?>"><?php esc_html_e('Download', 'wp_all_export_plugin'); ?>
                    TXT
                </button>

            <?php } else { ?>
                <button class="button button-primary button-hero wpallexport-large-button download_data"
                        rel="<?php echo esc_url_raw(esc_url(add_query_arg(array('action' => 'download', 'id' => $update_previous->id, '_wpnonce' => wp_create_nonce('_wpnonce-download_feed')), $this->baseUrl))); ?>"><?php esc_html_e('Download', 'wp_all_export_plugin'); ?><?php echo esc_html(strtoupper(wp_all_export_get_export_format($update_previous->options))); ?></button>

            <?php } ?>

    </div>
    <?php
}
?>
