<?php
/**
 * WooCommerce Cart Functions
 *
 * Handles all cart-related functionality including the mini cart dropdown,
 * AJAX cart updates, and automatic discount calculations.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Mini cart HTML rendering
 * - AJAX cart fragment updates (real-time cart updates without page reload)
 * - Automatic discount/fee calculations based on cart totals
 * - Cart-related display functions
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - Mini cart template/rendering functions
 * - WooCommerce cart fragment filters
 * - Cart fee calculations (discounts, surcharges)
 * - Cart-related AJAX handlers
 * - Cart page customizations
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Checkout functionality (use checkout.php)
 * - Product display (use product.php or hooks.php)
 * - Order processing (use admin.php or account.php)
 * - Shipping calculations (use shipping.php)
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
 * - woocommerce_cart_calculate_fees    : Add fees/discounts to cart
 * - woocommerce_add_to_cart_fragments  : Update cart HTML via AJAX
 * - woocommerce_update_order_review_fragments : Update during checkout
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce/Cart
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apply Automatic Discount for Orders Over Threshold
 *
 * Automatically applies a percentage discount when the cart subtotal exceeds
 * a configurable threshold. Settings are managed in WooCommerce > Settings > Products.
 *
 * Options used:
 * - wc_auto_discount_threshold  : Minimum subtotal to trigger discount
 * - wc_auto_discount_percentage : Percentage discount to apply
 *
 * @since 1.0.0
 * @param WC_Cart $cart Cart object containing cart data.
 * @return void
 */
function woocommerce_theme_apply_automatic_discount( $cart ) {
	if ( ! class_exists( 'WooCommerce' ) || ! is_a( $cart, 'WC_Cart' ) ) {
		return;
	}

	if ( $cart->is_empty() ) {
		return;
	}

	// Get cart subtotal and discount settings.
	$cart_subtotal = $cart->get_subtotal();
	$discount_threshold = get_option( 'wc_auto_discount_threshold' );
	$discount_percentage_raw = get_option( 'wc_auto_discount_percentage' );
	$discount_percentage = floatval( $discount_percentage_raw ) / 100;

	// Only apply if cart meets threshold.
	if ( $cart_subtotal <= $discount_threshold ) {
		return;
	}

	// Calculate discount amount.
	$discount_amount = $cart_subtotal * $discount_percentage;
	$discount_percentage_display = get_option( 'wc_auto_discount_percentage' );
	$discount_fee_name = sprintf( __( 'Bulk Order Discount (%g%%)', 'woocommerce' ), $discount_percentage_display );

	// Prevent duplicate discount application.
	$existing_fees = $cart->get_fees();
	$discount_already_applied = false;

	foreach ( $existing_fees as $fee_key => $fee ) {
		if ( isset( $fee->name ) && $discount_fee_name === $fee->name ) {
			$discount_already_applied = true;
			break;
		}
	}

	// Add discount as negative fee.
	if ( ! $discount_already_applied ) {
		$cart->add_fee(
			$discount_fee_name,
			-$discount_amount,
			false,
			''
		);
	}
}
add_action( 'woocommerce_cart_calculate_fees', 'woocommerce_theme_apply_automatic_discount', 10, 1 );

/**
 * Render Mini Cart HTML
 *
 * Generates the complete HTML for the mini cart dropdown. This function is
 * used both for initial page load and for AJAX fragment updates.
 *
 * Returns different HTML based on whether the cart is empty or has items.
 *
 * @since 1.0.0
 * @param bool $echo Whether to echo output (true) or return it (false).
 * @return string|void HTML string if $echo is false, void if $echo is true.
 */
