=== CodoBookings ===
Contributors: junaidte14
Tags: bookings, appointments, calendar, pmpro, membership
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

CodoBookings allows WordPress site owners to create a booking system integrated with PMPro membership levels. Users can select membership levels with booking enabled, view calendars, and reserve slots.

== Description ==

CodoBookings is a flexible booking plugin for WordPress that integrates with Paid Memberships Pro (PMPro). It allows you to:

- Enable bookings per membership level.
- Display booking calendars with available slots.
- Disable fully booked or past dates.
- Track booked slots and prevent double bookings.
- Show remaining slots per day.
- Link bookings automatically to PMPro orders.

This plugin is ideal for businesses, consultants, clinics, or any service where appointment bookings are required.

== Features ==

* Membership-level booking support (PMPro integration)
* Fully dynamic calendar view with slot counts
* Auto-disable fully booked or past days
* AJAX-based month navigation
* Show remaining available slots on each date
* Selected appointments displayed in checkout
* Shortcode: `[codo_booking_levels]` for showing all booking-enabled levels

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the “Plugins” menu in WordPress.
3. Configure the plugin under **Bookings → Settings**.

== Setup & Configuration ==

CodoBookings provides **membership-level-based booking** using the PMPRO plugin.

### Step 1: Enable Booking for Membership Levels
1. Go to **Memberships → Levels** (PMPro).
2. Create a membership level or edit an existing one.
3. Enable booking for that level by checking the **“Enable Booking”** option in the level settings.
4. Define slot availability per weekday for that level in **Bookings → Settings**.

### Step 2: Create a Booking Page
1. Create a new page (e.g., “Book Your Slot”).
2. Insert the shortcode: [codo_booking_levels]

This shortcode will:

- List all membership levels that have booking enabled.
- Display the booking calendar for each enabled level.
- Show available slots per day and disable fully booked or past days.
- Automatically link bookings to PMPro orders for that level.

### Step 3: User Flow
1. A user selects a membership level from the `[codo_booking_levels]` page.
2. The calendar for that level is displayed with available slots.
3. The user selects a date and time and proceeds to checkout.
4. Booking is automatically linked to the PMPro order.

== Frequently Asked Questions ==

= How are booked slots handled? =
Booked slots are removed from the calendar and cannot be selected again. Fully booked days are disabled.

== Screenshots ==

1. Booking-enabled membership levels listed on page.
2. Calendar view with available slots.
3. Selected appointment displayed in checkout.

== Changelog ==

= 1.0.0 =
* Initial release with PMPro membership-level booking.
* Dynamic calendar with AJAX month navigation.
* Fully booked days disabled and remaining slots shown.
* Shortcode `[codo_booking_levels]` implemented.

== Upgrade Notice ==

= 1.0.0 =
Initial release. No previous versions to upgrade from.

== License ==

This plugin is licensed under the GPLv2 or later.