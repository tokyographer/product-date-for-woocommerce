=== WooCommerce Product Date ===

Contributors: tokyographer
Tags: WooCommerce, Product Date, Retreat Start Date, REST API
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a custom "Retreat Start Date" field to WooCommerce products, including admin panel, cart, checkout, emails, and WooCommerce REST API.

== Description ==

This plugin allows you to set and manage a custom "Retreat Start Date" for WooCommerce products. The Retreat Start Date is displayed and integrated into:
- The WooCommerce product edit page.
- Quick Edit functionality for products.
- The WooCommerce cart and checkout pages.
- Order details, both in the admin and customer views.
- Emails sent to the customer and admin.
- WooCommerce REST API, including both product data and order line items.

New Features in Version 1.7:
- **Display in Cart, Checkout, and Emails**: The Retreat Start Date is displayed with the relevant product information.
- **WooCommerce REST API Integration**: The Retreat Start Date is available in the REST API for products and orders.
- **Product Categories in REST API**: Each line item in the WooCommerce REST API now includes the associated product categories.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Set the Retreat Start Date in the product edit screen under the "General" tab.

== Changelog ==

= 1.7 =
* Added product categories to WooCommerce REST API line items.
* Displayed Retreat Start Date in cart, checkout, and order emails.
* Integrated Retreat Start Date with WooCommerce REST API for products and orders.

= 1.6 =
* Added support for Quick Edit functionality.

= 1.5 =
* Initial release with Retreat Start Date field in the product edit page.

== Frequently Asked Questions ==

= How do I add a Retreat Start Date to my products? =
Go to the WooCommerce product edit page and find the "Retreat Start Date" field under the "General" tab.

= Can I see the Retreat Start Date in the REST API? =
Yes, the Retreat Start Date is included in both product and order data through the WooCommerce REST API.

= Can I use this for other custom fields? =
This plugin is specifically designed for Retreat Start Date functionality but can be adapted for similar use cases.