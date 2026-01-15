# WooCommerce Theme

**Version:** 1.0.0  
**Author:** Chamika Shashipriya  
**Author URI:** https://my-portfolio-html-css-js-sigma.vercel.app/  
**GitHub Repository:** https://github.com/ChamikaShashipriya99/WooCommerce-Theme-DoAcademy.git

---

## Table of Contents

1. [Theme Overview](#theme-overview)
2. [Installation Instructions](#installation-instructions)
3. [Theme Structure](#theme-structure)
4. [Features & Functionality](#features--functionality)
5. [Customization](#customization)
6. [WooCommerce Compatibility](#woocommerce-compatibility)
7. [Testing & Validation](#testing--validation)
8. [Credits & License](#credits--license)

---

## Theme Overview

### Description

**WooCommerce Theme** is a custom-built, WooCommerce-compatible WordPress theme designed for e-commerce websites. This theme emphasizes clean code architecture by using WordPress and WooCommerce hooks exclusively, avoiding template overrides and page builders. The theme provides a modern, responsive design with advanced e-commerce features including AJAX cart functionality, custom checkout fields, automatic discounts, and location-based shipping.

### Purpose

This theme was developed as an academic assignment to demonstrate:
- Deep understanding of WordPress theme development
- WooCommerce integration using hooks and filters
- AJAX implementation for enhanced user experience
- Custom functionality development without template overrides
- Responsive design principles
- Code organization and documentation best practices

### Main Features

- **AJAX Mini Cart**: Real-time cart updates without page reloads
- **Product Origin Badges**: Visual indicators for Local/Imported products
- **Custom Checkout Fields**: Business Type and VAT Number fields with conditional validation
- **Automatic Discounts**: 20% discount for orders exceeding LKR 20,000
- **Custom Shipping Method**: Location-based shipping rates (Local vs International)
- **Responsive Design**: Mobile-first approach supporting all device sizes
- **Admin Enhancements**: Custom product meta fields and order display improvements
- **Hook-Based Architecture**: No template overrides, uses WordPress/WooCommerce hooks exclusively

### Target Audience

- **E-commerce Store Owners**: Looking for a clean, customizable WooCommerce theme
- **Developers**: Seeking a reference implementation of hook-based WooCommerce integration
- **Students**: Learning WordPress and WooCommerce development best practices
- **Academics**: Reviewing modern WordPress theme development techniques

---

## Installation Instructions

### Prerequisites

- WordPress 5.2 or higher
- PHP 7.4 or higher
- WooCommerce plugin (latest version recommended)
- MySQL 5.6 or higher

### Step 1: Install WordPress

1. Download WordPress from [wordpress.org](https://wordpress.org/download/)
2. Upload WordPress files to your web server
3. Create a MySQL database for WordPress
4. Run the WordPress installation wizard
5. Complete the installation process

### Step 2: Install WooCommerce Plugin

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Search for "WooCommerce"
4. Click **Install Now** on the official WooCommerce plugin
5. Click **Activate** after installation completes
6. Follow the WooCommerce setup wizard:
   - Configure store address and currency
   - Set up payment methods (at least one for testing)
   - Configure shipping zones and methods
   - Create essential pages (Shop, Cart, Checkout, My Account)

### Step 3: Install Theme

#### Method 1: Via WordPress Dashboard (Recommended)

1. Log in to WordPress admin dashboard
2. Navigate to **Appearance → Themes**
3. Click **Add New**
4. Click **Upload Theme**
5. Click **Choose File** and select the theme ZIP file
6. Click **Install Now**
7. After installation, click **Activate**

#### Method 2: Via FTP/SFTP

1. Extract the theme ZIP file
2. Upload the theme folder to `/wp-content/themes/` directory
3. Log in to WordPress admin dashboard
4. Navigate to **Appearance → Themes**
5. Find "WooCommerce" theme and click **Activate**

### Step 4: Configure Theme

1. Navigate to **Appearance → Customize** (optional)
2. Configure site identity (logo, site title, tagline)
3. Set up navigation menus:
   - Go to **Appearance → Menus**
   - Create a menu and assign it to "Primary Menu" location
   - Add pages like Shop, Cart, My Account, etc.
4. Configure WooCommerce settings:
   - Go to **WooCommerce → Settings**
   - Set up shipping zones and methods
   - Configure payment gateways
   - Set up tax rates (if applicable)

### Step 5: Add Products

1. Navigate to **Products → Add New**
2. Fill in product details (name, description, price, images)
3. Set **Product Origin** (Local or Imported) in the General tab
4. Configure inventory, shipping, and other product settings
5. Click **Publish**

---

## Theme Structure

### Directory Structure

```
WooCommerce/
├── assets/
│   ├── css/
│   │   └── style.css          # Main stylesheet (all theme styles)
│   ├── js/
│   │   └── minicart.js       # AJAX mini cart functionality
│   └── images/
│       └── shop-poster.jpg   # Shop page hero banner image
├── Test Cases/
│   └── TESTING_CHECKOUT_VALIDATION.md  # Testing documentation
├── footer.php                 # Footer template
├── functions.php              # Theme functions and hooks
├── header.php                 # Header template
├── index.php                  # Main template (blog fallback)
├── screenshot.png             # Theme preview image
└── style.css                  # Theme header (for WordPress recognition)
```

### File Descriptions

#### Core Template Files

**`index.php`**
- Main template file (required by WordPress)
- Displays blog posts and pages when no specific template exists
- Uses The Loop to iterate through posts
- Serves as fallback template for all content types
- WooCommerce pages use their own templates, but this provides blog functionality

**`header.php`**
- Site header template
- Contains HTML document structure (`<html>`, `<head>`)
- Includes site branding (logo/title)
- Primary navigation menu
- WooCommerce mini cart trigger button and dropdown
- Calls `wp_head()` hook for scripts, styles, and meta tags

**`footer.php`**
- Site footer template
- Footer widgets area (About, Quick Links, Contact, Social Media)
- Copyright and site information
- Calls `wp_footer()` hook for footer scripts
- Closes HTML document structure

**`functions.php`**
- Main theme functions file (1737+ lines)
- Contains all theme functionality and WooCommerce integrations
- Registers theme support features
- Enqueues stylesheets and scripts
- Defines custom hooks and filters
- Contains all custom WooCommerce functionality

#### Asset Files

**`assets/css/style.css`**
- Main stylesheet (2000+ lines)
- Contains all theme CSS
- Organized into sections: Reset, Layout, Header, Footer, WooCommerce, Mini Cart, Responsive
- Mobile-first responsive design
- WooCommerce-specific styling for shop, product, cart, checkout pages

**`assets/js/minicart.js`**
- JavaScript for AJAX mini cart updates
- Listens for WooCommerce cart events
- Updates cart count and dropdown via AJAX fragments
- Handles cart item removals and quantity changes
- Uses jQuery and WooCommerce AJAX endpoints

**`assets/images/shop-poster.jpg`**
- Hero banner image for shop page
- Displayed at top of shop archive page
- Customizable via `woocommerce_theme_shop_poster_banner()` function

#### Configuration Files

**`style.css`**
- Theme header file (required by WordPress)
- Contains theme metadata (name, author, description)
- Used by WordPress to identify and display theme information
- Actual styles are in `assets/css/style.css`

**`screenshot.png`**
- Theme preview image
- Displayed in WordPress admin theme selection screen
- Recommended size: 1200×900 pixels

### Hook Usage Locations

#### In `functions.php`

**Theme Setup Hooks:**
- `after_setup_theme` → `woocommerce_theme_setup()` - Registers theme support features

**Asset Enqueuing:**
- `wp_enqueue_scripts` → `woocommerce_theme_enqueue_assets()` - Loads CSS and JS files

**WooCommerce Wrapper Hooks:**
- `woocommerce_before_main_content` → `woocommerce_theme_wrapper_start()` - Opens custom wrapper
- `woocommerce_after_main_content` → `woocommerce_theme_wrapper_end()` - Closes custom wrapper
- `wp` → `woocommerce_theme_remove_default_wrappers()` - Removes WooCommerce default wrappers

**Checkout Field Hooks:**
- `woocommerce_checkout_fields` → `woocommerce_theme_custom_checkout_fields()` - Adds custom fields
- `woocommerce_checkout_update_order_meta` → `woocommerce_theme_save_custom_checkout_fields()` - Saves field data
- `woocommerce_checkout_process` → `woocommerce_theme_validate_vat_number_conditionally()` - Validates fields

**Cart & Discount Hooks:**
- `woocommerce_cart_calculate_fees` → `woocommerce_theme_apply_automatic_discount()` - Applies automatic discount

**Shipping Hooks:**
- `woocommerce_shipping_methods` → `woocommerce_theme_register_custom_shipping_method()` - Registers custom shipping

**Product Meta Hooks:**
- `woocommerce_product_options_general_product_data` → `woocommerce_add_product_origin_field()` - Adds admin field
- `woocommerce_process_product_meta` → `woocommerce_save_product_origin_field()` - Saves admin field
- `woocommerce_before_shop_loop_item_title` → `woocommerce_display_product_origin_badge()` - Displays badge

**AJAX Fragment Hooks:**
- `woocommerce_add_to_cart_fragments` → `woocommerce_ajax_mini_cart_fragments()` - Returns cart HTML fragments
- `woocommerce_update_order_review_fragments` → `woocommerce_ajax_mini_cart_fragments_cart_updated()` - Updates fragments

**Admin Display Hooks:**
- `woocommerce_admin_order_data_after_billing_address` → `woocommerce_theme_admin_order_custom_fields()` - Shows custom fields in admin
- `woocommerce_email_order_meta_fields` → `woocommerce_theme_email_order_meta_fields()` - Adds fields to order emails
- `woocommerce_my_account_my_orders_column_order-status` → `woocommerce_theme_wrap_order_status_with_markup()` - Styles order status

**Checkout Display Hooks:**
- `woocommerce_cart_item_name` → `woocommerce_theme_checkout_order_review_product_image()` - Adds product images to checkout

#### In Template Files

**`header.php`:**
- `wp_head()` - Outputs head content (scripts, styles, meta tags)
- `wp_body_open()` - Allows content injection after `<body>` tag

**`footer.php`:**
- `wp_footer()` - Outputs footer scripts and content

---

## Features & Functionality

### 1. Product Origin Badge System

**Purpose:** Visually distinguish between locally sourced and imported products on the shop page.

**How It Works:**
- Admin can select "Local" or "Imported" in product edit screen (General tab)
- Badge displays on product cards in shop/category pages
- Badges use distinct color schemes:
  - **Local**: Green gradient badge
  - **Imported**: Blue gradient badge

**Implementation:**
- Custom meta field: `_product_origin`
- Admin field added via `woocommerce_product_options_general_product_data` hook
- Badge displayed via `woocommerce_before_shop_loop_item_title` hook
- Styled with CSS classes: `.product-origin-badge--local` and `.product-origin-badge--imported`

**Usage:**
1. Edit a product in WordPress admin
2. Scroll to "Product Origin" dropdown in General tab
3. Select "Local" or "Imported"
4. Save product
5. Badge appears on product card in shop page

[Insert screenshot: Product edit screen showing Product Origin field]

[Insert screenshot: Shop page showing products with Local/Imported badges]

---

### 2. AJAX Mini Cart

**Purpose:** Provide instant cart feedback without page reloads, improving user experience.

**How It Works:**
- Cart icon in header shows item count badge
- Hovering over cart icon reveals dropdown with cart items
- Cart updates automatically via AJAX when items are added/removed
- No page reload required for cart operations

**Features:**
- Real-time cart count updates
- Product thumbnails and details in dropdown
- Cart totals display
- Quick links to Cart and Checkout pages
- Remove items directly from mini cart
- Empty cart state handling

**Implementation:**
- JavaScript: `assets/js/minicart.js` handles AJAX updates
- PHP: `woocommerce_render_mini_cart()` function generates cart HTML
- Hooks: `woocommerce_add_to_cart_fragments` filter returns updated HTML
- CSS: `.mini-cart-wrapper` and related classes style the dropdown

**Technical Details:**
- Listens for WooCommerce events: `updated_wc_div`, `updated_cart_totals`, `removed_from_cart`
- Uses WooCommerce AJAX endpoints: `get_refreshed_fragments`
- Updates DOM elements: `.mini-cart__trigger-count` and `#mini-cart-dropdown`
- Handles edge cases: empty cart, item removal, quantity changes

[Insert screenshot: Mini cart dropdown showing cart items]

[Insert screenshot: Empty mini cart state]

---

### 3. Custom Checkout Fields

**Purpose:** Collect business information (Business Type and VAT Number) during checkout for B2B customers.

**Fields Added:**
1. **Business Type** (Dropdown)
   - Options: Individual, Company
   - Required: No
   - Position: After standard billing fields

2. **VAT Number** (Text Input)
   - Required: Conditionally (only if Business Type = Company)
   - Validation: Required when company is selected
   - Position: After Business Type field

**How It Works:**
- Fields appear in billing section of checkout form
- Business Type selection determines VAT Number requirement
- Data saved to order meta upon order completion
- Fields displayed in:
  - WordPress admin order edit screen
  - Order confirmation emails (admin and customer)
  - My Account order details

**Implementation:**
- Hook: `woocommerce_checkout_fields` - Adds fields to checkout form
- Hook: `woocommerce_checkout_update_order_meta` - Saves field data
- Hook: `woocommerce_checkout_process` - Validates VAT Number requirement
- Hook: `woocommerce_admin_order_data_after_billing_address` - Displays in admin
- Hook: `woocommerce_email_order_meta_fields` - Adds to emails

**Validation Logic:**
- If Business Type = "Company" AND billing country is selected → VAT Number is required
- Validation error prevents order completion until VAT Number is provided

[Insert screenshot: Checkout form showing Business Type and VAT Number fields]

[Insert screenshot: Admin order screen showing custom fields]

[Insert screenshot: Order email showing custom fields]

---

### 4. Automatic Discount System

**Purpose:** Automatically apply a 20% discount for bulk orders exceeding LKR 20,000.

**How It Works:**
- System monitors cart subtotal (excluding taxes and shipping)
- When subtotal exceeds LKR 20,000, discount is automatically applied
- Discount appears as "Bulk Order Discount (20%)" in cart totals
- Discount is calculated as: `Cart Subtotal × 20%`
- Applied as a negative fee (WooCommerce fee system)

**Example Calculation:**
- Cart Subtotal: LKR 25,000
- Discount (20%): LKR -5,000
- Final Subtotal: LKR 20,000

**Implementation:**
- Hook: `woocommerce_cart_calculate_fees`
- Function: `woocommerce_theme_apply_automatic_discount()`
- Checks cart subtotal against threshold (LKR 20,000)
- Prevents duplicate application by checking existing fees
- Discount is non-taxable

**Features:**
- Automatic application (no coupon code required)
- Real-time calculation (updates as cart changes)
- Clear display in cart totals section
- Works with other discounts/coupons (if configured)

[Insert screenshot: Cart page showing automatic discount applied]

[Insert screenshot: Checkout page showing discount in order review]

---

### 5. Custom Shipping Method

**Purpose:** Provide location-based shipping rates (different rates for local vs international destinations).

**How It Works:**
- Custom shipping method: "Custom Country Shipping"
- Two rate tiers:
  - **Local Rate**: For specified countries (default: LKR 500)
  - **International Rate**: For all other countries (default: LKR 2,000)
- Admin configurable rates and local country list
- Automatically detects customer's shipping country
- Applies appropriate rate based on destination

**Configuration:**
1. Go to **WooCommerce → Settings → Shipping**
2. Add shipping zone or edit existing zone
3. Click **Add shipping method**
4. Select **Custom Country Shipping**
5. Configure:
   - **Method Title**: Display name (e.g., "Standard Shipping")
   - **Local Rate**: Amount for local countries (e.g., 500)
   - **International Rate**: Amount for international (e.g., 2000)
   - **Local Countries**: Country codes, one per line (e.g., LK, IN, BD)

**Implementation:**
- Custom class: `WC_Theme_Custom_Shipping_Method` extends `WC_Shipping_Method`
- Hook: `woocommerce_shipping_methods` - Registers custom method
- Method: `calculate_shipping()` - Determines rate based on destination country
- Uses WooCommerce package data: `$package['destination']['country']`

**Technical Details:**
- Country detection from checkout shipping address
- Falls back to billing address if shipping same as billing
- Falls back to store base country if no address entered
- Supports multiple local countries (comma or newline separated)

[Insert screenshot: Shipping method configuration in WooCommerce settings]

[Insert screenshot: Checkout page showing shipping options]

---

### 6. Admin-Side Enhancements

**Product Meta Fields:**
- Product Origin dropdown in product edit screen
- Saves to post meta: `_product_origin`
- Displayed on frontend as badge

**Order Display Enhancements:**
- Custom checkout fields displayed in order edit screen
- Order status labels with color coding
- Product images in checkout order review

**Email Enhancements:**
- Custom checkout fields included in order emails
- Proper formatting and labeling

---

### Adjusting Mini Cart JavaScript

**Location:** `assets/js/minicart.js`

**Key Functions:**
- `updateMiniCart()` - Updates cart display with fragments
- `fetchCartFragments()` - Manually requests cart updates
- `initMiniCartAJAX()` - Sets up event listeners

**Example: Change Cart Update Delay**
```javascript
// Find setTimeout calls and adjust delay (in milliseconds)
setTimeout(function() {
    fetchCartFragments();
}, 300); // Change 300 to desired delay
```

**Example: Add Custom Event Listener**
```javascript
// In initMiniCartAJAX() function, add:
$(document.body).on('your_custom_event', function(event, data) {
    // Your custom code here
});
```

### Adding New Product Meta Options

**Location:** `functions.php`

**Step 1: Add Admin Field**
```php
// Hook: woocommerce_product_options_general_product_data
function your_custom_product_field() {
    woocommerce_wp_text_input(array(
        'id' => '_your_field_name',
        'label' => __('Your Field Label', 'woocommerce'),
        'placeholder' => __('Enter value', 'woocommerce'),
    ));
}
add_action('woocommerce_product_options_general_product_data', 'your_custom_product_field');
```

**Step 2: Save Field Value**
```php
// Hook: woocommerce_process_product_meta
function save_your_custom_field($post_id) {
    if (isset($_POST['_your_field_name'])) {
        update_post_meta($post_id, '_your_field_name', sanitize_text_field($_POST['_your_field_name']));
    }
}
add_action('woocommerce_process_product_meta', 'save_your_custom_field');
```

**Step 3: Display on Frontend**
```php
// Hook: woocommerce_before_shop_loop_item_title (or other appropriate hook)
function display_your_custom_field() {
    global $product;
    $value = get_post_meta($product->get_id(), '_your_field_name', true);
    if ($value) {
        echo '<div class="your-custom-class">' . esc_html($value) . '</div>';
    }
}
add_action('woocommerce_before_shop_loop_item_title', 'display_your_custom_field');
```

### Customizing Shop Hero Banner

**Location:** `functions.php` → `woocommerce_theme_shop_poster_banner()`

**Change Image:**
```php
// Find this line and change the image path:
$poster_image_url = get_template_directory_uri() . '/assets/images/your-new-image.jpg';
```

**Modify Content:**
```php
// Edit the title and subtitle text in the function
<h1 class="shop-hero-banner__title">
    <?php echo esc_html('Your Custom Title'); ?>
</h1>
```

---

## WooCommerce Compatibility

### Hook-Based Architecture

**Why No Template Overrides?**

This theme intentionally avoids template overrides to:
- **Maintain Compatibility**: Updates to WooCommerce won't break customizations
- **Follow Best Practices**: WordPress/WooCommerce recommend hooks over template overrides
- **Easier Maintenance**: Changes are centralized in `functions.php`
- **Plugin Compatibility**: Other plugins can still modify templates if needed
- **Code Organization**: All customizations in one place

**Template Override Alternative:**

Instead of copying `woocommerce/templates/` files, this theme:
- Removes default WooCommerce wrappers via `remove_action()`
- Adds custom wrappers via `add_action()` on same hooks
- Modifies output using filters (e.g., `woocommerce_checkout_fields`)
- Extends functionality via action hooks (e.g., `woocommerce_before_shop_loop_item_title`)

### Key WooCommerce Hooks Used

**Content Wrappers:**
- `woocommerce_before_main_content` - Before shop/product content
- `woocommerce_after_main_content` - After shop/product content

**Checkout:**
- `woocommerce_checkout_fields` - Modify checkout form fields
- `woocommerce_checkout_update_order_meta` - Save custom field data
- `woocommerce_checkout_process` - Validate checkout data
- `woocommerce_cart_item_name` - Modify cart item display

**Cart:**
- `woocommerce_cart_calculate_fees` - Add fees/discounts
- `woocommerce_add_to_cart_fragments` - Return AJAX fragments

**Products:**
- `woocommerce_product_options_general_product_data` - Add admin fields
- `woocommerce_process_product_meta` - Save product meta
- `woocommerce_before_shop_loop_item_title` - Display content before product title

**Shipping:**
- `woocommerce_shipping_methods` - Register custom shipping methods

**Admin:**
- `woocommerce_admin_order_data_after_billing_address` - Display custom fields
- `woocommerce_email_order_meta_fields` - Add fields to emails
- `woocommerce_my_account_my_orders_column_order-status` - Style order status

### AJAX Handling

**How AJAX Works in This Theme:**

1. **User Action**: User adds product to cart or updates cart
2. **WooCommerce AJAX**: WooCommerce processes request via AJAX endpoint
3. **Fragment Generation**: PHP filter `woocommerce_add_to_cart_fragments` generates updated HTML
4. **JavaScript Update**: `minicart.js` receives fragments and updates DOM
5. **Visual Update**: Cart count and dropdown update without page reload

**AJAX Endpoints Used:**
- `get_refreshed_fragments` - Get updated cart HTML
- `add_to_cart` - Add product to cart (WooCommerce default)
- `remove_item` - Remove item from cart (WooCommerce default)
- `update_cart` - Update cart quantities (WooCommerce default)

**Fragment Structure:**
```javascript
{
    '.mini-cart__trigger-count': '<span>5</span>',
    '#mini-cart-dropdown': '<div>...cart HTML...</div>'
}
```

**Event Listeners:**
- `updated_wc_div` - Cart updated via AJAX
- `updated_cart_totals` - Cart totals recalculated
- `removed_from_cart` - Item removed from cart
- `wc_cart_button_updated` - Cart button clicked
- `wc_fragments_refreshed` - Fragments explicitly refreshed

---

## Testing & Validation

### Fresh Install Testing Checklist

#### 1. WordPress Installation
- [ ] WordPress installed successfully
- [ ] Database connection working
- [ ] Admin login functional
- [ ] Permalinks configured (Settings → Permalinks → Post name)

#### 2. WooCommerce Setup
- [ ] WooCommerce plugin installed and activated
- [ ] WooCommerce setup wizard completed
- [ ] Store address configured
- [ ] Currency set (LKR recommended for this theme)
- [ ] Payment method enabled (at least one for testing)
- [ ] Shipping zone created with at least one method
- [ ] Shop, Cart, Checkout, My Account pages created

#### 3. Theme Installation
- [ ] Theme uploaded and activated
- [ ] No PHP errors in debug log
- [ ] Site displays correctly
- [ ] Header and footer visible
- [ ] Navigation menu displays (if configured)

#### 4. Product Setup
- [ ] At least 3 test products created
- [ ] Products have images
- [ ] Products have prices
- [ ] Product Origin set (some Local, some Imported)
- [ ] Products visible on shop page

#### 5. Feature Testing

**Product Origin Badges:**
- [ ] Badges display on shop page
- [ ] Local products show green badge
- [ ] Imported products show blue badge
- [ ] Badges positioned correctly on product cards

**AJAX Mini Cart:**
- [ ] Cart icon visible in header
- [ ] Cart count displays (shows 0 when empty)
- [ ] Hovering over cart icon shows dropdown
- [ ] Adding product updates cart count instantly
- [ ] Cart dropdown shows added products
- [ ] Product images display in cart dropdown
- [ ] Cart totals calculate correctly
- [ ] Remove button works in mini cart
- [ ] Empty cart message displays when cart is empty

**Custom Checkout Fields:**
- [ ] Business Type field appears in checkout
- [ ] VAT Number field appears in checkout
- [ ] Selecting "Company" makes VAT Number required
- [ ] Validation error shows if Company selected without VAT Number
- [ ] Fields save to order meta
- [ ] Fields display in admin order screen
- [ ] Fields appear in order confirmation emails

**Automatic Discount:**
- [ ] Cart with subtotal < LKR 20,000 shows no discount
- [ ] Cart with subtotal > LKR 20,000 shows 20% discount
- [ ] Discount calculates correctly (subtotal × 20%)
- [ ] Discount appears in cart totals
- [ ] Discount appears in checkout order review
- [ ] Discount applies to order total

**Custom Shipping:**
- [ ] Custom Country Shipping method available in shipping zones
- [ ] Method configurable (rates, local countries)
- [ ] Local address shows local rate
- [ ] International address shows international rate
- [ ] Shipping cost displays correctly in checkout

**Responsive Design:**
- [ ] Mobile view (0-767px) displays correctly
- [ ] Tablet view (768-1199px) displays correctly
- [ ] Desktop view (1200px+) displays correctly
- [ ] Product grid adapts to screen size
- [ ] Navigation menu responsive
- [ ] Mini cart dropdown responsive
- [ ] Footer responsive

#### 6. Order Flow Testing
- [ ] Add product to cart
- [ ] View cart page
- [ ] Proceed to checkout
- [ ] Fill checkout form (including custom fields)
- [ ] Select shipping method
- [ ] Place order
- [ ] Order confirmation page displays
- [ ] Order email received
- [ ] Order visible in admin
- [ ] Order visible in My Account

#### 7. Admin Testing
- [ ] Product Origin field visible in product edit screen
- [ ] Product Origin saves correctly
- [ ] Custom checkout fields visible in order edit screen
- [ ] Order status labels display with colors
- [ ] Order emails include custom fields

### Debugging Tips

**Enable WordPress Debug Mode:**
Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Check Browser Console:**
- Open browser Developer Tools (F12)
- Check Console tab for JavaScript errors
- Check Network tab for failed AJAX requests

**Check WooCommerce Logs:**
- Go to **WooCommerce → Status → Logs**
- Look for errors related to cart, checkout, or shipping

**Common Issues:**

1. **Mini Cart Not Updating:**
   - Check if jQuery is loaded
   - Verify `minicart.js` is enqueued
   - Check browser console for errors
   - Verify WooCommerce AJAX endpoints are accessible

2. **Custom Fields Not Saving:**
   - Check if fields are in correct hook
   - Verify field names match in add/save functions
   - Check WordPress debug log for errors

3. **Discount Not Applying:**
   - Verify cart subtotal exceeds threshold
   - Check if discount already applied (prevents duplicates)
   - Verify hook priority is correct

4. **Shipping Not Showing:**
   - Check shipping zone configuration
   - Verify customer address matches zone
   - Check shipping method is enabled

---

### Author

**Chamika Shashipriya**
- Portfolio: https://my-portfolio-html-css-js-sigma.vercel.app/
- GitHub: https://github.com/ChamikaShashipriya99/WooCommerce-Theme-DoAcademy.git

### Dependencies

**WordPress Core:**
- WordPress 5.2+ (uses `wp_body_open()` hook)

**WooCommerce Plugin:**
- WooCommerce 5.0+ (latest version recommended)
- Required for all e-commerce functionality

**External Libraries:**
- **Font Awesome 5.15.4** (CDN)
  - Used for cart icon and other UI icons
  - Loaded from: `cdnjs.cloudflare.com`
  - License: Font Awesome Free License (Icons: CC BY 4.0, Fonts: SIL OFL 1.1)

**jQuery:**
- Included with WordPress (no separate installation needed)
- Used by `minicart.js` for DOM manipulation and AJAX


For the most up-to-date information, please visit the GitHub repository:  
https://github.com/ChamikaShashipriya99/WooCommerce-Theme-DoAcademy.git

---

Made By Chamika Shashipriya Under DoAcadamy Module 3 Assignment WooCommerce of Full-Stack Web Developer Industrial Training Program