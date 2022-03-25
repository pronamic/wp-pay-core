<?php
/**
 * Meta Box Subscription Update
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPostType;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionStatus;

if ( ! isset( $post ) ) {
	return;
}

$states = SubscriptionPostType::get_states();

ksort( $states );

// WordPress by default doesn't allow `post_author` values of `0`, that's why we use a dash (`-`).
// @link https://github.com/WordPress/WordPress/blob/4.9.5/wp-admin/includes/post.php#L56-L64.
$post_author = get_post_field( 'post_author' );
$post_author = empty( $post_author ) ? '-' : $post_author;

?>
<input type="hidden" name="post_author_override" value="<?php echo esc_attr( $post_author ); ?>" />

<div class="pronamic-pay-inner">
	<div id="minor-publishing-actions">
		<div class="clear"></div>
	</div>

	<div class="pronamic-pay-minor-actions">
		<div class="misc-pub-post-status">
			<?php echo esc_html( __( 'Status:', 'pronamic_ideal' ) ); ?>

			<?php

			$status_object = get_post_status_object( $post->post_status );

			$status_label = isset( $status_object, $status_object->label ) ? $status_object->label : '—';

			?>

			<span id="pronamic-pay-status-display"><?php echo esc_html( $status_label ); ?></span>

			<?php if ( 'subscr_completed' !== $post->post_status ) : ?>

				<a href="#pronamic-pay-post-status" class="edit-pronamic-pay-post-status hide-if-no-js" role="button">
					<span aria-hidden="true"><?php _e( 'Edit', 'pronamic_ideal' ); ?></span>
					<span class="screen-reader-text"><?php _e( 'Edit status', 'pronamic_ideal' ); ?></span>
				</a>

				<div id="pronamic-pay-post-status-select" class="hide-if-js">
					<input type="hidden" name="hidden_pronamic_pay_post_status" id="hidden_pronamic_pay_post_status" value="<?php echo esc_attr( ( 'auto-draft' === $post->post_status ) ? 'draft' : $post->post_status ); ?>" />
					<label for="pronamic-pay-post-status" class="screen-reader-text"><?php _e( 'Set status' ); ?></label>
					<select id="pronamic-pay-post-status" name="pronamic_subscription_post_status">
						<?php

						$states_options = array(
							'subscr_active',
							'subscr_cancelled',
							'subscr_on_hold',
						);

						foreach ( $states as $subscription_status => $label ) {
							if ( ! in_array( $subscription_status, $states_options, true ) && $subscription_status !== $post->post_status ) {
								continue;
							}

							printf(
								'<option value="%s" %s>%s</option>',
								esc_attr( $subscription_status ),
								selected( $subscription_status, $post->post_status, false ),
								esc_html( $label )
							);
						}

						?>
					</select>

					<a href="#pronamic-pay-post-status" class="save-pronamic-pay-post-status hide-if-no-js button"><?php _e( 'OK' ); ?></a>
					<a href="#pronamic-pay-post-status" class="cancel-pronamic-pay-post-status hide-if-no-js button-cancel"><?php _e( 'Cancel' ); ?></a>
				</div>

			<?php endif; ?>
		</div>
	</div>
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
