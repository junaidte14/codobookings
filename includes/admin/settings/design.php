<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * CodoBookings Free Version - Basic Design Settings
 * Extensible foundation for premium design extensions
 */

/**
 * Register Design Settings
 */
add_action( 'codobookings_register_settings', 'codobookings_register_design_settings' );
function codobookings_register_design_settings() {
    $design_fields = codobookings_get_design_fields();
    
    foreach ( $design_fields as $field_id => $field ) {
        register_setting( 'codobookings_options', $field_id, [
            'type'              => $field['type'] ?? 'string',
            'sanitize_callback' => $field['sanitize'] ?? 'sanitize_text_field',
            'default'           => $field['default'] ?? '',
        ]);
    }
}

/**
 * Add Design tab to settings
 */
add_filter( 'codobookings_settings_tabs', 'codobookings_add_design_tab' );
function codobookings_add_design_tab( $tabs ) {
    $tabs['design'] = [
        'label'    => __( 'Design', 'codobookings' ),
        'callback' => 'codobookings_render_design_settings',
    ];
    return $tabs;
}

/**
 * Get all design fields configuration
 * Extensible via filters for premium extensions
 */
function codobookings_get_design_fields() {
    $fields = [
        // Colors - Basic
        'codobookings_primary_color' => [
            'type'      => 'string',
            'sanitize'  => 'sanitize_hex_color',
            'default'   => '',
            'label'     => __( 'Primary Color', 'codobookings' ),
            'desc'      => __( 'Main brand color for buttons and accents (leave empty to inherit from theme)', 'codobookings' ),
            'section'   => 'colors',
            'field_type' => 'color',
        ],
        'codobookings_text_color' => [
            'type'      => 'string',
            'sanitize'  => 'sanitize_hex_color',
            'default'   => '',
            'label'     => __( 'Text Color', 'codobookings' ),
            'desc'      => __( 'Main text color (leave empty to inherit from theme)', 'codobookings' ),
            'section'   => 'colors',
            'field_type' => 'color',
        ],

        // Spacing & Layout - Basic
        'codobookings_border_radius' => [
            'type'      => 'integer',
            'sanitize'  => 'absint',
            'default'   => 8,
            'label'     => __( 'Border Radius', 'codobookings' ),
            'desc'      => __( 'Corner roundness for cards and elements in pixels', 'codobookings' ),
            'section'   => 'layout',
            'field_type' => 'number',
            'min'       => 0,
            'max'       => 30,
        ],

        // Advanced - Custom CSS
        'codobookings_custom_css' => [
            'type'      => 'string',
            'sanitize'  => 'codobookings_sanitize_css',
            'default'   => '',
            'label'     => __( 'Custom CSS', 'codobookings' ),
            'desc'      => __( 'Add custom CSS to override or extend styles', 'codobookings' ),
            'section'   => 'advanced',
            'field_type' => 'textarea',
        ],
    ];

    /**
     * Filter: Allow extensions to add more design fields
     * Premium extensions can add presets, typography, advanced colors, etc.
     */
    return apply_filters( 'codobookings_design_fields', $fields );
}

/**
 * Sanitize CSS input
 */
function codobookings_sanitize_css( $css ) {
    $css = wp_strip_all_tags( $css );
    $css = preg_replace( '/<script\b[^>]*>(.*?)<\/script>/is', '', $css );
    return $css;
}

/**
 * Render Design Settings Tab
 */
