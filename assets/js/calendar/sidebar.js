
window.CodoBookings = window.CodoBookings || {};

(function(ns){
    const { formatTimeToLocal } = ns.utils;
    const { api } = ns;

    function showConfirmationMessage(containerEl, root) {
        const calendarSettings = window['codobookings_settings_' + root.dataset.calendarId];
        const msg = calendarSettings?.confirmation_message || 'Your booking has been confirmed successfully! Our team will soon contact you with further details. Thank you for choosing us.';
        const overlay = document.createElement('div');
        overlay.className = 'codo-confirm-overlay';
        overlay.style = `
            position:fixed; top:0; left:0; right:0; bottom:0;
            background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;
        `;
        const box = document.createElement('div');
        box.style = `
            background:#fff; padding:20px 30px; border-radius:10px;
            max-width:400px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.2);
        `;
        box.innerHTML = `
            <p style="font-size:16px;">${msg}</p>
            <a id="closeConfirmMsg" style="margin-top:15px; padding:8px 16px; background:#0073aa; color:#fff; border:none; border-radius:5px; cursor: pointer;">OK</a>
        `;
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        // Disable further interaction until closed
        document.body.style.pointerEvents = 'none';
        box.style.pointerEvents = 'auto';

        document.getElementById('closeConfirmMsg').addEventListener('click', () => {
            overlay.remove();
            document.body.style.pointerEvents = 'auto';
            reloadCalendar(containerEl);
        });
    }

    function reloadCalendar(containerEl){
        // ✅ Reload the calendar after booking
        const calendarRoot = containerEl.closest('.codo-calendar-wrapper');
        if (calendarRoot && calendarRoot.dataset.calendarId) {
            ns.api.fetchCalendar(calendarRoot.dataset.calendarId)
                .then(data => {
                    if (data.recurrence === 'weekly') ns.renderWeeklyCalendar(calendarRoot, data);
                    else ns.renderOneTimeCalendar(calendarRoot, data);
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
            header.style.marginBottom = '10px';
            sidebar.appendChild(header);

            const container = document.createElement('div');
            container.className = 'codo-sidebar-container';
            sidebar.appendChild(container);

            const footer = document.createElement('div');
            footer.className = 'codo-sidebar-footer';
            footer.style.marginTop = '10px';
            const confirmBtn = document.createElement('button');
            confirmBtn.textContent = 'Confirm Booking';
            confirmBtn.style.width = '100%';
            confirmBtn.style.padding = '8px';
            confirmBtn.style.background = '#0073aa';
            confirmBtn.style.color = '#fff';
            confirmBtn.style.border = 'none';
            confirmBtn.style.borderRadius = '4px';
            confirmBtn.style.cursor = 'pointer';
            confirmBtn.disabled = true;
            footer.appendChild(confirmBtn);
            sidebar.appendChild(footer);

            sidebar._confirmBtn = confirmBtn;

            confirmBtn.addEventListener('click', () => {
                confirmBtn.disabled = true;
                // Check guest booking
                const containerEl = sidebar.querySelector('.codo-sidebar-container');
                // dynamically use the per-calendar settings
                const calendarSettings = window['codobookings_settings_' + root.dataset.calendarId];
                //console.log(calendarSettings);
                const allowGuest = calendarSettings?.settings?.allow_guest === 'yes';
                const userEmail  = calendarSettings?.userEmail || '';
                const loginUrl   = calendarSettings?.loginUrl || '#';

                if (!allowGuest && !userEmail) {
                    containerEl.innerHTML = `
                        <div class="codo-booking-message" style="padding:15px; text-align:center; background:#ffe6e6; border:1px solid #cc0000; border-radius:6px;">
                            <p>You must be logged in to book this calendar.</p>
                            <a href="${loginUrl}" style="display:inline-block; margin-top:10px; padding:8px 12px; background:#cc0000; color:#fff; border-radius:4px;">Login & Continue</a>
                        </div>
                    `;
                    return;
                }

                // Guest booking allowed or user logged in
                let email = userEmail;
                if (!email) {
                    email = prompt('Enter your email to confirm booking:');
                    if (!email) return;
                }
                const selectedItems = Array.from(containerEl.querySelectorAll('.codo-sidebar-item.selected'));

                const slotsToBook = selectedItems.map(item => {
                    let recurrence_day = '';
                    if (type === 'weekly') recurrence_day = item.dataset.day; // 'monday', ...
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

                if (!slotsToBook.length) return;

                let successCount = 0; let failedCount = 0;

                const promises = slotsToBook.map(slotData => {
                    return api.createBooking({
                        calendar_id: slotData.calendar_id,
                        start: slotData.start,
                        end: slotData.end,
                        email: email,
                        day: slotData.day
                    })
                    .then(resp => { if (resp && resp.success) successCount++; else failedCount++; })
                    .catch(() => { failedCount++; });
                });

                Promise.all(promises).then(() => {
                    containerEl.innerHTML = '';
                    confirmBtn.disabled = true;
                    showConfirmationMessage(containerEl, root);
                });
            });
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

            item.innerHTML = `\n                <strong>${type === 'weekly' ? 'Every ' + label : label}</strong><br>\n                ${slot.start}-${slot.end} UTC / ${localStart}-${localEnd} Local\n                <button class="remove-slot" style="display:none;">Remove</button>\n            `;

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
