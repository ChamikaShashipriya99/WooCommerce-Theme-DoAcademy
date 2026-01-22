<?php
/**
 * WooCommerce Admin Functions
 *
 * Handles WooCommerce admin/backend functionality including order display
 * in admin, email customizations, and WooCommerce settings extensions.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Display custom checkout fields on admin order screens
 * - Include custom fields in WooCommerce order emails
 * - Add custom settings to WooCommerce settings pages
 * - Sanitize and validate custom settings
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - Admin order screen modifications (woocommerce_admin_order_data_* hooks)
 * - Email template modifications
 * - WooCommerce settings page extensions
 * - Settings sanitization/validation
 * - Admin-only WooCommerce functionality
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Frontend display (use hooks.php, cart.php, checkout.php, etc.)
 * - My Account page features (use account.php)
 * - Product admin fields (use product.php)
 * - Checkout field definitions (use checkout.php)
 * - Cart calculations (use cart.php)
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
 * - woocommerce_admin_order_data_after_billing_address : Add fields to order admin
 * - woocommerce_email_order_meta_fields               : Add fields to order emails
 * - woocommerce_get_settings_products                 : Extend Products settings tab
 * - woocommerce_admin_settings_sanitize_option        : Sanitize custom settings
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce/Admin
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display Custom Checkout Fields on Admin Order Page
 *
 * Shows Business Type and VAT Number in the billing address section
 * of the order edit screen (WooCommerce > Orders > Edit Order).
 *
 * @since 1.0.0
 * @param WC_Order $order Order object.
 * @return void
 */
