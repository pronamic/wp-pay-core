<?php
/**
 * Meta Box Subscription Update
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPostType;

$states = SubscriptionPostType::get_states();

// WordPress by default doesn't allow `post_author` values of `0`, that's why we use a dash (`-`).
// @link https://github.com/WordPress/WordPress/blob/4.9.5/wp-admin/includes/post.php#L56-L64.
$post_author = get_post_field( 'post_author' );
$post_author = empty( $post_author ) ? '-' : $post_author;

?>
<input type="hidden" name="post_author_override" value="<?php echo esc_attr( $post_author ); ?>" />

<div class="pronamic-pay-inner">
	<p>
		<label for="pronamic-subscription-status">Status:&nbsp;</label>
		<select id="pronamic-subscription-status" name="pronamic_subscription_post_status" class="medium-text">
			<?php

			foreach ( $states as $subscription_status => $label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $subscription_status ),
					selected( $subscription_status, $post->post_status, false ),
					esc_html( $label )
				);
			}

			?>
		</select>
	</p>

	<?php

	/**
	 * Start payment for next period action.
	 */
	$config_id = get_post_meta( $post->ID, '_pronamic_subscription_config_id', true );

	$gateway = Plugin::get_gateway( $config_id );

	$allow_next_period_payment_statuses = array(
		'subscr_active',
		'subscr_failed',
	);

	// Show action button if gateway exists and support recurring payments and starting payments for next periods is allowed for the subscription status.
	if ( $gateway && $gateway->supports( 'recurring' ) && in_array( $post->post_status,$allow_next_period_payment_statuses, true ) ) {
		$action_url = wp_nonce_url(
			add_query_arg(
				array(
					'pronamic_next_period' => true,
				),
				\get_edit_post_link( get_the_ID() )
			),
			'pronamic_next_period_' . get_the_ID()
		);

		printf(
			'<p><a class="button" href="%s">%s</a></p>',
			esc_url( $action_url ),
			esc_html__( 'Start payment for next period', 'pronamic_ideal' )
		);
	}

	?>
</div>

<div class="pronamic-pay-major-actions">
	<div class="pronamic-pay-action">
		<?php

		wp_nonce_field( 'pronamic_subscription_update', 'pronamic_subscription_nonce' );

		submit_button(
			__( 'Update', 'pronamic_ideal' ),
			'primary',
			'pronamic_subscription_update',
			false
		);

		?>
	</div>

	<div class="clear"></div>
</div>
