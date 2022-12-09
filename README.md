<p align="center">
	<a href="https://www.wp-pay.org/">
		<img src="https://www.wp-pay.org/assets/pronamic-pay.svgo-min.svg" alt="WordPress Pay » Core" width="72" height="72">
	</a>
</p>

<h1 align="center">WordPress Pay » Core</h3>

<p align="center">
	The WordPress payment processing library is intended to make payments and integrations with payment providers easier to set up and maintain within WordPress. It has similarities to libraries like https://github.com/Payum/Payum and https://github.com/thephpleague/omnipay, but is more focused on WordPress. The code complies with the WordPress Coding Standards and the WordPress APIs are used.
</p>

## Table of contents

- [Status](#status)
- [CLI](#cli)
- [WordPress Filters](#wordpress-filters)

## Status

[![Latest Stable Version](https://poser.pugx.org/wp-pay/core/v/stable.svg)](https://packagist.org/packages/wp-pay/core)
[![Total Downloads](https://poser.pugx.org/wp-pay/core/downloads.svg)](https://packagist.org/packages/wp-pay/core)
[![Latest Unstable Version](https://poser.pugx.org/wp-pay/core/v/unstable.svg)](https://packagist.org/packages/wp-pay/core)
[![License](https://poser.pugx.org/wp-pay/core/license.svg)](https://packagist.org/packages/wp-pay/core)
[![Built with Grunt](http://cdn.gruntjs.com/builtwith.svg)](http://gruntjs.com/)

## CLI

### Check pending payment status

```
wp pay payment status $( wp post list --field=ID --post_type=pronamic_payment --post_status=payment_pending --order=ASC --orderby=date --posts_per_page=100 --paged=1 )
```

## WordPress Filters

### pronamic_payment_gateway_configuration_id

```php
\add_filter(
	'pronamic_payment_gateway_configuration_id',
	/**
	 * Filter the payment gateway configuration ID to use specific 
	 * gateways for certain WooCommerce billing countries.
	 *
	 * @param int     $configuration_id Gateway configuration ID.
	 * @param Payment $payment          The payment resource data.
	 * @return int Gateway configuration ID.
	 */
	function( $configuration_id, $payment ) {
		if ( 'woocommerce' !== $payment->get_source() ) {
			return $configuration_id;
		}

		$billing_address = $payment->get_billing_address();

		if ( null === $billing_address ) {
			return $configuration_id;
		}

		$id = $configuration_id;

		switch ( $billing_address->get_country_code() ) {
			case 'US':
				$id = \get_option( 'custom_us_gateway_configuration_id', $id );
				break;
			case 'AU':
				$id = \get_option( 'custom_au_gateway_configuration_id', $id );
				break;
		}

		if ( 'pronamic_gateway' === \get_post_type( $id ) && 'publish' === \get_post_status( $id ) ) {
			$configuration_id = $id;
		}

		return $configuration_id;
	},
	10,
	2
);
```

### pronamic_gateway_configuration_display_value

```php
\add_filter(
	'pronamic_gateway_configuration_display_value',
	function( $display_value, $post_id ) {
		return \get_post_meta( $post_id, '_pronamic_gateway_display_value', true );
	},
	10,
	2
);
```

### pronamic_gateway_configuration_display_value_$id

```php
\add_filter(
	'pronamic_gateway_configuration_display_value_payvision',
	function( $display_value, $post_id ) {
		return \get_post_meta( $post_id, '_pronamic_gateway_payvision_business_id', true );
	},
	10,
	2
);
```

[![Pronamic - Work with us](https://github.com/pronamic/brand-resources/blob/main/banners/pronamic-work-with-us-leaderboard-728x90%404x.png)](https://www.pronamic.eu/contact/)
