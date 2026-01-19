/**
 * Transform variation size <select> into clickable label buttons
 * without removing the original select (kept for WooCommerce JS + accessibility).
 *
 * Works on single product pages for attributes named "pa_size".
 */
(function ($) {
	'use strict';

	function escapeAttr(val) {
		return String(val).replace(/"/g, '\\"');
	}

	function initSizeLabelGroup($select) {
		if (!$select.length) {
			return;
		}

		// Avoid double‑initialization
		if ($select.data('size-labels-initialized')) {
			return;
		}
		$select.data('size-labels-initialized', true);

		var selectName = $select.attr('name') || '';

		// Only apply to size attribute selects (attribute_pa_size / variations[attribute_pa_size], etc.)
		if (selectName.indexOf('attribute_pa_size') === -1 && selectName.indexOf('attribute_size') === -1) {
			return;
		}

		// Create wrapper
		var $wrapper = $('<div/>', {
			class: 'wc-size-labels',
			role: 'radiogroup',
			'aria-label': $select.closest('tr').find('label').text() || 'Select size'
		});

		// Build label buttons from options
		$select.find('option').each(function () {
			var $option = $(this);
			var value = $option.val();
			var text = $.trim($option.text());

			// Skip placeholder / empty option
			if (!value) {
				return;
			}

			var $btn = $('<button/>', {
				type: 'button',
				class: 'wc-size-labels__item',
				'data-value': value,
				role: 'radio',
				'aria-checked': 'false'
			}).text(text);

			// Disabled state from option
			if ($option.is(':disabled')) {
				$btn.addClass('is-disabled').attr('aria-disabled', 'true');
			}

			// Pre‑selected state
			if ($option.is(':selected')) {
				$btn.addClass('is-selected').attr('aria-checked', 'true');
			}

			$wrapper.append($btn);
		});

		// If we have no actual options, bail
		if (!$wrapper.children().length) {
			return;
		}

		// Visually hide the select but keep it in the DOM
		$select.addClass('wc-variation-select--hidden');

		// Insert wrapper after the select
		$select.after($wrapper);

		// Click handling
		$wrapper.on('click', '.wc-size-labels__item', function (e) {
			e.preventDefault();

			var $item = $(this);
			if ($item.hasClass('is-disabled')) {
				return;
			}

			var value = $item.data('value');

			// Update select value & trigger WooCommerce variation JS
			$select.val(value).trigger('change');

			// Visual selection state
			$wrapper.find('.wc-size-labels__item')
				.removeClass('is-selected')
				.attr('aria-checked', 'false');

			$item.addClass('is-selected').attr('aria-checked', 'true');
		});

		// Keep buttons in sync when WooCommerce changes / clears the select value
		$select.on('change', function () {
			var currentVal = $select.val();

			$wrapper.find('.wc-size-labels__item')
				.removeClass('is-selected')
				.attr('aria-checked', 'false');

			if (currentVal) {
				$wrapper
					.find('.wc-size-labels__item[data-value="' + escapeAttr(currentVal) + '"]')
					.addClass('is-selected')
					.attr('aria-checked', 'true');
			}
		});
	}

	function guessCssColor(value, text) {
		var v = (value || '').toString().trim();
		var t = (text || '').toString().trim();
		var s = (v || t).toLowerCase();

		// hex formats
		if (/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i.test(v)) {
			return v;
		}
		if (/^([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i.test(v)) {
			return '#' + v;
		}

		// rgb/hsl
		if (/^(rgb|rgba|hsl|hsla)\(/i.test(v)) {
			return v;
		}

		// common color names / slugs
		var map = {
			red: '#e53935',
			blue: '#1e88e5',
			green: '#43a047',
			black: '#111111',
			white: '#ffffff',
			yellow: '#fdd835',
			orange: '#fb8c00',
			pink: '#ec407a',
			purple: '#8e24aa',
			violet: '#7e57c2',
			grey: '#9e9e9e',
			gray: '#9e9e9e',
			brown: '#6d4c41',
			maroon: '#7b1f1f',
			navy: '#0d47a1',
			teal: '#00897b',
			lime: '#7cb342',
			cyan: '#00acc1',
			magenta: '#d81b60'
		};

		// normalize slugs like "dark-blue"
		s = s.replace(/[_\s]+/g, '-');
		if (map[s]) {
			return map[s];
		}
		if (map[s.replace(/-/g, '')]) {
			return map[s.replace(/-/g, '')];
		}

		return '#cccccc';
	}

	function initColorSwatches($select) {
		if (!$select.length) {
			return;
		}

		// Avoid double initialization
		if ($select.data('color-swatches-initialized')) {
			return;
		}
		$select.data('color-swatches-initialized', true);

		var selectName = $select.attr('name') || '';
		if (selectName.indexOf('attribute_pa_color') === -1 && selectName.indexOf('attribute_color') === -1) {
			return;
		}

		var labelText = $select.closest('tr').find('label').text() || 'Select color';
		var $wrapper = $('<div/>', {
			class: 'wc-color-swatches',
			role: 'radiogroup',
			'aria-label': labelText
		});

		$select.find('option').each(function () {
			var $option = $(this);
			var value = $option.val();
			var text = $.trim($option.text());

			if (!value) {
				return;
			}

			var swatchColor = guessCssColor(value, text);

			var $btn = $('<button/>', {
				type: 'button',
				class: 'wc-color-swatches__item',
				'data-value': value,
				role: 'radio',
				'aria-checked': 'false',
				title: text,
				'aria-label': text
			});

			var $circle = $('<span/>', {
				class: 'wc-color-swatches__circle'
			}).css('background-color', swatchColor);

			$btn.append($circle);

			if ($option.is(':disabled')) {
				$btn.addClass('is-disabled').attr('aria-disabled', 'true');
			}
			if ($option.is(':selected')) {
				$btn.addClass('is-selected').attr('aria-checked', 'true');
			}

			$wrapper.append($btn);
		});

		if (!$wrapper.children().length) {
			return;
		}

		$select.addClass('wc-variation-select--hidden');
		$select.after($wrapper);

		$wrapper.on('click', '.wc-color-swatches__item', function (e) {
			e.preventDefault();
			var $item = $(this);
			if ($item.hasClass('is-disabled')) {
				return;
			}

			var value = $item.data('value');
			$select.val(value).trigger('change');

			$wrapper.find('.wc-color-swatches__item')
				.removeClass('is-selected')
				.attr('aria-checked', 'false');

			$item.addClass('is-selected').attr('aria-checked', 'true');
		});

		$select.on('change', function () {
			var currentVal = $select.val();

			$wrapper.find('.wc-color-swatches__item')
				.removeClass('is-selected')
				.attr('aria-checked', 'false');

			if (currentVal) {
				$wrapper
					.find('.wc-color-swatches__item[data-value="' + escapeAttr(currentVal) + '"]')
					.addClass('is-selected')
					.attr('aria-checked', 'true');
			}
		});
	}

	/**
	 * Sync disabled state from <option> elements after WooCommerce
	 * updates variation options.
	 */
	function syncDisabledStates($form) {
		$form.find('select').each(function () {
			var $select = $(this);
			var selectName = $select.attr('name') || '';

			// Size buttons
			if (selectName.indexOf('attribute_pa_size') !== -1 || selectName.indexOf('attribute_size') !== -1) {
				var $sizeWrapper = $select.next('.wc-size-labels');
				if ($sizeWrapper.length) {
					$select.find('option').each(function () {
						var $option = $(this);
						var value = $option.val();
						if (!value) {
							return;
						}
						var $btn = $sizeWrapper.find('.wc-size-labels__item[data-value="' + escapeAttr(value) + '"]');
						if (!$btn.length) {
							return;
						}
						if ($option.is(':disabled')) {
							$btn.addClass('is-disabled').attr('aria-disabled', 'true');
						} else {
							$btn.removeClass('is-disabled').removeAttr('aria-disabled');
						}
					});
				}
			}

			// Color swatches
			if (selectName.indexOf('attribute_pa_color') !== -1 || selectName.indexOf('attribute_color') !== -1) {
				var $colorWrapper = $select.next('.wc-color-swatches');
				if ($colorWrapper.length) {
					$select.find('option').each(function () {
						var $option = $(this);
						var value = $option.val();
						if (!value) {
							return;
						}
						var $btn = $colorWrapper.find('.wc-color-swatches__item[data-value="' + escapeAttr(value) + '"]');
						if (!$btn.length) {
							return;
						}
						if ($option.is(':disabled')) {
							$btn.addClass('is-disabled').attr('aria-disabled', 'true');
						} else {
							$btn.removeClass('is-disabled').removeAttr('aria-disabled');
						}
					});
				}
			}
		});
	}

	$(function () {
		// Initialize on page load
		$('form.variations_form').each(function () {
			var $form = $(this);

			$form.find('select').each(function () {
				var $sel = $(this);
				initSizeLabelGroup($sel);
				initColorSwatches($sel);
			});

			// Hook into WooCommerce variation updates to refresh disabled states
			$form.on('woocommerce_update_variation_values', function () {
				syncDisabledStates($form);
			});

			// When "Clear" (reset variations) is clicked, clear selected button state
			$form.on('click', '.reset_variations', function () {
				// Give WooCommerce a tick to reset selects, then let our select change handler sync UI
				setTimeout(function () {
					$form.find('select').trigger('change');
				}, 0);
			});
		});
	});

})(jQuery);


