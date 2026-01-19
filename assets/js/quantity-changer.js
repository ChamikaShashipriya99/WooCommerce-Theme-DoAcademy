/**
 * Quantity Changer - Add Plus/Minus Buttons
 *
 * Adds modern plus/minus buttons to WooCommerce quantity inputs.
 * Creates a better UX for changing product quantities on product pages.
 *
 * Features:
 * - Creates minus and plus buttons dynamically
 * - Handles increment/decrement functionality
 * - Respects min/max quantity constraints
 * - Updates input value properly
 * - Maintains WooCommerce compatibility
 */

(function($) {
	'use strict';

	/**
	 * Quantity Changer Controller
	 */
	var QuantityChanger = {
		/**
		 * Initialize quantity changer functionality
		 */
		init: function() {
			this.createQuantityButtons();
		},

		/**
		 * Create plus/minus buttons for quantity inputs
		 */
		createQuantityButtons: function() {
			// Find all quantity inputs on product pages
			var $quantityInputs = $('.woocommerce div.product form.cart .quantity .qty');

			if ($quantityInputs.length === 0) {
				return;
			}

			$quantityInputs.each(function() {
				var $input = $(this);
				var $quantityWrapper = $input.closest('.quantity');

				// Skip if buttons already exist
				if ($quantityWrapper.find('.quantity-btn-minus, .quantity-btn-plus').length > 0) {
					return;
				}

				// Get min, max, and step values
				var min = parseFloat($input.attr('min')) || 1;
				var max = parseFloat($input.attr('max')) || '';
				var step = parseFloat($input.attr('step')) || 1;
				var currentValue = parseFloat($input.val()) || min;

				// Create minus button
				var $minusBtn = $('<button type="button" class="quantity-btn-minus minus">-</button>');
				
				// Create plus button
				var $plusBtn = $('<button type="button" class="quantity-btn-plus plus">+</button>');

				// Wrap input and buttons in a container if not already wrapped
				if (!$quantityWrapper.hasClass('quantity-wrapper-enhanced')) {
					$quantityWrapper.addClass('quantity-wrapper-enhanced');
					
					// Insert minus button before input
					$input.before($minusBtn);
					
					// Insert plus button after input
					$input.after($plusBtn);
				}

				// Update button states based on current value
				QuantityChanger.updateButtonStates($input, $minusBtn, $plusBtn, min, max);

				// Minus button click handler
				$minusBtn.on('click', function(e) {
					e.preventDefault();
					var currentVal = parseFloat($input.val()) || min;
					var newVal = currentVal - step;
					
					if (newVal >= min) {
						$input.val(newVal).trigger('change');
						QuantityChanger.updateButtonStates($input, $minusBtn, $plusBtn, min, max);
					}
				});

				// Plus button click handler
				$plusBtn.on('click', function(e) {
					e.preventDefault();
					var currentVal = parseFloat($input.val()) || min;
					var newVal = currentVal + step;
					
					if (max === '' || newVal <= max) {
						$input.val(newVal).trigger('change');
						QuantityChanger.updateButtonStates($input, $minusBtn, $plusBtn, min, max);
					}
				});

				// Update button states when input value changes manually
				$input.on('change input', function() {
					QuantityChanger.updateButtonStates($input, $minusBtn, $plusBtn, min, max);
				});
			});
		},

		/**
		 * Update button disabled states based on current value
		 */
		updateButtonStates: function($input, $minusBtn, $plusBtn, min, max) {
			var currentVal = parseFloat($input.val()) || min;

			// Disable minus button if at minimum
			if (currentVal <= min) {
				$minusBtn.prop('disabled', true);
			} else {
				$minusBtn.prop('disabled', false);
			}

			// Disable plus button if at maximum
			if (max !== '' && currentVal >= max) {
				$plusBtn.prop('disabled', true);
			} else {
				$plusBtn.prop('disabled', false);
			}
		}
	};

	/**
	 * Initialize when DOM is ready
	 */
	$(document).ready(function() {
		QuantityChanger.init();
	});

	/**
	 * Re-initialize after AJAX updates (for WooCommerce fragments)
	 */
	$(document.body).on('updated_wc_div', function() {
		QuantityChanger.init();
	});

})(jQuery);

