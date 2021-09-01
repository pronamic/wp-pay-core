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

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 241](../src/Payments/PaymentsDataStoreCPT.php#L241-L246)

### `pronamic_pay_new_payment`

*New payment created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 288](../src/Payments/PaymentsDataStoreCPT.php#L288-L293)

### `pronamic_payment_status_update_{$source}_{$old_status}_to_{$new_status}`

*Payment status updated for plugin integration source from old to new status.*

**Source**

Plugin | Source
------ | ------
Charitable | `charitable`
Contact Form 7 | `contact-form-7`
Event Espresso | `eventespresso`
Event Espresso (legacy) | `event-espresso`
Formidable Forms | `formidable-forms`
Give | `give`
Gravity Forms | `gravityformsideal`
MemberPress | `memberpress`
Ninja Forms | `ninja-forms`
s2Member | `s2member`
WooCommerce | `woocommerce`
WP eCommerce | `wp-e-commerce`

**Action status**

Status | Value
------ | -----
(empty) | `unknown`
Cancelled | `cancelled`
Expired | `expired`
Failure | `failure`
Open | `open`
Reserved | `reserved`
Success | `success`

**Payment status**

Status | Value
------ | -----
Cancelled | `Cancelled`
Expired | `Expired`
Failure | `Failure`
Open | `Open`
Reserved | `Reserved`
Success | `Success`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous payment status.
`$updated_status` | `string` | Updated payment status.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 902](../src/Payments/PaymentsDataStoreCPT.php#L902-L950)

### `pronamic_payment_status_update_{$source}`

*Payment status updated for plugin integration source.*

**Source**

Plugin | Source
------ | ------
Charitable | `charitable`
Contact Form 7 | `contact-form-7`
Event Espresso | `eventespresso`
Event Espresso (legacy) | `event-espresso`
Formidable Forms | `formidable-forms`
Give | `give`
Gravity Forms | `gravityformsideal`
MemberPress | `memberpress`
Ninja Forms | `ninja-forms`
s2Member | `s2member`
WooCommerce | `woocommerce`
WP eCommerce | `wp-e-commerce`

**Action status**

Status | Value
------ | -----
(empty) | `unknown`
Cancelled | `cancelled`
Expired | `expired`
Failure | `failure`
Open | `open`
Reserved | `reserved`
Success | `success`

**Payment status**

Status | Value
------ | -----
Cancelled | `Cancelled`
Expired | `Expired`
Failure | `Failure`
Open | `Open`
Reserved | `Reserved`
Success | `Success`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous payment status.
`$updated_status` | `string` | Updated payment status.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 952](../src/Payments/PaymentsDataStoreCPT.php#L952-L1000)

### `pronamic_payment_status_update`

*Payment status updated.*

**Payment status**

Status | Value
------ | -----
Cancelled | `Cancelled`
Expired | `Expired`
Failure | `Failure`
Open | `Open`
Reserved | `Reserved`
Success | `Success`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the payment update.
`$previous_status` | `null\|string` | Previous payment status.
`$updated_status` | `string` | Updated payment status.

Source: [src/Payments/PaymentsDataStoreCPT.php](../src/Payments/PaymentsDataStoreCPT.php), [line 1002](../src/Payments/PaymentsDataStoreCPT.php#L1002-L1021)

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


Source: [src/Admin/AdminSettings.php](../src/Admin/AdminSettings.php), [line 288](../src/Admin/AdminSettings.php#L288-L291)

### `pronamic_subscription_renewal_notice_{$source}`

*Send renewal notice for source.*

**Source**

Plugin | Source
------ | ------
Charitable | `charitable`
Contact Form 7 | `contact-form-7`
Event Espresso | `eventespresso`
Event Espresso (legacy) | `event-espresso`
Formidable Forms | `formidable-forms`
Give | `give`
Gravity Forms | `gravityformsideal`
MemberPress | `memberpress`
Ninja Forms | `ninja-forms`
s2Member | `s2member`
WooCommerce | `woocommerce`
WP eCommerce | `wp-e-commerce`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsModule.php](../src/Subscriptions/SubscriptionsModule.php), [line 1028](../src/Subscriptions/SubscriptionsModule.php#L1028-L1050)

### `pronamic_pay_new_subscription`

*New subscription created.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 279](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L279-L284)

### `pronamic_subscription_status_update_{$source}_{$old_status}_to_{$new_status}`

*Subscription status updated for plugin integration source from old to new status.*

**Source**

Plugin | Source
------ | ------
Charitable | `charitable`
Contact Form 7 | `contact-form-7`
Event Espresso | `eventespresso`
Event Espresso (legacy) | `event-espresso`
Formidable Forms | `formidable-forms`
Give | `give`
Gravity Forms | `gravityformsideal`
MemberPress | `memberpress`
Ninja Forms | `ninja-forms`
s2Member | `s2member`
WooCommerce | `woocommerce`
WP eCommerce | `wp-e-commerce`

**Action status**

Status | Value
------ | -----
(empty) | `unknown`
Active | `active`
Cancelled | `cancelled`
Completed | `completed`
Expired | `expired`
Failure | `failure`
On Hold | `on_hold`
Open | `open`

**Subscription status**

Status | Value
------ | -----
Active | `Active`
Cancelled | `Cancelled`
Completed | `Completed`
Expired | `Expired`
Failure | `Failure`
On Hold | `On Hold`
Open | `Open`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous subscription status.
`$updated_status` | `string` | Updated subscription status.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 752](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L752-L802)

### `pronamic_subscription_status_update_{$source}`

*Subscription status updated for plugin integration source.*

**Source**

Plugin | Source
------ | ------
Charitable | `charitable`
Contact Form 7 | `contact-form-7`
Event Espresso | `eventespresso`
Event Espresso (legacy) | `event-espresso`
Formidable Forms | `formidable-forms`
Give | `give`
Gravity Forms | `gravityformsideal`
MemberPress | `memberpress`
Ninja Forms | `ninja-forms`
s2Member | `s2member`
WooCommerce | `woocommerce`
WP eCommerce | `wp-e-commerce`

**Action status**

Status | Value
------ | -----
(empty) | `unknown`
Active | `active`
Cancelled | `cancelled`
Completed | `completed`
Expired | `expired`
Failure | `failure`
On Hold | `on_hold`
Open | `open`

**Subscription status**

Status | Value
------ | -----
Active | `Active`
Cancelled | `Cancelled`
Completed | `Completed`
Expired | `Expired`
Failure | `Failure`
On Hold | `On Hold`
Open | `Open`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous subscription status.
`$updated_status` | `string` | Updated subscription status.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 804](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L804-L854)