function woocommerce_render_mini_cart( $echo = false ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	$cart = WC()->cart;

	if ( ! $cart ) {
		return '';
	}

	$cart_count = $cart->get_cart_contents_count();

	ob_start();

	if ( ! $cart->is_empty() ) :
		$cart_total = $cart->get_cart_total();
		$cart_items = $cart->get_cart();
		?>
		<div class="mini-cart__dropdown" id="mini-cart-dropdown" role="region" aria-label="<?php esc_attr_e( 'Shopping Cart', 'woocommerce' ); ?>">
			<div class="mini-cart__header">
				<h3 class="mini-cart__title">
					<?php esc_html_e( 'Cart', 'woocommerce' ); ?>
					<span class="mini-cart__count" id="mini-cart-count-text" aria-label="<?php echo esc_attr( sprintf( _n( '%d item', '%d items', $cart_count, 'woocommerce' ), $cart_count ) ); ?>">
						(<?php echo esc_html( $cart_count ); ?>)
					</span>
				</h3>
			</div>

			<div class="mini-cart__items" id="mini-cart-items">
				<ul class="mini-cart__list" role="list" id="mini-cart-list">
					<?php
					foreach ( $cart_items as $cart_item_key => $cart_item ) :
						$product = wc_get_product( $cart_item['product_id'] );

						if ( ! $product ) {
							continue;
						}

						$product_permalink = $product->get_permalink();
						$product_name = $product->get_name();
						$remove_url = wc_get_cart_remove_url( $cart_item_key );
						$quantity = $cart_item['quantity'];
						$line_subtotal = $cart->get_product_subtotal( $product, $cart_item['quantity'] );
						?>
						<li class="mini-cart__item" role="listitem">
							<div class="mini-cart__item-content">
								<a
									href="<?php echo esc_url( $remove_url ); ?>"
									class="remove mini-cart__item-remove"
									aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), $product_name ) ); ?>"
									title="<?php echo esc_attr__( 'Remove item', 'woocommerce' ); ?>"
								>
									<i class="fas fa-times" aria-hidden="true"></i>
									<span class="screen-reader-text">
										<?php echo esc_html( sprintf( __( 'Remove %s from cart', 'woocommerce' ), $product_name ) ); ?>
									</span>
								</a>
								<?php
								echo $product->get_image( 'woocommerce_thumbnail', array(
									'alt'   => $product_name,
									'class' => 'mini-cart__item-image',
								) );
								?>

								<div class="mini-cart__item-details">
									<h4 class="mini-cart__item-title">
										<a href="<?php echo esc_url( $product_permalink ); ?>" class="mini-cart__item-link">
											<?php echo esc_html( $product_name ); ?>
										</a>
									</h4>

									<?php
									$variation_data = wc_get_formatted_cart_item_data( $cart_item );
									if ( $variation_data ) {
										echo '<div class="mini-cart__item-variation">' . $variation_data . '</div>';
									}
									?>

									<div class="mini-cart__item-meta">
										<span class="mini-cart__item-quantity">
											<?php printf( esc_html__( 'Quantity: %d', 'woocommerce' ), $quantity ); ?>
										</span>
										<span class="mini-cart__item-price">
											<?php echo $line_subtotal; // Already escaped by WooCommerce. ?>
										</span>
									</div>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="mini-cart__footer" id="mini-cart-footer">
				<div class="mini-cart__total">
					<strong class="mini-cart__total-label">
						<?php esc_html_e( 'Total:', 'woocommerce' ); ?>
					</strong>
					<span class="mini-cart__total-amount" id="mini-cart-total">
						<?php echo $cart_total; // Already escaped by WooCommerce. ?>
					</span>
				</div>

				<div class="mini-cart__actions">
					<?php
					$cart_url     = wc_get_cart_url();
					$checkout_url = wc_get_checkout_url();
					?>
					<a href="<?php echo esc_url( $cart_url ); ?>" class="mini-cart__button mini-cart__button--view-cart">
						<?php esc_html_e( 'View Cart', 'woocommerce' ); ?>
					</a>
					<a href="<?php echo esc_url( $checkout_url ); ?>" class="mini-cart__button mini-cart__button--checkout">
						<?php esc_html_e( 'Checkout', 'woocommerce' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	else :
		?>
		<div class="mini-cart__dropdown mini-cart__dropdown--empty" id="mini-cart-dropdown" role="region" aria-label="<?php esc_attr_e( 'Shopping Cart', 'woocommerce' ); ?>">
			<div class="mini-cart__empty-message" id="mini-cart-empty">
				<p><?php esc_html_e( 'Your cart is empty.', 'woocommerce' ); ?></p>
			</div>
		</div>
		<?php
	endif;

	$output = ob_get_clean();

	if ( $echo ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	return $output;
}

/**
 * AJAX Cart Fragments
 *
 * Provides updated HTML fragments for AJAX cart updates. When products are
 * added to cart via AJAX, WooCommerce uses these fragments to update the
 * page without a full reload.
 *
 * Fragments returned:
 * - .mini-cart__trigger-count : Cart count badge
 * - #mini-cart-dropdown       : Full mini cart HTML
 *
 * @since 1.0.0
 * @param array $fragments Existing fragments array from WooCommerce.
 * @return array Updated fragments array.
 */
function woocommerce_ajax_mini_cart_fragments( $fragments ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $fragments;
	}

	$cart = WC()->cart;

	if ( ! $cart ) {
		return $fragments;
	}

	$cart_count = $cart->get_cart_contents_count();

	// Update cart count badge.
	$fragments['.mini-cart__trigger-count'] = '<span class="mini-cart__trigger-count" aria-label="' . esc_attr( sprintf( _n( '%d item in cart', '%d items in cart', $cart_count, 'woocommerce' ), $cart_count ) ) . '">' . esc_html( $cart_count ) . '</span>';

	// Update full mini cart dropdown.
	$fragments['#mini-cart-dropdown']       = woocommerce_render_mini_cart( false );

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'woocommerce_ajax_mini_cart_fragments', 10, 1 );

/**
 * Add Mini Cart Fragments to Cart Updates
 *
 * Ensures mini cart fragments are updated during checkout order review updates,
 * not just when adding items to cart.
 *
 * @since 1.0.0
 * @param array $fragments Existing fragments array.
 * @return array Updated fragments array.
 */
function woocommerce_ajax_mini_cart_fragments_cart_updated( $fragments ) {
	return woocommerce_ajax_mini_cart_fragments( $fragments );
}
add_filter( 'woocommerce_update_order_review_fragments', 'woocommerce_ajax_mini_cart_fragments_cart_updated', 10, 1 );
