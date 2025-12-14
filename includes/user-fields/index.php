<?php
/**
 * User Fields - Feature
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/user-fields/common.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/user-fields/settings.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/user-fields/metabox.php';

/* --------------------------
 * Admin asset enqueue
 * -------------------------- */
add_action( 'admin_enqueue_scripts', function( $hook ) {
    // Load assets only on relevant screens: codobookings settings page & codo_calendar edit screens
    $load = false;

    // Settings page: settings_page_codobookings_settings
    if ( $hook === 'codobookings_page_codobookings_settings' ) {
        $load = true;
    }

    // Post edit screens for codo_calendar post type
    if ( in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
        $screen = get_current_screen();
        if ( $screen && $screen->post_type === 'codo_calendar' ) {
            $load = true;
        }
    }

    if ( ! $load ) return;

    // Enqueue jQuery UI sortable (bundled)
    wp_enqueue_script( 'jquery-ui-sortable' );

    // Enqueue our admin assets
    wp_register_script(
        'codobookingsuf-fields-editor',
        CODOBOOKINGS_PLUGIN_URL . 'includes/user-fields/assets/js/fields-editor.js',
        [ 'jquery', 'jquery-ui-sortable' ],
        CODOBOOKINGS_VERSION,
        true
    );

    wp_localize_script(
        'codobookingsuf-fields-editor',
        'codobookingsufEditor',
        [
            'i18n' => [
                'untitled'      => __( 'Untitled', 'codobookings' ),
                'remove_confirm'=> __( 'Remove this field?', 'codobookings' ),
            ],
            'nonce' => wp_create_nonce( 'codobookingsuf_admin_nonce' ),
        ]
    );

    wp_enqueue_script( 'codobookingsuf-fields-editor' );

    wp_enqueue_style(
        'codobookingsuf-fields-editor',
        CODOBOOKINGS_PLUGIN_URL . 'includes/user-fields/assets/css/fields-editor.css',
        [],
        CODOBOOKINGS_VERSION
    );

    // Dashicons
    wp_enqueue_style( 'dashicons' );
}, 10, 1 );

/**
 * Frontend asset enqueue
 */
add_action( 'wp_enqueue_scripts', function() {
    // Only load on pages with the calendar shortcode/block
    if ( ! is_singular() && ! is_page() ) {
        return;
    }
    
    wp_enqueue_script(
        'codobookingsuf-frontend-integration',
        CODOBOOKINGS_PLUGIN_URL . 'includes/user-fields/assets/js/frontend-integration.js',
        [ 'jquery' ],
        CODOBOOKINGS_VERSION,
        true
    );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'codobookingsuf-frontend',
        CODOBOOKINGS_PLUGIN_URL . 'includes/user-fields/assets/css/frontend.css',
        [],
        CODOBOOKINGS_VERSION
    );
} );