### `pronamic_subscription_status_update`

*Subscription status updated.*

**Subscription status**

Status | Value
------ | -----
Active | `Active`
Cancelled | `Cancelled`
Completed | `Completed`
Expired | `Expired`
Failure | `Failure`
On Hold | `On Hold`
Open | `Open`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.
`$can_redirect` | `bool` | Flag to indicate if redirect is allowed after the subscription update.
`$previous_status` | `null\|string` | Previous subscription status.
`$updated_status` | `string` | Updated subscription status.

Source: [src/Subscriptions/SubscriptionsDataStoreCPT.php](../src/Subscriptions/SubscriptionsDataStoreCPT.php), [line 856](../src/Subscriptions/SubscriptionsDataStoreCPT.php#L856-L876)

## Filters

### `pronamic_payment_redirect_url`

*Filters the payment return redirect URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` |  | 
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 542](../src/Payments/Payment.php#L542-L548)

### `pronamic_payment_source_text_{$source}`

*Filters the payment source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 605](../src/Payments/Payment.php#L605-L611)

### `pronamic_payment_source_text`

*Filters the payment source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 614](../src/Payments/Payment.php#L614-L620)

### `pronamic_payment_source_description`

*Filters the payment source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 637](../src/Payments/Payment.php#L637-L643)

### `pronamic_payment_source_description_{$source}`

*Filters the payment source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 646](../src/Payments/Payment.php#L646-L652)

### `pronamic_payment_source_url`

*Filters the payment source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 670](../src/Payments/Payment.php#L670-L676)

### `pronamic_payment_source_url_{$source}`

*Filters the payment source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 679](../src/Payments/Payment.php#L679-L685)

### `pronamic_payment_provider_url`

*Filters the payment provider URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 701](../src/Payments/Payment.php#L701-L707)

### `pronamic_payment_provider_url_{$gateway_id}`

*Filters the payment provider URL by gateway identifier.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Provider URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Payments/Payment.php](../src/Payments/Payment.php), [line 722](../src/Payments/Payment.php#L722-L728)

### `wp_doing_cron`

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$wp_doing_cron` |  | 

Source: [src/Core/Util.php](../src/Core/Util.php), [line 143](../src/Core/Util.php#L143-L143)

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

Source: [src/Plugin.php](../src/Plugin.php), [line 436](../src/Plugin.php#L436-L442)

### `pronamic_pay_gateways`

*Filters the gateway integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$gateways` | `\Pronamic\WordPress\Pay\AbstractGatewayIntegration[]` | Gateway integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 599](../src/Plugin.php#L599-L604)

### `pronamic_pay_plugin_integrations`

*Filters the plugin integrations.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$plugin_integrations` | `\Pronamic\WordPress\Pay\AbstractPluginIntegration[]` | Plugin integrations.

Source: [src/Plugin.php](../src/Plugin.php), [line 614](../src/Plugin.php#L614-L619)

### `pronamic_payment_gateway_configuration_id`

*Filters the payment gateway configuration ID.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$config_id` | `null\|int` | Gateway configuration ID.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1001](../src/Plugin.php#L1001-L1007)

### `pronamic_payment_redirect_url_{$source}`

*Filters the payment redirect URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Redirect URL.
`$payment` | `\Pronamic\WordPress\Pay\Payments\Payment` | Payment.

Source: [src/Plugin.php](../src/Plugin.php), [line 1181](../src/Plugin.php#L1181-L1187)

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

### `pronamic_subscription_source_text_{$source}`

*Filters the subscription source text by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 370](../src/Subscriptions/Subscription.php#L370-L376)

### `pronamic_subscription_source_text`

*Filters the subscription source text.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$text` | `string` | Source text.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 379](../src/Subscriptions/Subscription.php#L379-L385)

### `pronamic_subscription_source_description_{$source}`

*Filters the subscription source description by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 403](../src/Subscriptions/Subscription.php#L403-L409)

### `pronamic_subscription_source_description`

*Filters the subscription source description.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$description` | `string` | Source description.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 412](../src/Subscriptions/Subscription.php#L412-L418)

### `pronamic_subscription_source_url`

*Filters the subscription source URL.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 435](../src/Subscriptions/Subscription.php#L435-L441)

### `pronamic_subscription_source_url_{$source}`

*Filters the subscription source URL by plugin integration source.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$url` | `null\|string` | Source URL.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/Subscription.php](../src/Subscriptions/Subscription.php), [line 444](../src/Subscriptions/Subscription.php#L444-L450)

### `pronamic_pay_subscription_next_payment_delivery_date`

*Filters the subscription next payment delivery date.*

**Arguments**

Argument | Type | Description
-------- | ---- | -----------
`$next_payment_delivery_date` | `\Pronamic\WordPress\DateTime\DateTime` | Next payment delivery date.
`$subscription` | `\Pronamic\WordPress\Pay\Subscriptions\Subscription` | Subscription.

Source: [src/Subscriptions/SubscriptionHelper.php](../src/Subscriptions/SubscriptionHelper.php), [line 246](../src/Subscriptions/SubscriptionHelper.php#L246-L254)


<p align="center"><a href="https://github.com/pronamic/wp-documentor"><img src="https://cdn.jsdelivr.net/gh/pronamic/wp-documentor@main/logos/pronamic-wp-documentor.svgo-min.svg" alt="Pronamic WordPress Documentor" width="32" height="32"></a><br><em>Generated by <a href="https://github.com/pronamic/wp-documentor">Pronamic WordPress Documentor</a> <code>1.1.0</code></em><p>

