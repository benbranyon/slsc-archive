<?php

namespace pmai_acf_add_on;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

use PMXI_API;
use pmai_acf_add_on\fields\Field;

/**
 * Class ACFService
 * @package pmai_acf_add_on
 */
final class ACFService {

	private static $pro_fields = [
		'ButtonGroup',
		'Checkbox',
		'Clone',
		'Group',
		'Radio',
		'Repeater',
		'Select',
		'TrueFalse',
		'AcfCf7',
		'File',
		'FlexibleContent',
		'FontAwesome',
		'Gallery',
		'GoogleMap',
		'GoogleMapExtended',
		'GravityFormsField',
		'Image',
		'ImageAspectRatioCrop',
		'ImageCrop',
		'Limiter',
		'Link',
		'LocationField',
		'Oembed',
		'PageLink',
		'PaypalItem',
		'PostObject',
		'Range',
		'Relationship',
		'StarRating',
		'Table',
		'Taxonomy',
		'User',
		'ValidatedField',
		'Vimeo'
	];

	/**
	 * @param $version
	 *
	 * @return bool
	 */
	public static function isACFNewerThan( $version ) {
		// This uses the global $acf variable that is provided by the Advanced Custom Fields plugin which we cannot control.
		global $acf;

		return version_compare( $acf->settings['version'], $version ) >= 0;
	}

	/**
	 *
	 * Set ACF field value
	 *
	 * @param \pmai_acf_add_on\fields\Field $field
	 * @param $pid
	 * @param $name
	 * @param $value
	 */
	public static function update_post_meta( Field $field, $pid, $name, $value ) {
		$fieldData = [
			'key'               => $field->getFieldKey(),
			'name'              => $field->getFieldName(),
			'type'              => $field->getType(),
			'save_custom'       => 0,
			'save_other_choice' => 0,
			'allow_null'        => 0,
			'return_format'     => 'value',
			'save_terms'        => 0
		];
		
		// Apply filters to field value
		// acf/update_value is provided by the Advanced Custom Fields plugin
		$value = apply_filters( "acf/update_value", $value, $pid, $fieldData, $value );
		// pmxi_acf_custom_field references the WP All Import plugin
		$cf_value = apply_filters( 'pmxi_acf_custom_field', $value, $pid, $name );

		// First, check if this is an empty select field that needs special handling
		$is_empty_select = false;

		if ($field->getType() === 'select') {
			// Check if it's an empty value
			if ($cf_value === '' || (is_array($cf_value) && count($cf_value) === 1 && isset($cf_value[0]) && $cf_value[0] === '')) {
				$is_empty_select = true;
			}
		}
		
		// For all other cases, use the standard meta update functions
		switch ($field->getImportType()) {
			case 'import_users':
			case 'shop_customer':
				if ( $is_empty_select ) {
					update_user_meta($pid, $name, '');
				} else {
					update_user_meta($pid, $name, $cf_value);
				}
				break;
				
			case 'taxonomies':
				if ( $is_empty_select ) {
					update_term_meta($pid, $name, '');
				} else {
					update_term_meta($pid, $name, $cf_value);
				}
				break;
				
			default:
				if ( $is_empty_select ) {
					update_post_meta($pid, $name, '');
				} else {
					update_post_meta($pid, $name, $cf_value);
				}
				break;
		}
	}

