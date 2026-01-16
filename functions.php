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

	// Register navigation menus
	// This creates menu locations that can be assigned in Appearance > Menus
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'woocommerce' ),
		'footer'  => esc_html__( 'Footer Menu', 'woocommerce' ),
	) );

	// Add WooCommerce theme support
	// This enables WooCommerce features like product galleries, zoom, and lightbox
	add_theme_support( 'woocommerce' );

	// Add support for WooCommerce product gallery features
	// Enables zoom, lightbox, and slider functionality for product images
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Add WooCommerce product image sizes
	// These sizes are optimized for product display
	add_image_size( 'woocommerce-thumbnail', 300, 300, true );
	add_image_size( 'woocommerce-single', 600, 600, true );
	add_image_size( 'woocommerce-gallery-thumbnail', 150, 150, true );
}
// Hook into the 'after_setup_theme' action
// This ensures the setup function runs at the right time
add_action( 'after_setup_theme', 'woocommerce_theme_setup' );

/**
 * Enqueue Styles and Scripts
 *
 * This function properly loads CSS and JavaScript files using WordPress's
 * enqueue system. This is the WordPress best practice for including assets,
 * as it prevents duplicate loading, handles dependencies, and allows plugins
 * to modify or remove assets as needed.
 * 
 * Why use wp_enqueue_style/script instead of direct <link>/<script> tags:
 * - Prevents duplicate asset loading
 * - Handles dependency management automatically
 * - Allows plugins to modify/remove assets
 * - Enables conditional loading based on page context
 * - Supports cache busting via version numbers
 * 
 * Hook: wp_enqueue_scripts
 * Execution timing: Runs on every frontend page load, before wp_head() outputs assets.
 * This hook fires on all frontend pages (not admin), ensuring assets load correctly
 * on shop, product, cart, checkout, and regular WordPress pages.
 */
function woocommerce_theme_enqueue_assets() {
	/**
	 * Get Theme Version for Cache Busting
	 * 
	 * wp_get_theme()->get('Version') retrieves the version number from style.css
	 * header comment. This version is appended to asset URLs as a query parameter,
	 * forcing browsers to download new versions when the theme is updated.
	 * 
	 * Example: style.css?ver=1.0.0
	 * When theme updates to 1.0.1, browsers see a new URL and download fresh files.
	 */
	$theme_version = wp_get_theme()->get( 'Version' );

	/**
	 * Enqueue Font Awesome Icon Library
	 * 
	 * Font Awesome provides icon fonts used throughout the theme, particularly
	 * for the shopping cart icon in the header. Loading from CDN provides:
	 * - Fast delivery via CDN infrastructure
	 * - Reduced server load
	 * - Potential browser caching across sites
	 * 
	 * Dependencies: None (empty array) - Font Awesome loads independently
	 * Version: '5.15.4' - Specific version ensures consistency
	 */
	wp_enqueue_style(
		'font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
		array(), // No dependencies
		'5.15.4' // Version number
	);

	/**
	 * Prepare Style Dependencies Array
	 * 
	 * Dependencies ensure assets load in the correct order. WordPress will:
	 * 1. Load Font Awesome first (no dependencies)
	 * 2. Load WooCommerce styles (if active) after Font Awesome
	 * 3. Load theme styles last (depends on both above)
	 * 
	 * This ensures theme styles can override WooCommerce styles, and both
	 * can use Font Awesome icons without conflicts.
	 */
	$dependencies = array( 'font-awesome' ); // Theme style depends on Font Awesome
	if ( class_exists( 'WooCommerce' ) ) {
		/**
		 * Add WooCommerce Stylesheet as Dependency
		 * 
		 * 'woocommerce-general' is the handle for WooCommerce's main stylesheet.
		 * By adding it as a dependency, we ensure:
		 * - WooCommerce styles load before theme styles
		 * - Theme styles can override WooCommerce defaults
		 * - Styles load in correct order for proper cascading
		 */
		$dependencies[] = 'woocommerce-general';
	}

	/**
	 * Enqueue Main Theme Stylesheet
	 * 
	 * This loads the primary CSS file containing all theme styles. The file
	 * includes styles for:
	 * - Layout and typography
	 * - WooCommerce page styling (shop, product, cart, checkout)
	 * - Mini cart dropdown
	 * - Navigation and header
	 * - Footer and widgets
	 * 
	 * get_template_directory_uri() returns the theme's URL, allowing WordPress
	 * to construct the full path to the stylesheet regardless of site URL.
	 */
	wp_enqueue_style(
		'woocommerce-theme-style',                    // Handle (unique identifier)
		get_template_directory_uri() . '/assets/css/style.css', // Path to stylesheet
		$dependencies,                                // Dependencies (Font Awesome and WooCommerce if active)
		$theme_version                                // Version (for cache busting)
	);

	/**
	 * Enqueue Mini Cart JavaScript
	 * 
	 * This script handles AJAX-based cart updates, allowing the mini cart to
	 * update without page reloads when items are added/removed. The script:
	 * - Listens for cart update events
	 * - Makes AJAX requests to get updated cart HTML
	 * - Updates the mini cart dropdown dynamically
	 * - Updates cart count badge
	 * 
	 * Dependencies: jQuery (WordPress includes jQuery by default)
	 * $in_footer: true - Loads script before </body> tag for better performance
	 */
	wp_enqueue_script(
		'woocommerce-minicart-ajax',                    // Handle (unique identifier)
		get_template_directory_uri() . '/assets/js/minicart.js', // Path to JavaScript file
		array( 'jquery' ),                               // Dependencies (jQuery is required)
		$theme_version,                                 // Version (for cache busting)
		true                                            // Load in footer (better performance)
	);

	/**
	 * Enqueue Mobile Menu JavaScript
	 * 
	 * Handles hamburger menu toggle functionality for mobile devices.
	 * The menu slides in from left to right when the hamburger icon is clicked.
	 */
	wp_enqueue_script(
		'woocommerce-mobile-menu',                      // Handle (unique identifier)
		get_template_directory_uri() . '/assets/js/mobile-menu.js', // Path to JavaScript file
		array( 'jquery' ),                               // Dependencies (jQuery is required)
		$theme_version,                                 // Version (for cache busting)
		true                                            // Load in footer (better performance)
	);
}
/**
 * Hook Registration: wp_enqueue_scripts
 * 
 * This hook fires on every frontend page load, before wp_head() outputs the
 * <head> section. It is the standard WordPress hook for enqueuing frontend
 * assets. The hook does NOT fire in the WordPress admin area.
 * 
 * Execution timing: Early in page load, before template rendering begins.
 * This ensures all assets are registered before wp_head() tries to output them.
 */
add_action( 'wp_enqueue_scripts', 'woocommerce_theme_enqueue_assets' );

/**
 * Remove WooCommerce Default Wrappers
 *
 * WooCommerce adds default HTML wrapper elements around shop/product content.
 * These default wrappers don't match our theme's HTML structure, so we remove
 * them and replace them with custom wrappers that match our theme's layout.
 * 
 * Why this exists in a WooCommerce theme:
 * - Ensures consistent HTML structure across all pages
 * - Allows theme to control layout and styling
 * - Prevents WooCommerce default styles from conflicting with theme styles
 * - Maintains semantic HTML structure matching header.php and footer.php
 * 
 * Hook execution timing: This function runs on the 'wp' action, which fires
 * after WordPress core is fully loaded but before template rendering begins.
 * This timing is critical because WooCommerce hooks must be registered before
 * we can remove them.
 */
function woocommerce_theme_remove_default_wrappers() {
	/**
	 * Check if WooCommerce is Active
	 * 
	 * This prevents fatal errors if WooCommerce plugin is deactivated.
	 * Without this check, attempting to remove WooCommerce hooks would cause
	 * PHP errors when WooCommerce classes don't exist.
	 */
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	/**
	 * Remove WooCommerce Default Opening Wrapper
	 * 
	 * remove_action() removes a previously registered action hook. To successfully
	 * remove a hook, we must match:
	 * - The exact hook name: 'woocommerce_before_main_content'
	 * - The exact callback function: 'woocommerce_output_content_wrapper'
	 * - The exact priority: 10 (WooCommerce's default priority)
	 * 
	 * If any of these don't match exactly, the removal fails silently.
	 * 
	 * Hook: woocommerce_before_main_content
	 * When it runs: Before WooCommerce main content area (shop, product pages)
	 * What it does: Outputs opening wrapper div (we want to replace this)
	 */
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );

	/**
	 * Remove WooCommerce Default Closing Wrapper
	 * 
	 * Similar to above, this removes the closing wrapper that WooCommerce
	 * outputs after the main content area.
	 * 
	 * Hook: woocommerce_after_main_content
	 * When it runs: After WooCommerce main content area
	 * What it does: Outputs closing wrapper div (we want to replace this)
	 */
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
}
/**
 * Hook Registration: wp
 * 
 * The 'wp' action fires after WordPress core is fully loaded, including:
 * - All plugins loaded and initialized
 * - Theme functions.php loaded
 * - Query parsed and main query executed
 * - But before template rendering begins
 * 
 * Why use 'wp' instead of 'init':
 * - 'init' fires too early (before plugins fully loaded)
 * - 'wp' ensures WooCommerce hooks are registered before we try to remove them
 * - This is the standard pattern for modifying WooCommerce hooks
 * 
 * Execution timing: Runs on every page load, after plugins load, before template renders.
 */
add_action( 'wp', 'woocommerce_theme_remove_default_wrappers' );

/**
 * Add Custom Theme Opening Wrapper
 *
 * This function outputs the opening HTML wrapper that matches our theme's
 * structure. It replaces WooCommerce's default wrapper (which we removed above)
 * with our custom wrapper that ensures consistent styling across all pages.
 * 
 * Why this exists in a WooCommerce theme:
 * - Maintains consistent HTML structure across WordPress and WooCommerce pages
 * - Allows theme CSS to style WooCommerce pages correctly
 * - Ensures proper semantic HTML structure
 * - Matches the wrapper structure used in index.php for blog pages
 * 
 * The wrapper div uses:
 * - id="primary": Matches main content area ID from index.php
 * - class="site-main": Standard WordPress main content class
 * - class="woocommerce-page": WooCommerce-specific class for targeted styling
 */