function woocommerce_theme_admin_order_custom_fields( $order ) {
	if ( ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	$business_type = get_post_meta( $order->get_id(), '_billing_business_type', true );
	$vat_number    = get_post_meta( $order->get_id(), '_billing_vat_number', true );

	// Only display section if at least one field has a value.
	if ( ! $business_type && ! $vat_number ) {
		return;
	}

	echo '<div class="woocommerce-order-data__billing-business-wrapper">';

	if ( $business_type ) {
		$business_type_map = array(
			'individual' => __( 'Individual', 'woocommerce' ),
			'company'    => __( 'Company', 'woocommerce' ),
		);

		$business_type_label = isset( $business_type_map[ $business_type ] )
			? $business_type_map[ $business_type ]
			: $business_type;

		echo '<p><strong>' . esc_html__( 'Business Type', 'woocommerce' ) . ':</strong> ' . esc_html( $business_type_label ) . '</p>';
	}

	if ( $vat_number ) {
		echo '<p><strong>' . esc_html__( 'VAT Number', 'woocommerce' ) . ':</strong> ' . esc_html( $vat_number ) . '</p>';
	}

	echo '</div>';
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocommerce_theme_admin_order_custom_fields', 10, 1 );

/**
 * Add Custom Fields to Order Emails
 *
 * Includes Business Type and VAT Number in WooCommerce order emails
 * sent to both admin and customer (new order, processing, completed, etc.).
 *
 * @since 1.0.0
 * @param array    $fields        Existing email fields.
 * @param bool     $sent_to_admin Whether email is being sent to admin.
 * @param WC_Order $order         Order object.
 * @return array Modified fields array.
 */
function woocommerce_theme_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
	if ( ! class_exists( 'WooCommerce' ) || ! is_a( $order, 'WC_Order' ) ) {
		return $fields;
	}

	$business_type = get_post_meta( $order->get_id(), '_billing_business_type', true );
	$vat_number    = get_post_meta( $order->get_id(), '_billing_vat_number', true );

	// Add Business Type if set.
	if ( ! empty( $business_type ) ) {
		$business_type_map = array(
			'individual' => __( 'Individual', 'woocommerce' ),
			'company'    => __( 'Company', 'woocommerce' ),
		);

		$business_type_label = isset( $business_type_map[ $business_type ] )
			? $business_type_map[ $business_type ]
			: $business_type;

		$fields['billing_business_type'] = array(
			'label' => __( 'Business Type', 'woocommerce' ),
			'value' => $business_type_label,
		);
	}

	// Add VAT Number if set.
	if ( ! empty( $vat_number ) ) {
		$fields['billing_vat_number'] = array(
			'label' => __( 'VAT Number', 'woocommerce' ),
			'value' => $vat_number,
		);
	}

	return $fields;
}
add_filter( 'woocommerce_email_order_meta_fields', 'woocommerce_theme_email_order_meta_fields', 10, 3 );

/**
 * Add Automatic Discount Settings to WooCommerce Products Tab
 *
 * Extends WooCommerce > Settings > Products with settings for the
 * automatic bulk discount feature (threshold and percentage).
 *
 * @since 1.0.0
 * @param array $settings Existing settings for the Products tab.
 * @return array Modified settings array.
 */
function woocommerce_theme_add_discount_settings_fields( $settings ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $settings;
	}

	// Section title.
	$settings[] = array(
		'title' => __( 'Automatic Discount Settings', 'woocommerce' ),
		'type'  => 'title',
		'desc'  => __( 'Configure automatic discount that applies when cart subtotal exceeds a specified threshold.', 'woocommerce' ),
		'id'    => 'automatic_discount_options',
	);

	// Discount threshold setting.
	$settings[] = array(
		'title'             => __( 'Discount Threshold (LKR)', 'woocommerce' ),
		'desc'              => __( 'Minimum cart subtotal required to apply automatic discount.', 'woocommerce' ),
		'id'                => 'wc_auto_discount_threshold',
		'type'              => 'number',
		'default'           => '20000',
		'desc_tip'          => true,
		'placeholder'       => '20000',
		'custom_attributes' => array(
			'step' => '1',
			'min'  => '0',
		),
	);

	// Discount percentage setting.
	$settings[] = array(
		'title'             => __( 'Discount Percentage (%)', 'woocommerce' ),
		'desc'              => __( 'Percentage discount to apply when threshold is met.', 'woocommerce' ),
		'id'                => 'wc_auto_discount_percentage',
		'type'              => 'number',
		'default'           => '20',
		'desc_tip'          => true,
		'placeholder'       => '20',
		'custom_attributes' => array(
			'step' => '0.1',
			'min'  => '0',
			'max'  => '100',
		),
	);

	// Section end.
	$settings[] = array(
		'type' => 'sectionend',
		'id'   => 'automatic_discount_options',
	);

	return $settings;
}
add_filter( 'woocommerce_get_settings_products', 'woocommerce_theme_add_discount_settings_fields', 10, 1 );

/**
 * Sanitize Automatic Discount Settings
 *
 * Validates and sanitizes the discount threshold and percentage values
 * before they are saved to the database.
 *
 * @since 1.0.0
 * @param mixed  $value     The value being saved.
 * @param string $option    The option name.
 * @param mixed  $raw_value The raw value before sanitization.
 * @return mixed Sanitized value.
 */
function woocommerce_theme_sanitize_discount_settings( $value, $option, $raw_value ) {
	// Sanitize threshold (must be positive integer).
	if ( 'wc_auto_discount_threshold' === $option ) {
		$value = absint( floatval( $raw_value ) );
		if ( $value < 0 ) {
			$value = 20000; // Default fallback.
		}
	}

	// Sanitize percentage (must be between 0 and 100).
	if ( 'wc_auto_discount_percentage' === $option ) {
		$value = floatval( $raw_value );
		if ( $value < 0 ) {
			$value = 20; // Default fallback.
		} elseif ( $value > 100 ) {
			$value = 100; // Maximum cap.
		}
	}

	return $value;
}
add_filter( 'woocommerce_admin_settings_sanitize_option', 'woocommerce_theme_sanitize_discount_settings', 10, 3 );
