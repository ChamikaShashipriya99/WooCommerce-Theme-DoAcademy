<?php
/**
 * Theme Functions
 *
 * This file contains all theme-specific functions and setup.
 * It's loaded automatically by WordPress when the theme is activated.
 *
 * @package WooCommerce
 */

// Prevent direct access to this file
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 *
 * Sets up theme defaults and registers support for various WordPress features.
 * This function runs after the theme's functions.php file is loaded.
 */
function woocommerce_theme_setup() {
	// Add theme support for automatic feed links
	// This adds RSS feed links to the <head> section
	add_theme_support( 'automatic-feed-links' );

	// Add theme support for post thumbnails (featured images)
	// This allows posts and pages to have featured images
	add_theme_support( 'post-thumbnails' );

	// Add theme support for HTML5 markup
	// This enables HTML5 for search forms, comment forms, comment lists, gallery, and caption
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Add theme support for document title tag
	// WordPress will handle the <title> tag automatically
	add_theme_support( 'title-tag' );

	// Register navigation menu
	// This creates a menu location that can be assigned in Appearance > Menus
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'woocommerce' ),
	) );

	// Add WooCommerce theme support
	// This enables WooCommerce features like product galleries, zoom, and lightbox
	add_theme_support( 'woocommerce' );

	// Add support for WooCommerce product gallery features
	// Enables zoom, lightbox, and slider functionality for product images
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
// Hook into the 'after_setup_theme' action
// This ensures the setup function runs at the right time
add_action( 'after_setup_theme', 'woocommerce_theme_setup' );

/**
 * Enqueue Styles and Scripts
 *
 * Properly loads CSS and JavaScript files.
 * This is the WordPress way to include assets (not using <link> or <script> tags directly).
 */
function woocommerce_theme_enqueue_assets() {
	// Get the theme version from style.css (for cache busting)
	$theme_version = wp_get_theme()->get( 'Version' );

	// Enqueue the main stylesheet
	// This loads assets/css/style.css with proper dependency handling
	wp_enqueue_style(
		'woocommerce-theme-style',                    // Handle (unique identifier)
		get_template_directory_uri() . '/assets/css/style.css', // Path to stylesheet
		array(),                                      // Dependencies (none in this case)
		$theme_version                                // Version (for cache busting)
	);

	// Enqueue JavaScript if needed (commented out for minimal theme)
	// wp_enqueue_script(
	//     'woocommerce-theme-script',
	//     get_template_directory_uri() . '/assets/js/minicart.js',
	//     array(),
	//     $theme_version,
	//     true // Load in footer
	// );
}
// Hook into 'wp_enqueue_scripts' action
// This ensures styles/scripts are loaded on the frontend
add_action( 'wp_enqueue_scripts', 'woocommerce_theme_enqueue_assets' );

/**
 * Remove WooCommerce Default Wrappers
 *
 * WooCommerce adds default content wrappers that we need to remove
 * so we can add our custom theme wrappers instead.
 * We check if WooCommerce is active before removing hooks.
 */
function woocommerce_theme_remove_default_wrappers() {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	/**
	 * Remove WooCommerce default opening wrapper
	 * Priority 10 is the default, we need to match it exactly to remove it
	 */
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );

	/**
	 * Remove WooCommerce default closing wrapper
	 * Priority 10 is the default, we need to match it exactly to remove it
	 */
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
}
// Hook into 'wp' action (runs after WordPress is fully loaded)
// This ensures WooCommerce is loaded before we try to remove its hooks
add_action( 'wp', 'woocommerce_theme_remove_default_wrappers' );

/**
 * Add Custom Theme Opening Wrapper
 *
 * This adds our custom opening wrapper before WooCommerce main content.
 * This wrapper matches our theme's HTML structure from header.php and index.php.
 */
function woocommerce_theme_wrapper_start() {
	/**
	 * Output opening wrapper div that matches our theme structure
	 * This ensures WooCommerce pages (shop, product, cart, checkout) 
	 * have consistent layout with the rest of the theme
	 */
	echo '<div id="primary" class="site-main woocommerce-page">';
}
// Hook into 'woocommerce_before_main_content' action
// Priority 10 ensures it runs at the standard time
add_action( 'woocommerce_before_main_content', 'woocommerce_theme_wrapper_start', 10 );

/**
 * Add Custom Theme Closing Wrapper
 *
 * This adds our custom closing wrapper after WooCommerce main content.
 * This ensures proper HTML structure closure.
 */
function woocommerce_theme_wrapper_end() {
	/**
	 * Output closing wrapper div
	 * This closes the wrapper opened in woocommerce_theme_wrapper_start()
	 */
	echo '</div><!-- #primary -->';
}
// Hook into 'woocommerce_after_main_content' action
// Priority 10 ensures it runs at the standard time
add_action( 'woocommerce_after_main_content', 'woocommerce_theme_wrapper_end', 10 );

/**
 * Ensure WooCommerce Pages Load Correctly
 *
 * This function ensures that shop, product, cart, and checkout pages
 * have the proper theme structure by ensuring header and footer are loaded.
 * WooCommerce templates automatically call get_header() and get_footer(),
 * but we verify the structure is correct.
 */
function woocommerce_theme_ensure_page_structure() {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Check if we're on a WooCommerce page
	if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
		return;
	}

	/**
	 * Note: WooCommerce templates automatically include:
	 * - get_header() at the start
	 * - get_footer() at the end
	 * 
	 * Our custom wrappers (added above) ensure the content area
	 * matches our theme's structure between header and footer.
	 * 
	 * No additional action needed here - the wrapper hooks handle it.
	 */
}
// Hook into 'template_redirect' action
// This runs before the template is loaded, allowing us to verify structure
add_action( 'template_redirect', 'woocommerce_theme_ensure_page_structure' );

