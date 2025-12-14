<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register setting and settings tab
 */
add_action( 'admin_init', function() {
    $option_name = codobookingsuf_get_option_name();

    register_setting( 'codobookings_options', $option_name, [
        'type'              => 'string',
        'sanitize_callback' => 'codobookingsuf_sanitize_fields_array',
        'default'           => wp_json_encode( codobookingsuf_get_default_fields() ),
    ] );

    add_filter( 'codobookings_settings_tabs', function( $tabs ) {
        $tabs['user_fields'] = [
            'label'    => __( 'User Fields', 'codobookings' ),
            'callback' => 'codobookingsuf_render_settings_tab',
        ];
        return $tabs;
    } );
} );

/**
 * Render the settings tab UI
 */
function codobookingsuf_render_settings_tab() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $option_name = codobookingsuf_get_option_name();
    $stored      = get_option( $option_name, wp_json_encode( codobookingsuf_get_default_fields() ) );
    $fields      = json_decode( $stored, true );
    if ( ! is_array( $fields ) ) $fields = [];

    // Render HTML but NO inline styles/scripts — classes only. JS/CSS provided by admin assets.
    ?>
    <h2><?php esc_html_e( 'User Fields', 'codobookings' ); ?></h2>
    <p class="description"><?php esc_html_e( 'Define custom user fields used by CodoBookings. Drag to reorder. Names are auto-generated from the label but can be edited.', 'codobookings' ); ?></p>

    <table class="form-table">
        <tr>
            <th><?php esc_html_e( 'Fields editor', 'codobookings' ); ?></th>
            <td>
                <div id="codobookingsuf-fields-editor" class="codobookingsuf-editor" data-target-input="<?php echo esc_attr( codobookingsuf_get_option_name() ); ?>">

                    <ul id="codobookingsuf-fields-list" class="codobookingsuf-sortable codobookingsuf-fields-list">
                        <?php foreach ( $fields as $index => $f ) : ?>
                            <?php echo codobookingsuf_admin_render_field_li( $f, $index ); ?>
                        <?php endforeach; ?>
                    </ul>

                    <div class="codobookingsuf-editor-actions">
                        <button type="button" id="codobookingsuf-add-field" class="button"><?php esc_html_e( 'Add Field', 'codobookings' ); ?></button>
                        <span class="description"><?php esc_html_e( 'Use the editor to add, edit, reorder or remove fields.', 'codobookings' ); ?></span>
                    </div>

                    <input type="hidden" id="codobookingsuf-fields-json" name="<?php echo esc_attr( codobookingsuf_get_option_name() ); ?>" value="<?php echo esc_attr( wp_json_encode( $fields ) ); ?>">
                </div>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Helper: render one field LI for admin lists (shared between settings and metabox)
 */
function codobookingsuf_admin_render_field_li( $f, $index = 0, $open = false ) {
    // ensure field has defaults
    $field = wp_parse_args( (array) $f, codobookingsuf_user_field_default_item() );

    // collapsed by default unless $open true
    $settings_class = $open ? 'codobookingsuf-field-settings open' : 'codobookingsuf-field-settings collapsed';

    // available types (no 'file')
    $types = [ 'text' => __( 'Text', 'codobookings' ), 'number' => __( 'Number', 'codobookings' ), 'textarea' => __( 'Textarea', 'codobookings' ), 'select' => __( 'Select', 'codobookings' ), 'radio' => __( 'Radio', 'codobookings' ), 'checkbox' => __( 'Checkbox', 'codobookings' ) ];

    ob_start();
    ?>
    <li class="codobookingsuf-field-item" data-index="<?php echo esc_attr( $index ); ?>">
        <div class="codobookingsuf-field-summary">
            <span class="dashicons dashicons-menu"></span>
            <strong class="codobookingsuf-field-preview"><?php echo esc_html( $field['label'] ?: __( 'Untitled', 'codobookings' ) ); ?></strong>
            <small class="codobookingsuf-field-type-label">[<?php echo esc_html( $field['type'] ); ?>]</small>
            <a href="#" class="codobookingsuf-toggle-edit"><?php esc_html_e( 'Edit', 'codobookings' ); ?></a>
            <a href="#" class="codobookingsuf-remove-field" aria-label="<?php esc_attr_e( 'Remove field', 'codobookings' ); ?>"><?php esc_html_e( 'Remove', 'codobookings' ); ?></a>
        </div>

        <div class="<?php echo esc_attr( $settings_class ); ?>">
            <table class="form-table codobookingsuf-field-table">
                <tr>
                    <td class="codobookingsuf-col-label">
                        <label><?php esc_html_e( 'Label', 'codobookings' ); ?></label><br>
                        <input type="text" class="codobookingsuf-field-label regular-text" value="<?php echo esc_attr( $field['label'] ); ?>">
                    </td>
                    <td class="codobookingsuf-col-name">
                        <label><?php esc_html_e( 'Name (unique)', 'codobookings' ); ?></label><br>
                        <input type="text" class="codobookingsuf-field-name regular-text" value="<?php echo esc_attr( $field['name'] ); ?>">
                        <p class="description"><?php esc_html_e( 'Only lowercase letters, numbers and underscores. Auto-generated from label.', 'codobookings' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <td>
                        <label><?php esc_html_e( 'Type', 'codobookings' ); ?></label><br>
                        <select class="codobookingsuf-field-type">
                            <?php foreach ( $types as $key => $label ) : ?>
                                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $field['type'], $key ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <label><?php esc_html_e( 'Required', 'codobookings' ); ?></label><br>
                        <label><input type="checkbox" class="codobookingsuf-field-required" <?php checked( $field['required'], true ); ?>> <?php esc_html_e( 'Yes', 'codobookings' ); ?></label>
                    </td>
                </tr>

                <tr class="codobookingsuf-options-row" <?php if ( ! in_array( $field['type'], [ 'select', 'radio' ], true ) ) echo 'style="display:none;"'; ?>>
                    <td colspan="2">
                        <label><?php esc_html_e( 'Options (comma or newline separated)', 'codobookings' ); ?></label><br>
                        <textarea class="codobookingsuf-field-options" rows="3"><?php echo esc_textarea( str_replace( ',', "\n", $field['options'] ) ); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <label><?php esc_html_e( 'Hint / placeholder', 'codobookings' ); ?></label><br>
                        <input type="text" class="codobookingsuf-field-hint regular-text" value="<?php echo esc_attr( $field['hint'] ); ?>">
                    </td>
                </tr>
            </table>
        </div>
    </li>
    <?php
    return ob_get_clean();
}
