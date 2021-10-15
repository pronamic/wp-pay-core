<?php
/**
 * Page Debug
 *
 * @author Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license GPL-3.0-or-later
 * @package Pronamic\WordPress\Pay
 * @var \Pronamic\WordPress\Pay\Plugin $plugin Plugin.
 */

if ( isset( $_REQUEST['pronamic_pay_nonce'] ) && wp_verify_nonce( $_REQUEST['pronamic_pay_nonce'], 'pronamic_pay_delete_follow_up_payments_without_transaction_id' ) ) {
	$query = new \WP_Query( array(
		'post_type'   => 'pronamic_payment',
		'post_status' => 'payment_failed',
		'nopaging'    => true,
		'fields'      => 'ids',
	) );

	foreach ( $query->posts as $post_id ) {
		\as_enqueue_async_action(
			'pronamic_pay_delete_follow_up_payment_without_transaction_id',
			array(
				'pronamic_payment_id' => $post_id,
			),
			'pronamic_pay_delete_follow_up_payments_without_transaction_id'
		);
	}

	$url = \add_query_arg(
		array(
			'page'   => 'action-scheduler',
			'status' => 'pending',
			's'      => 'pronamic_pay_delete_follow_up_payment_without_transaction_id',
		),
		\admin_url( 'tools.php' )
	);

	\wp_safe_redirect( $url );

	exit;
}

?>

<div class="wrap pronamic-pay-debug">
	<h1 class="wp-heading-inline"><?php echo \esc_html( \get_admin_page_title() ); ?></h1>

	<hr class="wp-header-end">

	<h2><?php \esc_html_e( 'Tools', 'pronamic_ideal' ); ?></h2>

	<p>
		<?php

		$url = wp_nonce_url(
			\add_query_arg(
				array(
					'page'                => 'pronamic_pay_debug',
					'pronamic_pay_debug'  => 'true',
					'pronamic_pay_action' => 'pronamic_pay_delete_follow_up_payments_without_transaction_id',
				),
				\admin_url( 'admin.php' )
			),
			'pronamic_pay_delete_follow_up_payments_without_transaction_id',
			'pronamic_pay_nonce'
		);

		\printf(
			'<a href="%s" class="button button-large">%s</a>',
			\esc_url( $url ),
			\esc_html__( 'Delete follow-up payments without transaction ID', 'pronamic_ideal' )
		);

		?>
	</p>

	<h2><?php \esc_html_e( 'Subscriptions', 'pronamic_ideal' ); ?></h2>

	<?php

	$date   = new \DateTimeImmutable();
	$number = 10;

	$query = $plugin->subscriptions_module->get_subscriptions_wp_query_that_require_follow_up_payment(
		array(
			'date'   => $date,
			'number' => $number,
		)
	);

	$subscription_posts = \array_filter(
		$query->posts,
		function( $post ) {
			return $post instanceof \WP_Post;
		}
	);

	echo '<ul>';

	foreach ( $subscription_posts as $subscription_post ) {
		echo '<li>';

		printf(
			'<a href="%s">Processing post `%d` - "%s"…</a>',
			\esc_url( (string) \get_edit_post_link( $subscription_post ) ),
			\esc_html( (string) $subscription_post->ID ),
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
						<input name="number" id="number" type="number" class="regular-text code" value="<?php echo \esc_attr( (string) $number ); ?>" required="required" />
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
