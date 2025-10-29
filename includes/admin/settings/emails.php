<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register Email Notification Settings
 */
add_action( 'admin_init', 'codobookings_register_email_settings' );
function codobookings_register_email_settings() {
    register_setting( 'codobookings_options', 'codobookings_send_admin_email', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'yes',
    ]);

    register_setting( 'codobookings_options', 'codobookings_send_user_email', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'yes',
    ]);
}

/**
 * Add the new “Emails” tab
 */
add_filter( 'codobookings_settings_tabs', function( $tabs ) {
    $tabs['emails'] = [
        'label'    => __( 'Emails', 'codobookings' ),
        'callback' => 'codobookings_render_email_settings',
    ];
    return $tabs;
});

/**
 * Render the Email Settings tab
 */
function codobookings_render_email_settings() {
    ?>
    <table class="form-table">
        <tr>
            <th><?php _e( 'Send admin email notifications', 'codobookings' ); ?></th>
            <td>
                <select name="codobookings_send_admin_email">
                    <option value="yes" <?php selected( get_option('codobookings_send_admin_email', 'yes'), 'yes' ); ?>>
                        <?php _e( 'Yes', 'codobookings' ); ?>
                    </option>
                    <option value="no" <?php selected( get_option('codobookings_send_admin_email', 'yes'), 'no' ); ?>>
                        <?php _e( 'No', 'codobookings' ); ?>
                    </option>
                </select>
                <p class="description"><?php _e( 'If enabled, the site administrator will receive a booking email notification whenever a new booking is created or updated.', 'codobookings' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><?php _e( 'Send user email notifications', 'codobookings' ); ?></th>
            <td>
                <select name="codobookings_send_user_email">
                    <option value="yes" <?php selected( get_option('codobookings_send_user_email', 'yes'), 'yes' ); ?>>
                        <?php _e( 'Yes', 'codobookings' ); ?>
                    </option>
                    <option value="no" <?php selected( get_option('codobookings_send_user_email', 'yes'), 'no' ); ?>>
                        <?php _e( 'No', 'codobookings' ); ?>
                    </option>
                </select>
                <p class="description"><?php _e( 'If enabled, users will receive an email confirmation after creating a booking and upon any status updates.', 'codobookings' ); ?></p>
            </td>
        </tr>
    </table>
    <?php
}
