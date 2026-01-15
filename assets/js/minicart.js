/**
 * Mini Cart AJAX Updates
 *
 * This script handles AJAX-based updates to the mini cart dropdown.
 * When a product is added to cart via AJAX, WooCommerce sends updated HTML fragments.
 * This script receives those fragments and updates the DOM without page reload.
 *
 * Flow:
 * 1. User clicks "Add to Cart" button
 * 2. WooCommerce AJAX handler processes the request
 * 3. PHP filter (woocommerce_add_to_cart_fragments) returns updated HTML fragments
 * 4. WooCommerce triggers 'updated_wc_div' event with fragments
 * 5. This script listens for the event and updates the mini cart
 * 
 * Why AJAX updates are necessary:
 * - Provides instant feedback without page reload (better user experience)
 * - Maintains user's current page position and context
 * - Reduces server load by updating only specific page elements
 * - Allows seamless shopping experience across all pages
 */

/**
 * Immediately Invoked Function Expression (IIFE)
 * 
 * Wraps the entire script to:
 * - Avoid polluting global namespace
 * - Ensure jQuery is available before code executes
 * - Provide private scope for variables and functions
 * 
 * @param {Object} $ - jQuery library (passed as parameter)
 */
(function($) {
	'use strict'; // Enforces strict JavaScript mode for better error catching

	/**
	 * Update Mini Cart with AJAX Fragments
	 *
	 * This function is called when WooCommerce sends updated cart fragments via AJAX.
	 * It updates the cart count badge and the cart dropdown HTML without page reload.
	 * 
	 * How it works:
	 * - WooCommerce sends an object where keys are CSS selectors and values are HTML strings
	 * - This function finds the matching DOM elements and replaces them with new HTML
	 * - The replacement happens instantly, providing seamless user experience
	 *
	 * @param {Object} fragments - Object containing updated HTML fragments
	 *                             Key is CSS selector (e.g., '.mini-cart__trigger-count'),
	 *                             Value is HTML string to replace the element with
	 *                             Example: { '.mini-cart__trigger-count': '<span>5</span>' }
	 */
	function updateMiniCart(fragments) {
		/**
		 * Validate fragments object
		 * 
		 * This check prevents errors if fragments are undefined or malformed.
		 * Without this validation, attempting to access fragment properties would
		 * cause JavaScript errors and break the cart update functionality.
		 */
		if (!fragments || typeof fragments !== 'object') {
			return; // Exit early if fragments are invalid
		}

		/**
		 * Update Cart Count Badge
		 * 
		 * The cart count badge shows the number of items in the cart (e.g., "3").
		 * This badge is visible in the header next to the cart icon.
		 * 
		 * Why replace the entire element:
		 * - Ensures all attributes (aria-label, etc.) are updated correctly
		 * - Prevents partial updates that could cause display inconsistencies
		 * - Simplifies code by replacing rather than updating individual properties
		 * 
		 * CSS Selector: '.mini-cart__trigger-count'
		 * This selector targets the span element displaying the cart item count
		 */
		if (fragments['.mini-cart__trigger-count']) {
			// Find the cart count element using jQuery selector
			var $cartCount = $('.mini-cart__trigger-count');
			if ($cartCount.length) {
				/**
				 * Replace the entire element with updated HTML
				 * 
				 * replaceWith() removes the old element and inserts the new HTML.
				 * This is safer than innerHTML updates because it ensures proper
				 * DOM structure and event handler preservation.
				 */
				$cartCount.replaceWith(fragments['.mini-cart__trigger-count']);
			}
		}

		/**
		 * Update Cart Dropdown HTML
		 * 
		 * The cart dropdown contains the list of cart items, totals, and action buttons.
		 * This entire section needs to be updated when items are added/removed.
		 * 
		 * Why replace the entire dropdown:
		 * - Cart items list changes dynamically (items added/removed)
		 * - Cart totals need recalculation
		 * - Button states may change (e.g., "View Cart" vs empty cart message)
		 * - Ensures all cart data is synchronized with server state
		 * 
		 * CSS Selector: '#mini-cart-dropdown'
		 * This selector targets the main dropdown container by its ID
		 */
		if (fragments['#mini-cart-dropdown']) {
			// Find the cart dropdown element
			var $cartDropdown = $('#mini-cart-dropdown');
			if ($cartDropdown.length) {
				/**
				 * Replace existing dropdown with updated HTML
				 * 
				 * This updates the dropdown when it already exists on the page.
				 * Common scenario: Cart already has items, user adds another item.
				 */
				$cartDropdown.replaceWith(fragments['#mini-cart-dropdown']);
			} else {
				/**
				 * Append dropdown if it doesn't exist
				 * 
				 * This handles the case when cart was empty and now has items.
				 * The dropdown HTML doesn't exist initially, so we create it.
				 * 
				 * Why append after trigger button:
				 * - Maintains proper DOM structure (dropdown follows trigger)
				 * - Ensures CSS positioning works correctly
				 * - Keeps HTML semantic and accessible
				 */
				var $cartWrapper = $('.mini-cart-wrapper');
				if ($cartWrapper.length) {
					// Insert dropdown HTML after the trigger button
					$cartWrapper.find('.mini-cart__trigger').after(fragments['#mini-cart-dropdown']);
				}
			}
		}
	}

	/**
	 * Fetch Cart Fragments via AJAX
	 *
	 * This function manually requests updated cart fragments from the server.
	 * It's used when cart items are removed and WooCommerce doesn't automatically
	 * send fragments. This ensures the mini cart updates even when fragments
	 * aren't automatically provided.
	 * 
	 * Why manual fetching is necessary:
	 * - Some cart operations (like removals) don't always trigger automatic fragments
	 * - Provides fallback mechanism to ensure cart stays synchronized
	 * - Handles edge cases where WooCommerce events don't fire as expected
	 * - Ensures consistent cart state across all user interactions
	 * 
	 * How it works:
	 * 1. Makes AJAX POST request to WooCommerce fragment endpoint
	 * 2. Server returns updated cart HTML fragments
	 * 3. Calls updateMiniCart() to apply the fragments to DOM
	 */
	function fetchCartFragments() {
		/**
		 * Check for WooCommerce AJAX Parameters
		 * 
		 * wc_add_to_cart_params is a JavaScript object added by WooCommerce
		 * that contains AJAX endpoint URLs and configuration. It's only available
		 * when WooCommerce is active and on pages where AJAX cart is enabled.
		 * 
		 * Why check for this:
		 * - Prevents errors if WooCommerce is deactivated
		 * - Ensures AJAX endpoints are available before making requests
		 * - Provides fallback to alternative parameter object if needed
		 */
		if (typeof wc_add_to_cart_params !== 'undefined') {
			/**
			 * Use WooCommerce's Built-in Fragment Refresh Endpoint
			 * 
			 * WooCommerce provides a dedicated endpoint 'get_refreshed_fragments'
			 * specifically for retrieving updated cart fragments. This is the
			 * standard way to refresh cart data via AJAX.
			 * 
			 * URL construction:
			 * - wc_add_to_cart_params.wc_ajax_url contains base AJAX URL
			 * - Replace '%%endpoint%%' placeholder with 'get_refreshed_fragments'
			 * - Results in URL like: /wp-admin/admin-ajax.php?wc-ajax=get_refreshed_fragments
			 */
			$.ajax({
				url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
				type: 'POST', // POST request for security (prevents caching)
				data: {
					time: Date.now() // Timestamp prevents browser caching of AJAX response
				},
				success: function(data) {
					/**
					 * Handle Successful AJAX Response
					 * 
					 * The response contains a 'fragments' object with updated HTML.
					 * We pass this to updateMiniCart() to update the DOM.
					 * 
					 * Why check for fragments:
					 * - Response structure may vary in different scenarios
					 * - Ensures we only update when valid fragments are received
					 * - Prevents errors from malformed responses
					 */
					if (data && data.fragments) {
						updateMiniCart(data.fragments);
					}
				},
				error: function() {
					/**
				 * Handle AJAX Errors Gracefully
				 * 
				 * If the AJAX request fails, we log an error but don't break the page.
				 * The cart will update correctly on the next page load, so this is
				 * a non-critical failure. Silent failure prevents user confusion.
				 */
					console.log('Mini cart fragment refresh failed');
				}
			});
		} else if (typeof wc_cart_params !== 'undefined') {
			/**
			 * Fallback: Use Cart Parameters
			 * 
			 * If wc_add_to_cart_params isn't available, try wc_cart_params instead.
			 * This provides compatibility with different WooCommerce versions and
			 * configurations where parameter names may vary.
			 * 
			 * Why fallback is important:
			 * - Different WooCommerce versions use different parameter objects
			 * - Some themes/plugins may modify parameter availability
			 * - Ensures script works across various WooCommerce setups
			 */
			$.ajax({
				url: wc_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
				type: 'POST',
				data: {
					time: Date.now()
				},
				success: function(data) {
					if (data && data.fragments) {
						updateMiniCart(data.fragments);
					}
				},
				error: function() {
					console.log('Mini cart fragment refresh failed');
				}
			});
		}
	}

	/**
	 * Initialize Mini Cart AJAX Updates
	 *
	 * This function sets up event listeners for WooCommerce AJAX cart updates.
	 * It runs when the DOM is ready and listens for WooCommerce fragment update events.
	 * 
	 * Why multiple event listeners:
	 * - WooCommerce triggers different events for different cart operations
	 * - Some operations don't always trigger the same events consistently
	 * - Multiple listeners ensure cart updates in all scenarios
	 * - Provides redundancy for reliability
	 */
	function initMiniCartAJAX() {
		/**
		 * Listen for WooCommerce Cart Fragment Update Event
		 * 
		 * Event: 'updated_wc_div'
		 * When it fires: After AJAX add-to-cart operations complete
		 * What it provides: Updated HTML fragments object
		 * 
		 * Why use document.body:
		 * - Event bubbles up from cart elements to document body
		 * - Using body ensures we catch events from any cart element
		 * - More reliable than listening on specific elements that may not exist
		 * 
		 * Event parameters:
		 * - event: jQuery event object
		 * - fragments: Object containing updated HTML fragments
		 */
		$(document.body).on('updated_wc_div', function(event, fragments) {
			// Call update function with the fragments received from WooCommerce
			updateMiniCart(fragments);
		});

		/**
		 * Listen for Cart Totals Update Event
		 * 
		 * Event: 'updated_cart_totals'
		 * When it fires: When cart totals are recalculated (quantity changes, item removals)
		 * What it provides: Updated fragments with new totals
		 * 
		 * Why separate listener:
		 * - Totals updates may occur independently of item additions
		 * - Ensures totals stay synchronized when quantities change
		 * - Handles coupon applications and shipping calculations
		 */
		$(document.body).on('updated_cart_totals', function(event, fragments) {
			updateMiniCart(fragments);
		});

		/**
		 * Listen for Cart Item Removal Event
		 * 
		 * Event: 'removed_from_cart'
		 * When it fires: After an item is successfully removed from cart
		 * What it provides: Fragments (may be empty), cart hash, button element
		 * 
		 * Why manual fragment fetch:
		 * - Removal events don't always include fragments automatically
		 * - Need to explicitly request updated fragments to reflect removal
		 * - setTimeout ensures server has processed removal before fetching
		 * 
		 * Delay explanation:
		 * - 100ms delay allows server to complete removal operation
		 * - Prevents race conditions where fragments are fetched too early
		 * - Ensures cart state is fully synchronized before update
		 */
		$(document.body).on('removed_from_cart', function(event, fragments, cartHash, $button) {
			setTimeout(function() {
				fetchCartFragments();
			}, 100); // Small delay to ensure cart is fully updated on server
		});

		/**
		 * Listen for Cart Button Update Event
		 * 
		 * Event: 'wc_cart_button_updated'
		 * When it fires: When cart buttons (like "Update Cart") are clicked
		 * What it indicates: Cart has been modified and needs refresh
		 * 
		 * Why this listener:
		 * - Handles quantity updates and other cart modifications
		 * - Ensures mini cart reflects changes made on cart page
		 * - Provides fallback when other events don't fire
		 */
		$(document.body).on('wc_cart_button_updated', function() {
			fetchCartFragments();
		});

		/**
		 * Listen for Fragment Refresh Event
		 * 
		 * Event: 'wc_fragments_refreshed'
		 * When it fires: When WooCommerce explicitly refreshes fragments
		 * What it provides: Updated fragments object
		 * 
		 * Why this listener:
		 * - Some plugins/themes trigger this event directly
		 * - Provides compatibility with custom cart implementations
		 * - Ensures we catch all fragment update scenarios
		 */
		$(document.body).on('wc_fragments_refreshed', function(event, fragments) {
			if (fragments) {
				updateMiniCart(fragments);
			}
		});

		/**
		 * Listen for Cart Emptied Event
		 * 
		 * Event: 'wc_cart_emptied'
		 * When it fires: When cart is completely emptied (all items removed)
		 * What it indicates: Cart is now empty, needs to show empty state
		 * 
		 * Why manual fetch:
		 * - Empty cart events don't always include fragments
		 * - Need to fetch to show empty cart message in dropdown
		 * - Ensures UI reflects empty cart state correctly
		 */
		$(document.body).on('wc_cart_emptied', function() {
			fetchCartFragments();
		});

		/**
		 * Handle Remove Button Clicks in Mini Cart
		 * 
		 * This listener handles clicks on remove buttons within the mini cart dropdown.
		 * When a user clicks the "X" button to remove an item from the mini cart,
		 * we intercept the click, prevent default navigation, and handle removal via AJAX.
		 * 
		 * Why prevent default:
		 * - Default behavior would navigate to remove URL (causes page reload)
		 * - We want AJAX removal for seamless experience
		 * - Prevents page navigation and maintains user's current position
		 * 
		 * Selector: '.mini-cart__item .remove'
		 * Targets remove buttons specifically within mini cart items
		 */
		$(document).on('click', '.mini-cart__item .remove', function(e) {
			e.preventDefault(); // Prevent default link navigation
			
			// Get the button element and its href attribute
			var $button = $(this);
			var href = $button.attr('href'); // Contains remove URL with cart item key
			
			if (href) {
				/**
				 * Extract Cart Item Key from URL
				 * 
				 * WooCommerce remove URLs contain the cart item key as a parameter.
				 * Example: /cart/?remove_item=abc123&_wpnonce=xyz
				 * 
				 * Why AJAX removal:
				 * - Provides instant feedback without page reload
				 * - Maintains user's current page context
				 * - Updates mini cart immediately after removal
				 */
				$.ajax({
					url: href, // Use the remove URL from the button
					type: 'GET', // GET request for removal (WooCommerce standard)
					success: function() {
						/**
						 * After Successful Removal
						 * 
						 * After the server processes the removal, we fetch updated fragments
						 * to refresh the mini cart display. The delay ensures the server
						 * has completed the removal operation.
						 */
						setTimeout(function() {
							fetchCartFragments();
						}, 300); // Longer delay for removal to ensure server processing completes
					}
				});
			}
		});

		/**
		 * Handle Remove Button Clicks on Cart/Checkout Pages
		 * 
		 * This listener handles removal from the main cart page or checkout page.
		 * When users remove items from these pages, we ensure the mini cart also updates.
		 * 
		 * Why multiple selectors:
		 * - Different WooCommerce versions use different class names
		 * - Cart page and checkout page have different structures
		 * - Ensures we catch removals from all possible locations
		 * 
		 * Selectors:
		 * - '.woocommerce-cart-form .remove': Cart page remove buttons
		 * - '.cart .remove': Alternative cart page selector
		 * - 'a.remove': Generic remove link selector
		 */
		$(document).on('click', '.woocommerce-cart-form .remove, .cart .remove, a.remove', function(e) {
			/**
			 * Store Current Cart Count
			 * 
			 * This helps detect when cart has actually changed. However, in this
			 * implementation, we always fetch fragments regardless, ensuring
			 * the mini cart stays synchronized.
			 */
			var currentCount = parseInt($('.mini-cart__trigger-count').text()) || 0;
			
			/**
			 * Fetch Fragments After Removal
			 * 
			 * We use two timeouts with different delays to handle various scenarios:
			 * - First timeout (400ms): Handles standard removal operations
			 * - Second timeout (800ms): Handles slower server responses or complex removals
			 * 
			 * Why two timeouts:
			 * - Some removals take longer to process (e.g., with plugins)
			 * - Ensures update happens even if first attempt is too early
			 * - Provides redundancy for reliability
			 */
			setTimeout(function() {
				fetchCartFragments();
			}, 400);
			
			setTimeout(function() {
				fetchCartFragments();
			}, 800);
		});

		/**
		 * Enhanced WooCommerce Div Update Listener
		 * 
		 * This is a more robust version of the 'updated_wc_div' listener above.
		 * It checks if fragments were actually provided, and if not, manually fetches them.
		 * 
		 * Why this additional listener:
		 * - Some WooCommerce events fire but don't include fragments
		 * - Provides fallback mechanism to ensure updates always happen
		 * - Handles edge cases where automatic fragments fail
		 */
		$(document.body).on('updated_wc_div', function(event, fragments) {
			/**
			 * Check if Fragments Were Provided
			 * 
			 * Validates that fragments exist and contain data before updating.
			 * If fragments are missing or empty, we manually fetch them.
			 */
			if (fragments && typeof fragments === 'object' && Object.keys(fragments).length > 0) {
				updateMiniCart(fragments);
			} else {
				/**
				 * No Fragments Provided - Fetch Manually
				 * 
				 * If the event fired but didn't include fragments, we fetch them
				 * manually to ensure the mini cart still updates correctly.
				 */
				setTimeout(function() {
					fetchCartFragments();
				}, 100);
			}
		});

		/**
		 * Intercept All AJAX Requests
		 * 
		 * This listener catches ALL AJAX requests on the page and checks if they
		 * are cart-related. If so, it extracts fragments from the response and
		 * updates the mini cart.
		 * 
		 * Why intercept all AJAX:
		 * - Some cart operations use different AJAX endpoints
		 * - Plugins may use custom AJAX handlers for cart updates
		 * - Provides comprehensive coverage for all cart update scenarios
		 * 
		 * Event: 'ajaxComplete'
		 * When it fires: After ANY AJAX request completes on the page
		 * What it provides: Event object, xhr (XMLHttpRequest), settings (request config)
		 */
		$(document).ajaxComplete(function(event, xhr, settings) {
			/**
			 * Check if This is a Cart-Related AJAX Request
			 * 
			 * We examine the request URL to determine if it's a cart operation.
			 * Multiple URL patterns are checked to catch all possible cart endpoints.
			 * 
			 * URL patterns checked:
			 * - 'remove_item': Cart item removal
			 * - 'remove_from_cart': Alternative removal endpoint
			 * - 'remove_cart_item': Another removal variant
			 * - 'update_cart': Cart quantity updates
			 * - 'get_refreshed_fragments': Explicit fragment refresh
			 */
			var url = settings.url || '';
			if (url.indexOf('remove_item') !== -1 ||
				url.indexOf('wc-ajax=remove_item') !== -1 ||
				url.indexOf('remove_from_cart') !== -1 ||
				url.indexOf('wc-ajax=remove_from_cart') !== -1 ||
				url.indexOf('remove_cart_item') !== -1 ||
				url.indexOf('wc-ajax=remove_cart_item') !== -1 ||
				url.indexOf('update_cart') !== -1 ||
				url.indexOf('wc-ajax=update_cart') !== -1 ||
				url.indexOf('get_refreshed_fragments') !== -1) {
				
				/**
				 * Extract Fragments from AJAX Response
				 * 
				 * Try to get fragments from the AJAX response JSON. If fragments
				 * are present, use them. Otherwise, fetch fragments manually.
				 */
				try {
					var response = xhr.responseJSON || {};
					if (response && response.fragments && Object.keys(response.fragments).length > 0) {
						/**
						 * Fragments Found in Response
						 * 
						 * Use the fragments directly from the AJAX response.
						 * This is the most efficient approach as it avoids an extra request.
						 */
						updateMiniCart(response.fragments);
					} else {
						/**
						 * No Fragments in Response - Fetch Manually
						 * 
						 * Some cart operations don't include fragments in their response.
						 * In these cases, we make a separate request to get updated fragments.
						 * 
						 * Why delay:
						 * - Ensures the cart operation has completed on server
						 * - Prevents fetching fragments before cart state is updated
						 * - Common for remove_item operations that don't return fragments
						 */
						setTimeout(function() {
							fetchCartFragments();
						}, 300);
					}
				} catch (e) {
					/**
					 * Error Parsing Response - Fetch Manually
					 * 
					 * If parsing the response fails (e.g., not valid JSON), we still
					 * attempt to fetch fragments manually. This ensures the cart updates
					 * even if the response format is unexpected.
					 */
					setTimeout(function() {
						fetchCartFragments();
					}, 300);
				}
			}
		});

		/**
		 * Watch for Cart Table DOM Changes
		 * 
		 * This uses the MutationObserver API to watch for changes in the cart table.
		 * When items are removed from the cart table (DOM changes), we fetch updated fragments.
		 * 
		 * Why MutationObserver:
		 * - Detects DOM changes that don't trigger WooCommerce events
		 * - Catches removals handled by other scripts or plugins
		 * - Provides additional layer of cart synchronization
		 * 
		 * How it works:
		 * - Observes the cart form for child element changes
		 * - When changes detected, checks if cart table was modified
		 * - If modified, fetches updated fragments
		 */
		var cartObserver = new MutationObserver(function(mutations) {
			/**
			 * Check if Cart Table Content Changed
			 * 
			 * Loop through all mutations and check if any affect the cart table.
			 * We look for changes to elements with class 'shop_table' or within
			 * '.woocommerce-cart-form' container.
			 */
			var cartTableChanged = false;
			mutations.forEach(function(mutation) {
				/**
				 * Check Mutation Type and Target
				 * 
				 * childList mutations indicate elements were added/removed.
				 * We check if the mutation target is the cart table or within it.
				 */
				if (mutation.type === 'childList' && 
					(mutation.target.classList.contains('shop_table') || 
					 mutation.target.closest('.woocommerce-cart-form'))) {
					cartTableChanged = true;
				}
			});
			
			/**
			 * Fetch Fragments if Cart Table Changed
			 * 
			 * If we detected cart table changes, fetch updated fragments to
			 * synchronize the mini cart with the main cart display.
			 */
			if (cartTableChanged) {
				setTimeout(function() {
					fetchCartFragments();
				}, 200); // Delay ensures DOM changes are complete
			}
		});

		/**
		 * Start Observing Cart Form for Changes
		 * 
		 * Find the cart form element and start observing it for DOM mutations.
		 * The observer watches for child element additions/removals within the form.
		 * 
		 * Observer options:
		 * - childList: true - Watch for child elements being added/removed
		 * - subtree: true - Watch changes in all descendants, not just direct children
		 */
		var $cartForm = $('.woocommerce-cart-form, .cart');
		if ($cartForm.length) {
			cartObserver.observe($cartForm[0], {
				childList: true,
				subtree: true
			});
		}
	}

	/**
	 * Initialize When DOM is Ready
	 * 
	 * jQuery's $(document).ready() ensures the DOM is fully loaded before
	 * initializing event listeners. This prevents errors from trying to
	 * attach listeners to elements that don't exist yet.
	 * 
	 * Why DOM ready:
	 * - Cart elements may not exist when script first loads
	 * - Ensures all HTML is parsed before JavaScript execution
	 * - Prevents "element not found" errors
	 * 
	 * Execution timing: Runs once when page loads, after HTML is parsed
	 */
	$(document).ready(function() {
		initMiniCartAJAX();
	});

})(jQuery); // Pass jQuery as parameter to IIFE
