<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'add_meta_boxes', 'codobookings_add_calendar_meta' );
function codobookings_add_calendar_meta() {
    add_meta_box(
        'codo_calendar_settings',
        __( 'Calendar Settings', 'codobookings' ),
        'codobookings_calendar_settings_cb',
        'codo_calendar',
        'normal',
        'high'
    );
}

function codobookings_calendar_settings_cb( $post ) {
    wp_nonce_field( 'codobookings_save_calendar', 'codobookings_calendar_nonce' );
    $days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
    $slots = get_post_meta( $post->ID, '_codo_weekly_slots', true ) ?: array_fill_keys($days, array());
    //var_dump($slots);
    $recurrence = get_post_meta( $post->ID, '_codo_recurrence', true ) ?: 'none';
    ?>

    <h4><?php esc_html_e( 'Weekly Availability', 'codobookings' ); ?></h4>
    <p>
        <button type="button" class="button" id="fill_standard_hours"><?php esc_html_e( 'Fill Standard 9–5 (Mon–Fri)', 'codobookings' ); ?></button>
        <button type="button" class="button" id="copy_monday"><?php esc_html_e( 'Copy Monday → All Days', 'codobookings' ); ?></button>
        <button type="button" id="export-json" class="button"><?php esc_html_e( 'Export JSON', 'codobookings' ); ?></button>
        <button type="button" id="import-json" class="button"><?php esc_html_e( 'Import JSON', 'codobookings' ); ?></button>
        <input type="file" id="import-file" accept="application/json" style="display:none;">
    </p>
    
    <div id="weekly-slots">
        <?php foreach ( $days as $day ) : ?>
            <div class="codo-day-section" data-day="<?php echo esc_attr( $day ); ?>">
                <h4 style="margin-bottom:5px;"><?php echo esc_html( ucfirst( $day ) ); ?></h4>
                <div class="codo-slots-wrap">
                    <?php if ( ! empty( $slots[$day] ) ) :
                        foreach ( $slots[$day] as $i => $slot ) : ?>
                            <div class="codo-slot">
                                <label><?php esc_html_e( 'Start', 'codobookings' ); ?></label>
                                <input 
                                    type="time" 
                                    name="codo_weekly_slots[<?php echo esc_attr( $day ); ?>][<?php echo esc_attr( $i ); ?>][start]" 
                                    value="<?php echo esc_attr( $slot['start'] ?? '' ); ?>" 
                                />
                                <label><?php esc_html_e( 'End', 'codobookings' ); ?></label>
                                <input 
                                    type="time" 
                                    name="codo_weekly_slots[<?php echo esc_attr( $day ); ?>][<?php echo esc_attr( $i ); ?>][end]" 
                                    value="<?php echo esc_attr( $slot['end'] ?? '' ); ?>" 
                                />
                                <button type="button" class="button remove-slot">×</button>
                            </div>
                        <?php endforeach;
                    endif; ?>
                </div>
                <button type="button" class="button add-slot"><?php esc_html_e( 'Add Slot', 'codobookings' ); ?></button>
            </div>
        <?php endforeach; ?>
    </div>

    <hr>

    <h4><?php esc_html_e( 'Calendar Type', 'codobookings' ); ?></h4>

    <?php
    // Default recurrence types with descriptions
    $recurrence_types = array(
        'none' => array(
            'label'       => __( 'One-time Booking', 'codobookings' ),
            'description' => __( 'A single event that occurs only once.', 'codobookings' ),
        ),
        'weekly' => array(
            'label'       => __( 'Weekly', 'codobookings' ),
            'description' => __( 'Repeats every week on the same day.', 'codobookings' ),
        ),
    );

    // Allow extensions to add more recurrence types
    $recurrence_types = apply_filters( 'codobookings_recurrence_types', $recurrence_types );
    ?>

    <div class="codo-recurrence-options">
        <?php foreach ( $recurrence_types as $key => $data ) : 
            $is_active = ( $recurrence === $key ) ? 'active' : '';
        ?>
            <div class="codo-recurrence-box <?php echo esc_attr( $is_active ); ?>" data-value="<?php echo esc_attr( $key ); ?>">
                <strong><?php echo esc_html( $data['label'] ); ?></strong>
                <p><?php echo esc_html( $data['description'] ); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Hidden input for saving selected value -->
    <input type="hidden" name="codo_recurrence" id="codo_recurrence" value="<?php echo esc_attr( $recurrence ); ?>" />

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const boxes = document.querySelectorAll('.codo-recurrence-box');
        const hiddenInput = document.getElementById('codo_recurrence');

        boxes.forEach(box => {
            box.addEventListener('click', () => {
                boxes.forEach(b => b.classList.remove('active'));
                box.classList.add('active');
                hiddenInput.value = box.dataset.value;
            });
        });
    });
    </script>

    <style>
    .codo-recurrence-options {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .codo-recurrence-box {
        flex: 1 1 45%;
        border: 2px solid #ccc;
        border-radius: 8px;
        padding: 12px 14px;
        cursor: pointer;
        background: #f9f9f9;
        transition: all 0.2s ease-in-out;
    }

    .codo-recurrence-box:hover {
        border-color: #2271b1;
        background: #f0f8ff;
    }

    .codo-recurrence-box.active {
        border-color: #2271b1;
        background: #e6f2ff;
        box-shadow: 0 0 4px rgba(34, 113, 177, 0.4);
    }

    .codo-recurrence-box strong {
        display: block;
        font-size: 14px;
        color: #1d2327;
        margin-bottom: 4px;
    }

    .codo-recurrence-box p {
        margin: 0;
        font-size: 12px;
        color: #555;
    }
    </style>


    <?php 
    // Allow extensions to add more fields
    do_action( 'codobookings_calendar_settings_after', $post );
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

        // Add Slot button
        document.querySelectorAll('.add-slot').forEach(btn => {
            btn.addEventListener('click', e => {
                const daySection = btn.closest('.codo-day-section');
                const day = daySection.dataset.day;
                const wrap = daySection.querySelector('.codo-slots-wrap');
                const index = wrap.children.length;

                const slotDiv = document.createElement('div');
                slotDiv.classList.add('codo-slot');
                slotDiv.innerHTML = `
                    <label>Start</label>
                    <input type="time" name="codo_weekly_slots[${day}][${index}][start]" value="" />
                    <label>End</label>
                    <input type="time" name="codo_weekly_slots[${day}][${index}][end]" value="" />
                    <button type="button" class="button remove-slot">×</button>
                `;
                wrap.appendChild(slotDiv);
            });
        });

        // Remove Slot
        document.addEventListener('click', function(e){
            if(e.target.classList.contains('remove-slot')){
                const slotDiv = e.target.closest('.codo-slot');
                slotDiv.remove();
                // Re-index names
                const daySection = e.target.closest('.codo-day-section');
                const day = daySection.dataset.day;
                daySection.querySelectorAll('.codo-slot').forEach((slot, i) => {
                    slot.querySelectorAll('input').forEach(input => {
                        const type = input.name.match(/\[(start|end)\]/)[1];
                        input.name = `codo_weekly_slots[${day}][${i}][${type}]`;
                    });
                });
            }
        });

        // Fill standard 9–5 slots for weekdays (Mon–Fri)
        document.getElementById('fill_standard_hours').addEventListener('click', function() {

            // with half-hour breaks, example:
            const standardSlots = [
                { start: '09:00', end: '10:00' },
                { start: '10:00', end: '11:00' },
                { start: '11:00', end: '12:00' },
                { start: '13:00', end: '14:00' },
                { start: '14:00', end: '15:00' },
                { start: '15:00', end: '16:00' },
                { start: '16:00', end: '17:00' },
            ];

            const weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
            weekdays.forEach(day => {
                const wrap = document.querySelector(`[data-day="${day}"] .codo-slots-wrap`);
                if (!wrap) return;

                wrap.innerHTML = ''; // clear previous slots
                standardSlots.forEach((slot, i) => {
                    const newSlot = document.createElement('div');
                    newSlot.classList.add('codo-slot');
                    newSlot.innerHTML = `
                        <label>Start</label>
                        <input type="time" name="codo_weekly_slots[${day}][${i}][start]" value="${slot.start}" />
                        <label>End</label>
                        <input type="time" name="codo_weekly_slots[${day}][${i}][end]" value="${slot.end}" />
                        <button type="button" class="button remove-slot">×</button>
                    `;
                    wrap.appendChild(newSlot);
                });
            });

            alert('Standard 9–5 slots added for Monday to Friday.');
        });
        // Copy Monday → All Days
        document.getElementById('copy_monday').addEventListener('click', function(){
            const mondaySlots = document.querySelectorAll('[data-day="monday"] .codo-slot');
            if(!mondaySlots.length) return alert('No slots to copy from Monday.');

            days.forEach(day => {
                if(day==='monday') return;
                const wrap = document.querySelector(`[data-day="${day}"] .codo-slots-wrap`);
                wrap.innerHTML = '';
                mondaySlots.forEach((slotDiv, i) => {
                    const start = slotDiv.querySelector('input[name*="[start]"]').value;
                    const end = slotDiv.querySelector('input[name*="[end]"]').value;
                   
                    const newSlot = document.createElement('div');
                    newSlot.classList.add('codo-slot');
                    newSlot.innerHTML = `
                        <label>Start</label>
                        <input type="time" name="codo_weekly_slots[${day}][${i}][start]" value="${start}" />
                        <label>End</label>
                        <input type="time" name="codo_weekly_slots[${day}][${i}][end]" value="${end}" />
                        <button type="button" class="button remove-slot">×</button>
                    `;
                    wrap.appendChild(newSlot);
                });
            });
        });

        // Export JSON
        document.getElementById('export-json').addEventListener('click', () => {
            const data = {};
            days.forEach(day => {
                const slots = [];
                document.querySelectorAll(`[data-day="${day}"] .codo-slot`).forEach(slotDiv => {
                    slots.push({
                        start: slotDiv.querySelector('input[name*="[start]"]').value,
                        end: slotDiv.querySelector('input[name*="[end]"]').value,
                    });
                });
                data[day] = slots;
            });
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: "application/json" });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "codobookings_slots.json";
            a.click();
            URL.revokeObjectURL(url);
        });

        // Import JSON
        document.getElementById('import-json').addEventListener('click', () => {
            document.getElementById('import-file').click();
        });

        document.getElementById('import-file').addEventListener('change', e => {
            const file = e.target.files[0];
            if(!file) return;
            const reader = new FileReader();
            reader.onload = () => {
                try {
                    const data = JSON.parse(reader.result);
                    days.forEach(day => {
                        const wrap = document.querySelector(`[data-day="${day}"] .codo-slots-wrap`);
                        wrap.innerHTML = '';
                        if(data[day]){
                            data[day].forEach((slot, i)=>{
                                const slotDiv = document.createElement('div');
                                slotDiv.classList.add('codo-slot');
                                slotDiv.innerHTML = `
                                    <label>Start</label>
                                    <input type="time" name="codo_weekly_slots[${day}][${i}][start]" value="${slot.start}" />
                                    <label>End</label>
                                    <input type="time" name="codo_weekly_slots[${day}][${i}][end]" value="${slot.end}" />
                                    <button type="button" class="button remove-slot">×</button>
                                `;
                                wrap.appendChild(slotDiv);
                            });
                        }
                    });
                } catch(err){
                    alert('Invalid JSON');
                }
            };
            reader.readAsText(file);
        });

    });
    </script>
    <?php
}

