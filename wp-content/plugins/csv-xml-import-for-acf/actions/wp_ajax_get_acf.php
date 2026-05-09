<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use pmai_acf_add_on\groups\GroupFactory;

/**
 *  Render ACF group
 */
function pmai_wp_ajax_get_acf() {

	// wp_all_import_secure references the WP All Import plugin.
    if (!check_ajax_referer('wp_all_import_secure', 'security', FALSE)) {
        exit(wp_json_encode(array('html' => __('Security check', 'csv-xml-import-for-acf'))));
    }

    if (!current_user_can(PMXI_Plugin::$capabilities)) {
        exit(wp_json_encode(array('html' => __('Security check', 'csv-xml-import-for-acf'))));
    }

    ob_start();

    $acf_groups = PMXI_Plugin::$session->acf_groups;

    $acf_obj = FALSE;

    if (!empty($acf_groups)) {
        foreach ($acf_groups as $key => $group) {
			$acf_group_id = sanitize_text_field(wp_unslash(($_GET['acf'] ?? '')));
            if ($group['ID'] == $acf_group_id) {
                $acf_obj = $group;
                break;
            }
        }
    }

    $import = new PMXI_Import_Record();

    if (!empty($_GET['id']) && is_numeric($_GET['id']) && !empty(intval($_GET['id']))) {
        $import->getById(intval($_GET['id']));
    }

    $is_loaded_template = (!empty(PMXI_Plugin::$session->is_loaded_template)) ? PMXI_Plugin::$session->is_loaded_template : FALSE;

    if ($is_loaded_template) {
        $default = PMAI_Plugin::get_default_import_options();
        $template = new PMXI_Template_Record();
        if (!$template->getById($is_loaded_template)->isEmpty()) {
            $options = (!empty($template->options) ? $template->options : array()) + $default;
        }

    }
    elseif (!$import->isEmpty()) {
        $options = $import->options;
    }
    else {
        $options = PMXI_Plugin::$session->options;
    }

    $group = GroupFactory::create($acf_obj, $options);
    $group->view();

    exit(wp_json_encode(array('html' => ob_get_clean())));
}