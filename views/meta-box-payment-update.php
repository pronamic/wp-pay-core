<?php
/**
 * Meta Box Payment Update
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

use Pronamic\WordPress\Pay\Payments\PaymentStatus;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $post ) ) {
	return;
}

$states = [
	PaymentStatus::OPEN       => _x( 'Pending', 'Payment status', 'pronamic_ideal' ),
	PaymentStatus::ON_HOLD    => _x( 'On Hold', 'Payment status', 'pronamic_ideal' ),
	PaymentStatus::SUCCESS    => _x( 'Completed', 'Payment status', 'pronamic_ideal' ),
	PaymentStatus::CANCELLED  => _x( 'Cancelled', 'Payment status', 'pronamic_ideal' ),
	PaymentStatus::REFUNDED   => _x( 'Refunded', 'Payment status', 'pronamic_ideal' ),
	PaymentStatus::FAILURE    => _x( 'Failed', 'Payment status', 'pronamic_ideal' ),
	PaymentStatus::EXPIRED    => _x( 'Expired', 'Payment status', 'pronamic_ideal' ),
	PaymentStatus::AUTHORIZED => _x( 'Authorized', 'Payment status', 'pronamic_ideal' ),
];

$payment = \get_pronamic_payment( \get_the_ID() );

if ( null === $payment ) {
	return;
}

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
		<div class="misc-pub-section misc-pub-post-status">
			<?php echo esc_html( __( 'Status:', 'pronamic_ideal' ) ); ?>

			<?php

			$status_label = $payment->get_status_label();

			$status_label = ( null === $status_label ) ? '—' : $status_label;

			?>
			<span id="pronamic-pay-post-status-display"><?php echo esc_html( $status_label ); ?></span>

			<a href="#pronamic-pay-post-status" class="edit-pronamic-pay-post-status hide-if-no-js" role="button">
				<span aria-hidden="true"><?php esc_html_e( 'Edit', 'pronamic_ideal' ); ?></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Edit status', 'pronamic_ideal' ); ?></span>
			</a>

			<div id="pronamic-pay-post-status-input" class="hide-if-js">
				<label for="pronamic-pay-post-status" class="screen-reader-text"><?php esc_html_e( 'Set status', 'pronamic_ideal' ); ?></label>
				<select id="pronamic-pay-post-status" name="pronamic_payment_status">
					<?php

					foreach ( $states as $payment_status => $label ) {
						printf(
							'<option value="%s" %s>%s</option>',
							esc_attr( $payment_status ),
							selected( $payment_status, $payment->get_status(), false ),
							esc_html( $label )
						);
					}

					?>
				</select>

				<a href="#pronamic-pay-post-status" class="save-pronamic-pay-post-status hide-if-no-js button"><?php esc_html_e( 'OK', 'pronamic_ideal' ); ?></a>
				<a href="#pronamic-pay-post-status" class="cancel-pronamic-pay-post-status hide-if-no-js button-cancel"><?php esc_html_e( 'Cancel', 'pronamic_ideal' ); ?></a>
			</div>
		</div>

		<?php

		$gateway = $payment->get_gateway();

		/**
		 * Check status button.
		 */
		if ( null !== $gateway && $gateway->supports( 'payment_status_request' ) ) {
			// Only show button if gateway exists and status check is supported.
			$action_url = wp_nonce_url(
				add_query_arg(
					[
						'post'                      => $post->ID,
						'action'                    => 'edit',
						'pronamic_pay_check_status' => true,
					],
					admin_url( 'post.php' )
				),
				'pronamic_payment_check_status_' . $post->ID
			);

			printf(
				'<div class="misc-pub-section"><a class="button" href="%s">%s</a></div>',
				esc_url( $action_url ),
				esc_html__( 'Check status', 'pronamic_ideal' )
			);
		}

		?>
	</div>
</div>

<div class="pronamic-pay-major-actions">
	<div class="pronamic-pay-action">
		<?php

		wp_nonce_field( 'pronamic_payment_update', 'pronamic_payment_nonce' );

		printf(
			'<input type="hidden" name="pronamic_payment_id" value="%s" />',
			esc_attr( (string) $payment->get_id() )
		);

		submit_button(
			__( 'Update', 'pronamic_ideal' ),
			'primary',
			'pronamic_payment_update',
			false
		);

		?>
	</div>

	<div class="clear"></div>
</div>
