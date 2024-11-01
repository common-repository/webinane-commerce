=== WP Commerce ===

Contributors: wptech
Donate link: https://webinane.com/
Tags: ecommerce, payment gateways, paypal
Requires at least: 5.0
Tested up to: 5.5
Requires PHP: 7.0
Stable tag: 2.2.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


WP Commerce is developed using latest technologies, vuejs, pure javascript, PHP 7.x.

== Description ==

WP Commerce is a free, open-source WordPress plugin with an extendable, developer-friendly framework. It offers an all-out eCommerce solution for all your business needs – small and big, simple and extraordinary.  
WP Commerce has been developed using the latest technologies, Vue.js, Pure JavaScript, and PHP 7.0.x. It can be used for all online businesses, ranging from selling digital or physical products to running a drop-shipping business to raising funds and collecting (automated, recurring) donations.  
WP Commerce’s fully customizable and extendable framework allows developers to create extensions like payment gateways, donation systems, hotel bookings, appointments, reservations, etc. It has several useful hooks that help to develop extensions with ease.


### Key Features:
-	Payment gateways integrated including PayPal Standard.
-	Complete set of shopping-support pages – Checkout, User Profile, Cart, etc.
-	User Account Profile with ‘order/purchase history’.
-	Put “Add to Cart” button anywhere.
-	Light-weight for faster loading.
-	Translation support – translate into any language.
-	Safe and secure.

### Special Features:
-	Incredibly extension-friendly.
-	Lots of developer hooks to easily & quickly develop extensions and add-ons.
-	Create and integrate payment gateways, donation systems, hotel bookings, appointments, reservations, and so on.