function woocommerce_theme_wrapper_start() {
	/**
	 * Check if WooCommerce is Active
	 * 
	 * Prevents outputting wrapper HTML when WooCommerce is not installed.
	 * Without WooCommerce, this hook wouldn't fire anyway, but this check
	 * provides an extra safety layer.
	 */
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	/**
	 * Output Opening Wrapper Div
	 * 
	 * This HTML matches the structure in index.php, ensuring WooCommerce pages
	 * (shop, product, cart, checkout, myaccount) have the same wrapper structure
	 * as regular WordPress pages. This consistency allows theme CSS to work
	 * uniformly across all page types.
	 */
	echo '<div id="primary" class="site-main woocommerce-page">';
}
/**
 * Hook Registration: woocommerce_before_main_content
 * 
 * This WooCommerce action hook fires immediately before the main content area
 * on WooCommerce pages (shop, product, cart, checkout, myaccount). It is the
 * standard location for outputting opening wrapper elements.
 * 
 * When it runs:
 * - On shop archive pages: Before product loop
 * - On single product pages: Before product content
 * - On cart page: Before cart table
 * - On checkout page: Before checkout form
 * - On account pages: Before account content
 * 
 * Priority 10: Standard priority ensures it runs at the expected time, replacing
 * the default WooCommerce wrapper we removed earlier.
 */
add_action( 'woocommerce_before_main_content', 'woocommerce_theme_wrapper_start', 10 );

/**
 * Add Custom Theme Closing Wrapper
 *
 * This function outputs the closing HTML wrapper that closes the opening
 * wrapper added by woocommerce_theme_wrapper_start(). Proper HTML structure
 * requires matching opening and closing tags.
 * 
 * Why this exists:
 * - Closes the opening wrapper div properly
 * - Maintains valid HTML structure
 * - Ensures consistent page structure across all WooCommerce pages
 */
function woocommerce_theme_wrapper_end() {
	/**
	 * Output Closing Wrapper Div
	 * 
	 * This closes the <div id="primary" class="site-main woocommerce-page">
	 * that was opened in woocommerce_theme_wrapper_start(). The HTML comment
	 * helps developers identify which opening tag this closes.
	 */
	echo '</div><!-- #primary -->';
}
/**
 * Hook Registration: woocommerce_after_main_content
 * 
 * This WooCommerce action hook fires immediately after the main content area
 * on WooCommerce pages. It is the standard location for outputting closing
 * wrapper elements and is the counterpart to woocommerce_before_main_content.
 * 
 * When it runs:
 * - On shop archive pages: After product loop
 * - On single product pages: After product content
 * - On cart page: After cart table
 * - On checkout page: After checkout form
 * - On account pages: After account content
 * 
 * Priority 10: Standard priority ensures it runs at the expected time, matching
 * the priority of the opening wrapper function.
 */
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

/**
 * Shop Page Poster / Hero Banner
 *
 * Outputs a full-width poster-style banner with image at the top of the main shop page.
 * The image path can be customized by changing the $poster_image_url variable below.
 */
function woocommerce_theme_shop_poster_banner() {
	// Only show on the main shop page
	if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}

	// Customize the poster image URL here
	// Replace with your image path: get_template_directory_uri() . '/assets/images/shop-poster.jpg'
	// Or use an external URL: 'https://example.com/image.jpg'
	$poster_image_url = get_template_directory_uri() . '/assets/images/shop-poster.jpg';

	?>
	<section class="shop-hero-banner" aria-label="<?php esc_attr_e( 'Shop highlight', 'woocommerce' ); ?>">
		<div class="shop-hero-banner__image-wrapper">
			<img 
				src="<?php echo esc_url( $poster_image_url ); ?>" 
				alt="<?php esc_attr_e( 'Shop banner', 'woocommerce' ); ?>" 
				class="shop-hero-banner__image"
			/>
		</div>
	</section>
	<?php
}
// Display the poster just before the product grid on the shop page.
add_action( 'woocommerce_before_shop_loop', 'woocommerce_theme_shop_poster_banner', 5 );

/**
 * Checkout: Add Business Type and VAT Number Fields
 *
 * Uses woocommerce_checkout_fields to add:
 * - billing_business_type (select: Individual, Company)
 * - billing_vat_number (text)
 *
 * @param array $fields Existing checkout fields grouped by section.
 * @return array Modified checkout fields.
 */
function woocommerce_theme_custom_checkout_fields( $fields ) {
	// Ensure billing section exists.
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
		'priority'    => 115, // After standard billing fields.
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
 * Checkout: Save Custom Checkout Fields to Order Meta
 *
 * This function saves custom checkout fields (Business Type and VAT Number)
 * to order meta data when an order is created during checkout.
 *
 * Hook: woocommerce_checkout_update_order_meta
 * This hook fires after checkout validation passes and before order is finalized.
 * It receives sanitized checkout data from the checkout form.
 *
 * Meta keys use underscore prefix (_billing_business_type) to hide them from
 * the default custom fields meta box in WordPress admin.
 *
 * @param int   $order_id Order ID of the newly created order.
 * @param array $data     Array of sanitized checkout form data.
 */
function woocommerce_theme_save_custom_checkout_fields( $order_id, $data ) {
	// Check if Business Type field was submitted in checkout form
	// isset() ensures the field exists before trying to save it
	if ( isset( $data['billing_business_type'] ) ) {
		// Save Business Type to order meta
		// update_post_meta() handles both add and update operations
		// sanitize_text_field() removes any potentially dangerous characters
		update_post_meta(
			$order_id,                                    // Order ID
			'_billing_business_type',                     // Meta key (with underscore prefix)
			sanitize_text_field( $data['billing_business_type'] ) // Sanitized value
		);
	}

	// Check if VAT Number field was submitted in checkout form
	// isset() ensures the field exists before trying to save it
	if ( isset( $data['billing_vat_number'] ) ) {
		// Save VAT Number to order meta
		// update_post_meta() handles both add and update operations
		// sanitize_text_field() removes any potentially dangerous characters
		update_post_meta(
			$order_id,                                // Order ID
			'_billing_vat_number',                    // Meta key (with underscore prefix)
			sanitize_text_field( $data['billing_vat_number'] ) // Sanitized value
		);
	}
}
// Hook into WooCommerce checkout update order meta action
// Priority 10 ensures it runs at the standard time
// Accepts 2 parameters: order_id (int) and data (array)
add_action( 'woocommerce_checkout_update_order_meta', 'woocommerce_theme_save_custom_checkout_fields', 10, 2 );

/**
 * Admin: Display Custom Checkout Fields on Order Details Page
 *
 * This function displays custom checkout fields (Business Type and VAT Number)
 * on the WooCommerce order edit screen in the WordPress admin.
 *
 * Hook: woocommerce_admin_order_data_after_billing_address
 * This hook fires after the billing address section on the order edit screen,
 * allowing us to display custom fields in a logical location.
 *
 * The fields are displayed only if they have values, preventing empty sections.
 *
 * @param WC_Order $order Order object containing order data.
 */
function woocommerce_theme_admin_order_custom_fields( $order ) {
	// Check if order object is valid
	if ( ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	// Retrieve custom field values from order meta
	// get_post_meta() returns the saved value or empty string if not set
	$business_type = get_post_meta( $order->get_id(), '_billing_business_type', true );
	$vat_number    = get_post_meta( $order->get_id(), '_billing_vat_number', true );

	// Exit early if no custom fields have values
	// This prevents empty wrapper divs from being displayed
	if ( ! $business_type && ! $vat_number ) {
		return;
	}

	// Start wrapper div for custom fields section
	// This provides a container for styling and organization
	echo '<div class="woocommerce-order-data__billing-business-wrapper">';

	// Display Business Type if it exists
	if ( $business_type ) {
		// Map internal values to user-friendly display labels
		$business_type_map = array(
			'individual' => __( 'Individual', 'woocommerce' ),
			'company'    => __( 'Company', 'woocommerce' ),
		);

		// Get display label or fallback to raw value if mapping doesn't exist
		$business_type_label = isset( $business_type_map[ $business_type ] ) 
			? $business_type_map[ $business_type ] 
			: $business_type;

		// Output Business Type field
		// esc_html() ensures safe output and prevents XSS attacks
		echo '<p><strong>' . esc_html__( 'Business Type', 'woocommerce' ) . ':</strong> ' . esc_html( $business_type_label ) . '</p>';
	}

	// Display VAT Number if it exists
	if ( $vat_number ) {
		// Output VAT Number field
		// esc_html() ensures safe output and prevents XSS attacks
		echo '<p><strong>' . esc_html__( 'VAT Number', 'woocommerce' ) . ':</strong> ' . esc_html( $vat_number ) . '</p>';
	}

	// Close wrapper div
	echo '</div>';
}
// Hook into WooCommerce admin order data after billing address action
// Priority 10 ensures it displays at the standard time
// Accepts 1 parameter: order object (WC_Order)
add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocommerce_theme_admin_order_custom_fields', 10, 1 );

/**
 * Emails: Display Custom Checkout Fields in Order Emails
 *
 * This function displays custom checkout fields (Business Type and VAT Number)
 * in both admin order emails and customer order emails.
 *
 * Hook: woocommerce_email_order_meta_fields
 * This hook allows adding custom fields to order emails sent to:
 * - Admin (new order, processing, completed, etc.)
 * - Customer (order received, processing, completed, etc.)
 *
 * @param array    $fields    Array of existing email order meta fields.
 * @param bool     $sent_to_admin Whether the email is being sent to admin (true) or customer (false).
 * @param WC_Order $order     Order object containing order data.
 * @return array Modified fields array with custom checkout fields added.
 */
function woocommerce_theme_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) || ! is_a( $order, 'WC_Order' ) ) {
		return $fields;
	}

	// Get custom field values from order meta
	// Using get_post_meta() to retrieve saved values
	$business_type = get_post_meta( $order->get_id(), '_billing_business_type', true );
	$vat_number    = get_post_meta( $order->get_id(), '_billing_vat_number', true );

	// Only add fields if they have values
	// This prevents empty fields from appearing in emails

	// Add Business Type field if it exists
	if ( ! empty( $business_type ) ) {
		// Map internal values to display labels
		$business_type_map = array(
			'individual' => __( 'Individual', 'woocommerce' ),
			'company'    => __( 'Company', 'woocommerce' ),
		);

		// Get display label or fallback to raw value
		$business_type_label = isset( $business_type_map[ $business_type ] ) 
			? $business_type_map[ $business_type ] 
			: $business_type;

		// Add to fields array
		// Structure: label => value (WooCommerce will format this in email template)
		$fields['billing_business_type'] = array(
			'label' => __( 'Business Type', 'woocommerce' ),
			'value' => $business_type_label,
		);
	}

	// Add VAT Number field if it exists
	if ( ! empty( $vat_number ) ) {
		// Add to fields array
		// Structure: label => value (WooCommerce will format this in email template)
		$fields['billing_vat_number'] = array(
			'label' => __( 'VAT Number', 'woocommerce' ),
			'value' => $vat_number,
		);
	}

	// Return modified fields array
	// WooCommerce will display these in the order details section of emails
	return $fields;
}
// Hook into WooCommerce email order meta fields filter
// Priority 10 ensures it runs at the standard time
// Accepts 3 parameters: fields array, sent_to_admin boolean, order object
add_filter( 'woocommerce_email_order_meta_fields', 'woocommerce_theme_email_order_meta_fields', 10, 3 );

