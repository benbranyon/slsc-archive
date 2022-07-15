<?php

class FacetWP_Facet_Sort extends FacetWP_Facet
{

    function __construct() {
        $this->label = __( 'Sort', 'fwp' );
        $this->fields = [ 'sort_default_label', 'sort_options' ];
    }


    function render( $params ) {
        $facet = $params['facet'];
        $selected_values = (array) $params['selected_values'];

        $label = facetwp_i18n( $facet['default_label'] );
        $output = '<option value="">' . esc_attr( $label ) . '</option>';

        foreach ( $facet['sort_options'] as $choice ) {
            $label = facetwp_i18n( $choice['label'] );
            $selected = in_array( $choice['name'], $selected_values ) ? ' selected' : '';
            $output .= '<option value="' . esc_attr( $choice['name'] ) . '"' . $selected . '>' . esc_attr( $label ) . '</option>';
        }

        return '<select>' . $output . '</select>';
    }


    function filter_posts( $params ) {
        return 'continue';
    }


    function register_fields() {
        return [
            'sort_default_label' => [
                'type' => 'alias',
                'items' => [
                    'default_label' => [
                        'label' => __( 'Default label', 'fwp' ),
                        'notes' => 'The sort box placeholder text',
                        'default' => __( 'Sort by', 'fwp' )
                    ]
                ]
            ],
            'sort_options' => [
                'label' => __( 'Sort options', 'fwp' ),
                'notes' => 'Define the choices that appear in the sort box',
                'html' => '<sort-options :facet="facet"></sort-options><input type="hidden" class="facet-sort-options" value="[]" />'
            ]
        ];
    }
}
