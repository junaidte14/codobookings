=== CodoBookings ===
Contributors: junaidte14
Tags: booking, appointments, calendar, scheduler, wordpress-booking
Requires at least: 6.0
Tested up to: 6.9
Stable tag: 1.3.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
A Lightweight WordPress Booking & Appointment System

== Description ==

CodoBookings is a modern, lightweight booking and appointment-management plugin for WordPress. It’s designed to make scheduling, managing, and tracking appointments effortless.

Whether you're a coach, consultant, tutor, or agency, CodoBookings helps you manage your entire booking process directly inside WordPress — cleanly, securely, and efficiently.

**🎯 Key Features**

* **Flexible Booking Types** – Supports both one-time and weekly recurring appointments.
* **Design System** - A powerful design system to customize colors, layout and custom CSS.
* **Guest Bookings** – Customers can book appointments without creating an account by just providing their email address.
* **Email Notifications** – Automatic HTML email confirmations and status update notifications for you and your clients.
* **Shortcodes for Easy Embedding** – Display booking calendars or grids anywhere using simple shortcodes.
* **Admin Dashboard Widget** – See your key booking stats right on the WordPress dashboard.
* **Clean Front-End Interface** – Modern, minimal design that adapts to any WordPress theme.
* **Translation Ready** – Includes `.pot` file for localization with tools like Poedit.
* **Extensible Architecture** – Modular structure ready for integrations and add-ons.
* **Secure & Optimized** – Sanitized inputs, escaped outputs, and lightweight queries.

CodoBookings provides a full, professional booking system built natively for WordPress — without bloat or dependency chains.

---

== Screenshots ==

1. Single calendar view with available slots.  
2. Admin dashboard overview widget. 
3. Calendars list management in the WordPress admin.  
4. Edit calendar view in the WordPress admin.  
5. Edit calendar settings view in the WordPress admin.  
6. Define calendar type and confirmation message in the WordPress admin.  
7. Bookings list management in the WordPress admin.
8. Edit single booking in the WordPress admin.
9. Calendar categories management in the WordPress admin.
10. Plugin settings management in the WordPress admin.
11. Booking calendar grid on the frontend.
12. One-Time booking calendar view on the frontend.
13. Weekly recurring booking calendar view on the frontend.
---

== Installation ==

1. Upload the plugin folder `/codobookings/` to the `/wp-content/plugins/` directory, or install it via the Plugins screen in WordPress.
2. Activate **CodoBookings** through the *Plugins* menu in WordPress.
3. Navigate to **CodoBookings → Settings** to configure general options and email preferences.
4. Create your first booking calendar under **CodoBookings → Calendars**.
5. Add a calendar to any page using this shortcode: [codo_calendar id="123"]. 
6. You can also view any calendar on a page which is automatically created upon the plugin activation by providing **calendar_id** as a query parameter.
7. To display multiple calendars in a grid layout, use: [codo_calendars_grid columns="3"]
8. To display calendars from a specific category in a grid layout, use: [codo_calendars_grid columns="3" category="category-slug"]. The **category-slug** is available on **CodoBookings → Categories** page for each category.

---

== Frequently Asked Questions ==

= How can I manage calendars? =
All calendars can be viewed and managed under **CodoBookings → Calendars**. You can update, define availability slots, change the calendar type, or define a custom confirmation message easily from the admin area.

= How can I manage bookings? =
All bookings can be viewed and managed under **CodoBookings → Bookings**. You can update, confirm, or cancel bookings easily from the admin area.

= Is it developer-friendly? =
Yes! CodoBookings uses a modular structure and provides multiple hooks and filters for extending core functionality.

= Is CodoBookings translation ready? =
Yes, the plugin includes a `.pot` file under the `/languages/` directory for easy translation via Poedit or WPML.

= Can I sell bookings? =
Not yet, but upcoming extensions will integrate with WooCommerce and Paid Memberships Pro to handle payments and restrictions.

---