/**
 * Cart: Apply Automatic 20% Discount for Orders Over LKR 20,000
 *
 * This function automatically applies a 20% discount when the cart subtotal
 * exceeds LKR 20,000. The discount is applied as a negative fee, which
 * appears clearly in the cart totals section.
 *
 * Hook: woocommerce_cart_calculate_fees
 * This hook fires during cart calculation, allowing us to add fees (positive or negative).
 * Negative fees act as discounts and are displayed in the cart totals.
 *
 * Calculation Logic:
 * 1. Get cart subtotal (sum of all line items before taxes/shipping)
 * 2. Check if subtotal > 20,000 LKR
 * 3. If condition met, calculate 20% discount: subtotal × 0.20
 * 4. Add as negative fee (discount) to cart
 * 5. Check if fee already exists to prevent duplicate application
 *
 * Example:
 * - Cart subtotal: LKR 25,000
 * - Discount (20%): LKR -5,000
 * - Final subtotal: LKR 20,000
 *
 * @param WC_Cart $cart Cart object containing cart data and methods.
 */
function woocommerce_theme_apply_automatic_discount( $cart ) {
	// Check if WooCommerce is active and cart object is valid
	if ( ! class_exists( 'WooCommerce' ) || ! is_a( $cart, 'WC_Cart' ) ) {
		return;
	}

	// Only apply discount if cart is not empty
	// Empty carts don't need discount calculation
	if ( $cart->is_empty() ) {
		return;
	}

	// Get cart subtotal (excluding taxes and shipping)
	// get_subtotal() returns the sum of all line items before taxes
	// This is the base amount we use for discount calculation
	$cart_subtotal = $cart->get_subtotal();

	// Discount threshold: LKR 20,000
	// Orders must exceed this amount to qualify for discount
	$discount_threshold = 20000;

	// Discount percentage: 20%
	// This is the percentage applied to the cart subtotal
	$discount_percentage = 0.20; // 20% as decimal (0.20)

	// Check if cart subtotal exceeds the threshold
	// Only apply discount if subtotal is greater than LKR 20,000
	if ( $cart_subtotal <= $discount_threshold ) {
		return; // Exit early if threshold not met
	}

	// Calculate discount amount
	// Formula: subtotal × discount_percentage
	// Example: 25,000 × 0.20 = 5,000
	$discount_amount = $cart_subtotal * $discount_percentage;

	// Discount fee name (used for identification and display)
	// This name appears in the cart totals section
	$discount_fee_name = __( 'Bulk Order Discount (20%)', 'woocommerce' );

	// Check if discount fee already exists to prevent duplicate application
	// get_fees() returns all fees currently applied to the cart
	$existing_fees = $cart->get_fees();
	$discount_already_applied = false;

	// Loop through existing fees to check if our discount is already applied
	// We check by fee name to identify our specific discount
	foreach ( $existing_fees as $fee_key => $fee ) {
		// Check if fee name matches our discount fee name
		// This prevents the discount from being applied multiple times
		// WooCommerce stores fee name in the 'name' property
		if ( isset( $fee->name ) && $discount_fee_name === $fee->name ) {
			$discount_already_applied = true;
			break; // Exit loop if discount already found
		}
	}

	// Only add discount if it hasn't been applied already
	// This prevents duplicate discounts on cart recalculation
	// WooCommerce recalculates fees on every cart update, so this check is essential
	if ( ! $discount_already_applied ) {
		// Add discount as a negative fee
		// Negative fees appear as discounts in cart totals section
		// Parameters:
		// 1. Fee name: Displayed in cart/checkout (e.g., "Bulk Order Discount (20%)")
		// 2. Fee amount: Negative value creates a discount (e.g., -5000)
		// 3. Taxable: false = discount is not subject to tax
		// 4. Tax class: empty string = use default tax class
		$cart->add_fee(
			$discount_fee_name,  // Fee name (displayed in cart)
			-$discount_amount,    // Negative amount = discount
			false,                // Not taxable
			''                    // Tax class (empty = default)
		);
	}
}
// Hook into WooCommerce cart calculate fees action
// Priority 10 ensures it runs at the standard time during cart calculation
// Accepts 1 parameter: cart object (WC_Cart)
add_action( 'woocommerce_cart_calculate_fees', 'woocommerce_theme_apply_automatic_discount', 10, 1 );

/**
 * Custom Shipping Method: Country-Based Shipping Rates
 *
 * This class creates a custom WooCommerce shipping method that calculates
 * shipping rates dynamically based on the customer's country.
 *
 * Class Structure:
 * - Extends WC_Shipping_Method (WooCommerce base shipping class)
 * - Implements calculate_shipping() for dynamic rate calculation
 * - Provides admin settings for configuring country-specific rates
 * - Integrates seamlessly with WooCommerce shipping zones
 *
 * Usage:
 * 1. Enable in WooCommerce > Settings > Shipping
 * 2. Configure rates per country in shipping method settings
 * 3. Rates automatically apply based on customer's shipping country
 */
