<?php

if ( ! $gateway->supports( 'webhook-log' ) ) {
	esc_html_e( 'This gateway does not support webhook logging.', 'pronamic_ideal' );
}

if ( $gateway->supports( 'webhook-log' ) ) {
	esc_html_e( 'This gateway supports webhook logging.', 'pronamic_ideal' );
}

var_dump( $gateway );
