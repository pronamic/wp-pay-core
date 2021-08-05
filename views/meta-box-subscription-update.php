<?php
/**
 * Meta Box Subscription Update
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPostType;

if ( ! isset( $post ) ) {
	return;
}

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
