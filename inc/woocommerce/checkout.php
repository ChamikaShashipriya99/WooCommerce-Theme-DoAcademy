<?php
/**
 * WooCommerce Checkout Functions
 *
 * Handles all checkout page customizations including custom fields,
 * field validation, order meta saving, and checkout display modifications.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Add custom checkout fields (Business Type, VAT Number)
 * - Validate custom checkout fields
 * - Save custom fields to order meta
 * - Support both classic checkout and Checkout Blocks
 * - Modify checkout order review display
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - woocommerce_checkout_fields filter modifications
 * - Checkout validation hooks
 * - Order meta saving during checkout
 * - Checkout Blocks API integrations
 * - Checkout page display modifications
 * - Conditional field visibility (JavaScript)
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Cart functionality (use cart.php)
 * - Order admin display (use admin.php)
 * - Payment gateway handling (use setup.php)
 * - Shipping calculations (use shipping.php)
 * - Post-order processing (use account.php)
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
 * - woocommerce_checkout_fields              : Add/modify checkout fields
 * - woocommerce_checkout_update_order_meta   : Save custom fields to order
 * - woocommerce_checkout_process             : Validate checkout fields
 * - woocommerce_blocks_checkout_fields       : Checkout Blocks field support
 * - woocommerce_store_api_checkout_update_order_from_request : Blocks API saving
 * - woocommerce_cart_item_name               : Modify order review item display
 * - wp_footer                                : Output conditional field JavaScript
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce/Checkout
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Custom Checkout Fields: Business Type and VAT Number
 *
 * Adds two custom fields to the billing section of checkout:
 * - Business Type (select: Individual/Company)
 * - VAT Number (text, shown only for Company)
 *
 * @since 1.0.0
 * @param array $fields Existing checkout fields grouped by section.
 * @return array Modified checkout fields.
 */
function woocommerce_theme_custom_checkout_fields( $fields ) {
	if ( ! isset( $fields['billing'] ) || ! is_array( $fields['billing'] ) ) {
		$fields['billing'] = array();
	}

	// Business Type dropdown.
	$fields['billing']['billing_business_type'] = array(
		'type'        => 'select',
		'label'       => __( 'Business Type', 'woocommerce' ),
		'placeholder' => __( 'Select business type', 'woocommerce' ),
		'required'    => false,
		'options'     => array(
			''           => __( 'Select business type', 'woocommerce' ),
			'individual' => __( 'Individual', 'woocommerce' ),
			'company'    => __( 'Company', 'woocommerce' ),
		),
		'class'       => array( 'form-row-wide' ),
		'priority'    => 115,
	);

	// VAT Number text field.
	$fields['billing']['billing_vat_number'] = array(
		'type'        => 'text',
		'label'       => __( 'VAT Number', 'woocommerce' ),
		'placeholder' => __( 'Enter VAT number (if applicable)', 'woocommerce' ),
		'required'    => false,
		'class'       => array( 'form-row-wide' ),
		'priority'    => 116,
		'clear'       => true,
	);

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'woocommerce_theme_custom_checkout_fields' );

/**
 * Save Custom Checkout Fields to Order Meta
 *
 * Saves Business Type and VAT Number to order post meta when order is created.
 * Uses underscore prefix (_billing_business_type) to hide from default meta box.
 *
 * @since 1.0.0
 * @param int   $order_id Order ID of the newly created order.
 * @param array $data     Array of sanitized checkout form data.
 * @return void
 */
function woocommerce_theme_save_custom_checkout_fields( $order_id, $data ) {
	if ( isset( $data['billing_business_type'] ) ) {
		update_post_meta(
			$order_id,
			'_billing_business_type',
			sanitize_text_field( $data['billing_business_type'] )
		);
	}

	if ( isset( $data['billing_vat_number'] ) ) {
		update_post_meta(
			$order_id,
			'_billing_vat_number',
			sanitize_text_field( $data['billing_vat_number'] )
		);
	}
}
add_action( 'woocommerce_checkout_update_order_meta', 'woocommerce_theme_save_custom_checkout_fields', 10, 2 );

/**
 * Checkout Blocks: Add Custom Fields to Block-based Checkout
 *
 * Extends the WooCommerce Checkout Block (Store API) with the same custom
 * fields available in classic checkout. This ensures consistent fields
 * regardless of which checkout method is used.
 *
 * @since 1.0.0
 * @param array $fields Existing Blocks checkout field schema.
 * @return array Modified field schema.
 */
function woocommerce_theme_blocks_checkout_fields( $fields ) {
	if ( empty( $fields['billingAddress']['fields'] ) || ! is_array( $fields['billingAddress']['fields'] ) ) {
		return $fields;
	}

	$billing_fields = $fields['billingAddress']['fields'];

	// Business Type for Blocks.
	$billing_fields['business_type'] = array(
		'label'       => __( 'Business Type', 'woocommerce' ),
		'required'    => false,
		'type'        => 'select',
		'placeholder' => __( 'Select business type', 'woocommerce' ),
		'options'     => array(
			array(
				'value' => '',
				'label' => __( 'Select business type', 'woocommerce' ),
			),
			array(
				'value' => 'individual',
				'label' => __( 'Individual', 'woocommerce' ),
			),
			array(
				'value' => 'company',
				'label' => __( 'Company', 'woocommerce' ),
			),
		),
		'priority'    => 115,
	);

	// VAT Number for Blocks.
	$billing_fields['vat_number'] = array(
		'label'       => __( 'VAT Number', 'woocommerce' ),
		'required'    => false,
		'type'        => 'text',
		'placeholder' => __( 'Enter VAT number (if applicable)', 'woocommerce' ),
		'priority'    => 116,
	);

	$fields['billingAddress']['fields'] = $billing_fields;

	return $fields;
}
add_filter( 'woocommerce_blocks_checkout_fields', 'woocommerce_theme_blocks_checkout_fields' );

