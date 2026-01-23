<?php
/**
 * Enqueue Scripts and Styles
 *
 * Handles all CSS and JavaScript asset loading for the theme using WordPress's
 * enqueue system. This ensures proper dependency management, cache busting,
 * and allows plugins to modify or dequeue assets as needed.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Enqueue frontend stylesheets (CSS)
 * - Enqueue frontend scripts (JavaScript)
 * - Manage asset dependencies
 * - Handle conditional asset loading (e.g., only on certain pages)
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - wp_enqueue_style() calls
 * - wp_enqueue_script() calls
 * - wp_localize_script() for passing PHP data to JavaScript
 * - Conditional logic for page-specific assets
 * - External asset loading (CDN fonts, icons, etc.)
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Admin-only scripts (use admin_enqueue_scripts hook in separate file)
 * - Inline styles (use wp_add_inline_style or CSS file)
 * - Script logic or functionality (put in JS files)
 * - WooCommerce-specific conditional assets (consider /woocommerce/ files)
 *
 * ============================================================================
 * WORDPRESS HOOKS USED:
 * ============================================================================
 * - wp_enqueue_scripts : Frontend asset enqueuing (runs on every page load)
 *
 * ============================================================================
 * ASSET LOCATIONS:
 * ============================================================================
 * - CSS files: /assets/css/
 * - JS files:  /assets/js/
 * - Images:    /assets/images/
 *
 * @package    WooCommerce
 * @subpackage Theme/Enqueue
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Styles and Scripts
 *
 * Loads all CSS and JavaScript files for the frontend. Uses WordPress's
 * enqueue system which provides:
 * - Dependency management (load jQuery before scripts that need it)
 * - Version-based cache busting
 * - Proper script placement (header vs footer)
 * - Ability for plugins/child themes to modify assets
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_enqueue_assets() {
	// Get theme version for cache busting (from style.css header).
	$theme_version = wp_get_theme()->get( 'Version' );

	/*
	 * ========================================================================
	 * EXTERNAL STYLESHEETS
	 * ========================================================================
	 */

	// Font Awesome icon library (loaded from CDN for performance).
	wp_enqueue_style(
		'font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
		array(),
		'5.15.4'
	);

	// Google Fonts: League Spartan (theme's primary font).
	wp_enqueue_style(
		'league-spartan-font',
		'https://fonts.googleapis.com/css2?family=League+Spartan:wght@400;500;600;700&display=swap',
		array(),
		null // No version needed for Google Fonts.
	);

	/*
	 * ========================================================================
	 * THEME STYLESHEET
	 * ========================================================================
	 */

	// Build dependency array (theme CSS loads after fonts and WooCommerce).
	$dependencies = array( 'font-awesome', 'league-spartan-font' );
	if ( class_exists( 'WooCommerce' ) ) {
		$dependencies[] = 'woocommerce-general';
	}

	// Main theme stylesheet.
	wp_enqueue_style(
		'woocommerce-theme-style',
		get_template_directory_uri() . '/assets/css/style.css',
		$dependencies,
		$theme_version
	);

	/*
	 * ========================================================================
	 * JAVASCRIPT FILES
	 * ========================================================================
	 * All scripts use jQuery as a dependency and load in the footer
	 * (last parameter = true) for better page load performance.
	 */

	// Mini cart AJAX functionality.
	wp_enqueue_script(
		'woocommerce-minicart-ajax',
		get_template_directory_uri() . '/assets/js/minicart.js',
		array( 'jquery' ),
		$theme_version,
		true
	);

	// Mobile menu toggle functionality.
	wp_enqueue_script(
		'woocommerce-mobile-menu',
		get_template_directory_uri() . '/assets/js/mobile-menu.js',
		array( 'jquery' ),
		$theme_version,
		true
	);

	// Quantity input plus/minus buttons.
	wp_enqueue_script(
		'woocommerce-quantity-changer',
		get_template_directory_uri() . '/assets/js/quantity-changer.js',
		array( 'jquery' ),
		$theme_version,
		true
	);

	// Size labels for variable products (only on single product pages).
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_script(
			'woocommerce-variation-size-labels',
			get_template_directory_uri() . '/assets/js/variation-size-labels.js',
			array( 'jquery' ),
			$theme_version,
			true
		);
	}

	// Shop category filter auto-submit (only on shop page).
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		wp_enqueue_script(
			'woocommerce-shop-category-filter',
			get_template_directory_uri() . '/assets/js/shop-category-filter.js',
			array( 'jquery' ),
			$theme_version,
			true
		);
	}

	// Back to top button functionality.
	wp_enqueue_script(
		'woocommerce-back-to-top',
		get_template_directory_uri() . '/assets/js/back-to-top.js',
		array( 'jquery' ),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'woocommerce_theme_enqueue_assets' );