	/**
	 *
	 * Get ACF field value
	 *
	 * @param Field $field
	 * @param $pid
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function get_post_meta( Field $field, $pid, $name ) {
		switch ( $field->getImportType() ) {
			case 'import_users':
			case 'shop_customer':
				$value = get_user_meta( $pid, $name, true );
				break;
			case 'taxonomies':
				$value = get_term_meta( $pid, $name, true );
				break;
			default:
				$value = get_post_meta( $pid, $name, true );
				break;
		}

		return $value;
	}

	/**
	 *
	 * Assign taxonomy terms with particular post
	 *
	 * @param $pid
	 * @param $assign_taxes
	 * @param $tx_name
	 * @param bool $logger
	 */
	public static function associate_terms( $pid, $assign_taxes, $tx_name, $logger = false ) {
		// wp_all_import_use_wp_set_object_terms references the WP All Import plugin.
		$use_wp_set_object_terms = apply_filters( 'wp_all_import_use_wp_set_object_terms', false, $tx_name );
		if ( $use_wp_set_object_terms ) {
			$term_ids = [];
			if ( ! empty( $assign_taxes ) ) {
				foreach ( $assign_taxes as $ttid ) {
					$term = get_term_by( 'term_taxonomy_id', $ttid, $tx_name );
					if ( $term ) {
						$term_ids[] = $term->term_id;
					}
				}
			}

			return wp_set_object_terms( $pid, $term_ids, $tx_name );
		}

		global $wpdb;

		$term_ids = wp_get_object_terms( $pid, $tx_name, array( 'fields' => 'ids' ) );

		$assign_taxes = ( is_array( $assign_taxes ) ) ? array_filter( $assign_taxes ) : false;

		if ( ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ) {

			$placeholders = implode( ', ', array_fill( 0, count( $term_ids ), '%d' ) );

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->term_taxonomy} SET count = count - 1 WHERE term_taxonomy_id IN ($placeholders) AND count > 0", ...$term_ids ) );

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->term_relationships} WHERE object_id = %d AND term_taxonomy_id IN ($placeholders)", $pid, ...$term_ids ) );
		}
		if ( empty( $assign_taxes ) ) {
			return;
		}

		$values     = array();
		$term_order = 0;
		$term_ids   = array();

		foreach ( $assign_taxes as $tt ) {
			do_action( 'wp_all_import_associate_term', $pid, (int) $tt, $tx_name );
			$term_ids[] = (int) $tt;
			$values[]   = $pid;
			$values[]   = (int) $tt;
			$values[]   = ++ $term_order;
		}

		if ( ! empty( $term_ids ) ) {
			$wpdb->query( $wpdb->prepare(
				"UPDATE {$wpdb->term_taxonomy} SET count = count + 1 WHERE term_taxonomy_id IN (" . implode( ', ', array_fill( 0, count( $term_ids ), '%d' ) ) . ")",
				...$term_ids
			) );
		}

		if ( $values ) {

			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			if ( false === $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES " . implode( ', ', array_fill( 0, count( $values ), "(%d, %d, %d)" ) ) . " ON DUPLICATE KEY UPDATE term_order = VALUES(term_order)", ...$values ) ) ) {
				if ( $logger ) {
					call_user_func( $logger, __( '<b>ERROR</b> Could not insert term relationship into the database', 'csv-xml-import-for-acf' ) . ': ' . esc_html( $wpdb->last_error ) );
				}
			}
		}

		wp_cache_delete( $pid, $tx_name . '_relationships' );
	}

	/**
	 * @param $img_url
	 * @param $pid
	 * @param $logger
	 * @param bool $search_in_gallery
	 * @param bool $search_in_files
	 *
	 * @param array $importData
	 *
	 * @return bool|int|\WP_Error
	 */
	public static function import_image( $img_url, $pid, $logger, $search_in_gallery = false, $search_in_files = false, $importData = array() ) {

		// Search image attachment by ID.
		if ( $search_in_gallery and is_numeric( $img_url ) ) {
			if ( wp_get_attachment_url( $img_url ) ) {
				return $img_url;
			}
		}

		$downloadFiles = "yes";
		$fileName      = "";
		// Search for existing image in /files folder.
		if ( $search_in_files ) {
			// Before start searching check for existing image in pmxi_images table.
			if ( $search_in_gallery ) {
				// Translators: %1$s is the URL-decoded image filename.
				call_user_func( $logger, sprintf( __( '- Searching for existing image `%1$s` by Filename...', 'csv-xml-import-for-acf' ),
					esc_url( rawurldecode( $img_url ) )
				) );
				$imageList = new \PMXI_Image_List();
				$attch     = $imageList->getExistingImageByFilename( basename( $img_url ) );
				if ( $attch ) {
					// Translators: %1$s is the basename of the image URL.
					call_user_func( $logger, sprintf( esc_html__( 'Existing image was found by Filename `%1$s`...', 'csv-xml-import-for-acf' ),
						esc_html( basename( $img_url ) )
					) );

					return $attch->ID;
				}
			}

			$downloadFiles = "no";
			$fileName      = wp_all_import_basename( wp_parse_url( trim( $img_url ), PHP_URL_PATH ) );
		}

		return PMXI_API::upload_image( $pid, $img_url, $downloadFiles, $logger, true, $fileName, 'images', $search_in_gallery, $importData['articleData'], $importData );
	}

	/**
	 * @param $atch_url
	 * @param $pid
	 * @param $logger
	 * @param bool $fast
	 * @param bool $search_in_gallery
	 * @param bool $search_in_files
	 *
	 * @param array $importData
	 *
	 * @return bool|int|\WP_Error
	 */
	public static function import_file( $atch_url, $pid, $logger, $fast = false, $search_in_gallery = false, $search_in_files = false, $importData = array() ) {

		// search file attachment by ID
		if ( $search_in_gallery and is_numeric( $atch_url ) ) {
			if ( wp_get_attachment_url( $atch_url ) ) {
				return $atch_url;
			}
		}

		$downloadFiles = "yes";
		$fileName      = "";
		// Search for existing image in /files folder.
		if ( $search_in_files ) {
			// Before start searching check for existing file in pmxi_images table.
			if ( $search_in_gallery ) {
				// Translators: %1$s is the URL-decoded attachment filename.
				call_user_func( $logger, sprintf( esc_html__( '- Searching for existing file `%1$s` by Filename...', 'csv-xml-import-for-acf' ),
					esc_url( rawurldecode( $atch_url ) )
				) );
				$imageList = new \PMXI_Image_List();
				$attch     = $imageList->getExistingImageByFilename( basename( $atch_url ) );
				if ( $attch ) {
					// Translators: %1$s is the basename of the attachment URL.
					call_user_func( $logger, sprintf( esc_html__( 'Existing file was found by Filename `%1$s`...', 'csv-xml-import-for-acf' ),
						esc_html( basename( $atch_url ) )
					) );

					return $attch->ID;
				}
			}

			$downloadFiles = "no";
			$fileName      = basename( $atch_url );
		}

		return PMXI_API::upload_image( $pid, $atch_url, $downloadFiles, $logger, true, $fileName, "files", $search_in_gallery, $importData['articleData'], $importData );
	}

	/**
	 * @param $values
	 * @param array $post_types
	 *
	 * @return array
	 */
	public static function get_posts_by_relationship( $values, $post_types ) {
		$post_ids = array();
		$values   = array_filter( $values );
		if ( ! empty( $values ) ) {
			if ( ! empty( $post_types ) && ! is_array( $post_types ) ) {
				$post_types = [ $post_types ];
			}else if ( empty( $post_types ) ) {
				$post_types = [];
			}

			$placeholders = array_fill( 0, count( $post_types ), '%s' );
			$format       = implode( ', ', $placeholders );

			$values = array_map( 'trim', $values );
			global $wpdb;
			foreach ( $values as $ev ) {
				$relation = false;
				if ( ctype_digit( $ev ) ) {
					if ( empty( $post_types ) ) {
						$relation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $ev ) );
					} else {
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						$relation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_type IN ($format)", ...array_merge( array( $ev ), $post_types ) ) );
					}
				}
				if ( empty( $relation ) ) {
					if ( empty( $post_types ) ) {
						$relation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_type != %s AND ( post_title = %s OR post_name = %s )", 'revision', $ev, sanitize_title_for_query( $ev ) ) );
					} else {
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
						$relation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_type IN ($format) AND ( post_title = %s OR post_name = %s )", ...array_merge( $post_types, [
							$ev,
							sanitize_title_for_query( $ev )
						] ) ) );
					}
				}
				if ( $relation ) {
					$post_ids[] = (string) $relation->ID;
				}
			}
		}

		// pmxi_acf_post_relationship_ids references the WP All Import plugin.
		return apply_filters( 'pmxi_acf_post_relationship_ids', $post_ids );
	}

	public static function is_field_supported_by_pro( $field ) {
		return in_array( $field, self::$pro_fields );
	}
}