if ( ! class_exists( 'WC_Theme_Custom_Shipping_Method' ) ) {
	class WC_Theme_Custom_Shipping_Method extends WC_Shipping_Method {

		/**
		 * Constructor: Initialize Shipping Method
		 *
		 * Sets up the shipping method with ID, title, description, and default settings.
		 * This runs when WooCommerce loads shipping methods.
		 *
		 * @param int $instance_id Instance ID for this shipping method instance.
		 */
		public function __construct( $instance_id = 0 ) {
			// Call parent constructor to initialize base shipping method
			// This sets up core WooCommerce shipping functionality
			parent::__construct( $instance_id );

			// Set unique shipping method ID
			// This ID is used internally by WooCommerce to identify the method
			$this->id = 'theme_custom_shipping';

			// Set shipping method title (displayed to customers)
			// This appears in checkout as the shipping option name
			$this->method_title = __( 'Custom Country Shipping', 'woocommerce' );

			// Set shipping method description (shown in admin)
			// This helps admins understand what the shipping method does
			$this->method_description = __( 'Custom shipping method with country-based rates. Configure rates for different countries in the settings below.', 'woocommerce' );

			// Enable admin settings panel
			// This allows admins to configure shipping rates in WooCommerce settings
			$this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';

			// Set default title (displayed to customers at checkout)
			$this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Custom Shipping', 'woocommerce' );

			// Initialize the shipping method
			// This loads settings and prepares the method for use
			$this->init();
		}

		/**
		 * Initialize Shipping Method Settings
		 *
		 * Sets up default settings and loads saved configuration.
		 * This method is called from the constructor.
		 */
		public function init() {
			// Load settings from database
			// WooCommerce automatically saves/loads settings using the method ID
			$this->init_form_fields();
			$this->init_settings();

			// Save settings when admin updates them
			// This hook fires when settings are saved in WooCommerce admin
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Define Admin Settings Form Fields
		 *
		 * Creates the settings form that appears in WooCommerce > Settings > Shipping.
		 * Admins can configure title, enabled status, and country-specific rates here.
		 */
		public function init_form_fields() {
			// Define default settings fields
			// These fields appear in the shipping method settings panel
			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this shipping method', 'woocommerce' ),
					'default' => 'yes',
				),
				'title' => array(
					'title'       => __( 'Method Title', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default'     => __( 'Custom Shipping', 'woocommerce' ),
					'desc_tip'    => true,
				),
				'local_rate' => array(
					'title'       => __( 'Local Rate (LKR)', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Shipping rate for local deliveries (e.g., Sri Lanka). Enter amount without currency symbol.', 'woocommerce' ),
					'default'     => '500',
					'desc_tip'    => true,
				),
				'international_rate' => array(
					'title'       => __( 'International Rate (LKR)', 'woocommerce' ),
					'type'        => 'text',
					'description' => __( 'Shipping rate for international deliveries. Enter amount without currency symbol.', 'woocommerce' ),
					'default'     => '2000',
					'desc_tip'    => true,
				),
				'local_countries' => array(
					'title'       => __( 'Local Countries', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Enter country codes (one per line) that should use local rates. Leave empty to use default local country. Example: LK, IN, BD', 'woocommerce' ),
					'default'     => 'LK',
					'desc_tip'    => true,
				),
			);
		}

		/**
		 * Calculate Shipping Rates
		 *
		 * This is the core method that calculates shipping costs based on:
		 * - Customer's shipping country
		 * - Configured rates in admin settings
		 * - Cart contents (if needed for weight/quantity-based calculations)
		 *
		 * WooCommerce calls this method automatically during checkout when:
		 * - Customer enters/changes shipping address
		 * - Cart contents change
		 * - Page loads on checkout
		 *
		 * HOW THE SYSTEM GETS THE COUNTRY:
		 * 
		 * 1. Customer enters shipping address at checkout
		 *    - Country field is filled in checkout form
		 *    - Stored in: $_POST['shipping_country'] or customer session
		 * 
		 * 2. WooCommerce builds the $package array
		 *    - WooCommerce collects shipping address data
		 *    - Creates package array with destination information
		 *    - Package structure:
		 *      $package = array(
		 *          'destination' => array(
		 *              'country'  => 'LK',      // Country code (e.g., 'LK' for Sri Lanka)
		 *              'state'    => 'WP',      // State/Province code
		 *              'postcode' => '10100',   // Postal/ZIP code
		 *              'city'     => 'Colombo', // City name
		 *          ),
		 *          'contents' => array(...),     // Cart items
		 *          'contents_cost' => 25000,    // Total cart value
		 *      )
		 * 
		 * 3. WooCommerce calls calculate_shipping() with $package
		 *    - Passes package to all active shipping methods
		 *    - Each method extracts country from $package['destination']['country']
		 * 
		 * 4. Country source priority:
		 *    a) Shipping address (if "Ship to different address" is checked)
		 *    b) Billing address (if shipping same as billing)
		 *    c) Store base country (fallback if no address entered)
		 * 
		 * 5. Country code format:
		 *    - ISO 3166-1 alpha-2 format (2 letters)
		 *    - Examples: 'LK' (Sri Lanka), 'US' (USA), 'GB' (UK), 'IN' (India)
		 *
		 * @param array $package Package data containing destination and cart items.
		 *                       Structure: array(
		 *                           'destination' => array(
		 *                               'country'  => 'LK',
		 *                               'state'    => 'WP',
		 *                               'postcode' => '10100',
		 *                               'city'     => 'Colombo',
		 *                           ),
		 *                           'contents' => array(...),
		 *                       )
		 */
		public function calculate_shipping( $package = array() ) {
			// Check if shipping method is enabled
			// If disabled in admin, don't calculate rates
			if ( 'yes' !== $this->enabled ) {
				return;
			}

			// ============================================
			// HOW TO GET THE COUNTRY FROM PACKAGE
			// ============================================
			// 
			// The $package array is automatically provided by WooCommerce
			// It contains the customer's SHIPPING DESTINATION information
			// 
			// IMPORTANT: $package['destination'] contains:
			// - Shipping address country (if "Ship to different address" is checked)
			// - Billing address country (if shipping same as billing)
			// 
			// WooCommerce automatically determines which address to use:
			// 1. If customer checks "Ship to different address" → uses shipping address
			// 2. If shipping same as billing → uses billing address
			// 3. This is handled automatically by WooCommerce, we don't need to check
			// 
			// Package structure:
			// $package['destination']['country']  - Country code (e.g., 'LK', 'US', 'GB')
			// $package['destination']['state']    - State/Province code
			// $package['destination']['postcode'] - Postal code
			// $package['destination']['city']     - City name
			//
			// Extract country code from package destination
			// This is the ISO 3166-1 alpha-2 country code (2 letters)
			// This will be either shipping or billing country (WooCommerce decides)
			$destination_country = isset( $package['destination']['country'] ) 
				? $package['destination']['country'] 
				: '';

			// Fallback: If no country is set (customer hasn't entered address yet)
			// Use store's base country as default
			// This ensures shipping calculation works even before address is entered
			if ( empty( $destination_country ) ) {
				// Get store's base country from WooCommerce settings
				// This is set in: WooCommerce > Settings > General > Store Address
				$destination_country = WC()->countries->get_base_country();
			}

			// At this point, $destination_country contains:
			// - Shipping address country (if "Ship to different address" checked)
			// - Billing address country (if shipping same as billing)
			// - Store's base country (if no address entered)
			// Examples: 'LK', 'US', 'GB', 'IN', etc.

			// Get configured rates from settings
			// These are set by admin in WooCommerce > Settings > Shipping
			$local_rate = isset( $this->settings['local_rate'] ) 
				? floatval( $this->settings['local_rate'] ) 
				: 500; // Default local rate

			$international_rate = isset( $this->settings['international_rate'] ) 
				? floatval( $this->settings['international_rate'] ) 
				: 2000; // Default international rate

			// Get list of local countries from settings
			// Admin can configure which countries use local rates
			$local_countries_string = isset( $this->settings['local_countries'] ) 
				? $this->settings['local_countries'] 
				: 'LK'; // Default to Sri Lanka

			// Convert country list string to array
			// Supports both comma-separated and newline-separated lists
			$local_countries = array_map( 
				'trim', 
				preg_split( '/[,\n]/', $local_countries_string )
			);

			// Remove empty values from array
			$local_countries = array_filter( $local_countries );

			// If no local countries configured, use store's base country
			if ( empty( $local_countries ) ) {
				$local_countries = array( WC()->countries->get_base_country() );
			}

			// Determine shipping rate based on customer's country
			// Check if destination country is in the local countries list
			$is_local = in_array( strtoupper( $destination_country ), array_map( 'strtoupper', $local_countries ), true );

			// Select appropriate rate
			// Local countries get local rate, all others get international rate
			$shipping_cost = $is_local ? $local_rate : $international_rate;

			// Get country name for display (optional, for better UX)
			$countries = WC()->countries->get_countries();
			$country_name = isset( $countries[ $destination_country ] ) 
				? $countries[ $destination_country ] 
				: $destination_country;

			// Create rate label with country information
			// This helps customers understand why they're seeing this rate
			$rate_label = $is_local 
				? sprintf( __( '%s (Local)', 'woocommerce' ), $this->title )
				: sprintf( __( '%s (%s)', 'woocommerce' ), $this->title, $country_name );

			// Add shipping rate to WooCommerce
			// This makes the rate available for customer selection at checkout
			$rate = array(
				'id'       => $this->id . '_' . $this->instance_id, // Unique rate ID
				'label'    => $rate_label,                           // Display label
				'cost'     => $shipping_cost,                         // Shipping cost
				'calc_tax' => 'per_order',                           // Tax calculation method
			);

			// Register the rate with WooCommerce
			// This adds it to the list of available shipping options
			$this->add_rate( $rate );
		}
	}
}

/**
 * Register Custom Shipping Method with WooCommerce
 *
 * This function registers our custom shipping method class so WooCommerce
 * can load and use it. The method will appear in shipping zones configuration.
 *
 * Hook: woocommerce_shipping_methods
 * This filter allows adding custom shipping methods to WooCommerce's
 * list of available shipping methods.
 *
 * @param array $methods Array of registered shipping method class names.
 * @return array Modified array with our custom shipping method added.
 */
function woocommerce_theme_register_custom_shipping_method( $methods ) {
	// Check if WooCommerce is active
	// This prevents errors if WooCommerce is deactivated
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $methods;
	}

	// Add our custom shipping method class to the methods array
	// The array key becomes the method ID, value is the class name
	$methods['theme_custom_shipping'] = 'WC_Theme_Custom_Shipping_Method';

	// Return modified methods array
	// WooCommerce will now recognize and load our shipping method
	return $methods;
}
// Hook into WooCommerce shipping methods filter
// Priority 10 ensures it runs at the standard time
// Accepts 1 parameter: methods array
add_filter( 'woocommerce_shipping_methods', 'woocommerce_theme_register_custom_shipping_method', 10, 1 );

/**
 * Checkout Blocks: Add Business Type and VAT Number to Checkout block
 *
 * This customizes the WooCommerce Checkout block (Store API–based checkout),
 * so the same fields appear when using the block instead of the classic shortcode.
 *
 * @param array $fields Existing Blocks checkout field schema.
 * @return array Modified field schema.
 */
function woocommerce_theme_blocks_checkout_fields( $fields ) {
	// Ensure billing address field group exists.
	if ( empty( $fields['billingAddress']['fields'] ) || ! is_array( $fields['billingAddress']['fields'] ) ) {
		return $fields;
	}

	$billing_fields = $fields['billingAddress']['fields'];

	// Business Type select field for Blocks checkout.
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

	// VAT Number text field for Blocks checkout.
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
 * Checkout Blocks: Save Business Type and VAT Number from Store API request.
 *
 * This runs when the Checkout block submits via the Store API.
 *
 * @param WC_Order              $order   Order being created/updated.
 * @param \WP_REST_Request|array $request Request data from the Store API.
 */
function woocommerce_theme_blocks_save_checkout_fields( $order, $request ) {
	// $request can be a WP_REST_Request; normalize to array of params.
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
 * Checkout Validation: Require VAT Number only for company billing with country
 *
 * Hooked into woocommerce_checkout_process so it runs during checkout validation.
 * Rules:
 * - If Business Type = company
 * - AND a billing country is selected
 * - THEN billing_vat_number must not be empty.
 */
function woocommerce_theme_validate_vat_number_conditionally() {
	// Read posted values safely.
	$business_type = isset( $_POST['billing_business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_business_type'] ) ) : '';
	$country       = isset( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : '';
	$vat_number    = isset( $_POST['billing_vat_number'] ) ? trim( (string) wp_unslash( $_POST['billing_vat_number'] ) ) : '';

	// Only apply rule when business type is "company" and a country is chosen.
	if ( 'company' === $business_type && ! empty( $country ) && '' === $vat_number ) {
		// Add a checkout error notice; WooCommerce will prevent order submission.
		wc_add_notice(
			__( 'Please enter your VAT number for company billing.', 'woocommerce' ),
			'error'
		);
	}
}
add_action( 'woocommerce_checkout_process', 'woocommerce_theme_validate_vat_number_conditionally' );

/**
 * Checkout: Show product image in \"Your order\" section
 *
 * Uses woocommerce_cart_item_name to prepend the product thumbnail
 * before the product title in the order review table on checkout.
 *
 * @param string        $item_name Default item name HTML.
 * @param array         $cart_item Cart item data.
 * @param string        $cart_item_key Cart item key.
 * @return string Modified item name HTML.
 */
function woocommerce_theme_checkout_order_review_product_image( $item_name, $cart_item, $cart_item_key ) {
	// Only modify output on the checkout page (Your order table).
	if ( ! is_checkout() ) {
		return $item_name;
	}

	// Get product object from cart item.
	if ( empty( $cart_item['data'] ) || ! is_a( $cart_item['data'], 'WC_Product' ) ) {
		return $item_name;
	}

	$product   = $cart_item['data'];
	$thumbnail = $product->get_image( 'woocommerce_thumbnail', array(
		'class' => 'order-review-product-thumbnail',
		'alt'   => $product->get_name(),
	) );

	// Wrap image + name for easier styling.
	$item_html  = '<div class="order-review-product">';
	$item_html .= '<div class="order-review-product__thumb">' . $thumbnail . '</div>';
	$item_html .= '<div class="order-review-product__title">' . $item_name . '</div>';
	$item_html .= '</div>';

	return $item_html;
}
add_filter( 'woocommerce_cart_item_name', 'woocommerce_theme_checkout_order_review_product_image', 10, 3 );

/**
 * Remove Sidebar Support
 *
 * This function removes sidebar/widget area support from the theme.
 * It unregisters default WordPress sidebars to prevent them from displaying.
 * Note: unregister_sidebar() will silently fail if sidebar doesn't exist,
 * so no error handling is needed. However, we check for existence to avoid
 * potential warnings in debug mode.
 */
function woocommerce_theme_remove_sidebars() {
	global $wp_registered_sidebars;

	// Check if sidebar exists before unregistering to avoid warnings
	if ( isset( $wp_registered_sidebars['sidebar-1'] ) ) {
		unregister_sidebar( 'sidebar-1' );
	}
	if ( isset( $wp_registered_sidebars['sidebar-2'] ) ) {
		unregister_sidebar( 'sidebar-2' );
	}

	// Unregister WooCommerce sidebars if they exist
	if ( function_exists( 'is_woocommerce' ) ) {
		if ( isset( $wp_registered_sidebars['shop-sidebar'] ) ) {
			unregister_sidebar( 'shop-sidebar' );
		}
		if ( isset( $wp_registered_sidebars['woocommerce-sidebar'] ) ) {
			unregister_sidebar( 'woocommerce-sidebar' );
		}
	}
}
// Hook into 'widgets_init' action with high priority (99) to remove sidebars
// High priority ensures this runs after sidebars are registered by other themes/plugins
add_action( 'widgets_init', 'woocommerce_theme_remove_sidebars', 99 );

/**
 * Remove WooCommerce Sidebar Hook
 *
 * This function removes the WooCommerce sidebar action hook
 * that displays the sidebar on shop and product pages.
 */
function woocommerce_theme_remove_woocommerce_sidebar() {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	
	// Remove WooCommerce sidebar hook
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}
// Hook into 'wp' action to ensure WooCommerce is loaded
add_action( 'wp', 'woocommerce_theme_remove_woocommerce_sidebar' );

/**
 * Disable Sidebar Display
 *
 * This function prevents any sidebar from being displayed
 * by returning false for is_active_sidebar checks.
 * Note: This is intentional and may interfere with plugins that rely on
 * sidebar detection. This ensures no sidebars are displayed anywhere in the theme.
 *
 * @param bool   $is_active_sidebar Whether the sidebar is active.
 * @param string $index             Sidebar index/ID.
 * @return bool Always returns false to disable all sidebars.
 */
function woocommerce_theme_disable_sidebar_display( $is_active_sidebar, $index ) {
	return false;
}
// Filter to disable all sidebars
// Priority 10 is standard, accepts 2 parameters (is_active_sidebar result and sidebar index)
add_filter( 'is_active_sidebar', 'woocommerce_theme_disable_sidebar_display', 10, 2 );

/**
 * Add Product Origin Dropdown Field to Product Edit Screen
 *
 * This function adds a custom dropdown field "Product Origin" to the WooCommerce
 * product edit screen in the General tab. The field allows selecting between
 * "Local" and "Imported" options.
 *
 * Hook: woocommerce_product_options_general_product_data
 * This hook fires when WooCommerce renders the general product data section
 * in the product edit screen.
 */
function woocommerce_add_product_origin_field() {
	// Check if WooCommerce is active before proceeding
	// This prevents errors if WooCommerce is deactivated
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Get the current post object (the product being edited)
	global $post;
	
	// Get the saved product origin value from post meta
	// Returns empty string if no value exists
	$product_origin = get_post_meta( $post->ID, '_product_origin', true );

	// Define the dropdown options
	// Array structure: value => label
	$options = array(
		''         => __( 'Select Origin', 'woocommerce' ), // Default empty option
		'local'    => __( 'Local', 'woocommerce' ),
		'imported' => __( 'Imported', 'woocommerce' ),
	);

	// Start the custom field wrapper
	// This ensures proper styling and layout within WooCommerce's admin interface
	echo '<div class="options_group">';

	// Output the field label
	// Using woocommerce_wp_select() helper function which creates a properly styled select field
	woocommerce_wp_select( array(
		'id'          => '_product_origin',           // Field ID (also used as meta key)
		'label'       => __( 'Product Origin', 'woocommerce' ), // Field label displayed to user
		'options'     => $options,                    // Array of options for the dropdown
		'value'       => $product_origin,             // Current selected value
		'desc_tip'    => true,                        // Show description as tooltip
		'description' => __( 'Select whether this product is locally sourced or imported.', 'woocommerce' ), // Help text
	) );

	// Close the field wrapper
	echo '</div>';
}
// Hook into WooCommerce product options general product data section
// Priority 10 ensures it displays at the standard time
add_action( 'woocommerce_product_options_general_product_data', 'woocommerce_add_product_origin_field', 10 );

/**
 * Save Product Origin Field Value
 *
 * This function saves the Product Origin dropdown value to the product's post meta
 * when the product is saved or updated. It includes proper nonce verification
 * and data sanitization for security.
 *
 * Hook: woocommerce_process_product_meta
 * This hook fires when WooCommerce processes and saves product meta data.
 *
 * @param int $post_id The ID of the product being saved.
 */
function woocommerce_save_product_origin_field( $post_id ) {
	// Check if WooCommerce is active before proceeding
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Verify this is a product post type
	// This ensures we only save meta for products, not other post types
	if ( get_post_type( $post_id ) !== 'product' ) {
		return;
	}

	// Verify nonce for security
	// This ensures the request came from the WordPress admin and prevents CSRF attacks
	// The nonce name 'woocommerce_save_data' is automatically added by WooCommerce
	if ( ! isset( $_POST['woocommerce_meta_nonce'] ) || 
		 ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
		return; // Exit if nonce verification fails
	}

	// Check if this is an autosave
	// Autosaves shouldn't update our custom field
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check user permissions
	// Ensure the current user has permission to edit products
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return; // Exit if user doesn't have permission
	}

	// Check if the product origin field was submitted
	// isset() checks if the field exists in $_POST array
	if ( ! isset( $_POST['_product_origin'] ) ) {
		return; // Exit if field wasn't submitted
	}

	// Sanitize the submitted value
	// sanitize_text_field() removes any potentially dangerous characters
	// This prevents XSS attacks and ensures data integrity
	$product_origin = sanitize_text_field( $_POST['_product_origin'] );

	// Validate the value against allowed options
	// This ensures only valid values ('local' or 'imported') are saved
	// Empty string is also allowed (for clearing the field)
	$allowed_values = array( '', 'local', 'imported' );
	if ( ! in_array( $product_origin, $allowed_values, true ) ) {
		// If invalid value, set to empty string as fallback
		$product_origin = '';
	}

	// Save the sanitized value to post meta
	// update_post_meta() automatically handles add/update logic
	// The meta key '_product_origin' (with underscore prefix) is hidden from custom fields meta box
	update_post_meta( $post_id, '_product_origin', $product_origin );
}
// Hook into WooCommerce product meta processing
// Priority 10 ensures it saves at the standard time
add_action( 'woocommerce_process_product_meta', 'woocommerce_save_product_origin_field', 10 );

/**
 * Display Product Origin Badge on Product Cards
 *
 * This function displays a badge showing "Local" or "Imported" on product cards
 * in the shop and category pages. The badge appears before the product title.
 *
 * Hook: woocommerce_before_shop_loop_item_title
 * This hook fires before the product title on shop and category archive pages.
 * It automatically runs on shop and category pages, so no additional page checks are needed.
 */
function woocommerce_display_product_origin_badge() {
	// Check if WooCommerce is active before proceeding
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Get the current product object in the loop
	// Try global $product first (available in WooCommerce loops)
	global $product;
	
	// If global $product is not available, get it from the current post
	// This ensures compatibility with different WooCommerce versions and contexts
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

	// Get the product ID
	$product_id = $product->get_id();

	// Get the product origin meta value
	// Returns empty string if no value exists
	$product_origin = get_post_meta( $product_id, '_product_origin', true );

	// Only display badge if product origin is set and valid
	// Empty string means no origin selected, so skip badge display
	if ( empty( $product_origin ) ) {
		return;
	}

	// Validate the value against allowed options for security
	// This ensures only valid values are displayed
	$allowed_values = array( 'local', 'imported' );
	if ( ! in_array( $product_origin, $allowed_values, true ) ) {
		return; // Exit if invalid value
	}

	// Prepare badge text based on origin value
	// Map internal values to display labels
	$badge_text = '';
	if ( 'local' === $product_origin ) {
		$badge_text = __( 'Local', 'woocommerce' );
	} elseif ( 'imported' === $product_origin ) {
		$badge_text = __( 'Imported', 'woocommerce' );
	}

	// Output the badge with minimal HTML
	// Wrapped in a div for proper positioning within the product card structure
	// esc_html() ensures safe output and prevents XSS attacks
	// esc_attr() sanitizes the class name
	printf(
		'<div class="product-origin-badge-wrapper"><span class="product-origin-badge product-origin-badge--%s">%s</span></div>',
		esc_attr( $product_origin ),  // CSS class modifier (e.g., product-origin-badge--local)
		esc_html( $badge_text )       // Badge text (Local or Imported)
	);
}
// Hook into WooCommerce before shop loop item title
// Priority 3 ensures it displays very early, before the product title
// This hook automatically runs on shop and category archive pages
// Badge is positioned absolutely over the product image via CSS
add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_display_product_origin_badge', 3 );

/**
 * Render Mini Cart HTML
 *
 * This function generates the HTML for the mini cart dropdown.
 * It's used both in the header template and in AJAX fragments.
 * This ensures consistent HTML structure for both initial page load and AJAX updates.
 *
 * @param bool $echo Whether to echo the output or return it. Default false (return).
 * @return string|void The mini cart HTML if $echo is false, void if $echo is true.
 */
function woocommerce_render_mini_cart( $echo = false ) {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '';
	}

	// Get the cart object
	// WC()->cart is the global WooCommerce cart instance
	$cart = WC()->cart;

	// Check if cart exists
	if ( ! $cart ) {
		return '';
	}

	// Get cart item count (even if cart is empty, to show the icon)
	// get_cart_contents_count() returns the total number of items in cart
	$cart_count = $cart->get_cart_contents_count();

	// Start output buffering to capture HTML
	// This allows us to return the HTML as a string
	ob_start();
	?>

	<?php
	// Check if cart has items
	if ( ! $cart->is_empty() ) :
		// Get cart total
		// get_cart_total() returns formatted cart total with currency symbol
		$cart_total = $cart->get_cart_total();
		
		// Get cart items
		// get_cart() returns an array of cart items with their data
		$cart_items = $cart->get_cart();
		?>
		<div class="mini-cart__dropdown" id="mini-cart-dropdown" role="region" aria-label="<?php esc_attr_e( 'Shopping Cart', 'woocommerce' ); ?>">
			<div class="mini-cart__header">
				<h3 class="mini-cart__title">
					<?php esc_html_e( 'Cart', 'woocommerce' ); ?>
					<span class="mini-cart__count" id="mini-cart-count-text" aria-label="<?php echo esc_attr( sprintf( _n( '%d item', '%d items', $cart_count, 'woocommerce' ), $cart_count ) ); ?>">
						(<?php echo esc_html( $cart_count ); ?>)
					</span>
				</h3>
			</div>

			<div class="mini-cart__items" id="mini-cart-items">
				<ul class="mini-cart__list" role="list" id="mini-cart-list">
					<?php
					// Loop through each cart item
					// Each $cart_item_key is a unique identifier for the item
					// Each $cart_item contains product data, quantity, and variations
					foreach ( $cart_items as $cart_item_key => $cart_item ) :
						// Get the product object from cart item
						// wc_get_product() retrieves the WC_Product object by product ID
						$product = wc_get_product( $cart_item['product_id'] );
						
						// Skip if product doesn't exist (e.g., deleted product)
						if ( ! $product ) {
							continue;
						}
						
						// Get product permalink
						// get_permalink() returns the URL to view the product
						$product_permalink = $product->get_permalink();
						
						// Get product name
						// get_name() returns the product title
						$product_name = $product->get_name();

						// Build remove-from-cart URL for this cart line item
						// wc_get_cart_remove_url() returns a nonce-protected URL WooCommerce recognizes.
						$remove_url = wc_get_cart_remove_url( $cart_item_key );
						
						// Get item quantity
						// $cart_item['quantity'] contains the quantity for this cart item
						$quantity = $cart_item['quantity'];
						
						// Get line subtotal (price for this item * quantity)
						// get_product_subtotal() returns the subtotal for this line item
						$line_subtotal = $cart->get_product_subtotal( $product, $cart_item['quantity'] );
						?>
						<li class="mini-cart__item" role="listitem">
							<div class="mini-cart__item-content">
								<a
									href="<?php echo esc_url( $remove_url ); ?>"
									class="remove mini-cart__item-remove"
									aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), $product_name ) ); ?>"
									title="<?php echo esc_attr__( 'Remove item', 'woocommerce' ); ?>"
								>
									<i class="fas fa-times" aria-hidden="true"></i>
									<span class="screen-reader-text">
										<?php
										echo esc_html(
											sprintf(
												__( 'Remove %s from cart', 'woocommerce' ),
												$product_name
											)
										);
										?>
									</span>
								</a>
								<?php
								// Display product thumbnail
								// get_image() returns the product image HTML
								// 'woocommerce_thumbnail' is the image size to use
								echo $product->get_image( 'woocommerce_thumbnail', array(
									'alt' => $product_name,
									'class' => 'mini-cart__item-image',
								) );
								?>
								
								<div class="mini-cart__item-details">
									<h4 class="mini-cart__item-title">
										<a href="<?php echo esc_url( $product_permalink ); ?>" class="mini-cart__item-link">
											<?php echo esc_html( $product_name ); ?>
										</a>
									</h4>
									
									<?php
									// Display product variation attributes if it's a variable product
									// wc_get_formatted_cart_item_data() formats variation attributes nicely
									// Returns formatted string like "Color: Red, Size: Large"
									$variation_data = wc_get_formatted_cart_item_data( $cart_item );
									if ( $variation_data ) {
										echo '<div class="mini-cart__item-variation">' . $variation_data . '</div>';
									}
									?>
									
									<div class="mini-cart__item-meta">
										<span class="mini-cart__item-quantity">
											<?php
											// Display quantity
											// printf() formats the string with the quantity number
											// esc_html() ensures safe output
											printf(
												esc_html__( 'Quantity: %d', 'woocommerce' ),
												$quantity
											);
											?>
										</span>
										<span class="mini-cart__item-price">
											<?php echo $line_subtotal; // Already escaped by WooCommerce ?>
										</span>
									</div>
								</div>
							</div>
						</li>
						<?php
					endforeach;
					?>
				</ul>
			</div>

			<div class="mini-cart__footer" id="mini-cart-footer">
				<div class="mini-cart__total">
					<strong class="mini-cart__total-label">
						<?php esc_html_e( 'Total:', 'woocommerce' ); ?>
					</strong>
					<span class="mini-cart__total-amount" id="mini-cart-total">
						<?php echo $cart_total; // Already escaped by WooCommerce ?>
					</span>
				</div>

				<div class="mini-cart__actions">
					<?php
					// Get cart page URL
					// wc_get_cart_url() returns the URL to the cart page
					$cart_url = wc_get_cart_url();
					
					// Get checkout page URL
					// wc_get_checkout_url() returns the URL to the checkout page
					$checkout_url = wc_get_checkout_url();
					?>
					<a href="<?php echo esc_url( $cart_url ); ?>" class="mini-cart__button mini-cart__button--view-cart">
						<?php esc_html_e( 'View Cart', 'woocommerce' ); ?>
					</a>
					<a href="<?php echo esc_url( $checkout_url ); ?>" class="mini-cart__button mini-cart__button--checkout">
						<?php esc_html_e( 'Checkout', 'woocommerce' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php
	else :
		// Display empty cart message in dropdown
		?>
		<div class="mini-cart__dropdown mini-cart__dropdown--empty" id="mini-cart-dropdown" role="region" aria-label="<?php esc_attr_e( 'Shopping Cart', 'woocommerce' ); ?>">
			<div class="mini-cart__empty-message" id="mini-cart-empty">
				<p><?php esc_html_e( 'Your cart is empty.', 'woocommerce' ); ?></p>
			</div>
		</div>
		<?php
	endif;

	// Get the buffered output
	$output = ob_get_clean();

	// Echo or return based on parameter
	if ( $echo ) {
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	return $output;
}

/**
 * AJAX Cart Fragments
 *
 * This function is hooked into woocommerce_add_to_cart_fragments filter.
 * When a product is added to cart via AJAX, WooCommerce sends a request
 * to get updated HTML fragments. This function returns the updated mini cart HTML
 * and cart count so JavaScript can update the page without reloading.
 *
 * Flow:
 * 1. User clicks "Add to Cart" button
 * 2. WooCommerce AJAX handler processes the request
 * 3. This filter is called to get updated fragments
 * 4. Returns array with updated HTML for cart count and cart dropdown
 * 5. JavaScript receives fragments and updates DOM
 *
 * @param array $fragments Array of fragments to update. Key is CSS selector, value is HTML.
 * @return array Updated fragments array with mini cart HTML and cart count.
 */
function woocommerce_ajax_mini_cart_fragments( $fragments ) {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return $fragments;
	}

	// Get the cart object
	$cart = WC()->cart;

	// Check if cart exists
	if ( ! $cart ) {
		return $fragments;
	}

	// Get cart item count
	// This will be used to update the cart count badge
	$cart_count = $cart->get_cart_contents_count();

	// Add/update the cart count fragment
	// The key '.mini-cart__trigger-count' is the CSS selector for the cart count element
	// WooCommerce will find this element and replace its innerHTML with the new value
	$fragments['.mini-cart__trigger-count'] = '<span class="mini-cart__trigger-count" aria-label="' . esc_attr( sprintf( _n( '%d item in cart', '%d items in cart', $cart_count, 'woocommerce' ), $cart_count ) ) . '">' . esc_html( $cart_count ) . '</span>';

	// Add/update the mini cart dropdown fragment
	// The key '#mini-cart-dropdown' is the ID selector for the cart dropdown
	// This replaces the entire cart dropdown with updated HTML
	$fragments['#mini-cart-dropdown'] = woocommerce_render_mini_cart( false );

	// Return the updated fragments array
	// WooCommerce will send this to JavaScript as JSON
	return $fragments;
}
// Hook into WooCommerce add to cart fragments filter
// This filter is called whenever AJAX add-to-cart is performed
// Priority 10 is standard, accepts 1 parameter (fragments array)
add_filter( 'woocommerce_add_to_cart_fragments', 'woocommerce_ajax_mini_cart_fragments', 10, 1 );

