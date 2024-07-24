<?php
/**
 * Form
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 * @var       \Pronamic\WordPress\Pay\Payments\Payment $payment Payment.
 * @var       \Pronamic\WordPress\Pay\Core\Gateway     $this    Gateway.
 */

use Pronamic\WordPress\Html\Element;
use Pronamic\WordPress\Pay\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$action_url = $payment->get_action_url();

if ( empty( $action_url ) ) {
	esc_html_e( 'It is currently not possible to pay, please contact us for more information (error: no action URL found).', 'pronamic_ideal' );

	return;
}

?>
<form id="pronamic_ideal_form" name="pronamic_ideal_form" method="post" action="<?php echo esc_url( $action_url ); ?>">
	<?php

	$data = $this->get_output_fields( $payment );

	$data = Util::array_square_bracket( $data );

	foreach ( $data as $name => $value ) {
		printf(
			'<input type="hidden" name="%s" value="%s" />',
			esc_attr( $name ),
			esc_attr( $value )
		);
	}

	?>

	<input class="pronamic-pay-btn" type="submit" name="pay" value="<?php esc_attr_e( 'Pay', 'pronamic_ideal' ); ?>" />
</form>

<?php

$auto_submit = true;

if ( defined( 'PRONAMIC_PAY_DEBUG' ) && PRONAMIC_PAY_DEBUG ) {
	$auto_submit = false;
}

if ( $auto_submit ) {
	$element = new Element(
		'script',
		[
			'type' => 'text/javascript',
		]
	);

	$element->children[] = 'document.pronamic_ideal_form.submit();';

	$element->output();
}
