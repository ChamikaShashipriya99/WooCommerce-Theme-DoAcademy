<?php
/**
 * WooCommerce Product Functions
 *
 * Handles product-related customizations including custom product fields,
 * product badges (origin, sale), and single product page modifications.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Add custom fields to product admin edit screen
 * - Save custom product meta data
 * - Display product badges on shop archives and single products
 * - Control sale badge visibility per product
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - Product admin panel custom fields (woocommerce_product_options_* hooks)
 * - Product meta save handlers (woocommerce_process_product_meta hook)
 * - Product badge display (shop loop and single product)
 * - Sale flash/badge modifications
 * - Single product page content modifications
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Cart functionality (use cart.php)
 * - Checkout customizations (use checkout.php)
 * - Order processing (use admin.php)
 * - Shop page layout changes (use hooks.php)
 * - Shipping methods (use shipping.php)
 *
 * ============================================================================
 * CUSTOM PRODUCT FIELDS:
 * ============================================================================
 * - _product_origin    : "local" or "imported" (shown as badge)
 * - _hide_sale_badge   : "yes" to hide sale badge for this product
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
 * - woocommerce_product_options_general_product_data : Add admin fields
 * - woocommerce_process_product_meta                 : Save admin fields
 * - woocommerce_sale_flash                           : Filter sale badge
 * - woocommerce_before_shop_loop_item_title          : Shop loop badges
 * - woocommerce_single_product_summary               : Single product badges
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce/Product
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Product Origin and Sale Badge Fields to Product Admin
 *
 * Adds custom fields to the General tab of the product edit screen:
 * - Product Origin (select: Local/Imported)
 * - Hide Sale Badge (checkbox)
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_add_product_origin_field() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	global $post;

	$product_origin = get_post_meta( $post->ID, '_product_origin', true );

	$options = array(
		''         => __( 'Select Origin', 'woocommerce' ),
		'local'    => __( 'Local', 'woocommerce' ),
		'imported' => __( 'Imported', 'woocommerce' ),
	);

	echo '<div class="options_group">';

	// Product Origin dropdown.
	woocommerce_wp_select( array(
		'id'          => '_product_origin',
		'label'       => __( 'Product Origin', 'woocommerce' ),
		'options'     => $options,
		'value'       => $product_origin,
		'desc_tip'    => true,
		'description' => __( 'Select whether this product is locally sourced or imported.', 'woocommerce' ),
	) );

	// Hide Sale Badge checkbox.
	$hide_sale_badge = get_post_meta( $post->ID, '_hide_sale_badge', true );
	woocommerce_wp_checkbox( array(
		'id'          => '_hide_sale_badge',
		'label'       => __( 'Hide Sale badge', 'woocommerce' ),
		'value'       => $hide_sale_badge,
		'desc_tip'    => true,
		'description' => __( 'When enabled, the Sale badge will not be shown for this product.', 'woocommerce' ),
	) );

	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'woocommerce_add_product_origin_field', 10 );

/**
 * Save Product Origin and Sale Badge Fields
 *
 * Saves custom product fields when the product is saved in admin.
 * Includes nonce verification and capability checks for security.
 *
 * @since 1.0.0
 * @param int $post_id The product post ID being saved.
 * @return void
 */
function woocommerce_save_product_origin_field( $post_id ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Only process product post type.
	if ( get_post_type( $post_id ) !== 'product' ) {
		return;
	}

	// Verify nonce (WooCommerce adds this automatically).
	if ( ! isset( $_POST['woocommerce_meta_nonce'] ) ||
		 ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
		return;
	}

	// Skip autosaves.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check user permissions.
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	// Save Product Origin.
	if ( ! isset( $_POST['_product_origin'] ) ) {
		return;
	}

	$product_origin = sanitize_text_field( $_POST['_product_origin'] );
	$allowed_values = array( '', 'local', 'imported' );

	if ( ! in_array( $product_origin, $allowed_values, true ) ) {
		$product_origin = '';
	}

	update_post_meta( $post_id, '_product_origin', $product_origin );

	// Save Hide Sale Badge.
	$hide_sale_badge = isset( $_POST['_hide_sale_badge'] ) ? 'yes' : '';
	if ( 'yes' === $hide_sale_badge ) {
		update_post_meta( $post_id, '_hide_sale_badge', 'yes' );
	} else {
		delete_post_meta( $post_id, '_hide_sale_badge' );
	}
}
add_action( 'woocommerce_process_product_meta', 'woocommerce_save_product_origin_field', 10 );

