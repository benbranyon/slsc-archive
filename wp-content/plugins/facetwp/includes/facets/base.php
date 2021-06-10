<?php

class FacetWP_Facet
{

    /**
     * Grab the orderby, as needed by several facet types
     * @since 3.0.4
     */
    function get_orderby( $facet ) {
        $key = $facet['orderby'];

        // Count (default)
        $orderby = 'counter DESC, f.facet_display_value ASC';

        // Display value
        if ( 'display_value' == $key ) {
            $orderby = 'f.facet_display_value ASC';
        }
        // Raw value
        elseif ( 'raw_value' == $key ) {
            $orderby = 'f.facet_value ASC';
        }
        // Term order
        elseif ( 'term_order' == $key && 'tax' == substr( $facet['source'], 0, 3 ) ) {
            $term_ids = get_terms( [
                'taxonomy' => str_replace( 'tax/', '', $facet['source'] ),
                'term_order' => true, // Custom flag
                'fields' => 'ids',
            ] );

            if ( ! empty( $term_ids ) && ! is_wp_error( $term_ids ) ) {
                $term_ids = implode( ',', $term_ids );
                $orderby = "FIELD(f.term_id, $term_ids)";
            }
        }

        // Sort by depth just in case
        $orderby = "f.depth, $orderby";

        return $orderby;
    }


    /**
     * Grab the limit, and support -1
     * @since 3.5.4
     */
    function get_limit( $facet, $default = 10 ) {
        $count = $facet['count'];

        if ( '-1' == $count ) {
            return 1000;
        }
        elseif ( ctype_digit( $count ) ) {
            return $count;
        }

        return $default;
    }


    /**
     * Adjust the $where_clause for facets in "OR" mode
     *
     * FWP()->or_values contains EVERY facet and their matching post IDs
     * FWP()->unfiltered_post_ids contains original post IDs
     *
     * @since 3.2.0
     */
    function get_where_clause( $facet ) {

        // Ignore the current facet's selections
        if ( isset( FWP()->or_values ) && ( 1 < count( FWP()->or_values ) || ! isset( FWP()->or_values[ $facet['name'] ] ) ) ) {
            $post_ids = [];
            $or_values = FWP()->or_values; // Preserve original
            unset( $or_values[ $facet['name'] ] );

            $counter = 0;
            foreach ( $or_values as $name => $vals ) {
                $post_ids = ( 0 == $counter ) ? $vals : array_intersect( $post_ids, $vals );
                $counter++;
            }

            $post_ids = array_intersect( $post_ids, FWP()->unfiltered_post_ids );
        }
        else {
            $post_ids = FWP()->unfiltered_post_ids;
        }

        $post_ids = empty( $post_ids ) ? [ 0 ] : $post_ids;
        return ' AND post_id IN (' . implode( ',', $post_ids ) . ')';
    }


    /**
     * Render some commonly used admin settings
     * @since 3.5.6
     */
    function render_setting( $name ) {
        if ( 'label_any' == $name ) :
?>
        <div class="facetwp-row">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Default label', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content">Customize the "Any" label</div>
                </div>
            </div>
            <div>
                <input type="text" class="facet-label-any" value="<?php _e( 'Any', 'fwp' ); ?>" />
            </div>
        </div>
<?php
        elseif ( 'parent_term' == $name ) :
?>
        <div class="facetwp-row" v-show="facet.source.substr(0, 3) == 'tax'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Parent term', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content">
                        To show only child terms, enter the parent <a href="https://facetwp.com/how-to-find-a-wordpress-terms-id/" target="_blank">term ID</a>.
                        Otherwise, leave blank.
                    </div>
                </div>
            </div>
            <div>
                <input type="text" class="facet-parent-term" />
            </div>
        </div>
<?php
        elseif ( 'hierarchical' == $name ) :
?>
        <div class="facetwp-row" v-show="facet.source.substr(0, 3) == 'tax'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Hierarchical', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Is this a hierarchical taxonomy?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-hierarchical" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
<?php
        elseif ( 'show_expanded' == $name ) :
?>
        <div class="facetwp-row" v-show="facet.hierarchical == 'yes'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Show expanded', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Should child terms be visible by default?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-show-expanded" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
<?php
        elseif ( 'multiple' == $name ) :
?>
        <div class="facetwp-row">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Multi-select', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Allow multiple selections?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-multiple" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
<?php
        elseif ( 'ghosts' == $name ) :
?>
        <div class="facetwp-row">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Show ghosts', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Show choices that would return zero results?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-ghosts" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
        <div class="facetwp-row" v-show="facet.ghosts == 'yes'">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Preserve ghost order', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Keep ghost choices in the same order?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <label class="facetwp-switch">
                    <input type="checkbox" class="facet-preserve-ghosts" true-value="yes" false-value="no" />
                    <span class="facetwp-slider"></span>
                </label>
            </div>
        </div>
<?php
        elseif ( 'modifiers' == $name ) :
?>
        <div class="facetwp-row">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Value modifiers', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Include or exclude certain values?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <select class="facet-modifier-type">
                    <option value="off"><?php _e( 'Off', 'fwp' ); ?></option>
                    <option value="exclude"><?php _e( 'Exclude these values', 'fwp' ); ?></option>
                    <option value="include"><?php _e( 'Show only these values', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
        <div class="facetwp-row" v-show="facet.modifier_type != 'off'">
            <div><!-- empty --></div>
            <div><textarea class="facet-modifier-values" placeholder="Add one value per line"></textarea></div>
        </div>
<?php
        elseif ( 'operator' == $name ) :
?>
        <div class="facetwp-row">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Behavior', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'How should multiple selections affect the results?', 'fwp' ); ?></div>
                </div>
            </div>
            <div>
                <select class="facet-operator">
                    <option value="and"><?php _e( 'Match all', 'fwp' ); ?></option>
                    <option value="or"><?php _e( 'Match any', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
<?php
        elseif ( 'orderby' == $name ) :
?>
        <div class="facetwp-row">
            <div><?php _e( 'Sort by', 'fwp' ); ?>:</div>
            <div>
                <select class="facet-orderby">
                    <option value="count"><?php _e( 'Highest count', 'fwp' ); ?></option>
                    <option value="display_value"><?php _e( 'Display value', 'fwp' ); ?></option>
                    <option value="raw_value"><?php _e( 'Raw value', 'fwp' ); ?></option>
                    <option value="term_order"><?php _e( 'Term order', 'fwp' ); ?></option>
                </select>
            </div>
        </div>
<?php
        elseif ( 'count' == $name ) :
?>
        <div class="facetwp-row">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Count', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'The maximum number of choices to show (-1 for no limit)', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-count" value="10" /></div>
        </div>
<?php
        elseif ( 'soft_limit' == $name ) :
?>
        <div class="facetwp-row">
            <div>
                <div class="facetwp-tooltip">
                    <?php _e( 'Soft limit', 'fwp' ); ?>:
                    <div class="facetwp-tooltip-content"><?php _e( 'Show a toggle link after this many choices', 'fwp' ); ?></div>
                </div>
            </div>
            <div><input type="text" class="facet-soft-limit" value="5" /></div>
        </div>
<?php
        endif;
    }
}
