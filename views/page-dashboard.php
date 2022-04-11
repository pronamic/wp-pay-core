<?php
/**
 * Page Dashboard
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

$container_index = 1;

?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">

			<?php if ( current_user_can( 'edit_payments' ) ) : ?>

				<div id="postbox-container-<?php echo \esc_attr( (string) $container_index ); ?>" class="postbox-container">
					<div id="normal-sortables" class="meta-box-sortables ui-sortable">

						<div class="postbox">
							<div class="postbox-header">
								<h2 class="hndle"><span><?php esc_html_e( 'Pronamic Pay Status', 'pronamic_ideal' ); ?></span></h2>
							</div>

							<div class="inside">
								<?php

								pronamic_pay_plugin()->admin->dashboard->status_widget();

								?>
							</div>
						</div>
					</div>

					<div id="normal-sortables" class="meta-box-sortables ui-sortable">

						<div class="postbox">
							<div class="postbox-header">
								<h2 class="hndle"><span><?php esc_html_e( 'Latest Payments', 'pronamic_ideal' ); ?></span></h2>
							</div>

							<div class="inside">
								<?php

								$payments_post_type = \Pronamic\WordPress\Pay\Admin\AdminPaymentPostType::POST_TYPE;

								$query = new WP_Query(
									array(
										'post_type'      => $payments_post_type,
										'post_status'    => \array_keys( \Pronamic\WordPress\Pay\Payments\PaymentPostType::get_payment_states() ),
										'posts_per_page' => 5,
									)
								);

								if ( $query->have_posts() ) :

									$columns = array(
										'status',
										'subscription',
										'title',
										'amount',
										'date',
									);

									// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
									$column_titles = apply_filters( 'manage_edit-' . $payments_post_type . '_columns', array() );

									?>

									<div id="dashboard_recent_drafts">
										<table class="wp-list-table widefat fixed striped posts">

											<tr class="type-<?php echo esc_attr( $payments_post_type ); ?>">

												<?php

												foreach ( $columns as $column ) :
													$custom_column = sprintf( '%1$s_%2$s', $payments_post_type, $column );

													// Column classes.
													$classes = array(
														sprintf( 'column-%s', $custom_column ),
													);

													if ( 'pronamic_payment_title' === $custom_column ) :
														$classes[] = 'column-primary';
													endif;

													printf(
														'<th class="%1$s">%2$s</th>',
														esc_attr( implode( ' ', $classes ) ),
														wp_kses_post( $column_titles[ $custom_column ] )
													);

												endforeach;

												?>

											</tr>

											<?php
											while ( $query->have_posts() ) :
												$query->the_post();
												?>

												<tr class="type-<?php echo esc_attr( $payments_post_type ); ?>">
													<?php

													$payment_id = get_the_ID();

													// Loop columns.
													foreach ( $columns as $column ) :

														$custom_column = sprintf( '%1$s_%2$s', $payments_post_type, $column );

														// Column classes.
														$classes = array(
															$custom_column,
															'column-' . $custom_column,
														);

														if ( 'pronamic_payment_title' === $custom_column ) {
															$classes[] = 'column-primary';
														}

														printf(
															'<td class="%1$s" data-colname="%2$s">',
															esc_attr( implode( ' ', $classes ) ),
															esc_html( $column_titles[ $custom_column ] )
														);

														// Do custom column action.
														do_action(
															'manage_' . $payments_post_type . '_posts_custom_column',
															$custom_column,
															$payment_id
														);

														if ( 'pronamic_payment_title' === $custom_column ) :

															printf(
																'<button type = "button" class="toggle-row" ><span class="screen-reader-text">%1$s</span ></button>',
																esc_html( __( 'Show more details', 'pronamic_ideal' ) )
															);

														endif;

														echo '</td>';

													endforeach;

													?>

												</tr>

											<?php endwhile; ?>

										</table>
									</div>

									<?php wp_reset_postdata(); ?>

								<?php else : ?>

									<p><?php esc_html_e( 'No pending payments found.', 'pronamic_ideal' ); ?></p>

								<?php endif; ?>
							</div>
						</div>
					</div>

					<?php

					$subscriptions_post_type = \Pronamic\WordPress\Pay\Admin\AdminSubscriptionPostType::POST_TYPE;

					$query = new WP_Query(
						array(
							'post_type'      => $subscriptions_post_type,
							'post_status'    => \array_keys( \Pronamic\WordPress\Pay\Subscriptions\SubscriptionPostType::get_states() ),
							'posts_per_page' => 5,
						)
					);

					if ( $query->have_posts() ) :
						?>

						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<div class="postbox">
								<div class="postbox-header">
									<h2 class="hndle"><span><?php esc_html_e( 'Latest Subscriptions', 'pronamic_ideal' ); ?></span></h2>
								</div>

								<div class="inside">
									<?php

										$columns = array(
											'status',
											'title',
											'amount',
											'date',
										);

										// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
										$column_titles = apply_filters( 'manage_edit-' . $subscriptions_post_type . '_columns', array() );

										?>

										<div id="dashboard_pronamic_pay_subscriptions">
											<table class="wp-list-table widefat fixed striped posts">

												<tr class="type-<?php echo esc_attr( $subscriptions_post_type ); ?>">

													<?php

													foreach ( $columns as $column ) :
														$custom_column = sprintf( '%1$s_%2$s', 'pronamic_subscription', $column );

														// Column classes.
														$classes = array(
															sprintf( 'column-%s', $custom_column ),
														);

														if ( 'pronamic_subscription_title' === $custom_column ) :
															$classes[] = 'column-primary';
														endif;

														printf(
															'<th class="%1$s">%2$s</th>',
															esc_attr( implode( ' ', $classes ) ),
															wp_kses_post( $column_titles[ $custom_column ] )
														);

													endforeach;

													?>

												</tr>

												<?php
												while ( $query->have_posts() ) :
													$query->the_post();
													?>

													<tr class="type-<?php echo esc_attr( $subscriptions_post_type ); ?>">
														<?php

														$payment_id = get_the_ID();

														// Loop columns.
														foreach ( $columns as $column ) :

															$custom_column = sprintf( '%1$s_%2$s', 'pronamic_subscription', $column );

															// Column classes.
															$classes = array(
																$custom_column,
																'column-' . $custom_column,
															);

															if ( 'pronamic_subscription_title' === $custom_column ) {
																$classes[] = 'column-primary';
															}

															printf(
																'<td class="%1$s" data-colname="%2$s">',
																esc_attr( implode( ' ', $classes ) ),
																esc_html( $column_titles[ $custom_column ] )
															);

															// Do custom column action.
															do_action(
																'manage_' . $subscriptions_post_type . '_posts_custom_column',
																$custom_column,
																$payment_id
															);

															if ( 'pronamic_subscription_title' === $custom_column ) :

																printf(
																	'<button type = "button" class="toggle-row" ><span class="screen-reader-text">%1$s</span ></button>',
																	esc_html( __( 'Show more details', 'pronamic_ideal' ) )
																);

															endif;

															echo '</td>';

														endforeach;

														?>

													</tr>

												<?php endwhile; ?>

											</table>
										</div>

										<?php wp_reset_postdata(); ?>
								</div>
							</div>
						</div>

					<?php endif; ?>
				</div>

				<?php $container_index++; ?>

			<?php endif; ?>

			<div id="postbox-container-<?php echo \esc_attr( (string) $container_index ); ?>" class="postbox-container">
				<div id="side-sortables" class="meta-box-sortables ui-sortable">
					<?php if ( current_user_can( 'manage_options' ) ) : ?>

						<div class="postbox">
							<div class="postbox-header">
								<h2 class="hndle"><span><?php esc_html_e( 'Getting Started', 'pronamic_ideal' ); ?></span></h2>
							</div>

							<div class="inside">
								<p>
									<?php esc_html_e( "Please follow the tour, read the 'What is new' and 'Getting Started' pages before contacting us. Also, check the Site Health page for any issues.", 'pronamic_ideal' ); ?>
								</p>

								<?php

								printf(
									'<a href="%s" class="button-secondary">%s</a>',
									esc_attr(
										wp_nonce_url(
											add_query_arg(
												array(
													'page' => 'pronamic_ideal',
													'pronamic_pay_ignore_tour' => '0',
												)
											),
											'pronamic_pay_ignore_tour',
											'pronamic_pay_nonce'
										)
									),
									esc_html__( 'Start tour', 'pronamic_ideal' )
								);

								echo ' ';

								printf(
									'<a href="%s" class="button-secondary">%s</a>',
									esc_attr(
										add_query_arg(
											array(
												'page' => 'pronamic-pay-about',
												'tab'  => 'new',
											)
										)
									),
									esc_html__( 'What is new', 'pronamic_ideal' )
								);

								echo ' ';

								printf(
									'<a href="%s" class="button-secondary">%s</a>',
									esc_attr(
										add_query_arg(
											array(
												'page' => 'pronamic-pay-about',
												'tab'  => 'getting-started',
											)
										)
									),
									esc_html__( 'Getting started', 'pronamic_ideal' )
								);

								echo ' ';

								// Site Health button.

								if ( version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) && current_user_can( 'view_site_health_checks' ) ) :

									printf(
										'<a href="%s" class="button-secondary">%s</a>',
										esc_attr( get_admin_url( null, 'site-health.php' ) ),
										esc_html__( 'Site Health', 'pronamic_ideal' )
									);

								endif;

								// System Status button.
								if ( version_compare( get_bloginfo( 'version' ), '5.2', '<' ) ) :

									printf(
										'<a href="%s" class="button-secondary">%s</a>',
										esc_attr(
											add_query_arg(
												array(
													'page' => 'pronamic_pay_tools',
												)
											)
										),
										esc_html__( 'System Status', 'pronamic_ideal' )
									);

								endif;

								?>
							</div>
						</div>

					<?php endif; ?>

					<div class="postbox">
						<div class="postbox-header">
							<h2 class="hndle"><span><?php esc_html_e( 'Pronamic News', 'pronamic_ideal' ); ?></span></h2>
						</div>

						<div class="inside">
							<?php

							wp_widget_rss_output(
								'https://feeds.feedburner.com/pronamic',
								array(
									'link'  => __( 'http://www.pronamic.eu/', 'pronamic_ideal' ),
									'url'   => 'http://feeds.feedburner.com/pronamic',
									'title' => __( 'Pronamic News', 'pronamic_ideal' ),
									'items' => 5,
								)
							);

							?>
						</div>
					</div>

					<?php require __DIR__ . '/pronamic.php'; ?>

				</div>
			</div>

			<div class="clear"></div>
		</div>
	</div>
</div>
