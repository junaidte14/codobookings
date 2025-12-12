window.CodoBookings = window.CodoBookings || {};

(function(ns){
    const { formatTimeToLocal } = ns.utils;
    const { api } = ns;

    function showConfirmationMessage(containerEl, root) {
        const calendarSettings = window['codobookings_settings_' + root.dataset.calendarId];
        const msg = calendarSettings?.confirmation_message || 'Your booking has been received successfully! Our team will soon contact you with further details. Thank you for choosing us.';
        const overlay = document.createElement('div');
        overlay.className = 'codo-confirm-overlay';
        
        // ✅ REMOVED: All inline styles - use CSS variables instead
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        const box = document.createElement('div');
        box.className = 'codo-confirm-box';
        // ✅ REMOVED: Hardcoded colors - now uses CSS class
        box.style.cssText = `
            background: var(--codobookings-background-color, #fff);
            padding: 20px 30px;
            border-radius: var(--codobookings-border-radius, 10px);
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            font-family: var(--codobookings-font-family, inherit);
        `;
        
        const msgText = document.createElement('p');
        msgText.style.cssText = `
            font-size: var(--codobookings-base-font-size, 16px);
            color: var(--codobookings-text-color, #333);
            margin: 0 0 15px 0;
        `;
        msgText.textContent = msg;
        
        const closeBtn = document.createElement('button');
        closeBtn.id = 'closeConfirmMsg';
        closeBtn.className = 'codo-confirm-close-btn';
        closeBtn.textContent = 'OK';
        // ✅ REMOVED: Hardcoded button styles
        closeBtn.style.cssText = `
            margin-top: 15px;
            padding: 8px 16px;
            background: var(--codobookings-primary-color, #0073aa);
            color: var(--codobookings-button-text-color, #fff);
            border: none;
            border-radius: var(--codobookings-button-border-radius, 5px);
            cursor: pointer;
            transition: var(--codobookings-transition, all 0.25s ease);
            font-family: var(--codobookings-font-family, inherit);
        `;
        
        // Add hover effect via event listeners
        closeBtn.addEventListener('mouseenter', function() {
            this.style.background = `var(--codobookings-secondary-color, #005177)`;
        });
        closeBtn.addEventListener('mouseleave', function() {
            this.style.background = `var(--codobookings-primary-color, #0073aa)`;
        });
        
        box.appendChild(msgText);
        box.appendChild(closeBtn);
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        // Disable further interaction until closed
        document.body.style.pointerEvents = 'none';
        box.style.pointerEvents = 'auto';

        closeBtn.addEventListener('click', () => {
            overlay.remove();
            document.body.style.pointerEvents = 'auto';
            reloadCalendar(containerEl);
        });
    }

    function reloadCalendar(containerEl){
        const calendarRoot = containerEl.closest('.codo-calendar-wrapper');
        if (calendarRoot && calendarRoot.dataset.calendarId) {
            // ✅ Trigger before-reload hook
            if (ns.hooks && ns.hooks.beforeCalendarReload) {
                ns.hooks.beforeCalendarReload.forEach(callback => {
                    try {
                        callback(calendarRoot);
                    } catch(e) {
                        console.error('Error in beforeCalendarReload hook:', e);
                    }
                });
            }

            ns.api.fetchCalendar(calendarRoot.dataset.calendarId)
                .then(data => {
                    if (data.recurrence === 'weekly') ns.renderWeeklyCalendar(calendarRoot, data);
                    else ns.renderOneTimeCalendar(calendarRoot, data);

                    // ✅ Trigger after-reload hook
                    if (ns.hooks && ns.hooks.afterCalendarReload) {
                        ns.hooks.afterCalendarReload.forEach(callback => {
                            try {
                                callback(calendarRoot, data);
                            } catch(e) {
                                console.error('Error in afterCalendarReload hook:', e);
                            }
                        });
                    }
                })
                .catch(err => console.error('Failed to reload calendar:', err));
        }
    }

    function renderSidebar(slots, label, type, root){
        if (!Array.isArray(slots)) slots = [slots];

        // --- Make sidebar unique per calendar root ---
        let sidebar = root.querySelector('.codo-calendar-sidebar');
        if (!sidebar) {
            sidebar = document.createElement('div');
            sidebar.className = 'codo-calendar-sidebar';
            sidebar.dataset.calendarId = root.dataset.calendarId;
            root.appendChild(sidebar);
            requestAnimationFrame(() => sidebar.classList.add('visible'));

            const header = document.createElement('div');
            header.className = 'codo-sidebar-header';
            header.innerHTML = '<strong>Booking Details</strong><br><small>Select slots and click Confirm Booking</small>';
            // ✅ REMOVED: Hardcoded margin - now in CSS
            sidebar.appendChild(header);

            const container = document.createElement('div');
            container.className = 'codo-sidebar-container';
            sidebar.appendChild(container);

            const footer = document.createElement('div');
            footer.className = 'codo-sidebar-footer';
            // ✅ REMOVED: Hardcoded margin - now in CSS
            
            const confirmBtn = document.createElement('button');
            confirmBtn.textContent = 'Confirm Booking';
            confirmBtn.className = 'codo-confirm-booking-btn';
            // ✅ REMOVED: All hardcoded button styles - now uses CSS classes/variables
            confirmBtn.disabled = true;
            footer.appendChild(confirmBtn);
            sidebar.appendChild(footer);

            sidebar._confirmBtn = confirmBtn;

            confirmBtn.addEventListener('click', () => {
                // ✅ NEW: Trigger before-confirm hook (can prevent submission)
                if (ns.hooks && ns.hooks.beforeConfirmBooking) {
                    try {
                        for (let callback of ns.hooks.beforeConfirmBooking) {
                            const shouldContinue = callback({
                                sidebar: sidebar,
                                container: sidebar.querySelector('.codo-sidebar-container'),
                                root: root,
                                calendarId: root.dataset.calendarId
                            });
                            if (shouldContinue === false) {
                                confirmBtn.disabled = false;
                                return;
                            }
                        }
                    } catch(e) {
                        console.error('Error in beforeConfirmBooking hook:', e);
                        confirmBtn.disabled = false;
                        return;
                    }
                }

                confirmBtn.disabled = true;
                const containerEl = sidebar.querySelector('.codo-sidebar-container');
                const calendarSettings = window['codobookings_settings_' + root.dataset.calendarId];
                const allowGuest = calendarSettings?.settings?.allow_guest === 'yes';
                const userEmail  = calendarSettings?.userEmail || '';
                const loginUrl   = calendarSettings?.loginUrl || '#';

                if (!allowGuest && !userEmail) {
                    // ✅ REMOVED: Hardcoded styles - use CSS classes
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'codo-booking-message codo-booking-error';
                    
                    const messagePara = document.createElement('p');
                    messagePara.textContent = 'You must be logged in to book this calendar.';
                    
                    const loginLink = document.createElement('a');
                    loginLink.href = loginUrl;
                    loginLink.textContent = 'Login & Continue';
                    loginLink.className = 'codo-login-btn';
                    loginLink.style.cssText = `
                        display: inline-block;
                        margin-top: 10px;
                        padding: 8px 12px;
                        background: var(--codobookings-primary-color, #cc0000);
                        color: var(--codobookings-button-text-color, #fff);
                        border-radius: var(--codobookings-button-border-radius, 4px);
                        text-decoration: none;
                    `;
                    
                    messageDiv.appendChild(messagePara);
                    messageDiv.appendChild(loginLink);
                    containerEl.innerHTML = '';
                    containerEl.appendChild(messageDiv);
                    
                    confirmBtn.disabled = false;
                    return;
                }

                let email = userEmail;
                if (!email) {
                    email = prompt('Enter your email to confirm booking:');
                    if (!email) {
                        confirmBtn.disabled = false;
                        return;
                    }
                }
                
                const selectedItems = Array.from(containerEl.querySelectorAll('.codo-sidebar-item.selected'));

                const slotsToBook = selectedItems.map(item => {
                    let recurrence_day = '';
                    if (type === 'weekly') recurrence_day = item.dataset.day;
                    else {
                        const dateParts = item.dataset.day.split('-');
                        const dt = new Date(dateParts[0], dateParts[1]-1, dateParts[2]);
                        const dow = ns.utils.weekDayNamesLower()[dt.getDay()];
                        recurrence_day = dow;
                    }

                    return {
                        start: item.dataset.start,
                        end: item.dataset.end,
                        day: recurrence_day,
                        calendar_id: item.dataset.calendarId
                    };
                });

                if (!slotsToBook.length) {
                    confirmBtn.disabled = false;
                    return;
                }

                let successCount = 0; 
                let failedCount = 0;

                const promises = slotsToBook.map(slotData => {
                    return api.createBooking({
                        calendar_id: slotData.calendar_id,
                        start: slotData.start,
                        end: slotData.end,
                        email: email,
                        day: slotData.day
                    })
                    .then(resp => { 
                        if (resp && resp.success) {
                            successCount++;
                        } else {
                            failedCount++;
                        }
                        return resp;
                    })
                    .catch((err) => { 
                        failedCount++;
                        throw err;
                    });
                });

                Promise.all(promises)
                    .then((results) => {
                        // ✅ Trigger after all bookings complete
                        if (ns.hooks && ns.hooks.afterConfirmBooking) {
                            ns.hooks.afterConfirmBooking.forEach(callback => {
                                try {
                                    callback({
                                        successCount: successCount,
                                        failedCount: failedCount,
                                        results: results,
                                        sidebar: sidebar,
                                        root: root
                                    });
                                } catch(e) {
                                    console.error('Error in afterConfirmBooking hook:', e);
                                }
                            });
                        }

                        if (successCount > 0) {
                            showConfirmationMessage(containerEl, root);
                        }
                        
                        containerEl.innerHTML = '';
                        confirmBtn.disabled = true;
                    })
                    .catch((err) => {
                        console.error('Booking error:', err);
                        confirmBtn.disabled = false;
                    });
            });

            // ✅ Trigger after sidebar is created
            if (ns.hooks && ns.hooks.afterSidebarRender) {
                ns.hooks.afterSidebarRender.forEach(callback => {
                    try {
                        callback({
                            sidebar: sidebar,
                            header: header,
                            container: container,
                            footer: footer,
                            confirmBtn: confirmBtn,
                            root: root
                        });
                    } catch(e) {
                        console.error('Error in afterSidebarRender hook:', e);
                    }
                });
            }
        }

        const container = sidebar.querySelector('.codo-sidebar-container');
        const confirmBtn = sidebar._confirmBtn;

        slots.forEach(slot => {
            const slotKey = `${slot.day}-${slot.start}-${slot.end}`;
            if (container.querySelector(`[data-slot-key="${slotKey}"]`)) return;

            const localStart = formatTimeToLocal(slot.start);
            const localEnd = formatTimeToLocal(slot.end);

            const item = document.createElement('div');
            item.className = 'codo-sidebar-item';
            const isWeekly = type === 'weekly';

            if (isWeekly){
                item.dataset.start = slot.start;
                item.dataset.end = slot.end;
            } else {
                item.dataset.start = label + ' ' + slot.start + ':00';
                item.dataset.end = label + ' ' + slot.end + ':00';
            }

            item.dataset.day = label;
            item.dataset.calendarId = root.dataset.calendarId;
            item.dataset.slotKey = slotKey;

            item.innerHTML = `
                <strong>${type === 'weekly' ? 'Every ' + label : label}</strong><br>
                ${slot.start}-${slot.end} UTC / ${localStart}-${localEnd} Local
                <button class="remove-slot" style="display:none;">Remove</button>
            `;

            const removeBtn = item.querySelector('.remove-slot');

            item.addEventListener('click', () => {
                const selected = item.classList.toggle('selected');
                removeBtn.style.display = selected ? 'inline-block' : 'none';
                updateConfirmButtonState();
            });

            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                item.classList.remove('selected');
                removeBtn.style.display = 'none';
                updateConfirmButtonState();
            });

            container.appendChild(item);
        });

        function updateConfirmButtonState(){
            confirmBtn.disabled = container.querySelectorAll('.codo-sidebar-item.selected').length === 0;
        }
    }

    ns.sidebar = ns.sidebar || {};
    ns.sidebar.renderSidebar = renderSidebar;
})(window.CodoBookings);