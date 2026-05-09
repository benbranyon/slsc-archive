<?php
/**
 * @param $post
 * @return mixed
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function pmai_pmxi_save_options($post)
{
	$current_screen = PMXI_Plugin::getInstance()->getAdminCurrentScreen();
	if ($current_screen && $current_screen->action == 'options')
	{
		if ($post['update_acf_logic'] == 'only'){
			$post['acf_list'] = explode(",", $post['acf_only_list']); 
		}
		elseif ($post['update_acf_logic'] == 'all_except'){
			$post['acf_list'] = explode(",", $post['acf_except_list']); 	
		}
	}	
	return $post;
}