/**
 * Add Mini Cart Fragments to Cart Updates
 *
 * This function ensures that mini cart fragments are included when cart is updated
 * (items removed, quantities changed, etc.), not just when items are added.
 * WooCommerce uses different filters for different operations, so we need to hook into
 * multiple fragment filters to ensure updates work in all scenarios.
 *
 * @param array $fragments Array of fragments to update. Key is CSS selector, value is HTML.
 * @return array Updated fragments array with mini cart HTML and cart count.
 */
function woocommerce_ajax_mini_cart_fragments_cart_updated( $fragments ) {
	// Reuse the same fragment function for consistency
	// This ensures cart updates (removals, quantity changes) also update the mini cart
	return woocommerce_ajax_mini_cart_fragments( $fragments );
}
// Hook into WooCommerce cart update fragments filter
// This is called when cart is updated via AJAX (removals, quantity changes)
// Priority 10 is standard, accepts 1 parameter (fragments array)
add_filter( 'woocommerce_update_order_review_fragments', 'woocommerce_ajax_mini_cart_fragments_cart_updated', 10, 1 );

/**
 * Ensure Fragments Are Always Included in get_refreshed_fragments
 *
 * This function ensures our mini cart fragments are always included when
 * WooCommerce's get_refreshed_fragments endpoint is called. This endpoint
 * is used to refresh fragments after cart updates (removals, quantity changes).
 * By hooking into the filter that processes this endpoint, we ensure our
 * fragments are always available.
 *
 * Note: The woocommerce_add_to_cart_fragments filter is already hooked,
 * but we also ensure it works for the get_refreshed_fragments endpoint
 * which is called after cart removals.
 */