/**
 * Checkout Blocks: Save Custom Fields from Store API Request
 *
 * Handles saving custom checkout fields when using the Checkout Block
 * (which uses the Store API instead of traditional form submission).
 *
 * @since 1.0.0
 * @param WC_Order              $order   Order being created/updated.
 * @param \WP_REST_Request|array $request Request data from the Store API.
 * @return void
 */
function woocommerce_theme_blocks_save_checkout_fields( $order, $request ) {
	if ( is_object( $request ) && method_exists( $request, 'get_params' ) ) {
		$data = $request->get_params();
	} else {
		$data = (array) $request;
	}

	$billing = isset( $data['billing_address'] ) && is_array( $data['billing_address'] )
		? $data['billing_address']
		: array();

	if ( isset( $billing['business_type'] ) ) {
		update_post_meta(
			$order->get_id(),
			'_billing_business_type',
			sanitize_text_field( $billing['business_type'] )
		);
	}

	if ( isset( $billing['vat_number'] ) ) {
		update_post_meta(
			$order->get_id(),
			'_billing_vat_number',
			sanitize_text_field( $billing['vat_number'] )
		);
	}
}
add_action( 'woocommerce_store_api_checkout_update_order_from_request', 'woocommerce_theme_blocks_save_checkout_fields', 10, 2 );

/**
 * Validate VAT Number for Company Billing
 *
 * Server-side validation: requires VAT Number when Business Type is "Company"
 * and a billing country is selected. Adds a checkout error if validation fails.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_validate_vat_number_conditionally() {
	$business_type = isset( $_POST['billing_business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_business_type'] ) ) : '';
	$country       = isset( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : '';
	$vat_number    = isset( $_POST['billing_vat_number'] ) ? trim( (string) wp_unslash( $_POST['billing_vat_number'] ) ) : '';

	if ( 'company' === $business_type && ! empty( $country ) && '' === $vat_number ) {
		wc_add_notice(
			__( 'Please enter your VAT number for company billing.', 'woocommerce' ),
			'error'
		);
	}
}
add_action( 'woocommerce_checkout_process', 'woocommerce_theme_validate_vat_number_conditionally' );

/**
 * Conditionally Show/Hide VAT Number Field
 *
 * Outputs JavaScript to show the VAT Number field only when Business Type
 * is "Company". The field is hidden for "Individual" or when no selection
 * is made.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_checkout_conditional_vat_visibility_by_business_type() {
	// Only output on checkout page.
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	?>
	<script type="text/javascript">
	(function($) {
		'use strict';

		function toggleVatNumberVisibilityByBusinessType() {
			var $businessTypeField = $('#billing_business_type');
			var $vatFieldWrapper = $('#billing_vat_number_field');

			if (!$businessTypeField.length || !$vatFieldWrapper.length) {
				return;
			}

			var selectedBusinessType = $businessTypeField.val();

			if (selectedBusinessType === 'company') {
				$vatFieldWrapper.show();
			} else {
				$vatFieldWrapper.hide();
				$vatFieldWrapper.find('input').val('');
			}
		}

		$(document).ready(function() {
			toggleVatNumberVisibilityByBusinessType();

			$('body').on('change', '#billing_business_type', function() {
				toggleVatNumberVisibilityByBusinessType();
			});

			$('body').on('updated_checkout', function() {
				toggleVatNumberVisibilityByBusinessType();
			});
		});
	})(jQuery);
	</script>
	<?php
}
add_action( 'wp_footer', 'woocommerce_theme_checkout_conditional_vat_visibility_by_business_type' );

/**
 * Add Product Images to Checkout Order Review
 *
 * Adds product thumbnail images to the "Your order" section on checkout,
 * making it easier for customers to verify their cart contents.
 *
 * @since 1.0.0
 * @param string $item_name     Default item name HTML.
 * @param array  $cart_item     Cart item data.
 * @param string $cart_item_key Cart item key.
 * @return string Modified item name HTML with thumbnail.
 */
function woocommerce_theme_checkout_order_review_product_image( $item_name, $cart_item, $cart_item_key ) {
	// Only modify on checkout page.
	if ( ! is_checkout() ) {
		return $item_name;
	}

	if ( empty( $cart_item['data'] ) || ! is_a( $cart_item['data'], 'WC_Product' ) ) {
		return $item_name;
	}

	$product   = $cart_item['data'];
	$thumbnail = $product->get_image( 'woocommerce_thumbnail', array(
		'class' => 'order-review-product-thumbnail',
		'alt'   => $product->get_name(),
	) );

	$item_html  = '<div class="order-review-product">';
	$item_html .= '<div class="order-review-product__thumb">' . $thumbnail . '</div>';
	$item_html .= '<div class="order-review-product__title">' . $item_name . '</div>';
	$item_html .= '</div>';

	return $item_html;
}
add_filter( 'woocommerce_cart_item_name', 'woocommerce_theme_checkout_order_review_product_image', 10, 3 );