add_action( 'save_post', 'codobookings_save_calendar_meta', 10, 2 );
function codobookings_save_calendar_meta( $post_id, $post ) {
    if ( $post->post_type !== 'codo_calendar' ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    // ✅ Properly unslash and sanitize nonce before verifying
    if ( ! isset( $_POST['codobookings_calendar_nonce'] ) ) {
        return;
    }
    $nonce = sanitize_text_field( wp_unslash( $_POST['codobookings_calendar_nonce'] ) );
    if ( ! wp_verify_nonce( $nonce, 'codobookings_save_calendar' ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    $slots = isset( $_POST['codo_weekly_slots'] ) ? wp_unslash( $_POST['codo_weekly_slots'] ) : array();
    // sanitize slots
    $days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
    foreach($days as $day){
        if(isset($slots[$day])){
            foreach($slots[$day] as &$slot){
                $slot['start'] = sanitize_text_field($slot['start'] ?? '');
                $slot['end'] = sanitize_text_field($slot['end'] ?? '');
            }
        } else {
            $slots[$day] = array();
        }
    }

    update_post_meta( $post_id, '_codo_weekly_slots', $slots );
    update_post_meta( $post_id, '_codo_recurrence', sanitize_text_field( $_POST['codo_recurrence'] ?? 'none' ) );

    if ( isset( $_POST['codo_confirmation_message'] ) ) {
        update_post_meta(
            $post_id,
            '_codo_confirmation_message',
            sanitize_textarea_field( $_POST['codo_confirmation_message'] )
        );
    }
    
    do_action( 'codobookings_calendar_saved', $post_id );
}

add_action( 'codobookings_calendar_settings_after', 'codobookings_add_confirmation_message_field' );
function codobookings_add_confirmation_message_field( $post ) {
    $message = get_post_meta( $post->ID, '_codo_confirmation_message', true );
    if ( empty( $message ) ) {
        $message = __( 'Your booking has been confirmed successfully! Our team will soon contact you with further details. Thank you for choosing us.', 'codobookings' );
    }
    ?>
    <hr>
    <h4><?php esc_html_e( 'Confirmation Message', 'codobookings' ); ?></h4>
    <p>
        <textarea name="codo_confirmation_message" rows="3" style="width:100%;"><?php echo esc_textarea( $message ); ?></textarea>
        <small><?php esc_html_e( 'This message will be shown to the user after they confirm their booking.', 'codobookings' ); ?></small>
    </p>
    <?php
}


/**
 * Add Sidebar Meta Box for Calendar Settings
 */
add_action( 'add_meta_boxes', 'codobookings_add_sidebar_settings_meta' );
function codobookings_add_sidebar_settings_meta() {
    add_meta_box(
        'codo_calendar_sidebar_settings',
        __( 'Calendar Settings', 'codobookings' ),
        'codobookings_sidebar_settings_cb',
        'codo_calendar',
        'side',
        'default'
    );
}

/**
 * Render Sidebar Settings Meta Box
 */
function codobookings_sidebar_settings_cb( $post ) {
    wp_nonce_field( 'codobookings_save_sidebar_settings', 'codobookings_sidebar_nonce' );

    // Default settings
    $settings = wp_parse_args(
        get_post_meta( $post->ID, '_codo_sidebar_settings', true ),
        array(
            'show_title'        => 'yes',
            'allow_guest'       => 'no',
        )
    );

    // Allow other developers to add extra settings fields
    $extra_fields = apply_filters( 'codobookings_sidebar_settings_fields', array() );
    ?>

    <p>
        <label>
            <input type="checkbox" name="codo_sidebar_settings[show_title]" value="yes" <?php checked( $settings['show_title'], 'yes' ); ?> />
            <?php esc_html_e( 'Show Title', 'codobookings' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Toggle to display the calendar title on the frontend.', 'codobookings' ); ?></p>
    </p>

    <p>
        <label>
            <input type="checkbox" name="codo_sidebar_settings[allow_guest]" value="yes" <?php checked( $settings['allow_guest'], 'yes' ); ?> />
            <?php esc_html_e( 'Allow Guest Bookings', 'codobookings' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Allow users who are not logged in to make bookings with only providing their email address.', 'codobookings' ); ?></p>
    </p>

    <?php
    // Output extra fields added via hooks
    if( ! empty( $extra_fields ) && is_array( $extra_fields ) ) {
        foreach( $extra_fields as $field ) {
            echo '<p>' . esc_html( $field ) . '</p>';
        }
    }
}

/**
 * Save Sidebar Settings
 */
add_action( 'save_post', 'codobookings_save_sidebar_settings', 20, 2 );
function codobookings_save_sidebar_settings( $post_id, $post ) {
    if ( $post->post_type !== 'codo_calendar' ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['codobookings_sidebar_nonce'] ) || ! wp_verify_nonce( $_POST['codobookings_sidebar_nonce'], 'codobookings_save_sidebar_settings' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $settings = $_POST['codo_sidebar_settings'] ?? array();
    
    // Sanitize each known setting
    $sanitized = array(
        'show_title'       => isset( $settings['show_title'] ) && $settings['show_title'] === 'yes' ? 'yes' : 'no',
        'allow_guest'      => isset( $settings['allow_guest'] ) && $settings['allow_guest'] === 'yes' ? 'yes' : 'no',
    );

    /**
     * Allow developers to sanitize/modify settings before saving
     */
    $sanitized = apply_filters( 'codobookings_sidebar_settings_sanitize', $sanitized, $post_id );

    update_post_meta( $post_id, '_codo_sidebar_settings', $sanitized );
}
