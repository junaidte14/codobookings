<?php
/**
* Plugin Name: CodoBookings
* Plugin URI: https://wpdemo.codoplex.com/codobookings/
* Description: A Lightweight WordPress Booking & Appointment Plugin.
* Version: 1.3.0
* Author: CODOPLEX
* Author URI: https://codoplex.com/
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: codobookings
* Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// Constants
define( 'CODOBOOKINGS_VERSION', '1.3.0' );
define( 'CODOBOOKINGS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CODOBOOKINGS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Admin Menu
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/admin/menu.php';

// Calendar and Bookings
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/core/post-types.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/core/metaboxes.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/admin/list-tables.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/admin/manage-bookings.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/core/bookings-post-type.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/core/ajax-handlers.php';

// Settings
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/admin/settings/general.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/admin/settings/emails.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/admin/settings/design.php';

// Dashboard
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/admin/dashboard.php';

// Shortcodes
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/shortcodes/single-calendar.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/shortcodes/calendars-grid.php';
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/core/page-with-shortcode.php';

// Emails
require_once CODOBOOKINGS_PLUGIN_DIR . 'includes/emails/basic-emails.php';

register_activation_hook( __FILE__, 'codobookings_activate' );
function codobookings_activate() {
    // Register post types before creating pages
    codobookings_register_post_types();
    // Create or ensure Calendar page exists
    codobookings_create_calendar_page();
    do_action( 'codobookings_activate' );
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'codobookings_deactivate' );
function codobookings_deactivate() {
    flush_rewrite_rules();
}

// Load textdomain