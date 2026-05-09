<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Render admin notices if ACF or WP All Import plugins are activated
 */
function pmai_admin_notices() {

    // notify user if history folder is not writable
    if ( ! class_exists( 'PMXI_Plugin' ) ) {
        ?>
        <div class="error"><p>
		        <?php
		        // Translators: %1$s is the plugin name. The message includes bold text and two links, one to the free edition of WP All Import and one to the paid edition.
		        printf(wp_kses(__('<b>%1$s Plugin</b>: WP All Import must be installed. Free edition of WP All Import at <a href="http://wordpress.org/plugins/wp-all-import/" target="_blank">http://wordpress.org/plugins/wp-all-import/</a> and the paid edition at <a href="http://www.wpallimport.com/">http://www.wpallimport.com/</a>', 'csv-xml-import-for-acf'),
				        array(
					        'a' => array(
						        'href' => array(),
						        'target' => array()
					        ),
					        'b' => array()
				        )),
			        'csv-xml-import-for-acf'
		        );
		        ?>
            </p></div>
        <?php

        deactivate_plugins( PMAI_ROOT_DIR . '/plugin.php');

    }

    if ( class_exists( 'PMXI_Plugin' ) and ( version_compare(PMXI_VERSION, '4.1.1') < 0 and PMXI_EDITION == 'paid' or version_compare(PMXI_VERSION, '3.2.3') <= 0 and PMXI_EDITION == 'free') ) {
        ?>
        <div class="error"><p>
		        <?php
		        // Translators: %1$s is the plugin name. The message includes bold text.
		        printf(wp_kses(__('<b>%1$s Plugin</b>: Please update your WP All Import to the latest version', 'csv-xml-import-for-acf'),
				        array('b' => array())),
			        'csv-xml-import-for-acf'
		        );
		        ?>
            </p></div>
        <?php

        deactivate_plugins( PMAI_ROOT_DIR . '/plugin.php');
    }

    if ( ! class_exists( 'acf' ) ) {
        ?>
        <div class="error"><p>
		        <?php
		        // Translators: %1$s is the plugin name. The message includes bold text and a link to the Advanced Custom Fields plugin.
		        printf(wp_kses(__('<b>%1$s Plugin</b>: <a target="_blank" href="http://wordpress.org/plugins/advanced-custom-fields/">Advanced Custom Fields</a> must be installed', 'csv-xml-import-for-acf'),
				        array(
					        'a' => array(
						        'href' => array(),
						        'target' => array()
					        ),
					        'b' => array()
				        )),
			        'csv-xml-import-for-acf'
		        );
		        ?>
            </p></div>
        <?php

        deactivate_plugins( PMAI_ROOT_DIR . '/plugin.php');

    }
}