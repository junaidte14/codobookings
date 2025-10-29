window.CodoBookings = window.CodoBookings || {};

(function(ns){
    const { formatTimeToLocal, daysOfWeek } = ns.utils;
    const renderSidebar = ns.sidebar && ns.sidebar.renderSidebar;

    /**
     * Render Weekly Calendar (booked slots already filtered by backend)
     */
    function renderWeeklyCalendar(root, data) {
        //console.log('Calendar data:', data);
        const allSlots = data.slots || []; // Slots returned from backend
        const fullDays = daysOfWeek();     // ['monday','tuesday',...]
        const shortDays = fullDays.map(d => d.slice(0,3)); // ['Mon','Tue',...]

        // --- Build table ---
        const table = document.createElement('table');
        table.className = 'codo-weekly-calendar';

        // Header row
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        shortDays.forEach(d => {
            const th = document.createElement('th');
            th.textContent = d;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);

        // Body row (one row with all slots per day)
        const tbody = document.createElement('tbody');
        const row = document.createElement('tr');

        fullDays.forEach(fullDay => {
            const td = document.createElement('td');
            td.className = 'codo-weekly-cell';

            // Slots for this weekday
            const daySlots = allSlots.filter(s => s.day.toLowerCase() === fullDay.toLowerCase());

            if (daySlots.length) {
                daySlots.forEach(slot => {
                    const btn = document.createElement('button');
                    btn.className = 'codo-slot';
                    btn.textContent = `${slot.start}-${slot.end} UTC`;

                    // Tooltip with local time
                    const tooltip = document.createElement('div');
                    tooltip.className = 'codo-slot-tooltip';
                    tooltip.innerHTML = `
                        Every ${fullDay} ${slot.start}-${slot.end} (UTC)<br>
                        ${formatTimeToLocal(slot.start)}-${formatTimeToLocal(slot.end)} (Local)
                    `;
                    btn.appendChild(tooltip);

                    // Click → open sidebar
                    btn.addEventListener('click', () =>
                        renderSidebar(slot, fullDay, 'weekly', root)
                    );

                    td.appendChild(btn);
                });
            } else {
                td.innerHTML = '<span class="codo-no-slot">–</span>';
            }

            row.appendChild(td);
        });

        tbody.appendChild(row);
        table.appendChild(tbody);

        root.innerHTML = '';
        root.appendChild(table);
    }

    ns.renderWeeklyCalendar = renderWeeklyCalendar;
})(window.CodoBookings);
