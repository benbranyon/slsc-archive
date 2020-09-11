<?php

class FacetWP_Facet_Pager extends FacetWP_Facet
{

    public $pager_args;


    function __construct() {
        $this->label = __( 'Pager', 'fwp' );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {
        $facet = $params['facet'];
        $pager_type = $facet['pager_type'];
        $this->pager_args = FWP()->facet->pager_args;

        $method = 'render_' . $pager_type;
        if ( method_exists( $this, $method ) ) {
            $output = $this->$method( $facet );

            if ( 'numbers' == $pager_type ) {
                $output = '<div class="facetwp-pager">' . $output . '</div>';
            }

            return $output;
        }
    }


    function render_numbers( $facet ) {
        $inner_size = (int) $facet['inner_size'];
        $dots_label = facetwp_i18n( $facet['dots_label'] );
        $prev_label = facetwp_i18n( $facet['prev_label'] );
        $next_label = facetwp_i18n( $facet['next_label'] );

        $output = '';
        $page = $this->pager_args['page'];
        $total_pages = $this->pager_args['total_pages'];
        $inner_first = max( $page - $inner_size, 2 );
        $inner_last = min( $page + $inner_size, $total_pages - 1 );

        if ( 1 < $total_pages ) {

            // Prev button
            if ( 1 < $page && '' != $prev_label ) {
                $output .= $this->render_page( $page - 1, $prev_label, 'prev' );
            }

            // First page
            $output .= $this->render_page( 1, false, 'first' );

            // Dots
            if ( 2 < $inner_first && '' != $dots_label ) {
                $output .= $this->render_page( '', $dots_label, 'dots' );
            }

            for ( $i = $inner_first; $i <= $inner_last; $i++ ) {
                $output .= $this->render_page( $i );
            }

            // Dots
            if ( $inner_last < $total_pages - 1 && '' != $dots_label ) {
                $output .= $this->render_page( '', $dots_label, 'dots' );
            }

            // Last page
            $output .= $this->render_page( $total_pages, false, 'last' );

            // Next button
            if ( $page < $total_pages && '' != $next_label ) {
                $output .= $this->render_page( $page + 1, $next_label, 'next' );
            }
        }

        return $output;
    }


    function render_page( $page, $label = false, $extra_class = false ) {
        $label = ( false === $label ) ? $page : $label;
        $class = 'facetwp-page';

        if ( ! empty( $extra_class ) ) {
            $class .= ' ' . $extra_class;
        }

        if ( $page == $this->pager_args['page'] ) {
            $class .= ' active';
        }

        $data = empty( $page ) ? '' : ' data-page="' . $page . '"';
        return '<a class="' . $class . '"' . $data . '>' . $label . '</a>';
    }


    function render_counts( $facet ) {
        $text_singular = facetwp_i18n( $facet['count_text_singular'] );
        $text_plural = facetwp_i18n( $facet['count_text_plural'] );
        $text_none = facetwp_i18n( $facet['count_text_none'] );

        $page = $this->pager_args['page'];
        $per_page = $this->pager_args['per_page'];
        $total_rows = $this->pager_args['total_rows'];

        if ( 1 < $total_rows ) {
            $lower = ( 1 + ( ( $page - 1 ) * $per_page ) );
            $upper = ( $page * $per_page );
            $upper = ( $total_rows < $upper ) ? $total_rows : $upper;

            $output = $text_plural;
            $output = str_replace( '[lower]', $lower, $output );
            $output = str_replace( '[upper]', $upper, $output );
            $output = str_replace( '[total]', $total_rows, $output );
        }
        else {
            $output = ( 0 < $total_rows ) ? $text_singular : $text_none;
        }

        return $output;
    }


    function render_load_more( $facet ) {
        $text = facetwp_i18n( $facet['load_more_text'] );
        $loading_text = facetwp_i18n( $facet['loading_text'] );

        $output = '<button class="facetwp-load-more" data-loading="' . esc_attr( $loading_text ) . '">' . esc_attr( $text ) . '</button>';
        return $output;
    }


    function render_per_page( $facet ) {
        $label = facetwp_i18n( $facet['default_label'] );
        $options = explode( ',', str_replace( ' ', '', $facet['per_page_options'] ) );

        $output = '<select class="facetwp-per-page-select">';
        $output .= '<option value="">' . $label . '</option>';

        foreach ( $options as $option ) {
            $output .= '<option value="' . $option . '">' . $option . '</option>';
        }

        $output .= '</select>';
        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        return 'continue';
    }


    /**
     * (Front-end) Attach settings to the AJAX response
     */
    function settings_js( $params ) {
        $facet = $params['facet'];

        return [
            'pager_type' => $facet['pager_type']
        ];
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <div class="facetwp-row">
            <div><?php _e('Pager type', 'fwp'); ?>:</div>
            <div>
                <select class="facet-pager-type">
                    <option value="numbers"><?php _e( 'Page numbers', 'fwp' ); ?></option>
                    <option value="counts"><?php _e( 'Result counts', 'fwp' ); ?></option>
                    <option value="load_more"><?php _e( 'Load more', 'fwp' ); ?></option>
                    <option value="per_page"><?php _e( 'Per page', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'numbers'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e('Inner size', 'fwp'); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Number of pages to show on each side of the current page', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-inner-size" value="2" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'numbers'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e('Dots label', 'fwp'); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'The filler between the inner and outer pages', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-dots-label" value="…" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'numbers'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e('Prev button label', 'fwp'); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Leave blank to hide', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-prev-label" value="« Prev" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'numbers'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e('Next button label', 'fwp'); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Leave blank to hide', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-next-label" value="Next »" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'counts'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e('Count text (plural)', 'fwp'); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Available tags: [lower], [upper], and [total]', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-count-text-plural" value="[lower] - [upper] of [total] results" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'counts'">
            <div><?php _e('Count text (singular)', 'fwp'); ?>:</div>
            <div><input type="text" class="facet-count-text-singular" value="1 result" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'counts'">
            <div><?php _e('Count text (no results)', 'fwp'); ?>:</div>
            <div><input type="text" class="facet-count-text-none" value="No results" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'load_more'">
            <div><?php _e('Load more text', 'fwp'); ?>:</div>
            <div><input type="text" class="facet-load-more-text" value="Load more" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'load_more'">
            <div><?php _e('Loading text', 'fwp'); ?>:</div>
            <div><input type="text" class="facet-loading-text" value="Loading..." /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'per_page'">
            <div><?php _e('Default label', 'fwp'); ?>:</div>
            <div><input type="text" class="facet-default-label" value="Per page" /></div>
        </div>
        <div class="facetwp-row" v-show="facet.pager_type == 'per_page'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e('Per page options', 'fwp'); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'A comma-separated list of choices', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-per-page-options" value="10, 25, 50, 100" /></div>
        </div>
<?php
    }
}
