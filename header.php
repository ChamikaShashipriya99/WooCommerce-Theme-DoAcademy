<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	
	<?php
	/**
	 * wp_head() is a critical WordPress function that outputs:
	 * - The <title> tag (if title-tag support is enabled)
	 * - Enqueued stylesheets and scripts
	 * - Meta tags, RSS feeds, and other head content
	 * - Plugins can also hook into this to add their own content
	 */
	wp_head();
	?>
</head>

<body <?php body_class(); ?>>
<?php
/**
 * wp_body_open() allows plugins and themes to inject content
 * right after the opening <body> tag.
 * This is a WordPress 5.2+ feature.
 * Note: If WordPress < 5.2, this function won't exist, but won't cause errors
 * as it's only called if the function exists.
 */
if ( function_exists( 'wp_body_open' ) ) {
	wp_body_open();
}
?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary">
		<?php esc_html_e( 'Skip to content', 'woocommerce' ); ?>
	</a>

	<header id="masthead" class="site-header" role="banner">
		<div class="site-header-inner">
			<div class="site-branding">
				<?php
				// Display site title or logo
				if ( has_custom_logo() ) {
					// If a custom logo is set, display it
					the_custom_logo();
				} else {
					// Otherwise, display the site title as a link
					?>
					<h1 class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<?php bloginfo( 'name' ); ?>
						</a>
					</h1>
					<?php
					// Display site tagline if available
					$description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) {
						?>
						<p class="site-description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php
					}
				}
				?>
			</div><!-- .site-branding -->

			<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'woocommerce' ); ?>">
				<?php
				/**
				 * Display the primary menu
				 * wp_nav_menu() outputs the menu assigned to the 'primary' location
				 * If no menu is assigned, it falls back to wp_list_pages()
				 */
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'menu_id'        => 'primary-menu',
					'container'      => false,
					'fallback_cb'    => false, // Don't show fallback menu if none is set
				) );
				?>
			</nav><!-- #site-navigation -->

			<?php
			/**
			 * Custom Mini Cart Dropdown
			 * 
			 * Displays a cart icon/button that reveals a dropdown with:
			 * - Cart item count
			 * - List of cart items
			 * - Cart total
			 * - View Cart & Checkout buttons
			 * Only displays if WooCommerce is active.
			 */
			if ( class_exists( 'WooCommerce' ) ) :
				// Get the cart object
				// WC()->cart is the global WooCommerce cart instance
				$cart = WC()->cart;
				
				// Get cart item count (even if cart is empty, to show the icon)
				// get_cart_contents_count() returns the total number of items in cart
				$cart_count = $cart ? $cart->get_cart_contents_count() : 0;
				?>
				<div class="mini-cart-wrapper">
					<button type="button" class="mini-cart__trigger" aria-label="<?php esc_attr_e( 'View shopping cart', 'woocommerce' ); ?>" aria-expanded="false">
						<span class="mini-cart__icon">
							<i class="fas fa-cart-arrow-down" aria-hidden="true"></i>
						</span>
						<span class="mini-cart__trigger-count" aria-label="<?php echo esc_attr( sprintf( _n( '%d item in cart', '%d items in cart', $cart_count, 'woocommerce' ), $cart_count ) ); ?>">
							<?php echo esc_html( $cart_count ); ?>
						</span>
					</button>

					<?php
					// Check if cart exists and has items
					if ( $cart && ! $cart->is_empty() ) :
						// Get cart total
						// get_cart_total() returns formatted cart total with currency symbol
						$cart_total = $cart->get_cart_total();
						
						// Get cart items
						// get_cart() returns an array of cart items with their data
						$cart_items = $cart->get_cart();
						?>
						<div class="mini-cart__dropdown" role="region" aria-label="<?php esc_attr_e( 'Shopping Cart', 'woocommerce' ); ?>">
							<div class="mini-cart__header">
								<h3 class="mini-cart__title">
									<?php esc_html_e( 'Cart', 'woocommerce' ); ?>
									<span class="mini-cart__count" aria-label="<?php echo esc_attr( sprintf( _n( '%d item', '%d items', $cart_count, 'woocommerce' ), $cart_count ) ); ?>">
										(<?php echo esc_html( $cart_count ); ?>)
									</span>
								</h3>
							</div>

							<div class="mini-cart__items">
								<ul class="mini-cart__list" role="list">
									<?php
									// Loop through each cart item
									// Each $cart_item_key is a unique identifier for the item
									// Each $cart_item contains product data, quantity, and variations
									foreach ( $cart_items as $cart_item_key => $cart_item ) :
										// Get the product object from cart item
										// wc_get_product() retrieves the WC_Product object by product ID
										$product = wc_get_product( $cart_item['product_id'] );
										
										// Skip if product doesn't exist (e.g., deleted product)
										if ( ! $product ) {
											continue;
										}
										
										// Get product permalink
										// get_permalink() returns the URL to view the product
										$product_permalink = $product->get_permalink();
										
										// Get product name
										// get_name() returns the product title
										$product_name = $product->get_name();
										
										// Get item quantity
										// $cart_item['quantity'] contains the quantity for this cart item
										$quantity = $cart_item['quantity'];
										
										// Get line subtotal (price for this item * quantity)
										// get_product_subtotal() returns the subtotal for this line item
										$line_subtotal = $cart->get_product_subtotal( $product, $cart_item['quantity'] );
										?>
										<li class="mini-cart__item" role="listitem">
											<div class="mini-cart__item-content">
												<?php
												// Display product thumbnail
												// get_image() returns the product image HTML
												// 'woocommerce_thumbnail' is the image size to use
												echo $product->get_image( 'woocommerce_thumbnail', array(
													'alt' => $product_name,
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
													// Display product variation attributes if it's a variable product
													// wc_get_formatted_cart_item_data() formats variation attributes nicely
													// Returns formatted string like "Color: Red, Size: Large"
													$variation_data = wc_get_formatted_cart_item_data( $cart_item );
													if ( $variation_data ) {
														echo '<div class="mini-cart__item-variation">' . $variation_data . '</div>';
													}
													?>
													
													<div class="mini-cart__item-meta">
														<span class="mini-cart__item-quantity">
															<?php
															// Display quantity
															// printf() formats the string with the quantity number
															// esc_html() ensures safe output
															printf(
																esc_html__( 'Quantity: %d', 'woocommerce' ),
																$quantity
															);
															?>
														</span>
														<span class="mini-cart__item-price">
															<?php echo $line_subtotal; // Already escaped by WooCommerce ?>
														</span>
													</div>
												</div>
											</div>
										</li>
										<?php
									endforeach;
									?>
								</ul>
							</div>

							<div class="mini-cart__footer">
								<div class="mini-cart__total">
									<strong class="mini-cart__total-label">
										<?php esc_html_e( 'Total:', 'woocommerce' ); ?>
									</strong>
									<span class="mini-cart__total-amount">
										<?php echo $cart_total; // Already escaped by WooCommerce ?>
									</span>
								</div>

								<div class="mini-cart__actions">
									<?php
									// Get cart page URL
									// wc_get_cart_url() returns the URL to the cart page
									$cart_url = wc_get_cart_url();
									
									// Get checkout page URL
									// wc_get_checkout_url() returns the URL to the checkout page
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
					elseif ( $cart ) :
						// Display empty cart message in dropdown
						?>
						<div class="mini-cart__dropdown mini-cart__dropdown--empty" role="region" aria-label="<?php esc_attr_e( 'Shopping Cart', 'woocommerce' ); ?>">
							<div class="mini-cart__empty-message">
								<p><?php esc_html_e( 'Your cart is empty.', 'woocommerce' ); ?></p>
							</div>
						</div>
						<?php
					endif;
					?>
				</div>
				<?php
			endif;
			?>
		</div><!-- .site-header-inner -->
	</header><!-- #masthead -->

	<main id="primary" class="site-main">

