<?php

class FacetWP_Display
{

    /* (array) Facet types being used on the page */
    public $active_types = [];

    /* (array) Facets being used on the page */
    public $active_facets = [];

    /* (array) Extra features used on the page */
    public $active_extras = [];

    /* (array) Saved shortcode attributes */
    public $shortcode_atts = [];

    /* (boolean) Whether to enable FacetWP for the current page */
    public $load_assets = false;

    /* (array) Scripts and stylesheets to enqueue */
    public $assets = [];

    /* (array) Data to pass to front-end JS */
    public $json = [];


    function __construct() {
        add_filter( 'widget_text', 'do_shortcode' );
        add_action( 'loop_start', [ $this, 'add_template_tag' ] );
        add_action( 'loop_no_results', [ $this, 'add_template_tag' ] );
        add_action( 'wp_footer', [ $this, 'front_scripts' ], 25 );
        add_shortcode( 'facetwp', [ $this, 'shortcode' ] );
    }


    /**
     * Detect the loop container if the "facetwp-template" class is missing
     */
    function add_template_tag( $wp_query ) {
        if ( true === $wp_query->get( 'facetwp' ) && did_action( 'wp_head' ) ) {
            echo "<!--fwp-loop-->\n";
        }
    }


    /**
     * Register shortcodes
     */
    function shortcode( $atts ) {
        $this->shortcode_atts[] = $atts;

        $output = '';
        if ( isset( $atts['facet'] ) ) {
            $facet = FWP()->helper->get_facet_by_name( $atts['facet'] );

            if ( $facet ) {
                $output = '<div class="facetwp-facet facetwp-facet-' . $facet['name'] . ' facetwp-type-' . $facet['type'] . '" data-name="' . $facet['name'] . '" data-type="' . $facet['type'] . '"></div>';

                // Build list of active facet types
                $this->active_types[ $facet['type'] ] = $facet['type'];
                $this->active_facets[ $facet['name'] ] = $facet['name'];
                $this->load_assets = true;
            }
        }
        elseif ( isset( $atts['template'] ) ) {
            $template = FWP()->helper->get_template_by_name( $atts['template'] );

            if ( $template ) {
                global $wp_query;

                // Preload the template (search engine visible)
                $temp_query = $wp_query;
                $args = FWP()->request->process_preload_data( $template['name'] );
                $preload_data = FWP()->facet->render( $args );
                $wp_query = $temp_query;

                $output = '<div class="facetwp-template" data-name="' . $atts['template'] . '">';
                $output .= $preload_data['template'];
                $output .= '</div>';

                $this->load_assets = true;
            }
        }
        elseif ( isset( $atts['sort'] ) ) {
            $this->active_extras['sort'] = true;
            $output = '<div class="facetwp-sort"></div>';
        }
        elseif ( isset( $atts['selections'] ) ) {
            $output = '<div class="facetwp-selections"></div>';
        }
        elseif ( isset( $atts['counts'] ) ) {
            $this->active_extras['counts'] = true;
            $output = '<div class="facetwp-counts"></div>';
        }
        elseif ( isset( $atts['pager'] ) ) {
            $this->active_extras['pager'] = true;
            $output = '<div class="facetwp-pager"></div>';
        }
        elseif ( isset( $atts['per_page'] ) ) {
            $this->active_extras['per_page'] = true;
            $output = '<div class="facetwp-per-page"></div>';
        }

        $output = apply_filters( 'facetwp_shortcode_html', $output, $atts );

        return $output;
    }


    /**
     * Output facet scripts
     */
    function front_scripts() {

        // Not enqueued - front.js needs to load before front_scripts()
        if ( apply_filters( 'facetwp_load_assets', $this->load_assets ) ) {

            // Load CSS?
            if ( apply_filters( 'facetwp_load_css', true ) ) {
                $this->assets['front.css'] = FACETWP_URL . '/assets/css/front.css';
            }

            // Load required JS
            $this->assets['front.js'] = FACETWP_URL . '/assets/js/dist/front.min.js';

            // Backwards compat?
            if ( apply_filters( 'facetwp_load_deprecated', true ) ) {
                $this->assets['front-deprecated.js'] = FACETWP_URL . '/assets/js/src/deprecated.js';
            }

            // Load a11y?
            if ( apply_filters( 'facetwp_load_a11y', false ) ) {
                $this->assets['accessibility.js'] = FACETWP_URL . '/assets/js/src/accessibility.js';
            }

            // Pass GET and URI params
            $http_params = [
                'get' => $_GET,
                'uri' => FWP()->helper->get_uri(),
                'url_vars' => FWP()->request->url_vars,
            ];

            // See FWP()->facet->get_query_args()
            if ( ! empty( FWP()->facet->archive_args ) ) {
                $http_params['archive_args'] = FWP()->facet->archive_args;
            }

            // Populate the FWP_JSON object
            $this->json['prefix'] = FWP()->helper->get_setting( 'prefix' );
            $this->json['no_results_text'] = __( 'No results found', 'fwp-front' );
            $this->json['ajaxurl'] = get_rest_url() . 'facetwp/v1/refresh';
            $this->json['nonce'] = wp_create_nonce( 'wp_rest' );

            if ( apply_filters( 'facetwp_use_preloader', true ) ) {
                $overrides = FWP()->request->process_preload_overrides([
                    'facets' => $this->active_facets,
                    'extras' => $this->active_extras,
                ]);
                $args = FWP()->request->process_preload_data( false, $overrides );
                $this->json['preload_data'] = FWP()->facet->render( $args );
            }

            ob_start();

            foreach ( $this->active_types as $type ) {
                $facet_class = FWP()->helper->facet_types[ $type ];
                if ( method_exists( $facet_class, 'front_scripts' ) ) {
                    $facet_class->front_scripts();
                }
            }

            $inline_scripts = ob_get_clean();
            $assets = apply_filters( 'facetwp_assets', $this->assets );

            foreach ( $assets as $slug => $data ) {
                $data = (array) $data;
                $is_css = ( 'css' == substr( $slug, -3 ) );
                $version = empty( $data[1] ) ? FACETWP_VERSION : $data[1];
                $url = $data[0];

                if ( false !== strpos( $url, 'facetwp' ) ) {
                    $prefix = ( false !== strpos( $url, '?' ) ) ? '&' : '?';
                    $url .= $prefix . 'ver=' . $version;
                }

                $html = $is_css ? '<link href="{url}" rel="stylesheet">' : '<script src="{url}"></script>';
                $html = apply_filters( 'facetwp_asset_html', $html, $url );
                echo str_replace( '{url}', $url, $html ) . "\n";
            }

            echo $inline_scripts;
?>
<script>
window.FWP_JSON = <?php echo json_encode( $this->json ); ?>;
window.FWP_HTTP = <?php echo json_encode( $http_params ); ?>;
</script>
<?php
        }
    }
}
