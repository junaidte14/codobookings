# CodoBookings

**Contributors:** junaidte14  
**Tags:** bookings, appointments, calendar, scheduling, standalone, pmpro-extension, woocommerce, google-calendar  
**Requires at least:** 6.0  
**Tested up to:** 6.9  
**Requires PHP:** 7.4  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

---

## 🎯 Description

**CodoBookings** is a lightweight yet powerful **WordPress booking management plugin** designed for developers and site owners who want complete flexibility.  

Future extensions (coming soon) will seamlessly integrate with:
- 🧩 **Paid Memberships Pro** (membership-based bookings)
- 🛒 **WooCommerce** (sell bookings as products)
- 📅 **Google Calendar** (sync bookings with personal or business calendars)
- 💬 **Email & Notifications** (customized reminders, confirmations, and admin alerts)

---

## 🚀 Key Features

✅ **Standalone Booking System** – Manage bookings and appointments directly from your WordPress admin.  
✅ **Custom Calendar UI** – Interactive weekly and monthly calendars with available slots and tooltips.  
✅ **Recurring Booking Support** – Handle weekly recurring slots with future-date logic.  
✅ **Booking Status Management** – Track pending, confirmed, and cancelled bookings.  
✅ **Admin Dashboard Widgets** – Quick overview of calendar and booking stats with helpful links.  
✅ **Extensible Architecture** – Built modularly, allowing clean integration of add-ons and third-party APIs.  
✅ **Optimized and Secure** – Uses nonces, prepared SQL statements, and follows WordPress coding standards.  

---

## 🧱 Planned Extensions

| Extension | Description | Status |
|------------|--------------|--------|
| **PMPro Integration** | Restrict or enable bookings based on membership level. | 🚧 Coming soon |
| **WooCommerce Integration** | Convert bookings into WooCommerce products with checkout flow. | 🚧 Coming soon |
| **Google Calendar Sync** | Allow users and admins to link and sync bookings to Google Calendar. | 🚧 Coming soon |
| **Email Templates** | Customizable email notifications for bookings, cancellations, and reminders. | 🚧 Coming soon |
---

## 🧩 Developer Hooks

CodoBookings provides several developer hooks to extend its behavior.

---

## 🛠 Installation

1. Download the plugin ZIP file or clone the repository.  
2. Upload the folder to `/wp-content/plugins/codobookings/`.  
3. Activate **CodoBookings** through the WordPress admin dashboard.  
4. Access the **CodoBookings** menu to manage calendars, bookings and settings.  

---

## 💡 Usage

Once activated:
- Manage calendars via **CodoBookings → Calendars** in admin.
- Manage bookings via **CodoBookings → Bookings** in admin.  
- Add or update slots using the booking calendar.  
- View booking summaries in the WordPress **Dashboard Widget**.  
- Extend functionality using future add-ons or custom hooks.

---

**Hooks Overview:**

CodoBookings provides 40+ action hooks and filter hooks that allow developers to extend and customize the booking system without modifying core plugin files. These hooks enable you to add custom functionality, integrate with third-party services, modify the booking workflow, customize design settings, and enhance the user experience.

Common use cases include:
- Adding custom validation to booking forms
- Integrating with CRM systems and marketing tools
- Customizing email notifications and workflows
- Modifying calendar display and grid layouts
- Extending design customization options
- Implementing custom analytics tracking
- Adding promotional content and badges

**Developer Reference Guide:** [CodoBookings Hooks and Filters - Complete Developer Guide](https://wpdemo.codoplex.com/codobookings/codobookings-hooks-and-filters-complete-developer-guide-2026/)

The reference guide includes detailed explanations and working code examples for every hook, organized by functionality: dashboard hooks, calendar display hooks, booking process hooks, design customization hooks, styling hooks, and JavaScript hooks.

---

## 📘 Changelog

### 1.3.0 - Features and Improvements
- NEW: Design Customization System - Customize your booking calendars to match your brand
- NEW: Theme Color Inheritance - Automatically detects and uses your WordPress theme colors
- NEW: Basic Design Settings - Control primary color, text color, and border radius
- NEW: Custom CSS Field - Add your own CSS for advanced customization
- NEW: CSS Variables Architecture - All styles use CSS custom properties for easy theming
- NEW: 15+ Extension Hooks - Extensive filter and action hooks for developers
- NEW: New Setting - Define default booking status
- IMPROVED: Frontend Styling - Removed all hardcoded colors and sizes
- IMPROVED: Sidebar Rendering - Dynamic styles now use CSS variables
- IMPROVED: Theme Integration - Better compatibility with any WordPress theme
- IMPROVED: Performance - Optimized CSS generation with intelligent caching
- IMPROVED: Extensibility - Clean separation for premium extensions
- FIXED: Hardcoded styles in JavaScript sidebar rendering
- FIXED: Calendar grid responsive behavior on mobile devices
- DEVELOPER: New design-focused hooks and filters for extensions
- DEVELOPER: Smart auto-color generation (secondary from primary, heading from text)

### 1.2.0 - Features and Improvements
- Added User Fields Extension link on dashboard page
- Updated the translations file

### 1.1.0 - Features and Improvements
- New hooks introduced in PHP code
- Some wording changes
- Hooks added in JS code for future extensibility
- Fixed confirmation message visibility

### 1.0.0 - Initial Release
- Standalone booking plugin core
- Admin dashboard widget for stats
- Booking list and management UI
- Recurring booking logic (weekly)
- Modular architecture for future extensions

---

## 🧑‍💻 Developer Notes

CodoBookings is structured for scalability:
- Each functional area resides in its own file under `/includes/`.
- Hooks and filters are available for extension developers.

---

## 🔗 Links

- **Website:** [https://codoplex.com](https://codoplex.com)

---

## 🧾 License

This plugin is licensed under the **GPLv2 or later** license.  
You are free to use, modify, and redistribute it under the same license.

---
