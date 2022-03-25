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

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 242](../src/Payments/PaymentsDataStoreCPT.php#L242-L247)

### `pronamic_pay_pre_create_payment`

*Pre-create payment.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 263](../src/Payments/PaymentsDataStoreCPT.php#L263-L268)

### `pronamic_pay_new_payment`

*New payment created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 296](../src/Payments/PaymentsDataStoreCPT.php#L296-L301)

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
`$updated_status` | `string` | Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 917](../src/Payments/PaymentsDataStoreCPT.php#L917-L929)

### `pronamic_payment_status_update_{$source}`

*Payment status updated for plugin integration source.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
`$updated_status` | `string` | Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status)).

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 931](../src/Payments/PaymentsDataStoreCPT.php#L931-L941)

### `pronamic_payment_status_update`

*Payment status updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
`$updated_status` | `string` | Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 943](../src/Payments/PaymentsDataStoreCPT.php#L943-L951)

### `pronamic_pay_privacy_register_exporters`

*Register privacy exporters.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$privacy_manager` | `\Pronamic\WordPress\Pay\PrivacyManager` | Privacy manager.

Source: [src/PrivacyManager.php](../src/PrivacyManager.php), [line 54](../src/PrivacyManager.php#L54-L59)

### `pronamic_pay_privacy_register_erasers`

*Register privacy erasers.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$privacy_manager` | `\Pronamic\WordPress\Pay\PrivacyManager` | Privacy manager.

Source: [src/PrivacyManager.php](../src/PrivacyManager.php), [line 77](../src/PrivacyManager.php#L77-L82)

### `pronamic_pay_license_check`

*Perform license check.*


Source: [src/Admin/AdminSettings.php](../src/Admin/AdminSettings.php), [line 304](../src/Admin/AdminSettings.php#L304-L307)

### `pronamic_pay_pre_create_subscription`

*Pre-create subscription.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 260](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L260-L265)

### `pronamic_pay_new_subscription`

*New subscription created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 289](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L289-L294)

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
`$updated_status` | `string` | Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 725](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L725-L737)

### `pronamic_subscription_status_update_{$source}`

*Subscription status updated for plugin integration source.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
`$updated_status` | `string` | Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 739](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L739-L749)

### `pronamic_subscription_status_update`

*Subscription status updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
`$updated_status` | `string` | Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 751](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L751-L759)

### `pronamic_subscription_renewal_notice_{$source}`

