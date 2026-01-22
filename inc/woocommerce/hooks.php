<?php
/**
 * WooCommerce Hooks
 *
 * Contains general WooCommerce action and filter hooks for shop page
 * customizations, product display modifications, and visual badges.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Shop page visual enhancements (hero banners, layout changes)
 * - Product loop modifications (badges, display order)
 * - General WooCommerce hooks that don't fit in specialized files
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - Shop archive page customizations
 * - Product loop hooks (woocommerce_before_shop_loop_item_title, etc.)
 * - Hero banners and promotional content
 * - "Out of Stock" badge display
 * - General product card modifications
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Cart functionality (use cart.php)
 * - Checkout customizations (use checkout.php)
 * - Single product page hooks (use product.php)
 * - Admin/backend features (use admin.php)
 * - My Account customizations (use account.php)
 * - Custom shipping methods (use shipping.php)
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
 * - woocommerce_before_shop_loop       : Before product grid on shop pages
 * - woocommerce_before_shop_loop_item_title : Before product title in loop
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce/Hooks
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shop Page Hero Banner (Video)
 *
 * Outputs a full-width hero banner with a looping video at the top of the
 * main shop page. Only displays on the primary shop archive, not on category
 * or tag pages.
 *
 * The video file should be placed at: /assets/images/HB1.mp4
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_shop_poster_banner() {
	// Only display on the main shop page (not categories or tags).
	if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}

	$hero_video_url = get_template_directory_uri() . '/assets/images/HB1.mp4';

	?>
	<section class="shop-hero-banner" aria-label="<?php esc_attr_e( 'Shop highlight', 'woocommerce' ); ?>">
		<div class="shop-hero-banner__media">
			<video
				class="shop-hero-banner__video"
				autoplay
				muted
				loop
				playsinline
				aria-label="<?php esc_attr_e( 'Shop hero video', 'woocommerce' ); ?>"
			>
				<source src="<?php echo esc_url( $hero_video_url ); ?>" type="video/mp4" />
				<?php esc_html_e( 'Your browser does not support the video tag.', 'woocommerce' ); ?>
			</video>
		</div>
	</section>
	<?php
}
add_action( 'woocommerce_before_shop_loop', 'woocommerce_theme_shop_poster_banner', 5 );

/**
 * Display Out of Stock Badge on Product Cards
 *
 * Adds an "Out of Stock" badge overlay on product cards when products are
 * unavailable. Displays on shop archive pages, category pages, and anywhere
 * the product loop is used.
 *
 * The badge is positioned via CSS (see /assets/css/style.css).
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_display_out_of_stock_badge() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Get the current product object.
	global $product;

	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		global $post;
		if ( ! $post ) {
			return;
		}
		$product = wc_get_product( $post->ID );
		if ( ! $product ) {
			return;
		}
	}

	// Only show badge for out of stock products.
	if ( $product->is_in_stock() ) {
		return;
	}

	// Double-check stock status.
	$stock_status = $product->get_stock_status();

	if ( 'outofstock' !== $stock_status ) {
		return;
	}

	$badge_text = __( 'Out of Stock', 'woocommerce' );

	printf(
		'<div class="out-of-stock-badge-wrapper"><span class="out-of-stock-badge">%s</span></div>',
		esc_html( $badge_text )
	);
}
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_display_out_of_stock_badge', 5 );
