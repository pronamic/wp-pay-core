# Change Log

All notable changes to this project will be documented in this file.

This projects adheres to [Semantic Versioning](http://semver.org/) and [Keep a CHANGELOG](http://keepachangelog.com/).

## [Unreleased][unreleased]
- 

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

[unreleased]: https://github.com/wp-pay/core/compare/1.3.12...HEAD
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
