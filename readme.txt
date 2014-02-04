=== EDD CF7 Register Guest Customers ===
Author URI: http://isabelcastillo.com
Plugin URI: http://isabelcastillo.com/docs/category/edd-cf7-register-guest-customers
Contributors: isabel104
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=me%40isabelcastillo%2ecom
Tags: EDD, CF7, register, guest, guest checkout, registration, Contact Form 7, guest customers, customer, easy digital downloads, custom regisration, wpcf7
Requires at least: 3.6
Tested up to: 3.8.1
Stable Tag: 0.4.7
License: GNU Version 2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Register EDD guest customers with Contact Form 7 custom registration, disable registration for everyone else.

== Description ==

EDD CF7 Register Guest Buyer is an extension for 2 WordPress plugins:

1. [Easy Digital Downloads](http://wordpress.org/plugins/easy-digital-downloads/)
2. [Contact Form 7](http://wordpress.org/plugins/contact-form-7/)

**Key Features:**

- Let only customers register on your site
- Disable registration for everyone else
- Very lightweight, no .js or .css files
- Automatically sends polite rejection messages to non-customers
- Automatically sends email and password to new registrants
- Give guest customers access to their order history


The purpose of this plugin is to restrict registration on your site only to customers who have bought something on your Easy Digital Downloads site. It’s accomplished by using a custom registration form from Contact Form 7 and the Easy Digital Downloads API.

This plugin is useful if you allow Guest Checkout in your Easy Digital Downloads store, and you also disable registration on your site. This is the case if you want to allow only customers to register – you must disable site-wide registration.

If you allow guest purchases, those guests will eventually return to your site wanting to access areas of your site that are only for registered users (such as support or account history). If you have disabled registration, they will not be able to register and they will be annoyed. But, if you allow the WordPress default registration, then anyone can register on your site.

So, this plugin lets visitors submit a custom Registration form, but will only register those that have made a guest purchase. Free purchases do count as purchases. All others will receive a polite rejection message asking them to register with the email that they used during guest checkout. Unless the visitor happens to have an existing user account, in which case they will receive a kind reminder of their username.

This plugin is **very lightweight**, consisting of only 1 file. It does not load any `.js` or `.css` files. It will not slow your sight down. It’s perfect if you already use Contact Form 7.

**Localization**

The `.pot` file is included to make it easy for you to translate into other languages.

To see the logic for this plugin’s registration process, see: [The Registration Process](http://isabelcastillo.com/docs/the-registration-process)

For more info, see the [Documentation](http://isabelcastillo.com/docs/category/edd-cf7-register-guest-customers).

Fork on [Github](https://github.com/isabelc/EDD-CF7-Register-Guest-Customers).

== Installation ==

1. Unzip `edd-cf7-register-guest-customers.zip` directly into the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to `Downloads --> Settings --> Extensions` tab to enter your EDD public API key and token. You can get your key and token in your WordPress dashboard -> Users -> Your Profile. Check the box at the bottom that says "Generate API keys". Click "Update Profile".
4. To complete the setup, please see [Setup Steps](http://isabelcastillo.com/docs/set-up-the-plugin)
== Frequently Asked Questions ==

None yet.

== Changelog ==

= 0.4.7 =
* Bug fix: do not register guest customers when their order is only PENDING.
* Tweak: added check for duplicate email address before inserting user, in case guest email address is in use by a different user.
* Tweak: added check for rare case in which another user with a different email address may be using this guest customer email adress only as a userlogin.
* Tweak: delete option upon plugin deactivation
* Maintenance: updated .pot file.

= 0.4.6 =
* Bug fix: prevent duplicate email per minute, not per day.
* Bug fix: email headers From name had line break.
* New: added .pot file for localization.
* Tweaks: fixed a couple of localization errors.

= 0.4.5 =
* Initial release.
== Upgrade Notice ==

= 0.4.7 =
Bug fix: do not register guest customers when their order is only PENDING.