### Compatible Themes
- [Lifeline 2](https://themeforest.net/item/lifeline-2-an-ultimate-nonprofit-wordpress-theme-for-charity-fundraising-and-ngo-organizations/16660958)
- [Actavista](https://themeforest.net/item/actavista-a-responsive-wordpress-theme-for-politicians-and-political-organizations/23221878)
- [Deeds2](https://themeforest.net/item/deeds2-religion-and-church-wordpress-theme/24756421)

### Extensions
1- PayPal Standard (Included)
2- PayPal Express (Pro Extension)
3- Stripe for Credit Cards, SEPA, iDEAL ([Pro Extension](https://codecanyon.net/item/stripe-payment-gateway-for-lifeline-donations/24447315))
4- Braintree for Credit Cards (Pro Extension)
5- Amazon Pay (Pro Extension)
6- Authorize.net (Pro Extension)
7- 2Checkout (Pro Extension)
8- PayStack (Pro Extension)
9- PayUMoney (Pro Extension)
10- PayMaster for Russian (Pro Extension)
11- Yandex for Russian (Pro Extension)

### WP Commerce Basics

After the activation of the plugin, you’ll have to create some pages and place the following shortcodes:


- My Account `[wpcm_my_account]`  
- Checkout `[wpcm_checkout]`  
- Order Results `[wpcm_order_success]`  


You can add the following code manually where you want to show the "Add to Cart" button.


`<?php echo do_shortcode('[wpcm_add_to_cart_button item_id="POST_ID" quantity="1" price="20" class="btn"]Add to Cart[/wpcm_add_to_cart_button]'); ?>`



Replace **POST_ID**  with post, page or custom post ID.

After creating the above pages you'll have to select these pages from WP Commerce Settings > Display Settings.



WP Commerce comes with PayPal Standard gateway to accept the payments. If you are looking for more gateways then visit https://webinane.com . 

You'll have to fill out all the fields require for PayPal Standard, so it works smoothly.

### Available Extensions/Add-ons
- If you are looking for “Donations” extension for WP Commerce, please visit [Lifeline Donations WP Commerce Extension](https://wordpress.org/plugins/lifeline-donation)

### Payment Gateways
10+ Payment Gateways Coming Soon

### Having issues
You can post issues to [https://gitlab.com/webinane/webinane-commerce/-/issues](https://gitlab.com/webinane/webinane-commerce/-/issues)

== Installation ==


= Minimum Requirements =


* PHP 7.2 or greater is recommended  
* MySQL 5.6 or greater is recommended  



This section describes how to install the plugin and get it working. 

e.g.



1. Upload `webinane-commerce.zip` to the `/wp-content/plugins/` directory

1. Activate the plugin through the 'Plugins' menu in WordPress

1. Create required pages mentioned [above](#after-activation-of-the-plugin-youll-have-to-create-some-pages-and-place-the-following-shortcodes)

1. Choose those pages from WP Commerce settings.

1. Now Add shortcode `[wpcm_add_to_cart_button item_id="POST_ID" quantity="1" price="20" class="btn"]Add to Cart[/wpcm_add_to_cart_button]` where you want to "show add to cart" button



== Frequently Asked Questions ==


= Getting error on activation - Unable to save session =
Please check that if your host set a correct path to "save_session_path" . Because the path is incorrect and system tried to store session in /wp-content/uploads/

Secondly your uploads directory  is restricted and don't allow to create any file directly in uploads directory. Please set the permission of that directory to 0775.

If you want to fix it at your own from .htaccess file, please add the following line at the top of the file.

`php_value session.save_path '/tmp'`

= How to setup PayPal Standard ? =

Go to your [PayPal](https://paypal.com) account > Settings > My Selling Tools , in the **Selling Online** section, click **Update** for **API Access** item.


= How to setup IPN URL ? =


Go to your [PayPal](https://paypal.com) account > Settings > Automatic Notification and there you'll be able to see **Enable IPN**. You must enter URL like http://example.com/order-success-page/?type=notify&gateway=paypal

= WP Commerce will work with my theme ? =

Yes, we have designed it in a way that it should not conflict with any styles and JavaScript. But if you want to enhance the look and feel, you might have to work a little with CSS.

= Where can I find the developer documentation? =

You can find the detailed documentation about developing extensions and payment gateways [http://plugins.webinane.com/docs/WP-commerce-documentation/](http://plugins.webinane.com/docs/WP-commerce-documentation/)

== Credits ==
- The plugin uses vuejs components from [Element UI](https://element.eleme.io/#/en-US/component/quickstart)

== Screenshots ==

1. General Settings page.  
2. Orders page  
3. In Admin orders, Add new item popup  
4. Frontend user checkout page.  
5. Frontend user Account page.  

== More about WP Commerce ==

-	Translation Ready: WP Commerce comes with translation option, so user can translate it into any language.
-	Tested for Safety and Security: WP Commerce is tested with major WordPress versions, PHP 7.1, 7.2 and 7.3. WP Commerce is fully secured and tested for all XSS security issues.
-	Developer Friendly: WP Commerce is developer friendly. We invite developers to join the community and develop extensions that are demanding.



== Changelog ==
**Version 2.2.3**
- Added: Settings to manage email Templates
- Fixed: Session class issue on Order item update on checkout page.
- Added: Option to show / hide states in settings fields.
- Added: Taxonomy metaboxes
- Added: function to get states.

**Version 2.2.2**
- Fixed: Email templates issues.
- Fixed: Misc issues.

**Version 2.2.1**
- Fixed: add new order issue in admin.
- Added: New field management system.
- Fixed: Increased the transient time for API Token
- Fixed: In My Account Component added "Order" filter.

**Version 2.2.0**
- Fixed: My account avatar image upload issue is fixed.
- Fixed: Issue with `get_items_total()` is fixed.
- Added: New design for Settings.

**Version 2.1.1**
- Fixed: My account avatar image upload issue is fixed.
- Fixed: Issue with `get_items_total()` is fixed.

**Version 2.1.0**
- Added: Charts and statistics dashboard
- Added: Orders Export
- Added: Marketplace connect, view all extensions and download extensions

**Version 2.0.4**
- Added: Built-in Dashbaord for charts and statistics.
- Added: Extensions menu
- Added: Connect to connect with webinane.com account and downlaod extensions.

**Version 2.0.4**
- Fixed: Network active plugin custom tables creation.
- Fixed: Add extra columns in order_items table to maintain multi currencies.
- Added: Filter to convert curreny into base currency value.
- Fixed: Login / Register forms on my account page.
- Added: Displayed recurring Yes or No in admin orders.
- Fixed: issue with PayPal recurring payment period.

**Version 2.0.3**
- Fixed: Missing total value in Customer email template.
- Fixed: On success page email sending error.

**Version 2.0.2**
- Fixed: Compatibilities issues with lifeline donation.

**Version 2.0.1**
- Fixed: Issues with case senstive files and directories

**Version 2.0**
- Improved: This version has broken changes, must update dependent plugins before updating it.
- Improved: Loading speed, reducing javascript files size.
- Fixed: Compatible with php 7.x

**Version 1.0.8.3**
- Fixed: PayPal Standard recurring payment issue are fixed.

**Version 1.0.8.1**
- Fixed: Decimal places issues in prices.

**Version 1.0.8**
- Fixed: Admin Order amount/price formatting
- Fixed: PHP Notice in WP Menus.

**Version 1.0.7.4**
- Fixed: Admin Orders copy billing.
- Fixed: Compatibility issues

**Version 1.0.7.3**
- Fixed: Issue with PayPal agreement on single payment.
- Fixed: Element UI css issues.
- Removed: Unused files to reduce the plugin size.

**Version 1.0.7.2**
- Fixed: Currency symbol issue in admin order items.

**Version 1.0.7.1**
- Fixed: Issues with collection()->get() method with dot notation.

**Version 1.0.7**
- Fixed: Proper email templates for new orders.

**Version 1.0.6**
- Improved: checkout page layout.
- Fixed: issues with cookies, must clear all cookie to work it.
- Fixed: Session handling issues.
- Fixed: Email template for buyer and owner.
- Improved: Used WP Eloquent to improve the Customer queries.
- Added: New field type "image"
- Removed: wp_enqueue_media on frontend.
- Added: Options in Orders to add new customer or remove existing
- Improved: In admin orders on change customer, updated its billing and shipping data
- Added: Hooks to override "Orders" string with "Donations" or something else.


**Version 1.0.5**
- Fixed: Issue with customer table creation on plugin activation.
- Fixed: Setcookie globally to domain and all subdomains.

**Version 1.0.4**
- Fixed: Currency formatting with Currency separator issue is fixed.
- Fixed: Order admin menu label filter added for plugins to rename orders label.
- Fixed: Offline gateway payment instructions printed on order success page.

**Version 1.0.3**
- Fixed: Issue with Payment gateways not visible on checkout page.

**Version 1.0.2**
- Fixed: Currency converter function issue.
- Fixed: Saving settings boolean convert to string.

**Version 1.0.1**
- Fixed: Abstract Email file issue is fixed.

**Version 1.0.0**
- Fixed: Default pages creation issue fixed.

**Version 0.9.3**
- Improved: On Checkout page, place order button is improved.
- Fixed: Issue with multi-select dependency is fixed.

**Version 0.9.2**
- Improved: Add to cart button - Added custom loading icon.
- Added: Option to redirect to checkout page after add to cart.
- Added: new field type Multi Select (wpcm-multi-select).
- Added: new field type Color (wpcm-color).
- Fixed: Fields dependency for select, multi select and number fields.
- Updated: Language file updated including new strings.
- Fixed: Issues with dropdown fields are fixed.
- Fixed: Create default pages on plugin activation.

**Version 0.9.1**
- Fixed issues in class-wpcm-settings.php array_merge $settings is not an array.

**Version 0.9.0 **
- Fixed issues Vuejs enqueue url path.

**Version 0.8.0**
- Fixed issues with VUE Element-UI locales.

**Version 0.7.0**  
- Fixed issues with offline payment gateway.
- Updated wpcm_get_active_gateways() function.
- Added fields datepicker and timepicker for metaboxes.
- Fixed issues while loading empty WP Settings ( new stdClass with new \stdClass )
- Fixed issues with ajaxurl js variable on frontend.
- Loaded popper where needed.
- Vuejs development message in console is fixed.
- Element UI locale issue is fixed.
- Dropdown selection issue is fixed while adding new Cause or project.

**Version 0.6.0**  
- In checkout fixed issues call wrong Customers class.
- Misc issues are fixed.

**Version 0.3.0**  
- Frontend user profile save issue is fixed.
- Admin Dashboard widget fetal error issue is fixed.
- Admin "WP Commerce" gateway setting issue is fixed.
- Offline gateway PHP notice issue is fixed.

**Version 0.2.0**
- Issues fixed in plugin activation
**Version 0.1.0**

- First release.



== Upgrade Notice ==


= 0.1.0 =

No Notice

