<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	
	<?php
	/**
	 * wp_head() Hook - Critical WordPress Function
	 * 
	 * This function outputs essential content to the <head> section of every page.
	 * It is called automatically by WordPress and must be included in all themes.
	 * 
	 * What wp_head() outputs:
	 * 1. <title> tag (if theme supports 'title-tag' feature)
	 * 2. All enqueued stylesheets (via wp_enqueue_style)
	 * 3. All enqueued scripts that load in header
	 * 4. RSS feed links (if theme supports 'automatic-feed-links')
	 * 5. Meta tags for SEO, Open Graph, etc.
	 * 6. Plugin-generated content (analytics, tracking codes, etc.)
	 * 
	 * In WooCommerce themes, wp_head() also outputs:
	 * - WooCommerce stylesheets (enqueued in functions.php)
	 * - Theme stylesheets (enqueued in functions.php)
	 * - Font Awesome icons (if enqueued)
	 * 
	 * Hook execution: Runs on every page load, before </head> tag closes.
	 * Plugins can hook into 'wp_head' action to add custom content.
	 */
	wp_head();
	?>
</head>

<body <?php body_class(); ?>>
<?php
/**
 * wp_body_open() Hook - WordPress 5.2+ Feature
 * 
	 * This function allows themes and plugins to inject content immediately
	 * after the opening <body> tag. Common uses include:
	 * - Google Tag Manager code
	 * - Analytics tracking pixels
	 * - Accessibility skip links
	 * 
	 * Why check function_exists():
	 * - This function was introduced in WordPress 5.2
	 * - Older WordPress versions don't have this function
	 * - Checking prevents fatal errors on older installations
	 * 
	 * Hook execution: Runs once per page load, immediately after <body> tag opens.
	 * Plugins can hook into 'wp_body_open' action to add content.
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
				/**
				 * Site Branding Section
				 * 
				 * This section displays the site logo or site title. In WooCommerce
				 * themes, the branding typically links to the homepage and serves as
				 * a consistent navigation anchor across all pages (shop, cart, checkout, etc.).
				 * 
				 * has_custom_logo() checks if a custom logo is set via:
				 * Appearance > Customize > Site Identity > Logo
				 */
				if ( has_custom_logo() ) {
					/**
					 * Display Custom Logo
					 * 
					 * the_custom_logo() outputs the logo image set in WordPress Customizer.
					 * The logo is wrapped in a link to the homepage and includes proper
					 * alt text and responsive attributes. This is the preferred method
					 * for displaying site branding in modern WordPress themes.
					 */
					the_custom_logo();
				} else {
					/**
					 * Fallback: Display Site Title as Text Link
					 * 
					 * If no custom logo is set, display "CSK" as the site branding.
					 * This ensures branding is always visible, even without a logo image.
					 */
					?>
					<h1 class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
							<?php echo esc_html( 'CSK' ); ?>
						</a>
					</h1>
					<?php
					/**
					 * Display Site Tagline
					 * 
					 * get_bloginfo('description') retrieves the site tagline from
					 * Settings > General > Tagline. The tagline provides additional
					 * context about the site's purpose.
					 * 
					 * is_customize_preview() check ensures tagline appears in
					 * Customizer preview even if empty, allowing live editing.
					 */
					$description = get_bloginfo( 'description', 'display' );
					if ( $description || is_customize_preview() ) {
						/**
						 * Note: bloginfo() output is safe and doesn't need escaping
						 * when using 'display' parameter, as WordPress handles sanitization.
						 */
						?>
						<p class="site-description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
						<?php
					}
				}
				?>
			</div><!-- .site-branding -->

			<button type="button" class="menu-toggle" id="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle primary menu', 'woocommerce' ); ?>">
				<span class="menu-toggle-icon">
					<span class="menu-toggle-line"></span>
					<span class="menu-toggle-line"></span>
					<span class="menu-toggle-line"></span>
				</span>
			</button>

			<div class="menu-overlay" id="menu-overlay"></div>

			<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'woocommerce' ); ?>">
				<?php
				/**
				 * Primary Navigation Menu
				 * 
				 * wp_nav_menu() displays the menu assigned to the 'primary' theme location.
				 * The menu is created and assigned via Appearance > Menus in WordPress admin.
				 * 
				 * In WooCommerce themes, the primary menu typically includes:
				 * - Home link
				 * - Shop link (to WooCommerce shop page)
				 * - Product categories
				 * - My Account link (if user is logged in)
				 * - Custom pages
				 * 
				 * Parameters explained:
				 * - 'theme_location' => 'primary': Uses menu assigned to 'primary' location
				 * - 'menu_id' => 'primary-menu': Sets ID attribute for CSS/JS targeting
				 * - 'container' => false: Removes default <div> wrapper
				 * - 'fallback_cb' => false: No fallback if no menu assigned
				 * 
				 * Hook execution: This is not a hook, but a direct function call that
				 * outputs menu HTML. The menu location 'primary' must be registered
				 * in functions.php via register_nav_menus().
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
			 * WooCommerce Mini Cart Dropdown
			 * 
			 * This section displays a cart icon with item count that reveals a dropdown
			 * showing cart contents. This is a standard e-commerce feature that allows
			 * customers to view their cart without leaving the current page.
			 * 
			 * Why this exists in a WooCommerce theme:
			 * - Provides quick access to cart contents from any page
			 * - Shows cart item count as visual feedback
			 * - Allows customers to review items before checkout
			 * - Improves user experience and conversion rates
			 * 
			 * The mini cart only displays if WooCommerce plugin is active, preventing
			 * errors if WooCommerce is deactivated.
			 */
			if ( class_exists( 'WooCommerce' ) ) :
				/**
				 * Get WooCommerce Cart Object
				 * 
				 * WC()->cart is the global WooCommerce cart instance. It contains:
				 * - Cart items (products added to cart)
				 * - Cart totals (subtotal, tax, shipping, total)
				 * - Cart methods (add, remove, update quantities)
				 * 
				 * This object is available throughout WordPress once WooCommerce is loaded.
				 */
				$cart = WC()->cart;
				
				/**
				 * Get Cart Item Count
				 * 
				 * get_cart_contents_count() returns the total number of items in the cart.
				 * This count is displayed as a badge on the cart icon. The count updates
				 * automatically via AJAX when items are added/removed (handled by JavaScript).
				 * 
				 * Even if cart is empty, we get the count (which will be 0) to ensure
				 * the cart icon always displays, allowing users to see the empty state.
				 */
				$cart_count = $cart ? $cart->get_cart_contents_count() : 0;
				?>
				<div class="mini-cart-wrapper">
					<button type="button" class="mini-cart__trigger" id="mini-cart-trigger" aria-label="<?php esc_attr_e( 'View shopping cart', 'woocommerce' ); ?>" aria-expanded="false">
						<span class="mini-cart__icon">
							<i class="fas fa-cart-arrow-down" aria-hidden="true"></i>
						</span>
						<span class="mini-cart__trigger-count" aria-label="<?php echo esc_attr( sprintf( _n( '%d item in cart', '%d items in cart', $cart_count, 'woocommerce' ), $cart_count ) ); ?>">
							<?php echo esc_html( $cart_count ); ?>
						</span>
					</button>

					<?php
					/**
					 * Render Mini Cart Dropdown HTML
					 * 
					 * woocommerce_render_mini_cart() is a custom helper function defined
					 * in functions.php. It generates the complete HTML structure for the
					 * cart dropdown, including:
					 * - Cart items list (product name, image, quantity, price)
					 * - Cart total
					 * - View Cart and Checkout buttons
					 * 
					 * Parameter: true = echo output directly (false would return as string)
					 * 
					 * This function is also used in AJAX fragments (see functions.php)
					 * to update the mini cart when items are added/removed without page reload.
					 * 
					 * Hook execution: This is a direct function call, not a hook. The function
					 * itself may use WooCommerce hooks internally to gather cart data.
					 */
					woocommerce_render_mini_cart( true );
					?>
				</div>
				<?php
			endif;
			?>
		</div><!-- .site-header-inner -->
	</header><!-- #masthead -->

	<main id="primary" class="site-main">