function woocommerce_ensure_fragments_on_refresh() {
	// The woocommerce_add_to_cart_fragments filter is already registered
	// and will be called by WooCommerce's get_refreshed_fragments endpoint
	// No additional action needed here - the filter handles it
}
// This is just a placeholder - the actual filter is already registered above
// WooCommerce's get_refreshed_fragments endpoint automatically calls
// woocommerce_add_to_cart_fragments filter, so our fragments will be included

/**
 * My Account: Ensure Order Status Labels Have Proper Markup and Classes
 *
 * This function wraps order status labels with proper HTML markup and CSS classes
 * so they can be styled with colors. WooCommerce uses <mark> elements with
 * classes like "order-status status-{slug}" for status labels.
 *
 * Hook: woocommerce_my_account_my_orders_column_order-status
 * This action hook is called for each order in the My Account orders table.
 * It passes the order object as the first (and only) parameter.
 *
 * The CSS in style.css will automatically apply colors based on these classes:
 * - status-pending (Yellow)
 * - status-processing (Blue)
 * - status-on-hold (Orange)
 * - status-completed (Green)
 * - status-cancelled (Red)
 * - status-refunded (Gray)
 * - status-failed (Dark Red)
 *
 * @param WC_Order $order Order object containing order data.
 */
function woocommerce_theme_wrap_order_status_with_markup( $order ) {
	// Check if WooCommerce is active and order is valid
	if ( ! class_exists( 'WooCommerce' ) || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	// Get order status slug (e.g., 'pending', 'processing', 'completed')
	$status_slug = $order->get_status();

	// Get human-readable status name (e.g., 'Pending payment', 'Processing')
	$status_name = wc_get_order_status_name( $status_slug );

	// Output wrapped status with <mark> element and proper classes
	// This matches WooCommerce's default template structure
	// Classes: order-status (base class) + status-{slug} (status-specific class)
	printf(
		'<mark class="order-status status-%s">%s</mark>',
		esc_attr( $status_slug ),  // Status slug for CSS targeting (e.g., 'pending')
		esc_html( $status_name )   // Human-readable status name (e.g., 'Pending payment')
	);
}
// Hook into WooCommerce My Account orders table status column
// Priority 10 ensures it runs at the standard time
// Accepts 1 parameter: order object (WC_Order)
// Note: This is an ACTION hook, not a filter, so we output directly
add_action( 'woocommerce_my_account_my_orders_column_order-status', 'woocommerce_theme_wrap_order_status_with_markup', 10, 1 );

/**
 * Add Download Order Button to Order Details Page
 *
 * This function adds a "Download Order" button on the order details page
 * in the My Account section. The button allows customers to download
 * their order information as an HTML file that can be saved or printed.
 *
 * Hook: woocommerce_order_details_after_order_table
 * This hook fires after the order details table on the order view page.
 *
 * @param WC_Order $order Order object containing order data.
 */
function woocommerce_theme_add_order_download_button( $order ) {
	// Check if WooCommerce is active and order is valid
	if ( ! class_exists( 'WooCommerce' ) || ! is_a( $order, 'WC_Order' ) ) {
		return;
	}

	// Get order ID
	$order_id = $order->get_id();

	// Build download URL with nonce for security
	// The nonce ensures the request is legitimate and prevents CSRF attacks
	$download_url = wp_nonce_url(
		add_query_arg(
			array(
				'download_order' => $order_id,
			),
			home_url( '/' )
		),
		'download_order_' . $order_id,
		'order_nonce'
	);

	// Output download button
	?>
	<div class="woocommerce-order-download-wrapper">
		<a href="<?php echo esc_url( $download_url ); ?>" class="button woocommerce-order-download-button">
			<i class="fas fa-download" aria-hidden="true"></i>
			<?php esc_html_e( 'Download Order', 'woocommerce' ); ?>
		</a>
	</div>
	<?php
}
// Hook into WooCommerce order details after order table
// Priority 10 ensures it displays at the standard time
// Accepts 1 parameter: order object (WC_Order)
add_action( 'woocommerce_order_details_after_order_table', 'woocommerce_theme_add_order_download_button', 10, 1 );

/**
 * Handle Order Download Request
 *
 * This function processes the order download request, validates security,
 * and generates a downloadable HTML file containing the order details.
 *
 * Hook: template_redirect
 * This hook fires before WordPress determines which template to load,
 * allowing us to intercept the download request and serve the file.
 */
function woocommerce_theme_handle_order_download() {
	// Check if this is a download request
	if ( ! isset( $_GET['download_order'] ) ) {
		return;
	}

	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	// Get order ID from request
	$order_id = absint( $_GET['download_order'] );

	// Verify nonce for security
	// This ensures the request came from a legitimate source
	if ( ! isset( $_GET['order_nonce'] ) || ! wp_verify_nonce( $_GET['order_nonce'], 'download_order_' . $order_id ) ) {
		wp_die( esc_html__( 'Security check failed. Please try again.', 'woocommerce' ), esc_html__( 'Download Error', 'woocommerce' ), array( 'response' => 403 ) );
	}

	// Get order object
	$order = wc_get_order( $order_id );

	// Check if order exists
	if ( ! $order ) {
		wp_die( esc_html__( 'Order not found.', 'woocommerce' ), esc_html__( 'Download Error', 'woocommerce' ), array( 'response' => 404 ) );
	}

	// Check if user is logged in
	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'You must be logged in to download orders.', 'woocommerce' ), esc_html__( 'Download Error', 'woocommerce' ), array( 'response' => 403 ) );
	}

	// Check if current user owns this order
	// This ensures users can only download their own orders
	$current_user_id = get_current_user_id();
	$order_user_id   = $order->get_user_id();

	// Allow download if:
	// 1. User owns the order, OR
	// 2. User is an administrator (for admin access)
	if ( $order_user_id !== $current_user_id && ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'You do not have permission to download this order.', 'woocommerce' ), esc_html__( 'Download Error', 'woocommerce' ), array( 'response' => 403 ) );
	}

	// Generate order download content
	woocommerce_theme_generate_order_download( $order );

	// Exit after sending file (prevent further execution)
	exit;
}
// Hook into template_redirect to intercept download requests
// Priority 10 ensures it runs before template loading
add_action( 'template_redirect', 'woocommerce_theme_handle_order_download', 10 );

