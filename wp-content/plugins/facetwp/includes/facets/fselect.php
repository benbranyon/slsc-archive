<?php

class FacetWP_Facet_fSelect extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'fSelect', 'fwp' );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        return FWP()->helper->facet_types['checkboxes']->load_values( $params );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $facet = $params['facet'];
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        if ( FWP()->helper->facet_is( $facet, 'hierarchical', 'yes' ) ) {
            $values = FWP()->helper->sort_taxonomy_values( $params['values'], $facet['orderby'] );
        }

        $multiple = FWP()->helper->facet_is( $facet, 'multiple', 'yes' ) ? ' multiple="multiple"' : '';
        $label_any = empty( $facet['label_any'] ) ? __( 'Any', 'fwp-front' ) : $facet['label_any'];
        $label_any = facetwp_i18n( $label_any );

        $output .= '<select class="facetwp-dropdown"' . $multiple . '>';
        $output .= '<option value="">' . esc_html( $label_any ) . '</option>';

        foreach ( $values as $result ) {
            $selected = in_array( $result['facet_value'], $selected_values, true ) ? ' selected' : '';
            $selected .= ( 0 == $result['counter'] && '' == $selected ) ? ' disabled' : '';

            // Determine whether to show counts
            $display_value = esc_html( $result['facet_display_value'] );
            $show_counts = apply_filters( 'facetwp_facet_dropdown_show_counts', true, [ 'facet' => $facet ] );
            $counter = ( $show_counts ) ? $result['counter'] : '';

            $output .= '<option value="' . esc_attr( $result['facet_value'] ) . '" data-counter="' . $counter . '" class="d' . $result['depth'] . '"' . $selected . '>' . $display_value . '</option>';
        }

        $output .= '</select>';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        return FWP()->helper->facet_types['checkboxes']->filter_posts( $params );
    }


    /**
     * (Front-end) Attach settings to the AJAX response
     */
    function settings_js( $params ) {
        $facet = $params['facet'];

        $label_any = empty( $facet['label_any'] ) ? __( 'Any', 'fwp-front' ) : $facet['label_any'];
        $label_any = facetwp_i18n( $label_any );

        return [
            'placeholder'       => $label_any,
            'overflowText'      => __( '{n} selected', 'fwp-front' ),
            'searchText'        => __( 'Search', 'fwp-front' ),
            'noResultsText'     => __( 'No results found', 'fwp-front' ),
            'operator'          => $facet['operator']
        ];
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
        FWP()->display->assets['fSelect.css'] = FACETWP_URL . '/assets/vendor/fSelect/fSelect.css';
        FWP()->display->assets['fSelect.js'] = FACETWP_URL . '/assets/vendor/fSelect/fSelect.js';
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
        $this->render_setting( 'label_any' );
        $this->render_setting( 'parent_term' );
        $this->render_setting( 'modifiers' );
        $this->render_setting( 'hierarchical' );
        $this->render_setting( 'multiple' );
        $this->render_setting( 'ghosts' );
        $this->render_setting( 'operator' );
        $this->render_setting( 'orderby' );
        $this->render_setting( 'count' );
    }
}
