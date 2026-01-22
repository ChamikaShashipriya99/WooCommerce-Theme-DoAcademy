<?php
/**
 * Theme Functions Loader
 *
 * This is the main entry point for the theme. It acts as a "bootstrap" file that
 * loads all modular functionality from the /inc directory. This file should contain
 * ONLY require/include statements and constants—no business logic.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Define theme-wide constants (paths, URLs, version)
 * - Load core theme files (setup, enqueue, helpers)
 * - Conditionally load WooCommerce integration files
 * - Fire a custom action hook for extensibility
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - Constants (WOOCOMMERCE_THEME_VERSION, WOOCOMMERCE_THEME_DIR, etc.)
 * - require_once statements for /inc files
 * - Conditional checks for plugin availability
 * - do_action() for theme extensibility
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Function definitions (put in /inc files)
 * - Hook registrations (put in /inc files)
 * - Template logic or output
 * - Direct database queries
 * - Plugin-specific code (use conditional loading)
 *
 * ============================================================================
 * FILE STRUCTURE:
 * ============================================================================
 * /inc/
 *   ├── setup.php       → Theme setup, menus, image sizes, sidebars
 *   ├── enqueue.php     → CSS and JavaScript loading
 *   ├── helpers.php     → Utility functions
 *   └── woocommerce/    → WooCommerce-specific files (loaded conditionally)
 *       ├── setup.php   → WooCommerce wrappers, payment gateway support
 *       ├── hooks.php   → Shop page hooks, badges
 *       ├── cart.php    → Mini cart, AJAX fragments, discounts
 *       ├── checkout.php→ Custom fields, validation
 *       ├── shipping.php→ Custom shipping method
 *       ├── admin.php   → Admin order display, settings
 *       ├── product.php → Product custom fields, badges
 *       └── account.php → My Account customizations
 *
 * @package    WooCommerce
 * @subpackage Theme
 * @since      1.0.0
 * @author     Theme Developer
 * @license    GPL-2.0-or-later
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ============================================================================
 * THEME CONSTANTS
 * ============================================================================
 * Define global constants for use throughout the theme. Using constants ensures
 * consistent paths and prevents hardcoding values in multiple places.
 */
define( 'WOOCOMMERCE_THEME_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'WOOCOMMERCE_THEME_DIR', get_template_directory() );
define( 'WOOCOMMERCE_THEME_URI', get_template_directory_uri() );
define( 'WOOCOMMERCE_THEME_INC_DIR', WOOCOMMERCE_THEME_DIR . '/inc' );

/**
 * ============================================================================
 * CORE THEME FILES
 * ============================================================================
 * These files are always loaded regardless of which plugins are active.
 * They contain essential WordPress theme functionality.
 */

// Theme setup: registers theme support, menus, and image sizes.
require_once WOOCOMMERCE_THEME_INC_DIR . '/setup.php';

// Asset enqueuing: styles and scripts.
require_once WOOCOMMERCE_THEME_INC_DIR . '/enqueue.php';

// Helper functions: utility functions used throughout the theme.
require_once WOOCOMMERCE_THEME_INC_DIR . '/helpers.php';

/**
 * ============================================================================
 * WOOCOMMERCE INTEGRATION FILES
 * ============================================================================
 * These files are loaded ONLY when WooCommerce is active. This prevents errors
 * if WooCommerce is deactivated and improves performance when not needed.
 *
 * We check for both the class and the constant to ensure WooCommerce is truly
 * available before loading dependent code.
 */
if ( class_exists( 'WooCommerce' ) || defined( 'WC_PLUGIN_FILE' ) ) {

	// WooCommerce setup: wrappers, sidebar removal, payment gateway support.
	require_once WOOCOMMERCE_THEME_INC_DIR . '/woocommerce/setup.php';

	// WooCommerce hooks: shop page customizations, badges.
	require_once WOOCOMMERCE_THEME_INC_DIR . '/woocommerce/hooks.php';

	// Cart functionality: mini cart, AJAX fragments, automatic discounts.
	require_once WOOCOMMERCE_THEME_INC_DIR . '/woocommerce/cart.php';

	// Checkout functionality: custom fields, validation, order review.
	require_once WOOCOMMERCE_THEME_INC_DIR . '/woocommerce/checkout.php';

	// Shipping: custom location-based shipping method.
	require_once WOOCOMMERCE_THEME_INC_DIR . '/woocommerce/shipping.php';

	// Admin functions: order display, email fields, discount settings.
	require_once WOOCOMMERCE_THEME_INC_DIR . '/woocommerce/admin.php';

	// Product functions: custom fields, badges, origin display.
	require_once WOOCOMMERCE_THEME_INC_DIR . '/woocommerce/product.php';

	// Account functions: order status, download functionality.
	require_once WOOCOMMERCE_THEME_INC_DIR . '/woocommerce/account.php';
}

/**
 * ============================================================================
 * THEME LOADED ACTION
 * ============================================================================
 * Fires after all theme files have been loaded. Child themes or plugins can
 * hook into this action to add functionality that depends on theme code.
 *
 * Example usage in a child theme:
 *   add_action( 'woocommerce_theme_loaded', 'my_child_theme_init' );
 *
 * @since 1.0.0
 */
do_action( 'woocommerce_theme_loaded' );