/**
 * Generate Order Download File
 *
 * This function generates an HTML file containing all order details
 * that can be saved or printed by the customer.
 *
 * @param WC_Order $order Order object containing order data.
 */
function woocommerce_theme_generate_order_download( $order ) {
	// Get order data
	$order_id      = $order->get_id();
	$order_number  = $order->get_order_number();
	$order_date    = $order->get_date_created();
	$order_status  = wc_get_order_status_name( $order->get_status() );
	$order_total   = $order->get_formatted_order_total();

	// Get billing information
	$billing_first_name = $order->get_billing_first_name();
	$billing_last_name  = $order->get_billing_last_name();
	$billing_company    = $order->get_billing_company();
	$billing_address_1   = $order->get_billing_address_1();
	$billing_address_2   = $order->get_billing_address_2();
	$billing_city        = $order->get_billing_city();
	$billing_state       = $order->get_billing_state();
	$billing_postcode    = $order->get_billing_postcode();
	$billing_country     = $order->get_billing_country();
	$billing_email       = $order->get_billing_email();
	$billing_phone       = $order->get_billing_phone();

	// Get shipping information
	$shipping_first_name = $order->get_shipping_first_name();
	$shipping_last_name  = $order->get_shipping_last_name();
	$shipping_company    = $order->get_shipping_company();
	$shipping_address_1  = $order->get_shipping_address_1();
	$shipping_address_2   = $order->get_shipping_address_2();
	$shipping_city       = $order->get_shipping_city();
	$shipping_state      = $order->get_shipping_state();
	$shipping_postcode   = $order->get_shipping_postcode();
	$shipping_country    = $order->get_shipping_country();

	// Get custom fields
	$business_type = get_post_meta( $order_id, '_billing_business_type', true );
	$vat_number    = get_post_meta( $order_id, '_billing_vat_number', true );

	// Get order items
	$order_items = $order->get_items();

	// Get payment method
	$payment_method_title = $order->get_payment_method_title();

	// Get shipping method
	$shipping_methods = $order->get_shipping_methods();
	$shipping_method  = '';
	if ( ! empty( $shipping_methods ) ) {
		$shipping_method = reset( $shipping_methods );
		$shipping_method = $shipping_method->get_method_title();
	}

	// Format order date
	$formatted_date = $order_date ? $order_date->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) : '';

	// Get country names
	$countries = WC()->countries->get_countries();
	$billing_country_name  = isset( $countries[ $billing_country ] ) ? $countries[ $billing_country ] : $billing_country;
	$shipping_country_name = isset( $countries[ $shipping_country ] ) ? $countries[ $shipping_country ] : $shipping_country;

	// Set headers for file download
	$filename = 'order-' . $order_number . '-' . date( 'Y-m-d' ) . '.html';
	header( 'Content-Type: text/html; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Pragma: public' );

	// Start HTML output
	?>
	<!DOCTYPE html>
	<html lang="<?php echo esc_attr( get_locale() ); ?>">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?php echo esc_html( sprintf( __( 'Order #%s', 'woocommerce' ), $order_number ) ); ?></title>
		<style>
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
			}
			body {
				font-family: Arial, sans-serif;
				font-size: 12px;
				line-height: 1.6;
				color: #333;
				padding: 20px;
				background-color: #fff;
			}
			.order-header {
				border-bottom: 3px solid #0073aa;
				padding-bottom: 20px;
				margin-bottom: 30px;
			}
			.order-header h1 {
				color: #0073aa;
				font-size: 24px;
				margin-bottom: 10px;
			}
			.order-meta {
				display: flex;
				flex-wrap: wrap;
				gap: 20px;
				margin-top: 15px;
			}
			.order-meta-item {
				flex: 1;
				min-width: 200px;
			}
			.order-meta-label {
				font-weight: bold;
				color: #666;
				margin-bottom: 5px;
			}
			.order-meta-value {
				color: #333;
			}
			.section {
				margin-bottom: 30px;
				page-break-inside: avoid;
			}
			.section-title {
				background-color: #0073aa;
				color: #fff;
				padding: 10px 15px;
				font-size: 16px;
				font-weight: bold;
				margin-bottom: 15px;
			}
			.address-box {
				background-color: #f9f9f9;
				border: 1px solid #ddd;
				padding: 15px;
				margin-bottom: 15px;
			}
			.address-box strong {
				display: block;
				margin-bottom: 8px;
				color: #0073aa;
			}
			.order-items-table {
				width: 100%;
				border-collapse: collapse;
				margin-bottom: 20px;
			}
			.order-items-table th {
				background-color: #0073aa;
				color: #fff;
				padding: 10px;
				text-align: left;
				border: 1px solid #005a87;
			}
			.order-items-table td {
				padding: 10px;
				border: 1px solid #ddd;
			}
			.order-items-table tr:nth-child(even) {
				background-color: #f9f9f9;
			}
			.order-totals {
				width: 100%;
				border-collapse: collapse;
				margin-top: 20px;
			}
			.order-totals th,
			.order-totals td {
				padding: 8px 10px;
				text-align: right;
				border-bottom: 1px solid #ddd;
			}
			.order-totals th {
				text-align: left;
				font-weight: bold;
			}
			.order-totals .order-total-row {
				font-size: 16px;
				font-weight: bold;
				background-color: #f0f0f0;
			}
			.order-totals .order-total-row th,
			.order-totals .order-total-row td {
				padding: 12px 10px;
				border-top: 2px solid #0073aa;
			}
			.footer {
				margin-top: 40px;
				padding-top: 20px;
				border-top: 1px solid #ddd;
				text-align: center;
				color: #666;
				font-size: 11px;
			}
			@media print {
				body {
					padding: 10px;
				}
				.section {
					page-break-inside: avoid;
				}
			}
		</style>
	</head>
	<body>
		<div class="order-header">
			<h1><?php echo esc_html( sprintf( __( 'Order #%s', 'woocommerce' ), $order_number ) ); ?></h1>
			<div class="order-meta">
				<div class="order-meta-item">
					<div class="order-meta-label"><?php esc_html_e( 'Order Date', 'woocommerce' ); ?>:</div>
					<div class="order-meta-value"><?php echo esc_html( $formatted_date ); ?></div>
				</div>
				<div class="order-meta-item">
					<div class="order-meta-label"><?php esc_html_e( 'Order Status', 'woocommerce' ); ?>:</div>
					<div class="order-meta-value"><?php echo esc_html( $order_status ); ?></div>
				</div>
				<div class="order-meta-item">
					<div class="order-meta-label"><?php esc_html_e( 'Payment Method', 'woocommerce' ); ?>:</div>
					<div class="order-meta-value"><?php echo esc_html( $payment_method_title ); ?></div>
				</div>
			</div>
		</div>

		<div class="section">
			<div class="section-title"><?php esc_html_e( 'Billing Address', 'woocommerce' ); ?></div>
			<div class="address-box">
				<strong><?php echo esc_html( trim( $billing_first_name . ' ' . $billing_last_name ) ); ?></strong>
				<?php if ( $billing_company ) : ?>
					<div><?php echo esc_html( $billing_company ); ?></div>
				<?php endif; ?>
				<div><?php echo esc_html( $billing_address_1 ); ?></div>
				<?php if ( $billing_address_2 ) : ?>
					<div><?php echo esc_html( $billing_address_2 ); ?></div>
				<?php endif; ?>
				<div>
					<?php
					echo esc_html( $billing_city );
					if ( $billing_state ) {
						echo ', ' . esc_html( $billing_state );
					}
					if ( $billing_postcode ) {
						echo ' ' . esc_html( $billing_postcode );
					}
					?>
				</div>
				<div><?php echo esc_html( $billing_country_name ); ?></div>
				<?php if ( $billing_email ) : ?>
					<div style="margin-top: 8px;"><strong><?php esc_html_e( 'Email', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $billing_email ); ?></div>
				<?php endif; ?>
				<?php if ( $billing_phone ) : ?>
					<div><strong><?php esc_html_e( 'Phone', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $billing_phone ); ?></div>
				<?php endif; ?>
				<?php if ( $business_type ) : ?>
					<?php
					$business_type_map = array(
						'individual' => __( 'Individual', 'woocommerce' ),
						'company'    => __( 'Company', 'woocommerce' ),
					);
					$business_type_label = isset( $business_type_map[ $business_type ] ) ? $business_type_map[ $business_type ] : $business_type;
					?>
					<div style="margin-top: 8px;"><strong><?php esc_html_e( 'Business Type', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $business_type_label ); ?></div>
				<?php endif; ?>
				<?php if ( $vat_number ) : ?>
					<div><strong><?php esc_html_e( 'VAT Number', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $vat_number ); ?></div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $order->needs_shipping_address() && ! empty( $shipping_address_1 ) ) : ?>
			<div class="section">
				<div class="section-title"><?php esc_html_e( 'Shipping Address', 'woocommerce' ); ?></div>
				<div class="address-box">
					<strong><?php echo esc_html( trim( $shipping_first_name . ' ' . $shipping_last_name ) ); ?></strong>
					<?php if ( $shipping_company ) : ?>
						<div><?php echo esc_html( $shipping_company ); ?></div>
					<?php endif; ?>
					<div><?php echo esc_html( $shipping_address_1 ); ?></div>
					<?php if ( $shipping_address_2 ) : ?>
						<div><?php echo esc_html( $shipping_address_2 ); ?></div>
					<?php endif; ?>
					<div>
						<?php
						echo esc_html( $shipping_city );
						if ( $shipping_state ) {
							echo ', ' . esc_html( $shipping_state );
						}
						if ( $shipping_postcode ) {
							echo ' ' . esc_html( $shipping_postcode );
						}
						?>
					</div>
					<div><?php echo esc_html( $shipping_country_name ); ?></div>
					<?php if ( $shipping_method ) : ?>
						<div style="margin-top: 8px;"><strong><?php esc_html_e( 'Shipping Method', 'woocommerce' ); ?>:</strong> <?php echo esc_html( $shipping_method ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="section">
			<div class="section-title"><?php esc_html_e( 'Order Items', 'woocommerce' ); ?></div>
			<table class="order-items-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
						<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $order_items as $item_id => $item ) {
						$product = $item->get_product();
						$product_name = $item->get_name();
						$quantity = $item->get_quantity();
						$item_total = $order->get_formatted_line_subtotal( $item );
						?>
						<tr>
							<td>
								<?php echo esc_html( $product_name ); ?>
								<?php
								// Display variation attributes if available
								$meta_data = $item->get_formatted_meta_data( '_', true );
								if ( ! empty( $meta_data ) ) {
									echo '<div style="font-size: 11px; color: #666; margin-top: 5px;">';
									foreach ( $meta_data as $meta ) {
										echo esc_html( $meta->display_key . ': ' . $meta->display_value ) . '<br>';
									}
									echo '</div>';
								}
								?>
							</td>
							<td><?php echo esc_html( $quantity ); ?></td>
							<td><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item, true, true ) ); ?></td>
							<td><?php echo wp_kses_post( $item_total ); ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>

			<table class="order-totals">
				<?php
				// Display order totals
				foreach ( $order->get_order_item_totals() as $key => $total ) {
					?>
					<tr class="<?php echo ( 'order_total' === $key ) ? 'order-total-row' : ''; ?>">
						<th><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>

		<div class="footer">
			<p><?php echo esc_html( sprintf( __( 'This is a copy of order #%s from %s', 'woocommerce' ), $order_number, get_bloginfo( 'name' ) ) ); ?></p>
			<p><?php echo esc_html( sprintf( __( 'Generated on %s', 'woocommerce' ), current_time( 'Y-m-d H:i:s' ) ) ); ?></p>
		</div>
	</body>
	</html>
	<?php
}


