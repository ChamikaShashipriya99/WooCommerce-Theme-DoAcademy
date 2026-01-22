<?php
/**
 * WooCommerce Setup
 *
 * Handles WooCommerce-specific theme integration including content wrappers,
 * sidebar removal, and payment gateway compatibility.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Replace WooCommerce default content wrappers with theme-specific markup
 * - Remove WooCommerce sidebar functionality
 * - Provide payment gateway theme support (e.g., Stripe styling)
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - WooCommerce wrapper customization (before/after main content)
 * - WooCommerce sidebar management
 * - Payment gateway CSS/compatibility
 * - WooCommerce-specific theme support declarations
 * - Layout structure modifications for WooCommerce pages
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Cart functionality (use cart.php)
 * - Checkout customizations (use checkout.php)
 * - Product-related hooks (use product.php)
 * - Admin/backend features (use admin.php)
 * - My Account customizations (use account.php)
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
 * - woocommerce_before_main_content : Opening wrapper
 * - woocommerce_after_main_content  : Closing wrapper
 * - woocommerce_sidebar             : Sidebar display
 * - wp                              : Hook removal (late enough for WC hooks to exist)
 * - after_setup_theme               : Payment gateway support
 * - wp_head                         : Payment gateway styles
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce/Setup
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove WooCommerce Default Wrappers
 *
 * WooCommerce outputs default HTML wrappers around shop content. These wrappers
 * don't match our theme's structure, so we remove them and add our own.
 *
 * Hooked to 'wp' because WooCommerce hooks must be registered before we can
 * remove them, and 'wp' fires after all plugins are fully loaded.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_remove_default_wrappers() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
}
add_action( 'wp', 'woocommerce_theme_remove_default_wrappers' );

/**
 * Add Custom Theme Opening Wrapper
 *
 * Outputs the opening wrapper HTML that matches our theme's layout structure.
 * This wrapper is used on all WooCommerce pages (shop, product, cart, checkout, etc.).
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_wrapper_start() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	echo '<div id="primary" class="site-main woocommerce-page">';
}
add_action( 'woocommerce_before_main_content', 'woocommerce_theme_wrapper_start', 10 );

/**
 * Add Custom Theme Closing Wrapper
 *
 * Outputs the closing wrapper HTML that matches the opening wrapper.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_wrapper_end() {
	echo '</div><!-- #primary -->';
}
add_action( 'woocommerce_after_main_content', 'woocommerce_theme_wrapper_end', 10 );

/**
 * Remove WooCommerce Sidebar Hook
 *
 * Removes the default WooCommerce sidebar from shop and product pages.
 * This theme uses a full-width, sidebar-free layout.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_remove_woocommerce_sidebar() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}
add_action( 'wp', 'woocommerce_theme_remove_woocommerce_sidebar' );

/**
 * Stripe Payment Gateway Integration Support
 *
 * Provides theme-level support for the WooCommerce Stripe Payment Gateway plugin.
 * This ensures the Stripe payment form displays correctly within the theme.
 *
 * Note: Requires the "WooCommerce Stripe Payment Gateway" plugin to be installed.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_stripe_support() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Stripe plugin not installed - no action needed.
	if ( ! class_exists( 'WC_Stripe' ) ) {
		return;
	}
}
add_action( 'after_setup_theme', 'woocommerce_theme_stripe_support', 20 );

/**
 * Stripe Checkout Styling Enhancement
 *
 * Outputs custom CSS to ensure the Stripe payment form matches the theme's
 * design. Only loads on the checkout page when Stripe is active.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_stripe_checkout_styles() {
	// Only load on checkout page.
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	// Only load if Stripe is active.
	if ( ! class_exists( 'WC_Stripe' ) ) {
		return;
	}
	?>
	<style type="text/css">
		/* Stripe Payment Form Styling */
		.woocommerce-checkout #payment .stripe-card-group,
		.woocommerce-checkout #payment .wc-stripe-elements-field {
			border: 2px solid #e0e0e0;
			border-radius: 8px;
			padding: 12px;
			background: #ffffff;
			transition: border-color 0.2s ease, box-shadow 0.2s ease;
		}

		.woocommerce-checkout #payment .stripe-card-group:focus-within,
		.woocommerce-checkout #payment .wc-stripe-elements-field:focus-within {
			border-color: #0073aa;
			box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.15);
		}

		.woocommerce-checkout #payment .wc-stripe-elements-field iframe {
			min-height: 22px;
		}

		.woocommerce-checkout #payment .stripe-source-errors {
			color: #dc3545;
			font-size: 14px;
			margin-top: 8px;
		}

		.woocommerce-checkout #payment .wc-payment-form {
			padding: 15px 0;
		}

		.woocommerce-checkout #payment .payment_method_stripe label {
			font-weight: 600;
		}

		.woocommerce-checkout #payment .payment_method_stripe .stripe-card-group label {
			font-weight: 500;
			margin-bottom: 6px;
			display: block;
			color: #333;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'woocommerce_theme_stripe_checkout_styles' );
