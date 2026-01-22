<?php
/**
 * Theme Setup
 *
 * Handles core WordPress theme setup including theme support declarations,
 * navigation menus, image sizes, and sidebar management.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Register theme support for WordPress features (title-tag, thumbnails, etc.)
 * - Register navigation menu locations
 * - Define custom image sizes
 * - Manage sidebar registration/removal
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - add_theme_support() calls
 * - register_nav_menus() calls
 * - add_image_size() calls
 * - Sidebar registration/unregistration
 * - Any 'after_setup_theme' hook callbacks
 * - Widget area management
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Script/style enqueuing (use enqueue.php)
 * - WooCommerce-specific setup (use /woocommerce/setup.php)
 * - Custom post types or taxonomies
 * - Shortcodes or template tags
 * - AJAX handlers
 *
 * ============================================================================
 * WORDPRESS HOOKS USED:
 * ============================================================================
 * - after_setup_theme  : Theme setup (runs early, before init)
 * - widgets_init       : Sidebar/widget area management
 * - is_active_sidebar  : Filter to control sidebar display
 *
 * @package    WooCommerce
 * @subpackage Theme/Setup
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Setup
 *
 * Sets up theme defaults and registers support for various WordPress features.
 * This function runs on the 'after_setup_theme' hook, which fires early in the
 * WordPress loading process, before plugins are fully loaded.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_setup() {
	// Add theme support for automatic feed links (RSS).
	add_theme_support( 'automatic-feed-links' );

	// Add theme support for post thumbnails (featured images).
	add_theme_support( 'post-thumbnails' );

	// Add theme support for HTML5 markup in core WordPress elements.
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Let WordPress manage the document title (removes need for <title> in header.php).
	add_theme_support( 'title-tag' );

	// Register navigation menu locations for use in Appearance > Menus.
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'woocommerce' ),
		'footer'  => esc_html__( 'Footer Menu', 'woocommerce' ),
	) );

	// Add WooCommerce theme support (enables WooCommerce features).
	add_theme_support( 'woocommerce' );

	// Add support for WooCommerce product gallery features.
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Add WooCommerce product image sizes.
	add_image_size( 'woocommerce-thumbnail', 300, 300, true );
	add_image_size( 'woocommerce-single', 600, 600, true );
	add_image_size( 'woocommerce-gallery-thumbnail', 150, 150, true );
}
add_action( 'after_setup_theme', 'woocommerce_theme_setup' );

/**
 * Remove Sidebar Support
 *
 * Unregisters default WordPress sidebars to create a sidebar-free theme.
 * Runs on 'widgets_init' with high priority (99) to ensure it executes
 * after sidebars are registered by WordPress core and other plugins.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_remove_sidebars() {
	global $wp_registered_sidebars;

	// Remove default WordPress sidebars.
	if ( isset( $wp_registered_sidebars['sidebar-1'] ) ) {
		unregister_sidebar( 'sidebar-1' );
	}
	if ( isset( $wp_registered_sidebars['sidebar-2'] ) ) {
		unregister_sidebar( 'sidebar-2' );
	}

	// Remove WooCommerce sidebars if WooCommerce is active.
	if ( function_exists( 'is_woocommerce' ) ) {
		if ( isset( $wp_registered_sidebars['shop-sidebar'] ) ) {
			unregister_sidebar( 'shop-sidebar' );
		}
		if ( isset( $wp_registered_sidebars['woocommerce-sidebar'] ) ) {
			unregister_sidebar( 'woocommerce-sidebar' );
		}
	}
}
add_action( 'widgets_init', 'woocommerce_theme_remove_sidebars', 99 );

/**
 * Disable Sidebar Display
 *
 * Filters is_active_sidebar() to always return false, ensuring no sidebars
 * are displayed anywhere in the theme. This is a design decision for this
 * theme to have a full-width, sidebar-free layout.
 *
 * Note: This will affect any code that checks is_active_sidebar(), including
 * plugins. Remove this filter if you need sidebar support.
 *
 * @since 1.0.0
 * @param bool   $is_active_sidebar Whether the sidebar is active.
 * @param string $index             Sidebar index/ID being checked.
 * @return bool Always returns false to disable all sidebars.
 */
function woocommerce_theme_disable_sidebar_display( $is_active_sidebar, $index ) {
	return false;
}
add_filter( 'is_active_sidebar', 'woocommerce_theme_disable_sidebar_display', 10, 2 );
