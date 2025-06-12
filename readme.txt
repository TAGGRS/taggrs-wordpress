=== TAGGRS Server-Side Integration ===
Contributors: TAGGRS
Tags: woocommerce, google analytics, ga4, server-side tracking, e-commerce analytics, enhanced conversions, user data, taggrs, server side tracking, server side tagging, google ads, conversion api, capi
Requires at least: 4.5
Tested up to: 6.4
Stable tag: 1.1.5
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrate Google Analytics 4 (GA4) with server-side capabilities into your WooCommerce store for enhanced e-commerce analytics and user behavior tracking.

== Description ==

This TAGGRS Server-Side Integration plugin seamlessly embeds Google Analytics 4 (GA4) data layer into your WooCommerce site, combining client-side and server-side tracking for a more comprehensive analysis of customer interactions. With this plugin, store owners gain deeper insights into their sales funnel and user behavior, enabling data-driven decision-making and improved store performance.

Features:
- Easy integration of GA4 with WooCommerce.
- Dual tracking: Combines client-side and server-side data collection.
- Enhanced data accuracy and reliability.
- Better privacy compliance.
- Enhanced Conversions.
- In-depth insights into customer journey and sales metrics via GA4.

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory and unzip it, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the TAGGRS screen in the menu bar to configure the plugin.
4. Enter your GTM code to link your Google Tag Manager account.

== Frequently Asked Questions ==

= Do I need a Google Tag Manager account to use this plugin? =

Yes, you need to have a GoogleTag Manager account and a GTM code to integrate the plugin with GA4.

= Do I need a TAGGRS account to use this plugin? =

No, its not required to have a TAGGRS account. We recommend it to get the most out of your tracking.

= Is this plugin compliant with privacy regulations like GDPR? =

The plugin adheres to privacy regulations by providing server-side tracking, which enhances data privacy and compliance.

== Third-Party Service Dependencies ==
= Google Tag Manager =
This TAGGRS Server-Side Integration plugin integrates with Google Tag Manager (GTM) for server-side tracking. This integration allows us to send data directly to GTM servers without requiring client-side scripting. This approach enhances data accuracy and privacy compliance.

= Why We Use Google Tag Manager =
We leverage Google Tag Manager due to its robust infrastructure and flexibility in handling server-side tracking. This enables our users to implement custom tracking solutions that meet their unique business needs while ensuring data is managed securely and efficiently.

= Data Transmission Disclosure =
Please be aware that by using this TAGGRS Server-Side Integration plugin, data will be transmitted to Google's servers as part of the tracking process. This may include, but is not limited to, user interactions, IP addresses, and other data you configure to track via GTM.

= Third-Party Links =
- **Google Tag Manager Service**: [Google Tag Manager](https://tagmanager.google.com/)
- **Terms of Use**: [Google Terms of Service](https://policies.google.com/terms)
- **Privacy Policy**: [Google Privacy Policy](https://policies.google.com/privacy)

= TAGGRS.io =
This TAGGRS Server-Side Integration plugin integrates with TAGGRS for server-side tracking. This integration allows us to send data directly to TAGGRS servers without requiring client-side scripting. This approach enhances data accuracy and privacy compliance.

= Why We Use TAGGRS =
We leverage TAGGRS due to its robust infrastructure and flexibility in handling server-side tracking. This enables our users to implement custom tracking solutions that meet their unique business needs while ensuring data is managed securely and efficiently.

= Data Transmission Disclosure =
Please be aware that by using this TAGGRS Server-Side Integration plugin, data will be transmitted to TAGGRS's servers as part of the tracking process. This may include, but is not limited to, user interactions, IP addresses, and other data you configure to track.

= Third-Party Links =
- **TAGGRS Service**: [TAGGRS](https://taggrs.io/)
- **Terms and Conditions**: [TAGGRS Terms and Conditions](https://taggrs.io/en/terms-and-conditions/)
- **Privacy Statement**: [TAGGRS Privacy Statement](https://taggrs.io/en/privacy-statement/)
- **Service level agreement**: [TAGGRS Terms and Conditions](https://taggrs.io/en/service-level-agreement/)

= Legal Compliance =
We advise all users to review Google Tag Manager's Terms of Service and Privacy Policy to ensure compliance with data protection and privacy laws applicable to your use case. It is your responsibility to adhere to any legal requirements pertaining to data collection and processing activities conducted through GTM.

== Changelog ==

= 1.0.5 =
- Removed unnecessary navigation tabs in admin page

= 1.0.4 =
- Improved script robustness in the remove_from_cart event.

= 1.0.3 =
- Added TAGGRS Logo to the admins include files.
- Changed all function names to "tggr_".
- Changed versions in documents.
- Added TAGGRS third party usage notice.
- Added tggr_format_item & tggr_format_cart_items for consistency

= 1.0.2 =
- Added categories to ad_to_cart AJAX event.

= 1.0.1 =
- Update licenses to GPLv2
- Included TAGGRS logo, limiting external dependencies
- Updated required version
- Replaced short php codes to full php codes.
- Documented use of Google Tag Manager
- Properly escaped all echos
- Added prefix to all functions "tggr"
- Made all files non-directly accessible

= 1.0.0 =
- Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release. Please backup your website before installing.
