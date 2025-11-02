window.CodoBookings = window.CodoBookings || {};

(function (ns) {
    const { formatTimeToLocal } = ns.utils;
    const renderSidebar = ns.sidebar && ns.sidebar.renderSidebar;

    /**
     * Utility: Return available slots for a given date (for onetime calendars)
     */
    function getSlotsForDate(dateStr, data) {
        const dateObj = new Date(dateStr);
        const weekday = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'][dateObj.getDay()];
        const slotsForDay = (data.slots || []).filter(s => s.day.toLowerCase() === weekday);
        const bookings = data.bookings || [];

        const bookedTimes = new Set();

        bookings.forEach(b => {
            if (b.type === 'none') {
                // Match by date (YYYY-MM-DD)
                if (b.start.startsWith(dateStr)) {
                    const time = b.start.split(' ')[1].slice(0, 5); // HH:MM
                    bookedTimes.add(time);
                }
            } else if (b.type === 'weekly') {
                // Weekly recurrence — block every week for this weekday
                if (b.weekday === weekday) {
                    const time = b.start.split(' ')[1].slice(0, 5); // HH:MM from UTC datetime
                    bookedTimes.add(time);
                }
            }
        });

        // Return slots that are not booked
        return slotsForDay.filter(slot => !bookedTimes.has(slot.start));
    }

    /**
     * Render One-Time Calendar (monthly view)
     */
    function renderOneTimeCalendar(root, data, monthOffset = 0) {
        //console.log('Calendar data:', data);
        const now = new Date();
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        const current = new Date(now.getFullYear(), now.getMonth() + monthOffset, 1);
        const year = current.getFullYear();
        const month = current.getMonth();

        // Calculate days in this month
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Check if this month has any available slots
        let hasAvailableSlots = false;
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${('0' + (month + 1)).slice(-2)}-${('0' + day).slice(-2)}`;
            const cellDate = new Date(year, month, day);
            cellDate.setHours(0, 0, 0, 0);

            if (cellDate >= today) {
                const daySlots = getSlotsForDate(dateStr, data);
                if (daySlots.length > 0) {
                    hasAvailableSlots = true;
                    break;
                }
            }
        }

        // If no slots, move to next month (limit to 12 months ahead)
        if (!hasAvailableSlots && monthOffset < 12) {
            return renderOneTimeCalendar(root, data, monthOffset + 1);
        }

        // Monday = first day
        const firstDay = (new Date(year, month, 1).getDay() + 6) % 7;
        //const daysInMonth = new Date(year, month + 1, 0).getDate();

        // --- Header ---
        const header = document.createElement('div');
        header.className = 'codo-calendar-header';

        const prevBtn = document.createElement('button');
        prevBtn.textContent = '« Prev';
        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next »';
        const title = document.createElement('span');
        title.textContent = current.toLocaleString('default', { month: 'long', year: 'numeric' });

        header.append(prevBtn, title, nextBtn);
        root.innerHTML = '';
        root.appendChild(header);

        prevBtn.addEventListener('click', () => renderOneTimeCalendar(root, data, monthOffset - 1));
        nextBtn.addEventListener('click', () => renderOneTimeCalendar(root, data, monthOffset + 1));

        // --- Table ---
        const table = document.createElement('table');
        table.className = 'codo-onetime-calendar';
        const trHeader = document.createElement('tr');

        ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].forEach(d => {
            const th = document.createElement('th');
            th.textContent = d;
            trHeader.appendChild(th);
        });
        table.appendChild(trHeader);

        // --- Body ---
        for (let w = 0; w < 6; w++) {
            const tr = document.createElement('tr');

            for (let dow = 0; dow < 7; dow++) {
                const td = document.createElement('td');
                td.style.position = 'relative';

                const cellIndex = w * 7 + dow;
                const dayNumber = cellIndex - firstDay + 1;

                if (dayNumber < 1 || dayNumber > daysInMonth) {
                    td.textContent = '';
                } else {
                    const dateStr = `${year}-${('0' + (month + 1)).slice(-2)}-${('0' + dayNumber).slice(-2)}`;
                    const cellDate = new Date(year, month, dayNumber);
                    cellDate.setHours(0, 0, 0, 0);

                    const daySlots = getSlotsForDate(dateStr, data);

                    td.innerHTML = `<div class="codo-calendar-date">${dayNumber}</div>`;

                    if (cellDate < today) {
                        td.classList.add('past');
                    } else if (daySlots.length > 0) {
                        td.classList.add('available');

                        const tooltip = document.createElement('div');
                        tooltip.className = 'codo-calendar-tooltip';
                        const slotCount = daySlots.length;
                        tooltip.textContent = slotCount === 1 
                            ? '1 Slot Available' 
                            : `${slotCount} Slots Available`;
                        td.appendChild(tooltip);

                        td.addEventListener('click', () => {
                            // Remove 'codo-active' from any previously active slot in this calendar
                            const activeEls = root.querySelectorAll('.codo-active');
                            activeEls.forEach(el => el.classList.remove('codo-active'));
                            // Add 'codo-active' to this cell
                            td.classList.add('codo-active');
                            daySlots.forEach(slot => renderSidebar(slot, dateStr, 'none', root));
                        });
                    } else {
                        td.classList.add('past');
                    }
                }
                tr.appendChild(td);
            }
            table.appendChild(tr);
        }

        root.appendChild(table);
    }

    // Export to namespace
    ns.renderOneTimeCalendar = renderOneTimeCalendar;
})(window.CodoBookings);
