<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add metabox for codo_calendar
 */
add_action( 'add_meta_boxes', function() {
    $post_type = 'codo_calendar';
    if ( post_type_exists( $post_type ) ) {
        add_meta_box(
            'codobookingsuf_calendar_user_fields',
            __( 'User Fields (Calendar)', 'codobookings' ),
            'codobookingsuf_render_calendar_metabox',
            $post_type,
            'normal',
            'default'
        );
    }
} );

/**
 * Render metabox
 */
function codobookingsuf_render_calendar_metabox( $post ) {
    if ( ! current_user_can( 'edit_post', $post->ID ) ) return;

    $meta = get_post_meta( $post->ID, '_codobookings_user_fields', true );
    if ( ! is_array( $meta ) ) {
        $meta = [
            'mode'          => 'none',
            'custom_fields' => wp_json_encode( codobookingsuf_get_default_fields() ),
            'position'      => 'before', // default
        ];
    }

    wp_nonce_field( 'codobookingsuf_calendar_metabox_nonce', 'codobookingsuf_calendar_nonce' );

    $mode        = isset( $meta['mode'] ) ? $meta['mode'] : 'none';
    $position    = isset( $meta['position'] ) ? $meta['position'] : 'before'; // fallback
    $custom_json = isset( $meta['custom_fields'] ) ? $meta['custom_fields'] : wp_json_encode( codobookingsuf_get_default_fields() );
    $custom_arr  = json_decode( $custom_json, true );
    if ( ! is_array( $custom_arr ) ) $custom_arr = [];
    ?>

    <!-- FIELD MODE -->
    <p>
        <label><input type="radio" name="codobookingsuf_fields_mode" value="none" <?php checked( $mode, 'none' ); ?>> <?php esc_html_e( 'No User Fields', 'codobookings' ); ?></label><br>
        <label><input type="radio" name="codobookingsuf_fields_mode" value="global" <?php checked( $mode, 'global' ); ?>> <?php esc_html_e( 'Use Global User Fields (from plugin settings)', 'codobookings' ); ?></label><br>
        <label><input type="radio" name="codobookingsuf_fields_mode" value="custom" <?php checked( $mode, 'custom' ); ?>> <?php esc_html_e( 'Custom for this calendar', 'codobookings' ); ?></label>
    </p>

    <!-- NEW POSITION SETTING -->
    <p>
        <strong><?php esc_html_e( 'Show User Fields', 'codobookings' ); ?>:</strong><br>
        <label><input type="radio" name="codobookingsuf_fields_position" value="before" <?php checked( $position, 'before' ); ?>> <?php esc_html_e( 'Before the Calendar', 'codobookings' ); ?></label><br>
        <label><input type="radio" name="codobookingsuf_fields_position" value="after" <?php checked( $position, 'after' ); ?>> <?php esc_html_e( 'After the Calendar', 'codobookings' ); ?></label>
    </p>

    <div id="codobookingsuf-calendar-custom-editor" class="<?php echo ( $mode === 'custom' ) ? 'open' : 'collapsed'; ?>">
        <p class="description"><?php esc_html_e( 'Define custom fields for this calendar. These override the global fields for bookings attached to this calendar.', 'codobookings' ); ?></p>

        <div id="codobookingsuf-calendar-fields-wrapper" class="codobookingsuf-editor" data-target-input="codobookingsuf_calendar_custom_fields">
            <ul id="codobookingsuf-calendar-fields-list" class="codobookingsuf-sortable codobookingsuf-fields-list">
                <?php foreach ( $custom_arr as $cidx => $f ) : ?>
                    <?php echo codobookingsuf_admin_render_field_li( $f, $cidx ); ?>
                <?php endforeach; ?>
            </ul>

            <div class="codobookingsuf-editor-actions">
                <button type="button" class="button" id="codobookingsuf-calendar-add-field"><?php esc_html_e( 'Add Field', 'codobookings' ); ?></button>
            </div>

            <input type="hidden" name="codobookingsuf_calendar_custom_fields" id="codobookingsuf_calendar_custom_fields" value="<?php echo esc_attr( wp_json_encode( $custom_arr ) ); ?>">
        </div>
    </div>
    <?php
}

/**
 * Save metabox
 */
add_action( 'save_post', function( $post_id, $post ) {
    if ( $post->post_type !== 'codo_calendar' ) return;

    if ( empty( $_POST['codobookingsuf_calendar_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['codobookingsuf_calendar_nonce'] ), 'codobookingsuf_calendar_metabox_nonce' ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // MODE
    $mode = isset( $_POST['codobookingsuf_fields_mode'] ) ? sanitize_key( wp_unslash( $_POST['codobookingsuf_fields_mode'] ) ) : 'global';
    if ( ! in_array( $mode, [ 'global', 'none', 'custom' ], true ) ) $mode = 'global';

    // NEW POSITION SETTING
    $position = isset( $_POST['codobookingsuf_fields_position'] ) ? sanitize_key( wp_unslash( $_POST['codobookingsuf_fields_position'] ) ) : 'before';
    if ( ! in_array( $position, [ 'before', 'after' ], true ) ) {
        $position = 'before';
    }

    // CUSTOM FIELDS
    $custom_raw = isset( $_POST['codobookingsuf_calendar_custom_fields'] ) ? wp_unslash( $_POST['codobookingsuf_calendar_custom_fields'] ) : '';
    $custom_arr = json_decode( $custom_raw, true );
    if ( ! is_array( $custom_arr ) ) $custom_arr = [];

    $clean_json = codobookingsuf_sanitize_fields_array( $custom_arr );
    $clean_arr  = json_decode( $clean_json, true );

    // SAVE ALL
    $meta = [
        'mode'          => $mode,
        'custom_fields' => wp_json_encode( $clean_arr ),
        'position'      => $position,
    ];

    update_post_meta( $post_id, '_codobookings_user_fields', $meta );
}, 10, 2 );
