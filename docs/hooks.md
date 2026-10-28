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

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 179](../src/Payments/PaymentsDataStoreCPT.php#L179-L184)

### `pronamic_pay_new_payment`

*New payment created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 215](../src/Payments/PaymentsDataStoreCPT.php#L215-L220)

### `pronamic_pay_update_payment`

*Payment updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 282](../src/Payments/PaymentsDataStoreCPT.php#L282-L287)

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

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 554](../src/Payments/PaymentsDataStoreCPT.php#L554-L566)

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

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 568](../src/Payments/PaymentsDataStoreCPT.php#L568-L578)

### `pronamic_payment_status_update`

*Payment status updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).
`$updated_status` | `null\|string` | Updated [payment status](https://github.com/pronamic/wp-pronamic-pay/wiki#payment-status).

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 580](../src/Payments/PaymentsDataStoreCPT.php#L580-L588)

### `pronamic_pay_register_payment_methods`

*Register payment methods.*

Enables integrations and external libraries to register their own
payment methods in plugin space.

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$this->payment_methods` |  | 

Source: [src/Plugin.php](../src/Plugin.php), [line 622](../src/Plugin.php#L622-L630)

### `pronamic_pay_install`

*Install.*


Source: [src/Admin/Install.php](../src/Admin/Install.php), [line 83](../src/Admin/Install.php#L83-L106)

### `pronamic_pay_pre_create_subscription`

*Pre-create subscription.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 181](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L181-L186)

### `pronamic_pay_new_subscription`

*New subscription created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 222](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L222-L227)

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

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 561](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L561-L573)

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

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 575](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L575-L585)

### `pronamic_subscription_status_update`

*Subscription status updated.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).
`$updated_status` | `null\|string` | Updated [subscription status](https://github.com/pronamic/wp-pronamic-pay/wiki#subscription-status).

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 587](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L587-L595)

### `pronamic_subscription_renewal_notice_{$source}`

*Send renewal notice for source.*

[`{$source}`](https://github.com/pronamic/wp-pronamic-pay/wiki#sources)

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsNotificationsController.php](../src/Subscriptions/SubscriptionsNotificationsController.php), [line 249](../src/Subscriptions/SubscriptionsNotificationsController.php#L249-L256)

### `pronamic_pay_license_check`

*Perform license check.*


Source: [src/LicenseManager.php](../src/LicenseManager.php), [line 112](../src/LicenseManager.php#L112-L115)

## Filters

### `pronamic_payment_redirect_url`

*Filters the payment return redirect URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 448](../src/Payments/Payment.php#L448-L454)

### `pronamic_payment_source_text_{$source}`

*Filters the payment source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 498](../src/Payments/Payment.php#L498-L504)

### `pronamic_payment_source_text`

*Filters the payment source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 507](../src/Payments/Payment.php#L507-L513)

### `pronamic_payment_source_description`

*Filters the payment source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 530](../src/Payments/Payment.php#L530-L536)

### `pronamic_payment_source_description_{$source}`

*Filters the payment source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 539](../src/Payments/Payment.php#L539-L545)

### `pronamic_payment_source_url`

*Filters the payment source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 563](../src/Payments/Payment.php#L563-L569)

### `pronamic_payment_source_url_{$source}`

*Filters the payment source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 572](../src/Payments/Payment.php#L572-L578)

### `pronamic_payment_provider_url`

*Filters the payment provider URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 594](../src/Payments/Payment.php#L594-L600)

### `pronamic_payment_provider_url_{$gateway_id}`

*Filters the payment provider URL by gateway identifier.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 615](../src/Payments/Payment.php#L615-L621)

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

Source: [src/Plugin.php](../src/Plugin.php), [line 445](../src/Plugin.php#L445-L451)

### `pronamic_pay_gateways`

*Filters the gateway integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$gateways` | `\Pronamic\WordPress\Pay\AbstractGatewayIntegration[]` | Gateway integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 578](../src/Plugin.php#L578-L583)

### `pronamic_pay_plugin_integrations`

*Filters the plugin integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$plugin_integrations` | `\Pronamic\WordPress\Pay\AbstractPluginIntegration[]` | Plugin integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 593](../src/Plugin.php#L593-L598)

### `pronamic_payment_gateway_configuration_id`

*Filters the payment gateway configuration ID.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$config_id` | `null\|int` | Gateway configuration ID.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 972](../src/Plugin.php#L972-L978)

### `pronamic_payment_redirect_url_{$source}`

*Filters the payment redirect URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `string` | Redirect URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1173](../src/Plugin.php#L1173-L1179)

### `pronamic_pay_modules`

*Add meta boxes.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 

Source: [src/Admin/AdminPaymentPostType.php](../src/Admin/AdminPaymentPostType.php), [line 494](../src/Admin/AdminPaymentPostType.php#L494-L523)

### `pronamic_gateway_configuration_display_value`

*Filters the gateway configuration display value.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$display_value` | `string` | Display value.
`$post_id` | `int` | Gateway configuration post ID.

Source: [src/Admin/AdminGatewayPostType.php](../src/Admin/AdminGatewayPostType.php), [line 129](../src/Admin/AdminGatewayPostType.php#L129-L135)

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

Source: [src/Admin/AdminGatewayPostType.php](../src/Admin/AdminGatewayPostType.php), [line 137](../src/Admin/AdminGatewayPostType.php#L137-L148)

### `pronamic_pay_modules`

*Create the admin menu.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 

Source: [src/Admin/AdminModule.php](../src/Admin/AdminModule.php), [line 735](../src/Admin/AdminModule.php#L735-L803)

### `pronamic_pay_modules`

*Get pages.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 

Source: [src/Admin/AdminTour.php](../src/Admin/AdminTour.php), [line 330](../src/Admin/AdminTour.php#L330-L336)

### `pronamic_subscription_source_text_{$source}`

*Filters the subscription source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 177](../src/Subscriptions/Subscription.php#L177-L183)

### `pronamic_subscription_source_text`

*Filters the subscription source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 186](../src/Subscriptions/Subscription.php#L186-L192)

### `pronamic_subscription_source_description_{$source}`

*Filters the subscription source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 210](../src/Subscriptions/Subscription.php#L210-L216)

### `pronamic_subscription_source_description`

*Filters the subscription source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 219](../src/Subscriptions/Subscription.php#L219-L225)

### `pronamic_subscription_source_url`

*Filters the subscription source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 242](../src/Subscriptions/Subscription.php#L242-L248)

### `pronamic_subscription_source_url_{$source}`

*Filters the subscription source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 251](../src/Subscriptions/Subscription.php#L251-L257)

### `pronamic_pay_modules`

*Admin notices.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`[]` |  | 

Source: [src/HomeUrlController.php](../src/HomeUrlController.php), [line 62](../src/HomeUrlController.php#L62-L107)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.2.0</code></em><p>

