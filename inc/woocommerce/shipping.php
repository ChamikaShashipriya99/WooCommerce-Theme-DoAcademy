<?php
/**
 * WooCommerce Custom Shipping Method
 *
 * Defines a custom WooCommerce shipping method that calculates shipping rates
 * based on the customer's geographic location using a three-tier system.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Define custom shipping method class (WC_Theme_Custom_Shipping_Method)
 * - Register shipping method with WooCommerce
 * - Calculate location-based shipping rates
 * - Provide admin settings for shipping rate configuration
 *
 * ============================================================================
 * SHIPPING TIERS:
 * ============================================================================
 * - Tier 1: Sri Lanka (LK) → Default: LKR 500
 * - Tier 2: Asia (excluding Sri Lanka) → Default: LKR 1,500
 * - Tier 3: All other countries → Default: LKR 3,000
 *
 * ============================================================================
 * WHAT BELONGS HERE:
 * ============================================================================
 * - Shipping method class definitions (extending WC_Shipping_Method)
 * - Shipping rate calculations
 * - Shipping method registration
 * - Shipping-related admin settings
 *
 * ============================================================================
 * WHAT DOES NOT BELONG HERE:
 * ============================================================================
 * - Cart functionality (use cart.php)
 * - Checkout customizations (use checkout.php)
 * - Order processing (use admin.php)
 * - Non-shipping related WooCommerce features
 *
 * ============================================================================
 * WOOCOMMERCE DEPENDENCY:
 * ============================================================================
 * This file is loaded ONLY when WooCommerce is active (checked in functions.php).
 * The shipping class extends WC_Shipping_Method, which requires WooCommerce.
 *
 * ============================================================================
 * ADMIN CONFIGURATION:
 * ============================================================================
 * Settings are available at: WooCommerce > Settings > Shipping > Shipping Zones
 * After adding this method to a zone, click to configure rates.
 *
 * ============================================================================
 * WOOCOMMERCE HOOKS USED:
 * ============================================================================
 * - woocommerce_shipping_methods : Register custom shipping method
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce/Shipping
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Shipping Method Class
 *
 * Extends WC_Shipping_Method to create a location-based shipping calculator.
 * WooCommerce automatically calls calculate_shipping() when shipping rates
 * are needed (cart page, checkout, etc.).
 *
 * @since 1.0.0
 */
