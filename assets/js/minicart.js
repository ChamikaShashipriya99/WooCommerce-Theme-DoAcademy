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
 */

(function($) {
	'use strict';

	/**
	 * Update Mini Cart with AJAX Fragments
	 *
	 * This function is called when WooCommerce sends updated cart fragments via AJAX.
	 * It updates the cart count badge and the cart dropdown HTML.
	 *
	 * @param {Object} fragments - Object containing updated HTML fragments
	 *                             Key is CSS selector, value is HTML string
	 */
	function updateMiniCart(fragments) {
		// Check if fragments object exists and has data
		if (!fragments || typeof fragments !== 'object') {
			return;
		}

		// Update cart count badge
		// The fragment key '.mini-cart__trigger-count' contains the updated count HTML
		// We replace the entire element with the new HTML
		if (fragments['.mini-cart__trigger-count']) {
			// Find the cart count element and replace it with updated HTML
			var $cartCount = $('.mini-cart__trigger-count');
			if ($cartCount.length) {
				// Replace the element with the new HTML from fragments
				$cartCount.replaceWith(fragments['.mini-cart__trigger-count']);
			}
		}

		// Update cart dropdown
		// The fragment key '#mini-cart-dropdown' contains the entire updated cart dropdown HTML
		// We replace the entire dropdown element with the new HTML
		if (fragments['#mini-cart-dropdown']) {
			// Find the cart dropdown element
			var $cartDropdown = $('#mini-cart-dropdown');
			if ($cartDropdown.length) {
				// Replace the dropdown with the new HTML from fragments
				$cartDropdown.replaceWith(fragments['#mini-cart-dropdown']);
			} else {
				// If dropdown doesn't exist (e.g., cart was empty), append it to the wrapper
				var $cartWrapper = $('.mini-cart-wrapper');
				if ($cartWrapper.length) {
					// Append the new dropdown after the trigger button
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
	 */
	function fetchCartFragments() {
		// Check if WooCommerce AJAX parameters are available
		// wc_add_to_cart_params is added by WooCommerce and contains AJAX endpoint URLs
		if (typeof wc_add_to_cart_params !== 'undefined') {
			// Use WooCommerce's built-in fragment refresh method
			// This makes an AJAX call to get refreshed fragments
			$.ajax({
				url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
				type: 'POST',
				data: {
					time: Date.now()
				},
				success: function(data) {
					// If fragments are returned, update the mini cart
					if (data && data.fragments) {
						updateMiniCart(data.fragments);
					}
				},
				error: function() {
					// Silently fail - cart will update on next page load
					console.log('Mini cart fragment refresh failed');
				}
			});
		} else if (typeof wc_cart_params !== 'undefined') {
			// Fallback: use cart params if add_to_cart_params aren't available
			$.ajax({
				url: wc_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
				type: 'POST',
				data: {
					time: Date.now()
				},
				success: function(data) {
					// If fragments are returned, update the mini cart
					if (data && data.fragments) {
						updateMiniCart(data.fragments);
					}
				},
				error: function() {
					// Silently fail - cart will update on next page load
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
	 */
	function initMiniCartAJAX() {
		// Listen for WooCommerce cart fragment update event
		// This event is triggered by WooCommerce after AJAX add-to-cart operations
		// The event passes the updated fragments as data
		$(document.body).on('updated_wc_div', function(event, fragments) {
			// Call update function with the fragments
			updateMiniCart(fragments);
		});

		// Also listen for the updated_cart_totals event
		// This is triggered when cart totals are updated (e.g., quantity changes, item removals)
		$(document.body).on('updated_cart_totals', function(event, fragments) {
			// Call update function with the fragments
			updateMiniCart(fragments);
		});

		// Listen for cart item removal events
		// When items are removed from cart, WooCommerce triggers 'removed_from_cart' event
		$(document.body).on('removed_from_cart', function(event, fragments, cartHash, $button) {
			// Fetch updated fragments after item removal
			// This ensures mini cart updates even if fragments aren't automatically provided
			setTimeout(function() {
				fetchCartFragments();
			}, 100); // Small delay to ensure cart is fully updated on server
		});

		// Listen for cart updates (quantity changes, removals via AJAX)
		// This handles cases where cart is updated via AJAX but fragments aren't sent
		$(document.body).on('wc_cart_button_updated', function() {
			// Fetch fragments when cart button is updated
			fetchCartFragments();
		});

		// Also listen for cart widget updates
		// Some cart operations trigger widget updates
		$(document.body).on('wc_fragments_refreshed', function(event, fragments) {
			if (fragments) {
				updateMiniCart(fragments);
			}
		});

		// Listen for cart emptied event
		// When cart is completely emptied, we need to update the mini cart
		$(document.body).on('wc_cart_emptied', function() {
			fetchCartFragments();
		});

		// Listen for remove button clicks in the mini cart
		// If remove buttons exist in the mini cart dropdown, handle their clicks
		$(document).on('click', '.mini-cart__item .remove', function(e) {
			e.preventDefault();
			// Let WooCommerce handle the removal, then fetch fragments
			var $button = $(this);
			var href = $button.attr('href');
			
			if (href) {
				// Extract cart item key from URL
				// WooCommerce remove URLs contain the cart item key
				$.ajax({
					url: href,
					type: 'GET',
					success: function() {
						// After successful removal, fetch updated fragments
						setTimeout(function() {
							fetchCartFragments();
						}, 300);
					}
				});
			}
		});

		// Also listen for any cart updates on the cart/checkout pages
		// When user is on cart page and removes items, ensure mini cart updates
		$(document).on('click', '.woocommerce-cart-form .remove, .cart .remove, a.remove', function(e) {
			// Store the current cart count before removal
			// This helps us detect when the cart has actually changed
			var currentCount = parseInt($('.mini-cart__trigger-count').text()) || 0;
			
			// Wait for WooCommerce to process the removal
			// Then fetch updated fragments
			setTimeout(function() {
				fetchCartFragments();
			}, 400);
			
			// Also try again after a longer delay in case the first attempt was too early
			setTimeout(function() {
				fetchCartFragments();
			}, 800);
		});

		// Listen for WooCommerce cart table updates
		// When cart is updated (including removals), WooCommerce updates the cart table
		// We listen for this and fetch fragments
		$(document.body).on('updated_wc_div', function(event, fragments) {
			// Check if fragments were provided
			if (fragments && typeof fragments === 'object' && Object.keys(fragments).length > 0) {
				updateMiniCart(fragments);
			} else {
				// If no fragments or empty fragments, fetch them manually
				setTimeout(function() {
					fetchCartFragments();
				}, 100);
			}
		});

		// Intercept WooCommerce AJAX cart updates
		// This catches all cart-related AJAX operations
		$(document).ajaxComplete(function(event, xhr, settings) {
			// Check if this is a cart removal or update AJAX request
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
				
				// Try to get fragments from response
				try {
					var response = xhr.responseJSON || {};
					if (response && response.fragments && Object.keys(response.fragments).length > 0) {
						// Fragments were provided, update mini cart
						updateMiniCart(response.fragments);
					} else {
						// No fragments in response, fetch them manually
						// This is especially important for remove_item operations
						setTimeout(function() {
							fetchCartFragments();
						}, 300);
					}
				} catch (e) {
					// If parsing fails, fetch fragments anyway
					setTimeout(function() {
						fetchCartFragments();
					}, 300);
				}
			}
		});

		// Additional: Watch for cart table changes
		// When the cart table updates (items removed), fetch fragments
		var cartObserver = new MutationObserver(function(mutations) {
			// Check if cart table content changed
			var cartTableChanged = false;
			mutations.forEach(function(mutation) {
				if (mutation.type === 'childList' && 
					(mutation.target.classList.contains('shop_table') || 
					 mutation.target.closest('.woocommerce-cart-form'))) {
					cartTableChanged = true;
				}
			});
			
			if (cartTableChanged) {
				// Cart table changed, fetch fragments
				setTimeout(function() {
					fetchCartFragments();
				}, 200);
			}
		});

		// Observe the cart form for changes
		var $cartForm = $('.woocommerce-cart-form, .cart');
		if ($cartForm.length) {
			cartObserver.observe($cartForm[0], {
				childList: true,
				subtree: true
			});
		}
	}

	// Initialize when DOM is ready
	// jQuery's document.ready ensures the DOM is fully loaded before running
	$(document).ready(function() {
		initMiniCartAJAX();
	});

})(jQuery);