function codobookings_render_design_settings() {
    $fields = codobookings_get_design_fields();
    
    // Group fields by section
    $sections = [
        'colors'   => __( 'Colors', 'codobookings' ),
        'layout'   => __( 'Layout', 'codobookings' ),
        'advanced' => __( 'Advanced', 'codobookings' ),
    ];

    /**
     * Filter: Allow extensions to add more design sections
     */
    $sections = apply_filters( 'codobookings_design_sections', $sections );

    /**
     * Action: Before design settings form
     * Extensions can add preset selectors or upgrade notices here
     */
    do_action( 'codobookings_before_design_settings' );

    foreach ( $sections as $section_id => $section_label ) {
        /**
         * Action: Before each design section
         */
        do_action( "codobookings_before_design_section_{$section_id}" );
        
        ?>
        <h2><?php echo esc_html( $section_label ); ?></h2>
        <table class="form-table">
            <?php
            foreach ( $fields as $field_id => $field ) {
                if ( ( $field['section'] ?? '' ) === $section_id ) {
                    /**
                     * Action: Before individual field
                     */
                    do_action( "codobookings_before_design_field_{$field_id}" );
                    
                    codobookings_render_design_field( $field_id, $field );
                    
                    /**
                     * Action: After individual field
                     */
                    do_action( "codobookings_after_design_field_{$field_id}" );
                }
            }
            ?>
        </table>
        <?php
        
        /**
         * Action: After each design section
         */
        do_action( "codobookings_after_design_section_{$section_id}" );
    }

    /**
     * Action: After design settings form
     * Extensions can add reset buttons or additional controls
     */
    do_action( 'codobookings_after_design_settings' );
}

/**
 * Render individual design field
 */
