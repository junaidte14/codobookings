<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', function() {
    add_shortcode('codo_calendar', 'codobookings_calendar_shortcode');
});

function codobookings_calendar_shortcode( $atts ) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'codo_calendar');

    // If no ID is given in the shortcode, check for ?calendar_id= in the URL
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only displaying public data
    $calendar_id = intval( $atts['id'] );
    if ( ! $calendar_id && isset( $_GET['calendar_id'] ) ) {
        $calendar_id = intval( $_GET['calendar_id'] );
    }

    // Validate that a calendar ID exists and is valid
    if ( ! $calendar_id ) {
        return '<div class="codo-calendar-error">Invalid calendar ID.</div>';
    }

    // Optionally, confirm that the post exists and is of the correct type
    $calendar_post = get_post( $calendar_id );
    // Validate post exists, is published, and is the correct post type
    if (
        ! $calendar_post ||
        $calendar_post->post_type !== 'codo_calendar' ||
        $calendar_post->post_status !== 'publish'
    ) {
        return '<p>' . __( 'This calendar is not available.', 'codobookings' ) . '</p>';
    }

    // Enqueue frontend assets
    wp_enqueue_style( 'codobookings-frontend', CODOBOOKINGS_PLUGIN_URL . 'assets/css/calendar-frontend.css', array(), CODOBOOKINGS_VERSION );
    // JS dependency chain
    wp_enqueue_script( 'codobookings-utils', CODOBOOKINGS_PLUGIN_URL . 'assets/js/calendar/utils.js', array(), CODOBOOKINGS_VERSION, true );
    wp_enqueue_script( 'codobookings-api', CODOBOOKINGS_PLUGIN_URL . 'assets/js/calendar/api.js', array('codobookings-utils'), CODOBOOKINGS_VERSION, true );
    wp_enqueue_script( 'codobookings-sidebar', CODOBOOKINGS_PLUGIN_URL . 'assets/js/calendar/sidebar.js', array('codobookings-utils'), CODOBOOKINGS_VERSION, true );
    wp_enqueue_script( 'codobookings-calendar-weekly', CODOBOOKINGS_PLUGIN_URL . 'assets/js/calendar/calendar-weekly.js', array('codobookings-api','codobookings-sidebar'), CODOBOOKINGS_VERSION, true );
    wp_enqueue_script( 'codobookings-calendar-onetime', CODOBOOKINGS_PLUGIN_URL . 'assets/js/calendar/calendar-onetime.js', array('codobookings-api','codobookings-sidebar'), CODOBOOKINGS_VERSION, true );
    wp_enqueue_script( 'codobookings-main', CODOBOOKINGS_PLUGIN_URL . 'assets/js/calendar/main.js', array(
        'codobookings-utils',
        'codobookings-api',
        'codobookings-sidebar',
        'codobookings-calendar-weekly',
        'codobookings-calendar-onetime'
    ), CODOBOOKINGS_VERSION, true );

    // Get sidebar settings
    $settings = get_post_meta( $calendar_id, '_codo_sidebar_settings', true );
    $settings = wp_parse_args( $settings, array(
        'show_title'       => 'yes',
        'allow_guest'      => 'no',
    ));

    //var_dump($settings);

    // ✅ Safely build current URL
    $host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    $current_url = ( is_ssl() ? 'https://' : 'http://' ) . $host . $request_uri;
    $login_url = wp_login_url( esc_url_raw( $current_url ) );

    $confirmation_message = get_post_meta( $calendar_id, '_codo_confirmation_message', true ) ?: __( 'Your booking has been confirmed successfully! Our team will soon contact you with further details. Thank you for choosing us.', 'codobookings' );

    /**
     * Filter: Modify confirmation message before displaying
     * 
     * @param string $confirmation_message The confirmation message text
     * @param int $calendar_id The calendar post ID
     */
    $confirmation_message = apply_filters( 'codobookings_confirmation_message', $confirmation_message, $calendar_id );

    wp_localize_script( 'codobookings-main', 'CODOBookingsData', array(
        'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
        'nonce'      => wp_create_nonce( 'codobookings_nonce' ),
        'calendarId' => $calendar_id,
        'i18n'       => array(
            'loading' => __( 'Loading booking calendar...', 'codobookings' ),
            'failed'  => __( 'Failed to load calendar. Please refresh the page.', 'codobookings' ),
            'book'    => __( 'Book this slot', 'codobookings' ),
            'noSlots' => __( 'No slots available for selected period.', 'codobookings' ),
        )
    ));

    wp_add_inline_script( 'codobookings-main', sprintf(
        'window.codobookings_settings_%d = %s;',
        $calendar_id,
        wp_json_encode([
            'settings'  => $settings,
            'confirmation_message' => $confirmation_message,
            'userEmail' => is_user_logged_in() ? wp_get_current_user()->user_email : '',
            'loginUrl'  => $login_url,
        ])
    ) );

    $unique_id = 'codo-calendar-' . $calendar_id . '-' . uniqid();

    // Determine Back URL
    $back_url = '';
    if ( ! empty( $_GET['back'] ) ) {
        $back_id = intval( $_GET['back'] ); // sanitize the value
        $back_url = get_permalink( $back_id );
        
        /**
         * Filter: Modify the back button URL
         * 
         * @param string $back_url The back button URL
         * @param int $back_id The back page ID
         * @param int $calendar_id The current calendar ID
         */
        $back_url = apply_filters( 'codobookings_calendar_back_url', $back_url, $back_id, $calendar_id );
    }

    /**
     * Filter: Modify the back button text
     * 
     * @param string $button_text The back button text
     * @param int $calendar_id The calendar post ID
     */
    $back_button_text = apply_filters( 
        'codobookings_calendar_back_button_text', 
        __( 'Back to All Calendars', 'codobookings' ),
        $calendar_id 
    );

    ob_start();

    /**
     * Action: Fires before the calendar container
     * 
     * @param int $calendar_id The calendar post ID
     * @param WP_Post $calendar_post The calendar post object
     */
    do_action( 'codobookings_before_calendar_container', $calendar_id, $calendar_post );
    ?>

    <div class="codo-calendar-container">
        <?php 
        /**
         * Action: Fires at the start of calendar container
         * 
         * @param int $calendar_id The calendar post ID
         * @param WP_Post $calendar_post The calendar post object
         */
        do_action( 'codobookings_calendar_container_start', $calendar_id, $calendar_post );
        ?>

        <?php if ( !empty($back_url) ) : ?>
            <?php
            /**
             * Action: Fires before the back button
             * 
             * @param string $back_url The back button URL
             * @param int $calendar_id The calendar post ID
             */
            do_action( 'codobookings_before_back_button', $back_url, $calendar_id );
            ?>
            <a href="<?php echo esc_url( $back_url ); ?>" class="button codo-back-btn">
                ← <?php echo esc_html( $back_button_text ); ?>
            </a>
            <?php
            /**
             * Action: Fires after the back button
             * 
             * @param string $back_url The back button URL
             * @param int $calendar_id The calendar post ID
             */
            do_action( 'codobookings_after_back_button', $back_url, $calendar_id );
            ?>
        <?php endif; ?>

        <div class="codo-single-calendar">
            <?php
            /**
             * Action: Fires at the start of single calendar content
             * 
             * @param int $calendar_id The calendar post ID
             * @param WP_Post $calendar_post The calendar post object
             */
            do_action( 'codobookings_single_calendar_start', $calendar_id, $calendar_post );
            ?>

            <?php if ( has_post_thumbnail( $calendar_id ) ) : ?>
                <?php
                /**
                 * Action: Fires before the featured image
                 * 
                 * @param int $calendar_id The calendar post ID
                 */
                do_action( 'codobookings_before_calendar_featured_image', $calendar_id );
                ?>
                <div class="codo-calendar-featured">
                    <?php 
                    /**
                     * Filter: Modify featured image HTML
                     * 
                     * @param string $image_html The featured image HTML
                     * @param int $calendar_id The calendar post ID
                     */
                    $featured_image = apply_filters( 
                        'codobookings_calendar_featured_image', 
                        get_the_post_thumbnail( $calendar_id, 'large', array( 'class' => 'codo-calendar-img' ) ),
                        $calendar_id 
                    );
                    echo wp_kses_post( $featured_image );
                    ?>
                </div>
                <?php
                /**
                 * Action: Fires after the featured image
                 * 
                 * @param int $calendar_id The calendar post ID
                 */
                do_action( 'codobookings_after_calendar_featured_image', $calendar_id );
                ?>
            <?php endif; ?>

            <?php if ( $settings['show_title'] === 'yes' ) : ?>
                <?php
                /**
                 * Action: Fires before the calendar title
                 * 
                 * @param int $calendar_id The calendar post ID
                 */
                do_action( 'codobookings_before_calendar_title', $calendar_id );
                ?>
                <h2 class="codo-calendar-title">
                    <?php 
                    /**
                     * Filter: Modify calendar title
                     * 
                     * @param string $title The calendar title
                     * @param int $calendar_id The calendar post ID
                     */
                    $calendar_title = apply_filters( 
                        'codobookings_calendar_title', 
                        get_the_title( $calendar_id ),
                        $calendar_id 
                    );
                    echo esc_html( $calendar_title ); 
                    ?>
                </h2>
                <?php
                /**
                 * Action: Fires after the calendar title
                 * 
                 * @param int $calendar_id The calendar post ID
                 */
                do_action( 'codobookings_after_calendar_title', $calendar_id );
                ?>
            <?php endif; ?>

            <?php 
            $desc = trim( get_post_field( 'post_content', $calendar_id ) );
            if ( ! empty( $desc ) ) : 
                /**
                 * Action: Fires before the calendar description
                 * 
                 * @param int $calendar_id The calendar post ID
                 */
                do_action( 'codobookings_before_calendar_description', $calendar_id );
                ?>
                <p class="codo-calendar-description">
                    <?php 
                    /**
                     * Filter: Modify calendar description
                     * 
                     * @param string $description The calendar description
                     * @param int $calendar_id The calendar post ID
                     */
                    $calendar_description = apply_filters( 
                        'codobookings_calendar_description', 
                        $desc,
                        $calendar_id 
                    );
                    echo wp_kses_post( $calendar_description ); 
                    ?>
                </p>
                <?php
                /**
                 * Action: Fires after the calendar description
                 * 
                 * @param int $calendar_id The calendar post ID
                 */
                do_action( 'codobookings_after_calendar_description', $calendar_id );
                ?>
            <?php endif; ?>

            <?php 
            /**
             * Action: Fires before calendar output (main hook for extensions)
             * Allows extension plugins to inject User Fields BEFORE the calendar.
             * 
             * @param int $calendar_id The calendar post ID
             * @param WP_Post $calendar_post The calendar post object
             */
            do_action( 'codobookings_before_calendar', $calendar_id, $calendar_post );
            ?>

            <?php
            /**
             * Filter: Modify calendar wrapper classes
             * 
             * @param array $classes Array of CSS classes
             * @param int $calendar_id The calendar post ID
             */
            $wrapper_classes = apply_filters( 
                'codobookings_calendar_wrapper_classes', 
                array( 'codo-calendar-wrapper' ),
                $calendar_id 
            );
            ?>
            <div id="<?php echo esc_attr($unique_id); ?>" 
                class="<?php echo esc_attr( implode( ' ', $wrapper_classes ) ); ?>" 
                data-calendar-id="<?php echo esc_attr($calendar_id); ?>">
                <?php
                /**
                 * Action: Fires at the start of calendar wrapper
                 * 
                 * @param int $calendar_id The calendar post ID
                 */
                do_action( 'codobookings_calendar_wrapper_start', $calendar_id );
                ?>
                <div class="codo-calendar-loading">
                    <?php 
                    /**
                     * Filter: Modify loading message
                     * 
                     * @param string $message The loading message
                     * @param int $calendar_id The calendar post ID
                     */
                    $loading_message = apply_filters( 
                        'codobookings_calendar_loading_message', 
                        __('Loading booking calendar...', 'codobookings'),
                        $calendar_id 
                    );
                    echo esc_html( $loading_message ); 
                    ?>
                </div>
                <?php
                /**
                 * Action: Fires at the end of calendar wrapper
                 * 
                 * @param int $calendar_id The calendar post ID
                 */
                do_action( 'codobookings_calendar_wrapper_end', $calendar_id );
                ?>
            </div>

            <?php 
            /**
             * Action: Fires after calendar output (main hook for extensions)
             * Allows extension plugins to inject User Fields AFTER the calendar.
             * 
             * @param int $calendar_id The calendar post ID
             * @param WP_Post $calendar_post The calendar post object
             */
            do_action( 'codobookings_after_calendar', $calendar_id, $calendar_post );
            ?>

            <?php
            /**
             * Action: Fires at the end of single calendar content
             * 
             * @param int $calendar_id The calendar post ID
             * @param WP_Post $calendar_post The calendar post object
             */
            do_action( 'codobookings_single_calendar_end', $calendar_id, $calendar_post );
            ?>
        </div>

        <?php 
        /**
         * Action: Fires at the end of calendar container
         * 
         * @param int $calendar_id The calendar post ID
         * @param WP_Post $calendar_post The calendar post object
         */
        do_action( 'codobookings_calendar_container_end', $calendar_id, $calendar_post );
        ?>
    </div>

    <?php
    /**
     * Action: Fires after the calendar container
     * 
     * @param int $calendar_id The calendar post ID
     * @param WP_Post $calendar_post The calendar post object
     */
    do_action( 'codobookings_after_calendar_container', $calendar_id, $calendar_post );
    ?>

    <?php
    return ob_get_clean();
}