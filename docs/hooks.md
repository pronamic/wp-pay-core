# Hooks

- [Actions](#actions)
- [Filters](#filters)

## Actions

### `pronamic_pay_update_payment`

*Payment updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 243](../src/Payments/PaymentsDataStoreCPT.php#L243-L248)

### `pronamic_pay_pre_create_payment`

*Pre-create payment.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 265](../src/Payments/PaymentsDataStoreCPT.php#L265-L270)

### `pronamic_pay_new_payment`

*New payment created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 300](../src/Payments/PaymentsDataStoreCPT.php#L300-L305)

### `pronamic_payment_status_update_{$source}_{$old_status}_to_{$new_status}`

*Payment status updated for plugin integration source from old to new status.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)
[`{$old_status}`](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status)
[`{$new_status}`](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
`$updated_status` | `null\|string` | Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 931](../src/Payments/PaymentsDataStoreCPT.php#L931-L943)

### `pronamic_payment_status_update_{$source}`

*Payment status updated for plugin integration source.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
`$updated_status` | `null\|string` | Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status)).

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 945](../src/Payments/PaymentsDataStoreCPT.php#L945-L955)

### `pronamic_payment_status_update`

*Payment status updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
`$updated_status` | `null\|string` | Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 957](../src/Payments/PaymentsDataStoreCPT.php#L957-L965)

### `pronamic_pay_privacy_register_exporters`

*Register privacy exporters.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$privacy_manager` | `\Pronamic\WordPress\Pay\PrivacyManager` | Privacy manager.

Source: [src/PrivacyManager.php](../src/PrivacyManager.php), [line 51](../src/PrivacyManager.php#L51-L56)

### `pronamic_pay_privacy_register_erasers`

*Register privacy erasers.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$privacy_manager` | `\Pronamic\WordPress\Pay\PrivacyManager` | Privacy manager.

Source: [src/PrivacyManager.php](../src/PrivacyManager.php), [line 75](../src/PrivacyManager.php#L75-L80)

### `pronamic_pay_license_check`

*Perform license check.*


Source: [src/Admin/AdminSettings.php](../src/Admin/AdminSettings.php), [line 332](../src/Admin/AdminSettings.php#L332-L335)

### `pronamic_pay_pre_create_subscription`

*Pre-create subscription.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 294](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L294-L299)

### `pronamic_pay_new_subscription`

*New subscription created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 331](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L331-L336)

### `pronamic_subscription_status_update_{$source}_{$old_status}_to_{$new_status}`

*Subscription status updated for plugin integration source from old to new status.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)
[`{$old_status}`](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status)
[`{$new_status}`](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
`$updated_status` | `null\|string` | Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 774](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L774-L786)

### `pronamic_subscription_status_update_{$source}`

*Subscription status updated for plugin integration source.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
`$updated_status` | `null\|string` | Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 788](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L788-L798)

### `pronamic_subscription_status_update`

*Subscription status updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
`$updated_status` | `null\|string` | Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 800](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L800-L808)

### `pronamic_subscription_renewal_notice_{$source}`