function codobookings_render_design_field( $field_id, $field ) {
    $value = get_option( $field_id, $field['default'] ?? '' );
    
    /**
     * Filter: Allow extensions to modify field value before rendering
     */
    $value = apply_filters( "codobookings_design_field_value_{$field_id}", $value, $field );
    
    ?>
    <tr>
        <th scope="row">
            <label for="<?php echo esc_attr( $field_id ); ?>">
                <?php echo esc_html( $field['label'] ?? '' ); ?>
            </label>
        </th>
        <td>
            <?php
            /**
             * Action: Custom field renderer
             * Extensions can render custom field types
             */
            $rendered = false;
            ob_start();
            do_action( "codobookings_render_design_field_{$field['field_type']}", $field_id, $field, $value );
            $custom_output = ob_get_clean();
            
            if ( ! empty( trim( $custom_output ) ) ) {
                echo $custom_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                $rendered = true;
            }
            
            // Default field rendering if no custom renderer
            if ( ! $rendered ) {
                switch ( $field['field_type'] ?? 'text' ) {
                    case 'color':
                        ?>
                        <input type="text" 
                               id="<?php echo esc_attr( $field_id ); ?>" 
                               name="<?php echo esc_attr( $field_id ); ?>" 
                               value="<?php echo esc_attr( $value ); ?>" 
                               class="codobookings-color-picker" 
                               placeholder="#0073aa" />
                        <?php
                        break;

                    case 'number':
                        ?>
                        <input type="number" 
                               id="<?php echo esc_attr( $field_id ); ?>" 
                               name="<?php echo esc_attr( $field_id ); ?>" 
                               value="<?php echo esc_attr( $value ); ?>" 
                               min="<?php echo esc_attr( $field['min'] ?? 0 ); ?>"
                               max="<?php echo esc_attr( $field['max'] ?? 999 ); ?>"
                               class="small-text" />
                        <?php
                        break;

                    case 'select':
                        ?>
                        <select id="<?php echo esc_attr( $field_id ); ?>" 
                                name="<?php echo esc_attr( $field_id ); ?>">
                            <?php foreach ( $field['options'] ?? [] as $opt_value => $opt_label ) : ?>
                                <option value="<?php echo esc_attr( $opt_value ); ?>" 
                                        <?php selected( $value, $opt_value ); ?>>
                                    <?php echo esc_html( $opt_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                        break;

                    case 'checkbox':
                        ?>
                        <label>
                            <input type="checkbox" 
                                   id="<?php echo esc_attr( $field_id ); ?>" 
                                   name="<?php echo esc_attr( $field_id ); ?>" 
                                   value="yes" 
                                   <?php checked( $value, 'yes' ); ?> />
                            <?php echo esc_html( $field['desc'] ?? '' ); ?>
                        </label>
                        <?php
                        $field['desc'] = '';
                        break;

                    case 'textarea':
                        ?>
                        <textarea id="<?php echo esc_attr( $field_id ); ?>" 
                                  name="<?php echo esc_attr( $field_id ); ?>" 
                                  rows="10" 
                                  cols="50" 
                                  class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
                        <?php
                        break;

                    default:
                        ?>
                        <input type="text" 
                               id="<?php echo esc_attr( $field_id ); ?>" 
                               name="<?php echo esc_attr( $field_id ); ?>" 
                               value="<?php echo esc_attr( $value ); ?>" 
                               class="regular-text" />
                        <?php
                        break;
                }
            }

            if ( ! empty( $field['desc'] ) ) {
                echo '<p class="description">' . esc_html( $field['desc'] ) . '</p>';
            }
            ?>
        </td>
    </tr>
    <?php
}

/**
 * Enqueue color picker for design settings (free version - basic)
 */
add_action( 'admin_enqueue_scripts', 'codobookings_enqueue_design_settings_assets' );
function codobookings_enqueue_design_settings_assets( $hook ) {
    if ( $hook !== 'codobookings_page_codobookings_settings' ) {
        return;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
    
    if ( $active_tab !== 'design' ) {
        return;
    }

    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );

    wp_add_inline_script( 'wp-color-picker', "
        jQuery(document).ready(function($) {
            $('.codobookings-color-picker').wpColorPicker({
                change: function(event, ui) {
                    $(event.target).addClass('user-modified');
                }
            });
        });
    " );
    
    /**
     * Action: Enqueue additional design assets
     * Extensions can enqueue their own scripts/styles
     */
    do_action( 'codobookings_enqueue_design_assets', $hook, $active_tab );
}

/**
 * CodoBookings Free Version - CSS Generation
 * Theme-first approach with basic customization
 */

/**
 * Generate and enqueue dynamic CSS based on design settings
 */
add_action( 'wp_enqueue_scripts', 'codobookings_enqueue_dynamic_css', 20 );
function codobookings_enqueue_dynamic_css() {
    if ( ! codobookings_is_calendar_page() ) {
        return;
    }

    $css = codobookings_generate_dynamic_css();
    
    if ( ! empty( $css ) ) {
        wp_add_inline_style( 'codobookings-frontend', $css );
        wp_add_inline_style( 'codobookings-calendars-grid', $css );
    }
}

/**
 * Check if current page has a calendar
 */
function codobookings_is_calendar_page() {
    global $post;
    
    if ( ! is_a( $post, 'WP_Post' ) ) {
        return false;
    }

    if ( has_shortcode( $post->post_content, 'codo_calendar' ) || 
         has_shortcode( $post->post_content, 'codo_calendars_grid' ) ) {
        return true;
    }

    /**
     * Filter: Allow extensions to determine calendar page
     */
    return apply_filters( 'codobookings_is_calendar_page', false, $post );
}

/**
 * Get theme colors if available
 */
function codobookings_get_theme_colors() {
    $theme_colors = array();

    // Try to get theme.json colors (WordPress 5.8+)
    if ( function_exists( 'wp_get_global_settings' ) ) {
        $global_settings = wp_get_global_settings();
        if ( isset( $global_settings['color']['palette']['theme'] ) ) {
            foreach ( $global_settings['color']['palette']['theme'] as $color ) {
                if ( isset( $color['slug'] ) && isset( $color['color'] ) ) {
                    $theme_colors[ $color['slug'] ] = $color['color'];
                }
            }
        }
    }

    // Fallback: Try to get theme mod colors
    $theme_mods = array(
        'primary'   => get_theme_mod( 'primary_color' ),
        'secondary' => get_theme_mod( 'secondary_color' ),
        'text'      => get_theme_mod( 'text_color' ),
        'heading'   => get_theme_mod( 'heading_color' ),
    );

    foreach ( $theme_mods as $key => $value ) {
        if ( ! empty( $value ) && ! isset( $theme_colors[ $key ] ) ) {
            $theme_colors[ $key ] = $value;
        }
    }

    /**
     * Filter: Allow themes/extensions to provide colors
     */
    return apply_filters( 'codobookings_theme_colors', $theme_colors );
}

/**
 * Generate CSS from design settings
 */
function codobookings_generate_dynamic_css() {
    $css_vars = codobookings_get_css_variables();
    
    /**
     * Filter: Allow extensions to modify CSS variables
     */
    $css_vars = apply_filters( 'codobookings_css_variables', $css_vars );

    // Build CSS
    $css = ':root {' . "\n";
    foreach ( $css_vars as $var => $value ) {
        $css .= sprintf( '  --codobookings-%s: %s;' . "\n", esc_attr( $var ), esc_attr( $value ) );
    }
    $css .= '}' . "\n\n";

    // Core styles that use the variables
    $css .= codobookings_get_core_variable_styles();

    // Custom CSS
    $custom_css = get_option( 'codobookings_custom_css', '' );
    if ( ! empty( $custom_css ) ) {
        $css .= "\n/* Custom CSS */\n" . wp_strip_all_tags( $custom_css ) . "\n";
    }

    /**
     * Filter: Allow extensions to modify final CSS
     */
    return apply_filters( 'codobookings_dynamic_css', $css );
}

/**
 * Get CSS variables from settings
 */
function codobookings_get_css_variables() {
    $vars = [];
    $theme_colors = codobookings_get_theme_colors();

    // Primary Color
    $primary_color = get_option( 'codobookings_primary_color', '' );
    if ( ! empty( $primary_color ) ) {
        $vars['primary-color'] = $primary_color;
        // Auto-generate secondary (darker) if not set by extension
        if ( ! isset( $vars['secondary-color'] ) ) {
            $vars['secondary-color'] = codobookings_adjust_color_brightness( $primary_color, -20 );
        }
    } else {
        // Use theme color or default
        $vars['primary-color'] = $theme_colors['primary'] ?? '#0073aa';
        $vars['secondary-color'] = codobookings_adjust_color_brightness( $vars['primary-color'], -20 );
    }

    // Text Color
    $text_color = get_option( 'codobookings_text_color', '' );
    if ( ! empty( $text_color ) ) {
        $vars['text-color'] = $text_color;
    } else {
        $vars['text-color'] = $theme_colors['text'] ?? '#333333';
    }

    // Heading Color (auto-darken text color if not set)
    if ( ! isset( $vars['heading-color'] ) ) {
        $vars['heading-color'] = codobookings_adjust_color_brightness( $vars['text-color'], -30 );
    }

    // Border Radius
    $border_radius = get_option( 'codobookings_border_radius', 8 );
    $vars['border-radius'] = $border_radius . 'px';
    
    // Button border radius (slightly less rounded)
    $vars['button-border-radius'] = max( 0, $border_radius - 4 ) . 'px';

    // Default values for extensibility
    $defaults = [
        'border-color'         => '#e3e3e3',
        'background-color'     => '#ffffff',
        'button-text-color'    => '#ffffff',
        'card-spacing'         => '25px',
        'card-padding'         => '10px',
        'box-shadow'           => '0 2px 8px rgba(0,0,0,0.05)',
        'box-shadow-hover'     => '0 6px 16px rgba(0,0,0,0.08)',
        'transition'           => 'all 0.25s ease',
        'font-family'          => 'inherit',
        'base-font-size'       => '14px',
        'heading-font-size'    => '18px',
    ];

    // Merge defaults with vars (extensions can override)
    foreach ( $defaults as $key => $value ) {
        if ( ! isset( $vars[ $key ] ) ) {
            $vars[ $key ] = $value;
        }
    }

    return $vars;
}

/**
 * Adjust color brightness
 */
function codobookings_adjust_color_brightness( $hex, $steps ) {
    $hex = str_replace( '#', '', $hex );

    if ( strlen( $hex ) === 3 ) {
        $hex = str_repeat( substr( $hex, 0, 1 ), 2 ) . 
               str_repeat( substr( $hex, 1, 1 ), 2 ) . 
               str_repeat( substr( $hex, 2, 1 ), 2 );
    }

    $r = hexdec( substr( $hex, 0, 2 ) );
    $g = hexdec( substr( $hex, 2, 2 ) );
    $b = hexdec( substr( $hex, 4, 2 ) );

    $r = max( 0, min( 255, $r + $steps ) );
    $g = max( 0, min( 255, $g + $steps ) );
    $b = max( 0, min( 255, $b + $steps ) );

    return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) .
                 str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) .
                 str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
}

