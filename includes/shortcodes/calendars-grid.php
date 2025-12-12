<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register and enqueue CSS for calendars grid
 */
function codobookings_enqueue_calendars_grid_assets() {
    wp_register_style(
        'codobookings-calendars-grid',
        CODOBOOKINGS_PLUGIN_URL . 'assets/css/calendars-grid.css',
        array(),
        CODOBOOKINGS_VERSION
    );
}
add_action( 'wp_enqueue_scripts', 'codobookings_enqueue_calendars_grid_assets' );

/**
 * Shortcode: [codo_calendars_grid columns="3" category="workshops"]
 * Displays all booking calendars in a grid, or a single calendar view with a back button.
 *
 * @param array $atts
 * @return string
 */
function codobookings_calendars_grid_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'columns'      => 3,
        'post_type'    => 'codo_calendar',
        'details_page' => 'current', // 'current' or a specific page URL
        'category'     => '',
    ), $atts, 'codo_calendars_grid' );

    $columns = max( 1, intval( $atts['columns'] ) );

    // Build query args for grid view
    $query_args = array(
        'post_type'      => $atts['post_type'],
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );

    // Filter by category if provided
    if ( ! empty( $atts['category'] ) ) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'codo_calendar_category',  // custom taxonomy
                'field'    => 'slug',
                'terms'    => sanitize_text_field( $atts['category'] ),
            ),
        );
    }

    /**
     * Filter: Modify calendar grid query arguments
     * 
     * @param array $query_args The WP_Query arguments
     * @param array $atts Shortcode attributes
     */
    $query_args = apply_filters( 'codobookings_grid_query_args', $query_args, $atts );

    $calendars = get_posts( $query_args );

    if ( empty( $calendars ) ) {
        /**
         * Filter: Customize the "no calendars" message
         * 
         * @param string $message The default message
         * @param array $atts Shortcode attributes
         */
        $no_calendars_message = apply_filters( 
            'codobookings_no_calendars_message', 
            '<p>' . __( 'No calendars available at the moment.', 'codobookings' ) . '</p>',
            $atts
        );
        return $no_calendars_message;
    }

    // ✅ Enqueue CSS before returning
    wp_enqueue_style( 'codobookings-calendars-grid' );

    ob_start();

    /**
     * Action: Fires before calendar grid output
     * 
     * @param array $calendars Array of calendar post objects
     * @param array $atts Shortcode attributes
     */
    do_action( 'codobookings_before_calendars_grid', $calendars, $atts );
    ?>

    <div class="codo-calendars-grid" style="--codo-grid-columns: <?php echo esc_attr( $columns ); ?>;">
        <?php foreach ( $calendars as $calendar ) :
            // Validate post type and status for extra safety
            if ( $calendar->post_type !== 'codo_calendar' || $calendar->post_status !== 'publish' ) {
                continue;
            }

            /**
             * Filter: Control whether a calendar appears in grid
             * 
             * @param bool $show Whether to show the calendar (default true)
             * @param int $calendar_id The calendar post ID
             * @param array $atts Shortcode attributes
             */
            $show_calendar = apply_filters( 'codobookings_show_calendar_in_grid', true, $calendar->ID, $atts );
            
            if ( ! $show_calendar ) {
                continue;
            }

            /**
             * Action: Fires before each grid item
             * 
             * @param int $calendar_id The calendar post ID
             * @param WP_Post $calendar The calendar post object
             */
            do_action( 'codobookings_before_calendar_grid_item', $calendar->ID, $calendar );

            $title = esc_html( get_the_title( $calendar ) );
            $desc  = esc_html( wp_trim_words( $calendar->post_content, 25 ) );
            $img   = has_post_thumbnail( $calendar->ID ) ? get_the_post_thumbnail( $calendar->ID, 'medium', array( 'class' => 'codo-calendar-thumb' ) ) : '';

            // Determine details page ID
            $calendar_page_id = get_option( 'codobookings_calendar_page_id' );
            if ( $calendar_page_id && get_post_status( $calendar_page_id ) === 'publish' ) {
                $calendar_page_url = get_permalink( $calendar_page_id );
            } else {
                $calendar_page_url = codobookings_create_calendar_page();
            }

            $current_page_id = get_queried_object_id(); 
            $details_url = add_query_arg( array(
                'calendar_id' => $calendar->ID,
                'back'        => $current_page_id,
            ), esc_url( $calendar_page_url ) );

            /**
             * Filter: Modify the details URL for grid items
             * 
             * @param string $details_url The generated URL
             * @param int $calendar_id The calendar post ID
             * @param array $atts Shortcode attributes
             */
            $details_url = apply_filters( 'codobookings_calendar_details_url', $details_url, $calendar->ID, $atts );

            /**
             * Filter: Customize "Book Now" button text per calendar
             * 
             * @param string $button_text The button text
             * @param int $calendar_id The calendar post ID
             */
            $button_text = apply_filters( 'codobookings_grid_button_text', __( 'Book Now', 'codobookings' ), $calendar->ID );
            ?>
            <div class="codo-calendar-item">
                <?php if ( $img ) : ?>
                    <div class="codo-calendar-thumb-wrap">
                        <a href="<?php echo esc_url( $details_url ); ?>" class="codo-calendar-link">
                            <?php echo wp_kses_post( $img ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div class="codo-calendar-content">
                    <?php
                    /**
                     * Action: Fires at start of grid item content
                     * 
                     * @param int $calendar_id The calendar post ID
                     * @param WP_Post $calendar The calendar post object
                     */
                    do_action( 'codobookings_calendar_grid_item_content_start', $calendar->ID, $calendar );
                    ?>

                    <h3 class="codo-calendar-title"><?php echo esc_html( $title ); ?></h3>
                    <?php if ( ! empty( trim( $desc ) ) ) : ?>
                        <p class="codo-calendar-desc"><?php echo esc_html( $desc ); ?></p>
                    <?php endif; ?>

                    <?php
                    /**
                     * Action: Fires before the booking button
                     * 
                     * @param int $calendar_id The calendar post ID
                     * @param WP_Post $calendar The calendar post object
                     */
                    do_action( 'codobookings_calendar_grid_item_before_button', $calendar->ID, $calendar );
                    ?>

                    <a href="<?php echo esc_url( $details_url ); ?>" class="button codo-book-btn">
                        <?php echo esc_html( $button_text ); ?>
                    </a>

                    <?php
                    /**
                     * Action: Fires at end of grid item content
                     * 
                     * @param int $calendar_id The calendar post ID
                     * @param WP_Post $calendar The calendar post object
                     */
                    do_action( 'codobookings_calendar_grid_item_content_end', $calendar->ID, $calendar );
                    ?>
                </div>
            </div>
            <?php
            /**
             * Action: Fires after each grid item
             * 
             * @param int $calendar_id The calendar post ID
             * @param WP_Post $calendar The calendar post object
             */
            do_action( 'codobookings_after_calendar_grid_item', $calendar->ID, $calendar );
            ?>
        <?php endforeach; ?>
    </div>

    <?php
    /**
     * Action: Fires after calendar grid output
     * 
     * @param array $calendars Array of calendar post objects
     * @param array $atts Shortcode attributes
     */
    do_action( 'codobookings_after_calendars_grid', $calendars, $atts );

    return ob_get_clean();
}
add_shortcode( 'codo_calendars_grid', 'codobookings_calendars_grid_shortcode' );