<?php

class FacetWP_Facet_Radio_Core extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'Radio', 'fwp' );
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
        $label_any = empty( $facet['label_any'] ) ? false : facetwp_i18n( $facet['label_any'] );

        if ( $label_any ) {
            $selected = empty( $selected_values ) ? ' checked' : '';
            $output .= '<div class="facetwp-radio' . $selected . '" data-value="">' . esc_attr( $label_any ) . '</div>';
        }

        foreach ( $values as $row ) {
            $label = esc_html( $row['facet_display_value'] );
            $selected = in_array( $row['facet_value'], $selected_values ) ? ' checked' : '';
            $selected .= ( 0 == $row['counter'] && '' == $selected ) ? ' disabled' : '';
            $output .= '<div class="facetwp-radio' . $selected . '" data-value="' . esc_attr( $row['facet_value'] ) . '">';
            $output .= apply_filters( 'facetwp_facet_display_value', $label, [
                'selected' => ( '' !== $selected ),
                'facet' => $facet,
                'row' => $row
            ]);
            $output .= ' <span class="facetwp-counter">(' . $row['counter'] . ')</span>';
            $output .= '</div>';
        }

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
        $this->render_setting( 'ghosts' );
        $this->render_setting( 'orderby' );
        $this->render_setting( 'count' );
?>
        <div><input type="hidden" class="facet-operator" value="or" /></div>
<?php
    }
}
