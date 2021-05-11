<?php

class FacetWP_Facet_Dropdown extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'Dropdown', 'fwp' );
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

        $label_any = empty( $facet['label_any'] ) ? __( 'Any', 'fwp-front' ) : $facet['label_any'];
        $label_any = facetwp_i18n( $label_any );

        $output .= '<select class="facetwp-dropdown">';
        $output .= '<option value="">' . esc_attr( $label_any ) . '</option>';

        foreach ( $values as $result ) {
            $selected = in_array( $result['facet_value'], $selected_values ) ? ' selected' : '';

            $display_value = '';
            for ( $i = 0; $i < (int) $result['depth']; $i++ ) {
                $display_value .= '&nbsp;&nbsp;';
            }

            // Determine whether to show counts
            $display_value .= esc_attr( $result['facet_display_value'] );
            $show_counts = apply_filters( 'facetwp_facet_dropdown_show_counts', true, [ 'facet' => $facet ] );

            if ( $show_counts ) {
                $display_value .= ' (' . $result['counter'] . ')';
            }

            $output .= '<option value="' . esc_attr( $result['facet_value'] ) . '"' . $selected . '>' . $display_value . '</option>';
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
     * Output admin settings HTML
     */
    function settings_html() {
        $this->render_setting( 'label_any' );
        $this->render_setting( 'parent_term' );
        $this->render_setting( 'modifiers' );
        $this->render_setting( 'hierarchical' );
        $this->render_setting( 'orderby' );
        $this->render_setting( 'count' );
    }
}
