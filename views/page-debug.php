<?php
/**
 * Page Debug
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

?>

<div class="wrap pronamic-pay-debug">
	<h1 class="wp-heading-inline"><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

	<hr class="wp-header-end">

	<h2><?php \esc_html_e( 'Subscriptions', 'pronamic_ideal' ); ?></h2>

	<?php

	$date   = new \DateTimeImmutable();
	$number = 10;

	$query = $this->plugin->subscriptions_module->get_subscriptions_wp_query_that_require_follow_up_payment(
		array(
			'date'   => $date,
			'number' => $number,
		) 
	);

	echo '<ul>';

	foreach ( $query->posts as $subscription_post ) {
		echo '<li>';

		printf(
			'<a href="%s">Processing post `%d` - "%s"…</a>',
			\esc_url( \get_edit_post_link( $subscription_post ) ),
			\esc_html( $subscription_post->ID ),
			\esc_html( \get_the_title( $subscription_post ) )
		);

		$subscription = \get_pronamic_subscription( $subscription_post->ID );

		$next_payment_date = \get_post_meta( $subscription_post->ID, '_pronamic_subscription_next_payment', true );

		$next_payment_delivery_date = \get_post_meta( $subscription_post->ID, '_pronamic_subscription_next_payment_delivery_date', true );

		echo '<dl>';

		echo '<dt><code>_pronamic_subscription_next_payment</code></dt>';
		echo '<dd><code>', esc_html( $next_payment_date ), '</code></dd>';

		echo '<dt><code>_pronamic_subscription_next_payment_delivery_date</code></dt>';
		echo '<dd><code>', esc_html( $next_payment_delivery_date ), '</code></dd>';

		echo '</dl>';

		echo '</li>';
	}

	echo '</ul>';

	?>

	<h2><?php \esc_html_e( 'Process', 'pronamic_ideal' ); ?></h2>

	<form action="admin.php" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">
						<label for="date"><?php \esc_html_e( 'Date', 'pronamic_ideal' ); ?></label>
					</th>
					<td>
						<input name="date" id="date" type="date" class="regular-text" value="<?php echo \esc_attr( $date->format( 'Y-m-d' ) ); ?>" required="required" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="number"><?php \esc_html_e( 'Number', 'pronamic_ideal' ); ?></label>
					</th>
					<td>
						<input name="number" id="number" type="number" class="regular-text code" value="<?php echo \esc_attr( $number ); ?>" required="required" />
					</td>
				</tr>
			</tbody>
		</table>

		<?php

		\wp_nonce_field( 'pronamic_pay_process_subscriptions_follow_up_payment', 'pronamic_pay_nonce' );

		\submit_button(
			\esc_html__( 'Process subscriptions follow-up payment', 'pronamic_ideal' ),
			'primary',
			'pronamic_pay_process_subscriptions_follow_up_payment'
		);

		?>
	</form>

	<?php require __DIR__ . '/pronamic.php'; ?>
</div>
