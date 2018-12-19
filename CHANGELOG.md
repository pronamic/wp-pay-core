# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
-

## [2.1.1] - 2018-12-19
- Fix incomplete payment customer from legacy meta.

## [2.1.0] - 2018-12-10
- Added support for payment lines.
- Store payment data as JSON.
- Added support for customer data in payment.
- Added support for billing and shipping address in payment.
- Added support for AfterPay payment methods.
- Added Capayable.
- Updated Tippy.js to version 3.3.0.
- Removed unused payment processing status.
- Added new WordPress 5.0 post type labels.

## [2.0.8] - 2018-09-28
- Added `get_meta()` method to core gateway config factory.
- Updated Tippy.js from 2.6.0 to 3.0.2.

## [2.0.7] - 2018-09-14
- Fixed issue with Flot dependency.

## [2.0.6] - 2018-09-14
- Use non-locale aware float values in data stores and Items amount calculation.
- Updated Tippy.js from version 2.5.4 to 2.6.0.

## [2.0.5] - 2018-09-12
- Set default status of new payments to 'Open'.
- Added a personal name class.
- Use empty issuers array by default, instead of null.
- Introduced a private `complement_payment` function in preparation for removal of the payment data interface constructions.
- Deprecated unused `has_feedback` and `amount_minimum`.
- Moved `pronamic_pay_plugin()` to core functions.

## [2.0.4] - 2018-08-28
- New payments with amount equal to 0 (or empty) will now directly get the completed status.
- Use PHP BCMath library for money calculations when available.

## [2.0.3] - 2018-08-16
- Use pronamic/wp-money library to parse money strings.
- Added Maestro to list of payment methods.

## [2.0.2] - 2018-06-21
- Removed version and extensions from the plugin class, is now part of the arguments array.
- Added support for WordPress core privacy export and erasure feature.

## [2.0.1] - 2018-06-01
- Moved all Pronamic Pay plugin classes to this core library.

## [2.0.0] - 2018-05-09
- Switched to PHP namespaces.

## [1.3.14] - 2017-12-12
- Improved direct debit payment method support and add helper methods.

## [1.3.13] - 2017-09-14
- Added support for credit card issuers.
- Added bunq payment method constant.
- Added `Direct Debit mandate via Bancontact` payment method constant and name.
- Added Bunq payment method name and use permanent URL to news article.
- Changed HTML/CSS class of pay button.

## [1.3.12] - 2017-03-15
- Make sure payment methods are stored as array in transient.

## [1.3.11] - 2017-01-25
- Added new constant for the KBC/CBC Payment Button payment method.
- Added new constant for the Belfius Direct Net payment method.

## [1.3.10] - 2016-11-16
- Added new constant for the Maestro payment method.

## [1.3.9] - 2016-10-20
- Added some helper functions for mandates.

## [1.3.8] - 2016-07-06
- Changed order of payment methods (alphabetic).
- Added Bancontact payment constant to payments methods getter function.
- Added PayPal payment constant to payments methods getter function.
- Renamed 'Bancontact/Mister Cash' to 'Bancontact'.

## [1.3.7] - 2016-06-08
- Added PayPal payment method constant.
- Simplified the gateay payment start function.
- Added new constant for Bancontact payment method.
- Fixed text domain for translations.

## [1.3.6] - 2016-04-29
- Set payment method choice key for iDEAL only gateways.

## [1.3.5] - 2016-03-22
- Add Pronamic_WP_Pay_GatewaySettings::save_post() to modify data when a gateway is saved.

## [1.3.4] - 2016-03-02
- Use the new get_gateway_class() function which is new on the config objects.

## [1.3.3] - 2016-02-04
- Readded the MiniTix payment method constant for backwards compatibility.

## [1.3.2] - 2016-02-02
- Make sure to look to parent config class in the gateway factory.

## [1.3.1] - 2016-01-22
- Also try the parent class to fix issue with extended config.
- Improved the Pronamic_WP_Pay_Util::string_to_amount() function.
- Removed discontinued MiniTix gateway.

## [1.3.0] - 2016-01-07
- Added an gateway settings class.
- Added support for payment methods.
- Added utility to convert an amount from user input to float.

## [1.2.3] - 2015-10-19
- Added `get_payment_method()` and `set_payment_method()` function on gateway class.

## [1.2.2] - 2015-10-15
- Add payment method 'Bank transfer'.

## [1.2.1] - 2015-04-29
- Added XML utility class.

## [1.2.0] - 2015-03-26
- Added default filter to server variables get function.
- Allow gateways to return array with output fields in stead of HTML.

## [1.1.0] - 2015-02-27
- Added helper class for retrieving $_SERVER values.
- Added helper class to check of class method exists.

## [1.0.1] - 2015-02-16
- Added constant for the SOFORT Banking payment method.

## 1.0.0
- First release.

[unreleased]: https://github.com/wp-pay/core/compare/2.1.0...HEAD
[2.1.0]: https://github.com/wp-pay/core/compare/2.0.8...2.1.0
[2.0.8]: https://github.com/wp-pay/core/compare/2.0.7...2.0.8
[2.0.7]: https://github.com/wp-pay/core/compare/2.0.6...2.0.7
[2.0.6]: https://github.com/wp-pay/core/compare/2.0.5...2.0.6
[2.0.5]: https://github.com/wp-pay/core/compare/2.0.4...2.0.5
[2.0.4]: https://github.com/wp-pay/core/compare/2.0.3...2.0.4
[2.0.3]: https://github.com/wp-pay/core/compare/2.0.2...2.0.3
[2.0.2]: https://github.com/wp-pay/core/compare/2.0.1...2.0.2
[2.0.1]: https://github.com/wp-pay/core/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/wp-pay/core/compare/1.3.14...2.0.0
[1.3.14]: https://github.com/wp-pay/core/compare/1.3.13...1.3.14
[1.3.13]: https://github.com/wp-pay/core/compare/1.3.12...1.3.13
[1.3.12]: https://github.com/wp-pay/core/compare/1.3.11...1.3.12
[1.3.11]: https://github.com/wp-pay/core/compare/1.3.10...1.3.11
[1.3.10]: https://github.com/wp-pay/core/compare/1.3.9...1.3.10
[1.3.9]: https://github.com/wp-pay/core/compare/1.3.8...1.3.9
[1.3.8]: https://github.com/wp-pay/core/compare/1.3.7...1.3.8
[1.3.7]: https://github.com/wp-pay/core/compare/1.3.6...1.3.7
[1.3.6]: https://github.com/wp-pay/core/compare/1.3.5...1.3.6
[1.3.5]: https://github.com/wp-pay/core/compare/1.3.4...1.3.5
[1.3.4]: https://github.com/wp-pay/core/compare/1.3.3...1.3.4
[1.3.3]: https://github.com/wp-pay/core/compare/1.3.2...1.3.3
[1.3.2]: https://github.com/wp-pay/core/compare/1.3.1...1.3.2
[1.3.1]: https://github.com/wp-pay/core/compare/1.3.0...1.3.1
[1.3.0]: https://github.com/wp-pay/core/compare/1.2.3...1.3.0
[1.2.3]: https://github.com/wp-pay/core/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/wp-pay/core/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/wp-pay/core/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/wp-pay/core/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/wp-pay/core/compare/1.0.1...1.1.0
[1.0.1]: https://github.com/wp-pay/core/compare/1.0.0...1.0.1