/**
 * Conditionally Hide Sale Badge Per Product
 *
 * Filters the sale flash HTML to hide it for products where
 * the _hide_sale_badge meta is set to "yes".
 *
 * @since 1.0.0
 * @param string     $html    Sale flash HTML.
 * @param WP_Post    $post    Post object.
 * @param WC_Product $product Product object.
 * @return string Modified HTML (empty string to hide badge).
 */
function woocommerce_theme_maybe_hide_sale_flash( $html, $post, $product ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $html;
	}

	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $html;
	}

	$product_id = $product->get_id();
	if ( ! $product_id ) {
		return $html;
	}

	$hide_sale_badge = get_post_meta( $product_id, '_hide_sale_badge', true );
	if ( 'yes' === $hide_sale_badge ) {
		return ''; // Return empty to hide badge.
	}

	return $html;
}
add_filter( 'woocommerce_sale_flash', 'woocommerce_theme_maybe_hide_sale_flash', 10, 3 );

/**
 * Display Product Origin Badge on Shop Archives
 *
 * Shows "Local" or "Imported" badge on product cards in shop loops.
 * Badge is positioned via CSS (see /assets/css/style.css).
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_display_product_origin_badge() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Get product object.
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

	$product_id     = $product->get_id();
	$product_origin = get_post_meta( $product_id, '_product_origin', true );

	// Only display if origin is set.
	if ( empty( $product_origin ) ) {
		return;
	}

	// Validate against allowed values.
	$allowed_values = array( 'local', 'imported' );
	if ( ! in_array( $product_origin, $allowed_values, true ) ) {
		return;
	}

	// Determine badge text.
	$badge_text = '';
	if ( 'local' === $product_origin ) {
		$badge_text = __( 'Local', 'woocommerce' );
	} elseif ( 'imported' === $product_origin ) {
		$badge_text = __( 'Imported', 'woocommerce' );
	}

	printf(
		'<div class="product-origin-badge-wrapper"><span class="product-origin-badge product-origin-badge--%s">%s</span></div>',
		esc_attr( $product_origin ),
		esc_html( $badge_text )
	);
}
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_display_product_origin_badge', 3 );

/**
 * Display Product Origin Badge on Single Product Page
 *
 * Shows "Local" or "Imported" badge in the product summary area
 * on single product pages, above the product title.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_display_product_origin_badge_single() {
	// Only display on single product pages.
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Get product object.
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

	$product_id     = $product->get_id();
	$product_origin = get_post_meta( $product_id, '_product_origin', true );

	// Only display if origin is set.
	if ( empty( $product_origin ) ) {
		return;
	}

	// Validate against allowed values.
	$allowed_values = array( 'local', 'imported' );
	if ( ! in_array( $product_origin, $allowed_values, true ) ) {
		return;
	}

	// Determine badge text.
	$badge_text = '';
	if ( 'local' === $product_origin ) {
		$badge_text = __( 'Local', 'woocommerce' );
	} elseif ( 'imported' === $product_origin ) {
		$badge_text = __( 'Imported', 'woocommerce' );
	}

	printf(
		'<div class="product-origin-badge-wrapper product-origin-badge-wrapper--single"><span class="product-origin-badge product-origin-badge--%s">%s</span></div>',
		esc_attr( $product_origin ),
		esc_html( $badge_text )
	);
}
add_action( 'woocommerce_single_product_summary', 'woocommerce_display_product_origin_badge_single', 5 );