== Changelog ==

= 1.3.0 =
* NEW: Design Customization System - Customize your booking calendars to match your brand
* NEW: Theme Color Inheritance - Automatically detects and uses your WordPress theme colors
* NEW: Basic Design Settings - Control primary color, text color, and border radius
* NEW: Custom CSS Field - Add your own CSS for advanced customization
* NEW: CSS Variables Architecture - All styles use CSS custom properties for easy theming
* NEW: 15+ Extension Hooks - Extensive filter and action hooks for developers
* NEW: New Setting - Define default booking status
* IMPROVED: Frontend Styling - Removed all hardcoded colors and sizes
* IMPROVED: Sidebar Rendering - Dynamic styles now use CSS variables
* IMPROVED: Theme Integration - Better compatibility with any WordPress theme
* IMPROVED: Performance - Optimized CSS generation with intelligent caching
* IMPROVED: Extensibility - Clean separation for premium extensions
* FIXED: Hardcoded styles in JavaScript sidebar rendering
* FIXED: Calendar grid responsive behavior on mobile devices
* DEVELOPER: New design-focused hooks and filters for extensions
* DEVELOPER: Smart auto-color generation (secondary from primary, heading from text)

= 1.2.0 =
* Added User Fields Extension link on dashboard page
* Updated the translations file

= 1.1.0 =
* New hooks introduced in PHP code
* Some wording changes
* Hooks added in JS code for future extensibility
* Fixed confirmation message visibility

= 1.0.0 =
* Initial Release with following features
* Added booking grid and single calendar shortcodes.
* Added recurring booking logic (weekly).
* Added guest bookings feature.
* Added admin dashboard widget for quick stats.
* Added booking management interface.
* Added global email notification system.
* Translation ready with `.pot` file included.

---

== Upgrade Notice ==

= 1.2.0 =
Added user fields extension link on dashboard page and updated the translations file

= 1.1.0 =
Introduced new PHP/JS hooks, improved wording, and fixed confirmation message visibility.

= 1.0.0 =
CodoBookings is a complete standalone booking system — no dependencies required. Please update to enjoy the latest stable features.

---

== Developer Hooks & Filters ==

CodoBookings provides 40+ action hooks and filter hooks that allow developers to extend and customize the booking system without modifying core plugin files. These hooks enable you to add custom functionality, integrate with third-party services, modify the booking workflow, customize design settings, and enhance the user experience.

Common use cases include:
* Adding custom validation to booking forms
* Integrating with CRM systems and marketing tools
* Customizing email notifications and workflows
* Modifying calendar display and grid layouts
* Extending design customization options
* Implementing custom analytics tracking
* Adding promotional content and badges

**Developer Reference Guide:** [CodoBookings Hooks and Filters - Complete Developer Guide](https://wpdemo.codoplex.com/codobookings/codobookings-hooks-and-filters-complete-developer-guide-2026/)

The reference guide includes detailed explanations and working code examples for every hook, organized by functionality: dashboard hooks, calendar display hooks, booking process hooks, design customization hooks, styling hooks, and JavaScript hooks.

---

== Planned Extensions ==

* **PMPro Integration** – Restrict or enable bookings based on membership level. *(Coming soon)*
* **WooCommerce Integration** – Sell bookings as WooCommerce products with a full checkout flow. *(Coming soon)*
* **Google Calendar Sync** – Sync bookings with Google Calendar. *(Coming soon)*
* **Custom Email Templates** – Create branded, customizable email templates. *(Coming soon)*

---

== License ==

This plugin is licensed under the GPLv2 or later license.  
You are free to use, modify, and redistribute it under the same license.

---

== Author & Links ==

**Author:** CodoBookings Team – [Codoplex](https://codoplex.com)  
**Demo:** [https://wpdemo.codoplex.com/codobookings/demo/](https://wpdemo.codoplex.com/codobookings/demo/)  
**Support:** [https://care.codoplex.com/](https://care.codoplex.com/)