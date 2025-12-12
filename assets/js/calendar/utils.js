// =========================
// File: js/utils.js
// =========================

window.CodoBookings = window.CodoBookings || {};

(function(ns){
    const utils = {
        el(q, parent){ return (parent || document).querySelector(q); },

        formatTimeToLocal(utcTime){
            if(!utcTime) return '';
            const [h, m] = utcTime.split(':');
            const d = new Date();
            // setUTCHours accepts numbers
            d.setUTCHours(parseInt(h,10), parseInt(m,10), 0, 0);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        // Lowercase week names starting from sunday for getDay() compatibility
        weekDayNamesLower() {
            return ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
        },

        // Human-readable days (Monday..Sunday)
        daysOfWeek() {
            return ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
        }
    };

    ns.utils = utils;
})(window.CodoBookings);