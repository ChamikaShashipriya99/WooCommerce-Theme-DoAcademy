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

