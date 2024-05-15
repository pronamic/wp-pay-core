# Hooks

- [Actions](#actions)
- [Filters](#filters)

## Actions

### `pronamic_pay_pre_create_payment`

*Pre-create payment.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 190](../src/Payments/PaymentsDataStoreCPT.php#L190-L195)

### `pronamic_pay_new_payment`

*New payment created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 226](../src/Payments/PaymentsDataStoreCPT.php#L226-L231)

### `pronamic_pay_update_payment`

*Payment updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 293](../src/Payments/PaymentsDataStoreCPT.php#L293-L298)

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

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 860](../src/Payments/PaymentsDataStoreCPT.php#L860-L872)

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

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 874](../src/Payments/PaymentsDataStoreCPT.php#L874-L884)

### `pronamic_payment_status_update`

*Payment status updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
`$updated_status` | `null\|string` | Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 886](../src/Payments/PaymentsDataStoreCPT.php#L886-L894)

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

### `pronamic_pay_install`

*Install.*


Source: [src/Admin/Install.php](../src/Admin/Install.php), [line 84](../src/Admin/Install.php#L84-L100)

### `pronamic_pay_pre_create_subscription`

*Pre-create subscription.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 189](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L189-L194)

### `pronamic_pay_new_subscription`

*New subscription created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 230](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L230-L235)

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

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 682](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L682-L694)

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

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 696](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L696-L706)

### `pronamic_subscription_status_update`

*Subscription status updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
`$updated_status` | `null\|string` | Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 708](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L708-L716)

### `pronamic_subscription_renewal_notice_{$source}`

*Send renewal notice for source.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsNotificationsController.php](../src/Subscriptions/SubscriptionsNotificationsController.php), [line 247](../src/Subscriptions/SubscriptionsNotificationsController.php#L247-L254)

### `pronamic_pay_license_check`

*Perform license check.*


Source: [src/LicenseManager.php](../src/LicenseManager.php), [line 113](../src/LicenseManager.php#L113-L116)

## Filters

### `pronamic_payment_redirect_url`

*Filters the payment return redirect URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 445](../src/Payments/Payment.php#L445-L451)

### `pronamic_payment_source_text_{$source}`

*Filters the payment source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 495](../src/Payments/Payment.php#L495-L501)

### `pronamic_payment_source_text`

*Filters the payment source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 504](../src/Payments/Payment.php#L504-L510)

### `pronamic_payment_source_description`

*Filters the payment source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 527](../src/Payments/Payment.php#L527-L533)

### `pronamic_payment_source_description_{$source}`

*Filters the payment source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 536](../src/Payments/Payment.php#L536-L542)

### `pronamic_payment_source_url`

*Filters the payment source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 560](../src/Payments/Payment.php#L560-L566)

### `pronamic_payment_source_url_{$source}`

*Filters the payment source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 569](../src/Payments/Payment.php#L569-L575)

### `pronamic_payment_provider_url`

*Filters the payment provider URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 591](../src/Payments/Payment.php#L591-L597)

### `pronamic_payment_provider_url_{$gateway_id}`

*Filters the payment provider URL by gateway identifier.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 612](../src/Payments/Payment.php#L612-L618)

### `pronamic_pay_merge_tags`

*Filter merge tags.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$merge_tags` | `\Pronamic\WordPress\Pay\MergeTags\MergeTag[]` | Merge tags.
`$controller` | `\Pronamic\WordPress\Pay\MergeTags\MergeTagsController` | Merge tags controller.

Source: [src/MergeTags/MergeTagsController.php](../src/MergeTags/MergeTagsController.php), [line 43](../src/MergeTags/MergeTagsController.php#L43-L53)

### `pronamic_pay_return_should_redirect`

*Filter whether or not to allow redirects on payment return.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$should_redirect` | `bool` | Flag to indicate if redirect is allowed on handling payment return.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 943](../src/Plugin.php#L943-L949)

### `pronamic_pay_gateways`

*Filters the gateway integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$gateways` | `\Pronamic\WordPress\Pay\AbstractGatewayIntegration[]` | Gateway integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 1089](../src/Plugin.php#L1089-L1094)

### `pronamic_pay_plugin_integrations`

*Filters the plugin integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$plugin_integrations` | `\Pronamic\WordPress\Pay\AbstractPluginIntegration[]` | Plugin integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 1104](../src/Plugin.php#L1104-L1109)

### `pronamic_payment_gateway_configuration_id`

*Filters the payment gateway configuration ID.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$config_id` | `null\|int` | Gateway configuration ID.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1467](../src/Plugin.php#L1467-L1473)

### `pronamic_payment_redirect_url_{$source}`

*Filters the payment redirect URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `string` | Redirect URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1668](../src/Plugin.php#L1668-L1674)

### `pronamic_pay_modules`

*Add meta boxes.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 

Source: [src/Admin/AdminPaymentPostType.php](../src/Admin/AdminPaymentPostType.php), [line 533](../src/Admin/AdminPaymentPostType.php#L533-L562)

### `pronamic_gateway_configuration_display_value`

*Filters the gateway configuration display value.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$display_value` | `string` | Display value.
`$post_id` | `int` | Gateway configuration post ID.

Source: [src/Admin/AdminGatewayPostType.php](../src/Admin/AdminGatewayPostType.php), [line 131](../src/Admin/AdminGatewayPostType.php#L131-L137)

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

Source: [src/Admin/AdminGatewayPostType.php](../src/Admin/AdminGatewayPostType.php), [line 139](../src/Admin/AdminGatewayPostType.php#L139-L150)

### `pronamic_pay_modules`

*Create the admin menu.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 

Source: [src/Admin/AdminModule.php](../src/Admin/AdminModule.php), [line 681](../src/Admin/AdminModule.php#L681-L749)

### `pronamic_pay_modules`

*Get pages.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 

Source: [src/Admin/AdminTour.php](../src/Admin/AdminTour.php), [line 312](../src/Admin/AdminTour.php#L312-L318)

### `pronamic_subscription_source_text_{$source}`

*Filters the subscription source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 178](../src/Subscriptions/Subscription.php#L178-L184)

### `pronamic_subscription_source_text`

*Filters the subscription source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 187](../src/Subscriptions/Subscription.php#L187-L193)

### `pronamic_subscription_source_description_{$source}`

*Filters the subscription source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 211](../src/Subscriptions/Subscription.php#L211-L217)

### `pronamic_subscription_source_description`

*Filters the subscription source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 220](../src/Subscriptions/Subscription.php#L220-L226)

### `pronamic_subscription_source_url`

*Filters the subscription source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 243](../src/Subscriptions/Subscription.php#L243-L249)

### `pronamic_subscription_source_url_{$source}`

*Filters the subscription source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 252](../src/Subscriptions/Subscription.php#L252-L258)

### `pronamic_pay_subscription_next_payment_delivery_date`

*Filters the subscription next payment delivery date.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$next_payment_delivery_date` | `\Pronamic\WordPress\DateTime\DateTimeImmutable` | Next payment delivery date.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 475](../src/Subscriptions/Subscription.php#L475-L482)

### `pronamic_pay_modules`

*Admin notices.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 

Source: [src/HomeUrlController.php](../src/HomeUrlController.php), [line 62](../src/HomeUrlController.php#L62-L107)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

