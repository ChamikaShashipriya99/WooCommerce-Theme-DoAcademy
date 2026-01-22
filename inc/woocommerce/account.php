<?php
/**
 * WooCommerce Account Functions
 *
 * Handles My Account page customizations including order display,
 * order status styling, and order download functionality.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Customize order status display in My Account orders table
 * - Add order download button and handle download requests
 * - Generate downloadable order HTML files
 * - My Account page modifications
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - My Account page hooks (woocommerce_my_account_* hooks)
 * - Order details page modifications
 * - Customer-facing order functionality
 * - Order download/export features
 * - Account dashboard customizations
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Admin order management (use admin.php)
 * - Checkout process (use checkout.php)
 * - Cart functionality (use cart.php)
 * - Product display (use product.php)
 * - Order emails (use admin.php)
 *
 * ============================================================================
 * SECURITY NOTES:
 * ============================================================================
 * - Order downloads are protected by nonce verification
 * - Users can only download their own orders (or admin can download any)
 * - All user input is sanitized and validated
 *
 * ============================================================================
 * WOOCOMMERCE DEPENDENCY:
 * ============================================================================
 * This file is loaded ONLY when WooCommerce is active (checked in functions.php).
 * All functions should still include WooCommerce checks as a safety measure.
 *
 * ============================================================================
 * WOOCOMMERCE HOOKS USED:
 * ============================================================================
 * - woocommerce_my_account_my_orders_column_order-status : Order status display
 * - woocommerce_order_details_after_order_table          : Add download button
 * - template_redirect                                     : Handle download requests
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce/Account
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Style Order Status Labels in My Account Orders Table
 *
 * Wraps order status with proper HTML markup and CSS classes for styling.
 * Classes follow WooCommerce convention: "order-status status-{slug}".
 *
 * @since 1.0.0
 * @param WC_Order $order Order object.
 * @return void
 */