if ( ! class_exists( 'WC_Theme_Custom_Shipping_Method' ) ) {
	/**
	 * Class WC_Theme_Custom_Shipping_Method
	 *
	 * Location-based shipping method with configurable rates for different
	 * geographic regions.
	 */
	class WC_Theme_Custom_Shipping_Method extends WC_Shipping_Method {

		/**
		 * Constructor
		 *
		 * Sets up the shipping method with ID, title, and description.
		 * Called by WooCommerce when initializing shipping methods.
		 *
		 * @param int $instance_id Instance ID for this shipping method instance.
		 */
		public function __construct( $instance_id = 0 ) {
			parent::__construct( $instance_id );

			$this->id                 = 'theme_custom_shipping';
			$this->method_title       = __( 'Location-Based Shipping', 'woocommerce' );
			$this->method_description = __( 'Location-based shipping method with three-tier rates: Sri Lanka, Asia (excluding LK), and Other countries.', 'woocommerce' );
			$this->enabled            = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
			$this->title              = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Custom Shipping', 'woocommerce' );

			$this->init();
		}

		/**
		 * Initialize Settings
		 *
		 * Loads form fields and settings from the database.
		 *
		 * @return void
		 */
		public function init() {
			$this->init_form_fields();
			$this->init_settings();

			// Save settings when updated in admin.
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Define Admin Settings Form Fields
		 *
		 * Creates the settings interface shown in WooCommerce shipping settings.
		 *
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'              => array(
					'title'   => __( 'Enable/Disable', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this shipping method', 'woocommerce' ),
					'default' => 'yes',
				),
				'title'                => array(
					'title'       => __( 'Method Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'     => __( 'Custom Shipping', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'sri_lanka_rate'       => array(
					'title'       => __( 'Sri Lanka Rate (LKR)', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Shipping rate for Sri Lanka. Enter amount without currency symbol.', 'woocommerce' ),
					'default'     => '500',
					'desc_tip'    => true,
				),
				'asia_rate'            => array(
					'title'       => __( 'Asia Rate (LKR)', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Shipping rate for Asian countries (excluding Sri Lanka).', 'woocommerce' ),
					'default'     => '1500',
					'desc_tip'    => true,
				),
				'other_countries_rate' => array(
					'title'       => __( 'Other Countries Rate (LKR)', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Shipping rate for all other countries (non-Asian).', 'woocommerce' ),
					'default'     => '3000',
					'desc_tip'    => true,
				),
			);
		}

		/**
		 * Calculate Shipping Rates
		 *
		 * Called by WooCommerce to get shipping rates for the cart. Determines
		 * the customer's country from the shipping destination and applies the
		 * appropriate rate tier.
		 *
		 * @param array $package Package data containing destination and cart items.
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {
			// Skip if disabled.
			if ( 'yes' !== $this->enabled ) {
				return;
			}

			// Get destination country from package (set by WooCommerce from customer address).
			$destination_country = isset( $package['destination']['country'] )
				? $package['destination']['country']
				: '';

			// Fallback to store's base country if no destination set.
			if ( empty( $destination_country ) ) {
				$destination_country = WC()->countries->get_base_country();
			}

			// Get configured rates from settings.
			$sri_lanka_rate       = isset( $this->settings['sri_lanka_rate'] )
				? floatval( $this->settings['sri_lanka_rate'] )
				: 500;

			$asia_rate            = isset( $this->settings['asia_rate'] )
				? floatval( $this->settings['asia_rate'] )
				: 1500;

			$other_countries_rate = isset( $this->settings['other_countries_rate'] )
				? floatval( $this->settings['other_countries_rate'] )
				: 3000;

			// ISO 3166-1 alpha-2 codes for Asian countries (excluding Sri Lanka).
			$asian_countries = array(
				'AF', 'AM', 'AZ', 'BH', 'BD', 'BT', 'BN', 'KH', 'CN', 'GE',
				'HK', 'IN', 'ID', 'IR', 'IQ', 'IL', 'JP', 'JO', 'KZ', 'KW',
				'KG', 'LA', 'LB', 'MO', 'MY', 'MV', 'MN', 'MM', 'NP', 'KP',
				'OM', 'PK', 'PS', 'PH', 'QA', 'SA', 'SG', 'KR', 'SY', 'TW',
				'TJ', 'TH', 'TL', 'TR', 'TM', 'AE', 'UZ', 'VN', 'YE',
			);

			$destination_country_upper = strtoupper( $destination_country );

			// Determine rate based on destination country.
			if ( 'LK' === $destination_country_upper ) {
				// Tier 1: Sri Lanka.
				$shipping_cost = $sri_lanka_rate;
				$rate_label    = sprintf( __( '%s (Sri Lanka)', 'woocommerce' ), $this->title );
			} elseif ( in_array( $destination_country_upper, $asian_countries, true ) ) {
				// Tier 2: Asia (excluding Sri Lanka).
				$shipping_cost = $asia_rate;
				$rate_label    = sprintf( __( '%s (Asia)', 'woocommerce' ), $this->title );
			} else {
				// Tier 3: All other countries.
				$shipping_cost = $other_countries_rate;
				$countries     = WC()->countries->get_countries();
				$country_name  = isset( $countries[ $destination_country ] )
					? $countries[ $destination_country ]
					: $destination_country;
				$rate_label    = sprintf( __( '%s (%s)', 'woocommerce' ), $this->title, $country_name );
			}

			// Register the rate with WooCommerce.
			$rate = array(
				'id'       => $this->id . '_' . $this->instance_id,
				'label'    => $rate_label,
				'cost'     => $shipping_cost,
				'calc_tax' => 'per_order',
			);

			$this->add_rate( $rate );
		}
	}
}

/**
 * Register Custom Shipping Method
 *
 * Adds the custom shipping method to WooCommerce's list of available
 * shipping methods so it can be added to shipping zones.
 *
 * @since 1.0.0
 * @param array $methods Existing shipping methods.
 * @return array Modified shipping methods array.
 */
function woocommerce_theme_register_custom_shipping_method( $methods ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $methods;
	}

	$methods['theme_custom_shipping'] = 'WC_Theme_Custom_Shipping_Method';

	return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'woocommerce_theme_register_custom_shipping_method', 10, 1 );
