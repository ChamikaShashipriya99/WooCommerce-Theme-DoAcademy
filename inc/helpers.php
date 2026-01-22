<?php
/**
 * Theme Helper Functions
 *
 * Contains reusable utility functions used throughout the theme. These are
 * "pure" helper functions that don't hook into WordPress—they're called
 * directly by other theme code when needed.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Provide utility functions for common operations
 * - Abstract repetitive code into reusable functions
 * - Offer wrapper functions for better code readability
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - Utility functions (sanitization helpers, validation, formatting)
 * - Wrapper functions that simplify WordPress/WooCommerce API calls
 * - Functions that return data (not output HTML)
 * - Plugin availability checks
 * - Path/URL helper functions
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Functions that hook into WordPress (add_action, add_filter)
 * - Functions that output HTML directly
 * - WooCommerce-specific logic (use /woocommerce/ files)
 * - Template functions (use template-tags.php if needed)
 * - Admin-only functions
 *
 * ============================================================================
 * NAMING CONVENTION:
 * ============================================================================
 * All helper functions should be prefixed with 'woocommerce_theme_' to avoid
 * conflicts with WordPress core, plugins, or other themes.
 *
 * @package    WooCommerce
 * @subpackage Theme/Helpers
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if WooCommerce is active
 *
 * Helper function to check if the WooCommerce plugin is installed and active.
 * Use this before calling any WooCommerce functions to prevent fatal errors.
 *
 * Example usage:
 *   if ( woocommerce_theme_is_woocommerce_active() ) {
 *       // Safe to use WooCommerce functions.
 *   }
 *
 * @since 1.0.0
 * @return bool True if WooCommerce is active, false otherwise.
 */
function woocommerce_theme_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Get current product object safely
 *
 * Retrieves the current WC_Product object in various contexts (loops, single
 * product pages, etc.). Handles cases where the global $product may not be set.
 *
 * Example usage:
 *   $product = woocommerce_theme_get_current_product();
 *   if ( $product ) {
 *       echo $product->get_name();
 *   }
 *
 * @since 1.0.0
 * @return WC_Product|false Product object or false if not available.
 */
function woocommerce_theme_get_current_product() {
	global $product, $post;

	// First, try the global $product (set in WooCommerce loops).
	if ( $product && is_a( $product, 'WC_Product' ) ) {
		return $product;
	}

	// Fallback: try to get product from global $post.
	if ( $post && function_exists( 'wc_get_product' ) ) {
		return wc_get_product( $post->ID );
	}

	return false;
}

/**
 * Sanitize and validate allowed values
 *
 * Validates a value against a whitelist of allowed values. Returns the value
 * if it's in the allowed list, otherwise returns a default value.
 *
 * Example usage:
 *   $size = woocommerce_theme_validate_value( $_POST['size'], array( 'S', 'M', 'L' ), 'M' );
 *
 * @since 1.0.0
 * @param string $value   The value to validate.
 * @param array  $allowed Array of allowed values.
 * @param string $default Default value if validation fails.
 * @return string Validated value or default.
 */
function woocommerce_theme_validate_value( $value, $allowed, $default = '' ) {
	return in_array( $value, $allowed, true ) ? $value : $default;
}

/**
 * Get theme asset URL
 *
 * Returns the full URL to a file in the theme's /assets directory.
 * Useful for referencing images, fonts, or other assets in templates.
 *
 * Example usage:
 *   $logo_url = woocommerce_theme_get_asset_url( 'images/logo.png' );
 *
 * @since 1.0.0
 * @param string $path Relative path to the asset (from /assets/).
 * @return string Full URL to the asset.
 */
function woocommerce_theme_get_asset_url( $path ) {
	return get_template_directory_uri() . '/assets/' . ltrim( $path, '/' );
}

/**
 * Get theme asset path
 *
 * Returns the full server file path to a file in the theme's /assets directory.
 * Useful for file_exists() checks or file_get_contents() operations.
 *
 * Example usage:
 *   $logo_path = woocommerce_theme_get_asset_path( 'images/logo.png' );
 *   if ( file_exists( $logo_path ) ) { ... }
 *
 * @since 1.0.0
 * @param string $path Relative path to the asset (from /assets/).
 * @return string Full file path to the asset.
 */
function woocommerce_theme_get_asset_path( $path ) {
	return get_template_directory() . '/assets/' . ltrim( $path, '/' );
}