*Send renewal notice for source.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsNotificationsController.php](../src/Subscriptions/SubscriptionsNotificationsController.php), [line 253](../src/Subscriptions/SubscriptionsNotificationsController.php#L253-L260)

## Filters

### `pronamic_payment_redirect_url`

*Filters the payment return redirect URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 427](../src/Payments/Payment.php#L427-L433)

### `pronamic_payment_source_text_{$source}`

*Filters the payment source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 476](../src/Payments/Payment.php#L476-L482)

### `pronamic_payment_source_text`

*Filters the payment source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 485](../src/Payments/Payment.php#L485-L491)

### `pronamic_payment_source_description`

*Filters the payment source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 508](../src/Payments/Payment.php#L508-L514)

### `pronamic_payment_source_description_{$source}`

*Filters the payment source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 517](../src/Payments/Payment.php#L517-L523)

### `pronamic_payment_source_url`

*Filters the payment source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 541](../src/Payments/Payment.php#L541-L547)

### `pronamic_payment_source_url_{$source}`

*Filters the payment source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 550](../src/Payments/Payment.php#L550-L556)

### `pronamic_payment_provider_url`

*Filters the payment provider URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 572](../src/Payments/Payment.php#L572-L578)

### `pronamic_payment_provider_url_{$gateway_id}`

*Filters the payment provider URL by gateway identifier.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 593](../src/Payments/Payment.php#L593-L599)

### `pronamic_pay_google_analytics_ecommerce_item_name`

*Filters the item name for Google Analytics e-commerce tracking.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$name` | `string` | Item name.
`$line` | `\Pronamic\WordPress\Pay\Payments\PaymentLine` | Payment line.

Source: [src/GoogleAnalyticsEcommerce.php](../src/GoogleAnalyticsEcommerce.php), [line 214](../src/GoogleAnalyticsEcommerce.php#L214-L222)

### `pronamic_pay_google_analytics_ecommerce_item_product_category`

*Filters the product category for Google Analytics e-commerce tracking.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$product_category` | `string\|null` | Product category.
`$line` | `\Pronamic\WordPress\Pay\Payments\PaymentLine` | Payment line.

Source: [src/GoogleAnalyticsEcommerce.php](../src/GoogleAnalyticsEcommerce.php), [line 262](../src/GoogleAnalyticsEcommerce.php#L262-L270)

### `pronamic_pay_return_should_redirect`

*Filter whether to allow redirects on payment return.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$should_redirect` | `bool` | Flag to indicate if redirect is allowed on handling payment return.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 538](../src/Plugin.php#L538-L544)

### `pronamic_pay_gateways`

*Filters the gateway integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$gateways` | `\Pronamic\WordPress\Pay\AbstractGatewayIntegration[]` | Gateway integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 697](../src/Plugin.php#L697-L702)

### `pronamic_pay_plugin_integrations`

*Filters the plugin integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$plugin_integrations` | `\Pronamic\WordPress\Pay\AbstractPluginIntegration[]` | Plugin integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 712](../src/Plugin.php#L712-L717)

### `pronamic_payment_gateway_configuration_id`

*Filters the payment gateway configuration ID.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$config_id` | `null\|int` | Gateway configuration ID.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1132](../src/Plugin.php#L1132-L1138)

### `pronamic_payment_redirect_url_{$source}`

*Filters the payment redirect URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `string` | Redirect URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1381](../src/Plugin.php#L1381-L1387)

### `pronamic_gateway_configuration_display_value`

*Filters the gateway configuration display value.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$display_value` | `string` | Display value.
`$post_id` | `int` | Gateway configuration post ID.

Source: [src/Admin/AdminGatewayPostType.php](../src/Admin/AdminGatewayPostType.php), [line 132](../src/Admin/AdminGatewayPostType.php#L132-L138)

### `pronamic_gateway_configuration_display_value_{$id}`

*Filters the gateway configuration display value.*

The dynamic portion of the hook name, `$id`, refers to the gateway ID.
For example, the gateway ID for Payvision is `payvision`, so the filter
for that gateway would be:
`pronamic_gateway_configuration_display_value_payvision`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$display_value` | `string` | Display value.
`$post_id` | `int` | Gateway configuration post ID.

Source: [src/Admin/AdminGatewayPostType.php](../src/Admin/AdminGatewayPostType.php), [line 140](../src/Admin/AdminGatewayPostType.php#L140-L151)

### `pronamic_pay_removed_extension_notifications`

*Filters the removed extensions notifications.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$notifications` | `\Pronamic\WordPress\Pay\Admin\AdminNotification[]` | Notifications for removed extensions.

Source: [src/Admin/AdminNotices.php](../src/Admin/AdminNotices.php), [line 95](../src/Admin/AdminNotices.php#L95-L100)

### `pronamic_subscription_source_text_{$source}`

*Filters the subscription source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 175](../src/Subscriptions/Subscription.php#L175-L181)

### `pronamic_subscription_source_text`

*Filters the subscription source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 184](../src/Subscriptions/Subscription.php#L184-L190)

### `pronamic_subscription_source_description_{$source}`

*Filters the subscription source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 208](../src/Subscriptions/Subscription.php#L208-L214)

### `pronamic_subscription_source_description`

*Filters the subscription source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 217](../src/Subscriptions/Subscription.php#L217-L223)

### `pronamic_subscription_source_url`

*Filters the subscription source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 240](../src/Subscriptions/Subscription.php#L240-L246)

### `pronamic_subscription_source_url_{$source}`

*Filters the subscription source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 249](../src/Subscriptions/Subscription.php#L249-L255)

### `pronamic_pay_subscription_next_payment_delivery_date`

*Filters the subscription next payment delivery date.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$next_payment_delivery_date` | `\Pronamic\WordPress\DateTime\DateTimeImmutable` | Next payment delivery date.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 474](../src/Subscriptions/Subscription.php#L474-L482)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