/**
 * Get core styles that use CSS variables
 */
function codobookings_get_core_variable_styles() {
    ob_start();
    ?>

/* CodoBookings Core Styles - Variable-Based */

/* Calendar Grid */
.codo-calendars-grid {
    gap: var(--codobookings-card-spacing, 25px);
}

.codo-calendar-item {
    background: var(--codobookings-background-color, #fff);
    border: 1px solid var(--codobookings-border-color, #e3e3e3);
    border-radius: var(--codobookings-border-radius, 14px);
    box-shadow: var(--codobookings-box-shadow, 0 2px 8px rgba(0,0,0,0.05));
    padding: var(--codobookings-card-padding, 10px);
    transition: var(--codobookings-transition, all 0.25s ease);
}

.codo-calendar-item:hover {
    box-shadow: var(--codobookings-box-shadow-hover, 0 6px 16px rgba(0,0,0,0.08));
}

.codo-calendar-title {
    color: var(--codobookings-heading-color, #1a1a1a);
    font-size: var(--codobookings-heading-font-size, 18px);
    font-family: var(--codobookings-font-family, inherit);
}

.codo-calendar-desc,
.codo-calendar-description {
    color: var(--codobookings-text-color, #333333);
    font-size: var(--codobookings-base-font-size, 14px);
    font-family: var(--codobookings-font-family, inherit);
}

/* Buttons */
.codo-book-btn,
.codo-back-btn {
    background-color: var(--codobookings-primary-color, #0073aa);
    color: var(--codobookings-button-text-color, #ffffff);
    border-radius: var(--codobookings-button-border-radius, 4px);
    font-family: var(--codobookings-font-family, inherit);
    transition: var(--codobookings-transition, all 0.25s ease);
}

.codo-book-btn:hover,
.codo-back-btn:hover {
    background-color: var(--codobookings-secondary-color, #005177);
}

/* Single Calendar View */
.codo-single-calendar,
.codo-calendar-wrapper {
    background: var(--codobookings-background-color, #ffffff);
}

/* Sidebar */
.codo-calendar-sidebar {
    background: var(--codobookings-background-color, #fff);
    border: 1px solid var(--codobookings-border-color, #ddd);
    border-radius: var(--codobookings-border-radius, 8px);
}

.codo-sidebar-item {
    background: var(--codobookings-background-color, #fdfdfd);
    border: 1px solid var(--codobookings-border-color, #e0e0e0);
    border-radius: calc(var(--codobookings-border-radius, 8px) / 1.5);
}

.codo-sidebar-item.selected {
    border-color: var(--codobookings-primary-color, #0073aa);
}

.codo-sidebar-footer button {
    background: var(--codobookings-primary-color, #0073aa);
    color: var(--codobookings-button-text-color, #fff);
    border-radius: var(--codobookings-button-border-radius, 4px);
    transition: var(--codobookings-transition, all 0.25s ease);
}

.codo-sidebar-footer button:not(:disabled):hover {
    background: var(--codobookings-secondary-color, #005f8d);
}

/* Calendar Table Elements */
.codo-slot {
    background: var(--codobookings-primary-color, #0073aa);
    border-radius: calc(var(--codobookings-border-radius, 8px) / 2);
}

.codo-slot:hover {
    background: var(--codobookings-secondary-color, #005f8d);
}

.codo-onetime-calendar td.available {
    background: var(--codobookings-primary-color-light, #e6f4ff);
}

.codo-onetime-calendar td.available:hover,
.codo-onetime-calendar td.codo-active {
    background: var(--codobookings-primary-color-lighter, #cce6ff);
}

    <?php
    $css = ob_get_clean();
    
    /**
     * Filter: Allow extensions to modify core variable styles
     */
    return apply_filters( 'codobookings_core_variable_styles', $css );
}