function woocommerce_theme_wrap_order_status_with_markup( $order ) {
	if ( ! class_exists( 'WooCommerce' ) || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	$status_slug = $order->get_status();
	$status_name = wc_get_order_status_name( $status_slug );

	printf(
		'<mark class="order-status status-%s">%s</mark>',
		esc_attr( $status_slug ),
		esc_html( $status_name )
	);
}
add_action( 'woocommerce_my_account_my_orders_column_order-status', 'woocommerce_theme_wrap_order_status_with_markup', 10, 1 );

/**
 * Add Download Order Button to Order Details Page
 *
 * Displays a "Download Order" button after the order details table,
 * allowing customers to download their order as an HTML file.
 *
 * @since 1.0.0
 * @param WC_Order $order Order object.
 * @return void
 */
function woocommerce_theme_add_order_download_button( $order ) {
	if ( ! class_exists( 'WooCommerce' ) || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	$order_id = $order->get_id();

	// Build secure download URL with nonce.
	$download_url = wp_nonce_url(
		add_query_arg(
			array(
				'download_order' => $order_id,
			),
			home_url( '/' )
		),
		'download_order_' . $order_id,
		'order_nonce'
	);

	?>
	<div class="woocommerce-order-download-wrapper">
		<a href="<?php echo esc_url( $download_url ); ?>" class="button woocommerce-order-download-button">
			<i class="fas fa-download" aria-hidden="true"></i>
			<?php esc_html_e( 'Download Order', 'woocommerce' ); ?>
		</a>
	</div>
	<?php
}
add_action( 'woocommerce_order_details_after_order_table', 'woocommerce_theme_add_order_download_button', 10, 1 );

/**
 * Handle Order Download Request
 *
 * Processes download requests, validates permissions, and generates
 * the downloadable HTML file. Hooked to template_redirect to intercept
 * requests before template loading.
 *
 * Security checks:
 * - Nonce verification
 * - User must be logged in
 * - User must own the order (or be admin)
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_handle_order_download() {
	// Check if this is a download request.
	if ( ! isset( $_GET['download_order'] ) ) {
		return;
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$order_id = absint( $_GET['download_order'] );

	// Verify nonce.
	if ( ! isset( $_GET['order_nonce'] ) || ! wp_verify_nonce( $_GET['order_nonce'], 'download_order_' . $order_id ) ) {
		wp_die( esc_html__( 'Security check failed. Please try again.', 'woocommerce' ), esc_html__( 'Download Error', 'woocommerce' ), array( 'response' => 403 ) );
	}

	// Get order.
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		wp_die( esc_html__( 'Order not found.', 'woocommerce' ), esc_html__( 'Download Error', 'woocommerce' ), array( 'response' => 404 ) );
	}

	// Require login.
	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'You must be logged in to download orders.', 'woocommerce' ), esc_html__( 'Download Error', 'woocommerce' ), array( 'response' => 403 ) );
	}

	// Verify ownership (user owns order OR is admin).
	$current_user_id = get_current_user_id();
	$order_user_id   = $order->get_user_id();

	if ( $order_user_id !== $current_user_id && ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You do not have permission to download this order.', 'woocommerce' ), esc_html__( 'Download Error', 'woocommerce' ), array( 'response' => 403 ) );
	}

	// Generate and output the download file.
	woocommerce_theme_generate_order_download( $order );

	exit;
}
add_action( 'template_redirect', 'woocommerce_theme_handle_order_download', 10 );

/**
 * Generate Order Download HTML File
 *
 * Creates a complete HTML document containing all order details,
 * formatted for printing or saving. Outputs directly with appropriate
 * headers for file download.
 *
 * Includes:
 * - Order header (number, date, status, payment method)
 * - Billing address (with custom fields)
 * - Shipping address (if applicable)
 * - Order items with variations
 * - Order totals
 *
 * @since 1.0.0
 * @param WC_Order $order Order object.
 * @return void
 */
function woocommerce_theme_generate_order_download( $order ) {
	$order_id     = $order->get_id();
	$order_number = $order->get_order_number();
	$order_date   = $order->get_date_created();
	$order_status = wc_get_order_status_name( $order->get_status() );
	$order_total  = $order->get_formatted_order_total();

	// Get billing information.
	$billing_first_name = $order->get_billing_first_name();
	$billing_last_name  = $order->get_billing_last_name();
	$billing_company    = $order->get_billing_company();
	$billing_address_1  = $order->get_billing_address_1();
	$billing_address_2  = $order->get_billing_address_2();
	$billing_city       = $order->get_billing_city();
	$billing_state      = $order->get_billing_state();
	$billing_postcode   = $order->get_billing_postcode();
	$billing_country    = $order->get_billing_country();
	$billing_email      = $order->get_billing_email();
	$billing_phone      = $order->get_billing_phone();

	// Get shipping information.
	$shipping_first_name = $order->get_shipping_first_name();
	$shipping_last_name  = $order->get_shipping_last_name();
	$shipping_company    = $order->get_shipping_company();
	$shipping_address_1  = $order->get_shipping_address_1();
	$shipping_address_2  = $order->get_shipping_address_2();
	$shipping_city       = $order->get_shipping_city();
	$shipping_state      = $order->get_shipping_state();
	$shipping_postcode   = $order->get_shipping_postcode();
	$shipping_country    = $order->get_shipping_country();

	// Get custom fields.
	$business_type = get_post_meta( $order_id, '_billing_business_type', true );
	$vat_number    = get_post_meta( $order_id, '_billing_vat_number', true );

	$order_items          = $order->get_items();
	$payment_method_title = $order->get_payment_method_title();

	$shipping_methods = $order->get_shipping_methods();
	$shipping_method  = '';
	if ( ! empty( $shipping_methods ) ) {
		$shipping_method = reset( $shipping_methods );
		$shipping_method = $shipping_method->get_method_title();
	}

	$formatted_date = $order_date ? $order_date->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : '';

	$countries             = WC()->countries->get_countries();
	$billing_country_name  = isset( $countries[ $billing_country ] ) ? $countries[ $billing_country ] : $billing_country;
	$shipping_country_name = isset( $countries[ $shipping_country ] ) ? $countries[ $shipping_country ] : $shipping_country;

	// Set download headers.
	$filename = 'order-' . $order_number . '-' . gmdate( 'Y-m-d' ) . '.html';
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Pragma: public' );

	?>
	<!DOCTYPE html>
	<html lang="<?php echo esc_attr( get_locale() ); ?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo esc_html( sprintf( __( 'Order #%s', 'woocommerce' ), $order_number ) ); ?></title>
		<style>
			* { margin: 0; padding: 0; box-sizing: border-box; }
			body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; color: #333; padding: 20px; background-color: #fff; }
			.order-header { border-bottom: 3px solid #0073aa; padding-bottom: 20px; margin-bottom: 30px; }
			.order-header h1 { color: #0073aa; font-size: 24px; margin-bottom: 10px; }
			.order-meta { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 15px; }
			.order-meta-item { flex: 1; min-width: 200px; }
			.order-meta-label { font-weight: bold; color: #666; margin-bottom: 5px; }
			.order-meta-value { color: #333; }
			.section { margin-bottom: 30px; page-break-inside: avoid; }
			.section-title { background-color: #0073aa; color: #fff; padding: 10px 15px; font-size: 16px; font-weight: bold; margin-bottom: 15px; }
			.address-box { background-color: #f9f9f9; border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; }
			.address-box strong { display: block; margin-bottom: 8px; color: #0073aa; }
			.order-items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
			.order-items-table th { background-color: #0073aa; color: #fff; padding: 10px; text-align: left; border: 1px solid #005a87; }
			.order-items-table td { padding: 10px; border: 1px solid #ddd; }
			.order-items-table tr:nth-child(even) { background-color: #f9f9f9; }
			.order-totals { width: 100%; border-collapse: collapse; margin-top: 20px; }
			.order-totals th, .order-totals td { padding: 8px 10px; text-align: right; border-bottom: 1px solid #ddd; }
			.order-totals th { text-align: left; font-weight: bold; }
			.order-totals .order-total-row { font-size: 16px; font-weight: bold; background-color: #f0f0f0; }
			.order-totals .order-total-row th, .order-totals .order-total-row td { padding: 12px 10px; border-top: 2px solid #0073aa; }
			.footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #666; font-size: 11px; }
			@media print { body { padding: 10px; } .section { page-break-inside: avoid; } }
		</style>
	</head>
	<body>
		<div class="order-header">
			<h1><?php echo esc_html( sprintf( __( 'Order #%s', 'woocommerce' ), $order_number ) ); ?></h1>
			<div class="order-meta">
				<div class="order-meta-item">
					<div class="order-meta-label"><?php esc_html_e( 'Order Date', 'woocommerce' ); ?>:</div>
					<div class="order-meta-value"><?php echo esc_html( $formatted_date ); ?></div>
				</div>
				<div class="order-meta-item">
					<div class="order-meta-label"><?php esc_html_e( 'Order Status', 'woocommerce' ); ?>:</div>
					<div class="order-meta-value"><?php echo esc_html( $order_status ); ?></div>
				</div>
				<div class="order-meta-item">
					<div class="order-meta-label"><?php esc_html_e( 'Payment Method', 'woocommerce' ); ?>:</div>
					<div class="order-meta-value"><?php echo esc_html( $payment_method_title ); ?></div>
				</div>
			</div>
		</div>

		<div class="section">
			<div class="section-title"><?php esc_html_e( 'Billing Address', 'woocommerce' ); ?></div>
			<div class="address-box">
				<strong><?php echo esc_html( trim( $billing_first_name . ' ' . $billing_last_name ) ); ?></strong>
				<?php if ( $billing_company ) : ?>
					<div><?php echo esc_html( $billing_company ); ?></div>
				<?php endif; ?>
				<div><?php echo esc_html( $billing_address_1 ); ?></div>
				<?php if ( $billing_address_2 ) : ?>
					<div><?php echo esc_html( $billing_address_2 ); ?></div>
				<?php endif; ?>
				<div>
					<?php
					echo esc_html( $billing_city );
					if ( $billing_state ) {
						echo ', ' . esc_html( $billing_state );
					}
					if ( $billing_postcode ) {
						echo ' ' . esc_html( $billing_postcode );
					}
					?>
				</div>
				<div><?php echo esc_html( $billing_country_name ); ?></div>
				<?php if ( $billing_email ) : ?>
					<div style="margin-top: 8px;"><strong><?php esc_html_e( 'Email', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $billing_email ); ?></div>
				<?php endif; ?>
				<?php if ( $billing_phone ) : ?>
					<div><strong><?php esc_html_e( 'Phone', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $billing_phone ); ?></div>
				<?php endif; ?>
				<?php if ( $business_type ) : ?>
					<?php
					$business_type_map   = array(
						'individual' => __( 'Individual', 'woocommerce' ),
						'company'    => __( 'Company', 'woocommerce' ),
					);
					$business_type_label = isset( $business_type_map[ $business_type ] ) ? $business_type_map[ $business_type ] : $business_type;
					?>
					<div style="margin-top: 8px;"><strong><?php esc_html_e( 'Business Type', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $business_type_label ); ?></div>
				<?php endif; ?>
				<?php if ( $vat_number ) : ?>
					<div><strong><?php esc_html_e( 'VAT Number', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $vat_number ); ?></div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $order->needs_shipping_address() && ! empty( $shipping_address_1 ) ) : ?>
			<div class="section">
				<div class="section-title"><?php esc_html_e( 'Shipping Address', 'woocommerce' ); ?></div>
				<div class="address-box">
					<strong><?php echo esc_html( trim( $shipping_first_name . ' ' . $shipping_last_name ) ); ?></strong>
					<?php if ( $shipping_company ) : ?>
						<div><?php echo esc_html( $shipping_company ); ?></div>
					<?php endif; ?>
					<div><?php echo esc_html( $shipping_address_1 ); ?></div>
					<?php if ( $shipping_address_2 ) : ?>
						<div><?php echo esc_html( $shipping_address_2 ); ?></div>
					<?php endif; ?>
					<div>
						<?php
						echo esc_html( $shipping_city );
						if ( $shipping_state ) {
							echo ', ' . esc_html( $shipping_state );
						}
						if ( $shipping_postcode ) {
							echo ' ' . esc_html( $shipping_postcode );
						}
						?>
					</div>
					<div><?php echo esc_html( $shipping_country_name ); ?></div>
					<?php if ( $shipping_method ) : ?>
						<div style="margin-top: 8px;"><strong><?php esc_html_e( 'Shipping Method', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $shipping_method ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="section">
			<div class="section-title"><?php esc_html_e( 'Order Items', 'woocommerce' ); ?></div>
			<table class="order-items-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $order_items as $item_id => $item ) {
						$product      = $item->get_product();
						$product_name = $item->get_name();
						$quantity     = $item->get_quantity();
						$item_total   = $order->get_formatted_line_subtotal( $item );

						$item_meta_data = $item->get_meta_data();
						$color_value    = '';
						$size_value     = '';

						foreach ( $item_meta_data as $meta ) {
							$meta_key   = $meta->key;
							$meta_value = $meta->value;

							if ( strpos( $meta_key, 'color' ) !== false || strpos( $meta_key, 'pa_color' ) !== false ) {
								$color_value = $meta_value;
							}

							if ( strpos( $meta_key, 'size' ) !== false || strpos( $meta_key, 'pa_size' ) !== false ) {
								$size_value = $meta_value;
							}
						}

						$formatted_meta = $item->get_formatted_meta_data( '_', true );
						foreach ( $formatted_meta as $meta ) {
							$meta_key_lower = strtolower( $meta->display_key );
							if ( strpos( $meta_key_lower, 'color' ) !== false && empty( $color_value ) ) {
								$color_value = $meta->display_value;
							}
							if ( strpos( $meta_key_lower, 'size' ) !== false && empty( $size_value ) ) {
								$size_value = $meta->display_value;
							}
						}
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $product_name ); ?></strong>
								<?php
								if ( ! empty( $color_value ) || ! empty( $size_value ) ) {
									echo '<div style="font-size: 11px; color: #666; margin-top: 8px; padding-top: 5px; border-top: 1px solid #eee;">';
									if ( ! empty( $color_value ) ) {
										echo '<strong>' . esc_html__( 'Color', 'woocommerce' ) . ':</strong> ' . esc_html( $color_value ) . '<br>';
									}
									if ( ! empty( $size_value ) ) {
										echo '<strong>' . esc_html__( 'Size', 'woocommerce' ) . ':</strong> ' . esc_html( $size_value ) . '<br>';
									}
									echo '</div>';
								}

								if ( ! empty( $formatted_meta ) ) {
									$other_meta = array();
									foreach ( $formatted_meta as $meta ) {
										$meta_key_lower = strtolower( $meta->display_key );
										$is_color       = strpos( $meta_key_lower, 'color' ) !== false;
										$is_size        = strpos( $meta_key_lower, 'size' ) !== false;

										if ( ! $is_color && ! $is_size ) {
											$other_meta[] = $meta;
										}
									}

									if ( ! empty( $other_meta ) ) {
										echo '<div style="font-size: 11px; color: #666; margin-top: 5px;">';
										foreach ( $other_meta as $meta ) {
											echo esc_html( $meta->display_key . ': ' . $meta->display_value ) . '<br>';
										}
										echo '</div>';
									}
								}
								?>
							</td>
							<td><?php echo esc_html( $quantity ); ?></td>
							<td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item, true, true ) ); ?></td>
							<td><?php echo wp_kses_post( $item_total ); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>

			<table class="order-totals">
				<?php
				foreach ( $order->get_order_item_totals() as $key => $total ) {
					?>
					<tr class="<?php echo ( 'order_total' === $key ) ? 'order-total-row' : ''; ?>">
						<th><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>

		<div class="footer">
			<p><?php echo esc_html( sprintf( __( 'This is a copy of order #%s from %s', 'woocommerce' ), $order_number, get_bloginfo( 'name' ) ) ); ?></p>
			<p><?php echo esc_html( sprintf( __( 'Generated on %s', 'woocommerce' ), current_time( 'Y-m-d H:i:s' ) ) ); ?></p>
		</div>
	</body>
	</html>
	<?php
}
