<?php
/**
 * Meta Box Payment Lines
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Money\TaxedMoney;
use Pronamic\WordPress\Number\Number;
use Pronamic\WordPress\Pay\Payments\PaymentLine;

if ( empty( $lines ) ) : ?>

	<p>
		<?php \esc_html_e( 'No payment lines found.', 'pronamic_ideal' ); ?>
	</p>

<?php else : ?>

	<div class="pronamic-pay-table-responsive">
		<table class="pronamic-pay-table widefat">
			<thead>
				<tr>
					<th scope="col"><?php \esc_html_e( 'ID', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'SKU', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Image', 'pronamic_ideal' ); ?></th>
					<th scope="col"><?php \esc_html_e( 'Name', 'pronamic_ideal' ); ?></th>
					<th scope="col">
						<?php

						\printf(
							'<span class="pronamic-pay-tip" title="%s">%s</span>',
							\esc_attr__( 'Unit price with discount including tax.', 'pronamic_ideal' ),
							\esc_html__( 'Unit Price', 'pronamic_ideal' )
						);

						?>
					</th>
					<th scope="col"><?php \esc_html_e( 'Quantity', 'pronamic_ideal' ); ?></th>
					<th scope="col">
						<?php

						\printf(
							'<span class="pronamic-pay-tip" title="%s">%s</span>',
							\esc_attr__( 'Total discount.', 'pronamic_ideal' ),
							\esc_html__( 'Discount', 'pronamic_ideal' )
						);

						?>
					</th>
					<th scope="col">
						<?php

						\printf(
							'<span class="pronamic-pay-tip" title="%s">%s</span>',
							\esc_attr__( 'Total amount with discount including tax.', 'pronamic_ideal' ),
							\esc_html__( 'Total Amount', 'pronamic_ideal' )
						);

						?>
					</th>
					<th scope="col"><?php \esc_html_e( 'Total Tax', 'pronamic_ideal' ); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>
						<?php

						$quantity_total = new Number( 0 );

						foreach ( $lines as $line ) {
							$quantity = $line->get_quantity();

							if ( null !== $quantity ) {
								$quantity_total = $quantity_total->add( Number::from_int( $quantity ) );
							}
						}

						echo \esc_html( $quantity_total->format_i18n() );

						?>
					</td>
					<td>
						<?php

						$values = \array_map(
							function( PaymentLine $line ) {
								$discount_amount = $line->get_discount_amount();

								return ( null === $discount_amount ) ? null : $discount_amount->get_value();
							},
							$lines->get_array()
						);

						$discount_amount = new Money( \array_sum( $values ), $lines->get_amount()->get_currency() );

						echo \esc_html( $discount_amount->format_i18n() );

						?>
					</td>
					<td>
						<?php echo \esc_html( $lines->get_amount()->format_i18n() ); ?>
					</td>
					<td>
						<?php

						$values = \array_map(
							function( PaymentLine $line ) {
								$total_amount = $line->get_total_amount();

								if ( $total_amount instanceof TaxedMoney ) {
									return $total_amount->get_tax_value();
								}

								return null;
							},
							$lines->get_array()
						);

						$tax_amount = new Money( \array_sum( $values ), $lines->get_amount()->get_currency() );

						echo \esc_html( $tax_amount->format_i18n() );

						?>
					</td>
				</tr>
			</tfoot>

			<tbody>

				<?php foreach ( $lines as $line ) : ?>

					<tr>
						<td><?php echo \esc_html( $line->get_id() ); ?></td>
						<td><?php echo \esc_html( $line->get_sku() ); ?></td>
						<td>
							<?php

							$image_url = $line->get_image_url();

							if ( ! empty( $image_url ) ) {
								\printf(
									'<img src="%s" alt="" width="50" height="50" />',
									\esc_url( $image_url )
								);
							}

							?>
						</td>
						<td>
							<?php

							$product_url = $line->get_product_url();

							$description = $line->get_description();

							if ( ! empty( $product_url ) ) {
								// Product URL with or without description.
								$line_title = $line->get_name();

								$classes = array();

								if ( ! empty( $description ) ) {
									$line_title = $line->get_description();
									$classes[]  = 'pronamic-pay-tip';
								}

								\printf(
									'<a class="%1$s" href="%2$s" title="%3$s">%4$s<a/>',
									\esc_attr( \implode( ' ', $classes ) ),
									\esc_url( $line->get_product_url() ),
									\esc_attr( $line_title ),
									\esc_html( $line->get_name() )
								);
							} elseif ( ! empty( $description ) ) {
								// Description without product URL.
								\printf(
									'<span class="pronamic-pay-tip" title="%1$s">%2$s</span>',
									\esc_attr( $line->get_description() ),
									\esc_html( $line->get_name() )
								);
							} else {
								// No description and no product URL.
								echo \esc_html( $line->get_name() );
							}

							?>
						</td>
						<td>
							<?php

							$unit_price = $line->get_unit_price();

							if ( null !== $unit_price ) {
								$tips = array(
									\__( 'No tax information.', 'pronamic_ideal' ),
								);

								if ( $unit_price instanceof TaxedMoney ) {
									$tips = array(
										\sprintf(
											/* translators: %s: price excluding tax */
											\__( 'Exclusive tax: %s', 'pronamic_ideal' ),
											$unit_price->get_excluding_tax()
										),
										\sprintf(
											/* translators: %s: price including tax */
											\__( 'Inclusive tax: %s', 'pronamic_ideal' ),
											$unit_price->get_including_tax()
										),
									);
								}

								\printf(
									'<span class="pronamic-pay-tip" title="%s">%s</span>',
									\esc_attr( \implode( '<br />', $tips ) ),
									\esc_html( $unit_price->format_i18n() )
								);

							}

							?>
						</td>
						<td><?php echo \esc_html( $line->get_quantity() ); ?></td>
						<td>
							<?php

							$discount_amount = $line->get_discount_amount();

							if ( null !== $discount_amount ) {
								echo \esc_html( $discount_amount );
							}

							?>
						</td>
						<td>
							<?php

							$line_total = $line->get_total_amount();

							$tips = array(
								\__( 'No tax information.', 'pronamic_ideal' ),
							);

							if ( $line_total instanceof TaxedMoney ) {
								$tips = array(
									\sprintf(
										/* translators: %s: price excluding tax */
										\__( 'Exclusive tax: %s', 'pronamic_ideal' ),
										$line->get_total_amount()->get_excluding_tax()
									),
									\sprintf(
										/* translators: %s: price including tax */
										\__( 'Inclusive tax: %s', 'pronamic_ideal' ),
										$line->get_total_amount()->get_including_tax()
									),
								);
							}

							\printf(
								'<span class="pronamic-pay-tip" title="%s">%s</span>',
								\esc_attr( \implode( '<br />', $tips ) ),
								\esc_html( $line_total->format_i18n() )
							);

							?>
						</td>
						<td>
							<?php

							if ( $line_total instanceof TaxedMoney ) {
								$tax_amount     = $line_total->get_tax_amount();
								$tax_percentage = $line_total->get_tax_percentage();

								if ( null !== $tax_amount ) {
									$tip = '';

									if ( null !== $tax_percentage ) {
										$number = Number::from_mixed( $tax_percentage );

										$tip = $number->format_i18n() . '%';
									}

									\printf(
										'<span class="pronamic-pay-tip" title="%s">%s</span>',
										\esc_attr( $tip ),
										\esc_html( $tax_amount->format_i18n() )
									);
								}
							}

							?>
						</td>
					</tr>

				<?php endforeach; ?>

			</tbody>
		</table>
	</div>

<?php endif; ?>
