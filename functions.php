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
 * Properly loads CSS and JavaScript files.
 * This is the WordPress way to include assets (not using <link> or <script> tags directly).
 */
function woocommerce_theme_enqueue_assets() {
	// Get the theme version from style.css (for cache busting)
	$theme_version = wp_get_theme()->get( 'Version' );

	// Enqueue Font Awesome 5.15.4
	// This loads Font Awesome icons library from CDN
	// Used for cart icon and other icons throughout the theme
	wp_enqueue_style(
		'font-awesome',
		'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
		array(), // No dependencies
		'5.15.4' // Version number
	);

	// Prepare dependencies array
	// Add WooCommerce stylesheet as dependency if WooCommerce is active
	$dependencies = array( 'font-awesome' ); // Theme style depends on Font Awesome
	if ( class_exists( 'WooCommerce' ) ) {
		$dependencies[] = 'woocommerce-general';
	}

	// Enqueue the main stylesheet
	// This loads assets/css/style.css with proper dependency handling
	wp_enqueue_style(
		'woocommerce-theme-style',                    // Handle (unique identifier)
		get_template_directory_uri() . '/assets/css/style.css', // Path to stylesheet
		$dependencies,                                // Dependencies (Font Awesome and WooCommerce if active)
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

/**
 * Remove WooCommerce Default Wrappers
 *
 * WooCommerce adds default content wrappers that we need to remove
 * so we can add our custom theme wrappers instead.
 * We check if WooCommerce is active before removing hooks.
 */
function woocommerce_theme_remove_default_wrappers() {
	// Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	/**
	 * Remove WooCommerce default opening wrapper
	 * Priority 10 is the default, we need to match it exactly to remove it.
	 * Priority determines the order in which hooks are executed.
	 */
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );

	/**
	 * Remove WooCommerce default closing wrapper
	 * Priority 10 is the default, we need to match it exactly to remove it.
	 * Priority determines the order in which hooks are executed.
	 */
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
}
// Hook into 'wp' action (runs after WordPress is fully loaded)
// This ensures WooCommerce is loaded before we try to remove its hooks
add_action( 'wp', 'woocommerce_theme_remove_default_wrappers' );

/**
 * Add Custom Theme Opening Wrapper
 *
 * This adds our custom opening wrapper before WooCommerce main content.
 * This wrapper matches our theme's HTML structure from header.php and index.php.
 */
function woocommerce_theme_wrapper_start() {
	// Only output wrapper if WooCommerce is active
	// This prevents wrapper from being output when WooCommerce is not installed
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	/**
	 * Output opening wrapper div that matches our theme structure
	 * This ensures WooCommerce pages (shop, product, cart, checkout) 
	 * have consistent layout with the rest of the theme
	 */
	echo '<div id="primary" class="site-main woocommerce-page">';
}
// Hook into 'woocommerce_before_main_content' action
// Priority 10 ensures it runs at the standard time (same as default WooCommerce hooks)
add_action( 'woocommerce_before_main_content', 'woocommerce_theme_wrapper_start', 10 );

/**
 * Add Custom Theme Closing Wrapper
 *
 * This adds our custom closing wrapper after WooCommerce main content.
 * This ensures proper HTML structure closure.
 */
function woocommerce_theme_wrapper_end() {
	/**
	 * Output closing wrapper div
	 * This closes the wrapper opened in woocommerce_theme_wrapper_start()
	 */
	echo '</div><!-- #primary -->';
}
// Hook into 'woocommerce_after_main_content' action
// Priority 10 ensures it runs at the standard time (same as default WooCommerce hooks)
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

