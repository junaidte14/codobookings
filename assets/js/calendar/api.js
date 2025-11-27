window.CodoBookings = window.CodoBookings || {};

(function(ns){
    const api = {
        fetchCalendar(id){
            const fd = new FormData();
            fd.append('action','codo_get_calendar');
            fd.append('calendar_id', id);
            fd.append('nonce', window.CODOBookingsData && CODOBookingsData.nonce);

            return fetch(window.CODOBookingsData.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
                .then(r => r.json())
                .then(json => json.success ? json.data : Promise.reject(json.data && json.data.message || 'Failed to load calendar'));
        },

        createBooking(payload){
            const fd = new FormData();
            fd.append('action', 'codobookings_create_booking');
            fd.append('nonce', window.CODOBookingsData && CODOBookingsData.nonce);
            fd.append('calendar_id', payload.calendar_id);
            fd.append('start', payload.start);
            fd.append('end', payload.end);
            fd.append('email', payload.email);
            fd.append('day', payload.day);

            // ✅ FIXED: Catch errors from hooks and prevent booking if validation fails
            try {
                if (ns.hooks && ns.hooks.beforeCreateBooking) {
                    for (let callback of ns.hooks.beforeCreateBooking) {
                        callback(payload, fd);
                    }
                }
            } catch(e) {
                console.error('Error in beforeCreateBooking hook:', e);
                // ✅ Return a rejected promise to stop the booking
                return Promise.reject(e);
            }

            return fetch(window.CODOBookingsData.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
                .then(r => r.json())
                .then(response => {
                    // ✅ NEW: Trigger after-create hook
                    if (ns.hooks && ns.hooks.afterCreateBooking) {
                        ns.hooks.afterCreateBooking.forEach(callback => {
                            try {
                                callback(response, payload);
                            } catch(e) {
                                console.error('Error in afterCreateBooking hook:', e);
                            }
                        });
                    }
                    return response;
                });
        }
    };

    // ✅ NEW: Hook system for extensions
    const hooks = {
        beforeCreateBooking: [],
        afterCreateBooking: [],
        beforeCalendarReload: [],
        afterCalendarReload: [],
        beforeConfirmBooking: [],      
        afterConfirmBooking: [],       
        afterSidebarRender: []         
    };

    // ✅ NEW: Public API for registering hooks
    const registerHook = (hookName, callback) => {
        if (hooks[hookName] && typeof callback === 'function') {
            hooks[hookName].push(callback);
            return true;
        }
        console.warn('Invalid hook name or callback:', hookName);
        return false;
    };

    // ✅ NEW: Public API for unregistering hooks
    const unregisterHook = (hookName, callback) => {
        if (hooks[hookName]) {
            const index = hooks[hookName].indexOf(callback);
            if (index > -1) {
                hooks[hookName].splice(index, 1);
                return true;
            }
        }
        return false;
    };

    ns.api = api;
    ns.hooks = hooks;
    ns.registerHook = registerHook;
    ns.unregisterHook = unregisterHook;
})(window.CodoBookings);