*Send renewal notice for source.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsNotificationsController.php](../src/Subscriptions/SubscriptionsNotificationsController.php), [line 247](../src/Subscriptions/SubscriptionsNotificationsController.php#L247-L254)

## Filters

### `pronamic_payment_redirect_url`

*Filters the payment return redirect URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 384](../src/Payments/Payment.php#L384-L390)

### `pronamic_payment_source_text_{$source}`

*Filters the payment source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 434](../src/Payments/Payment.php#L434-L440)

### `pronamic_payment_source_text`

*Filters the payment source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 443](../src/Payments/Payment.php#L443-L449)

### `pronamic_payment_source_description`

*Filters the payment source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 466](../src/Payments/Payment.php#L466-L472)

### `pronamic_payment_source_description_{$source}`

*Filters the payment source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 475](../src/Payments/Payment.php#L475-L481)

### `pronamic_payment_source_url`

*Filters the payment source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 499](../src/Payments/Payment.php#L499-L505)

### `pronamic_payment_source_url_{$source}`

*Filters the payment source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 508](../src/Payments/Payment.php#L508-L514)

### `pronamic_payment_provider_url`

*Filters the payment provider URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 530](../src/Payments/Payment.php#L530-L536)

### `pronamic_payment_provider_url_{$gateway_id}`

*Filters the payment provider URL by gateway identifier.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 551](../src/Payments/Payment.php#L551-L557)

### `pronamic_pay_google_analytics_ecommerce_item_name`

*Filters the item name for Google Analytics e-commerce tracking.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$name` | `string` | Item name.
`$line` | `\Pronamic\WordPress\Pay\Payments\PaymentLine` | Payment line.

Source: [src/GoogleAnalyticsEcommerce.php](../src/GoogleAnalyticsEcommerce.php), [line 215](../src/GoogleAnalyticsEcommerce.php#L215-L222)

### `pronamic_pay_google_analytics_ecommerce_item_product_category`

*Filters the product category for Google Analytics e-commerce tracking.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$product_category` | `string` | Product category.
`$line` | `\Pronamic\WordPress\Pay\Payments\PaymentLine` | Payment line.

Source: [src/GoogleAnalyticsEcommerce.php](../src/GoogleAnalyticsEcommerce.php), [line 262](../src/GoogleAnalyticsEcommerce.php#L262-L269)

### `pronamic_pay_return_should_redirect`

*Filter whether or not to allow redirects on payment return.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$should_redirect` | `bool` | Flag to indicate if redirect is allowed on handling payment return.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 462](../src/Plugin.php#L462-L468)

### `pronamic_pay_gateways`

*Filters the gateway integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$gateways` | `\Pronamic\WordPress\Pay\AbstractGatewayIntegration[]` | Gateway integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 622](../src/Plugin.php#L622-L627)

### `pronamic_pay_plugin_integrations`

*Filters the plugin integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$plugin_integrations` | `\Pronamic\WordPress\Pay\AbstractPluginIntegration[]` | Plugin integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 637](../src/Plugin.php#L637-L642)

### `pronamic_payment_gateway_configuration_id`

*Filters the payment gateway configuration ID.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$config_id` | `null\|int` | Gateway configuration ID.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1019](../src/Plugin.php#L1019-L1025)

### `pronamic_payment_redirect_url_{$source}`

*Filters the payment redirect URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Redirect URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1201](../src/Plugin.php#L1201-L1207)

### `pronamic_gateway_configuration_display_value`

*Filters the gateway configuration display value.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$display_value` | `string` | Display value.
`$post_id` | `int` | Gateway configuration post ID.

Source: [src/Admin/AdminGatewayPostType.php](../src/Admin/AdminGatewayPostType.php), [line 139](../src/Admin/AdminGatewayPostType.php#L139-L145)

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

Source: [src/Admin/AdminGatewayPostType.php](../src/Admin/AdminGatewayPostType.php), [line 147](../src/Admin/AdminGatewayPostType.php#L147-L158)

### `pronamic_pay_removed_extension_notifications`

*Filters the removed extensions notifications.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$notifications` | `\Pronamic\WordPress\Pay\Admin\AdminNotification[]` | Notifications for removed extensions.

Source: [src/Admin/AdminNotices.php](../src/Admin/AdminNotices.php), [line 106](../src/Admin/AdminNotices.php#L106-L111)

### `pronamic_subscription_source_text_{$source}`

*Filters the subscription source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 157](../src/Subscriptions/Subscription.php#L157-L163)

### `pronamic_subscription_source_text`

*Filters the subscription source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 166](../src/Subscriptions/Subscription.php#L166-L172)

### `pronamic_subscription_source_description_{$source}`

*Filters the subscription source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 190](../src/Subscriptions/Subscription.php#L190-L196)

### `pronamic_subscription_source_description`

*Filters the subscription source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 199](../src/Subscriptions/Subscription.php#L199-L205)

### `pronamic_subscription_source_url`

*Filters the subscription source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 222](../src/Subscriptions/Subscription.php#L222-L228)

### `pronamic_subscription_source_url_{$source}`

*Filters the subscription source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 231](../src/Subscriptions/Subscription.php#L231-L237)

### `pronamic_pay_subscription_next_payment_delivery_date`

*Filters the subscription next payment delivery date.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$next_payment_delivery_date` | `\Pronamic\WordPress\DateTime\DateTimeImmutable` | Next payment delivery date.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 443](../src/Subscriptions/Subscription.php#L443-L450)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.1.1</code></em><p>

