/**
 * Shop Category Filter
 *
 * Auto-submits the category filter form when the dropdown selection changes,
 * so users don't need to click the "Filter" button.
 */
(function ($) {
	'use strict';

	$(function () {
		var $form = $('.shop-category-filter__form');
		var $select = $('#shop-filter-category');

		if (!$form.length || !$select.length) {
			return;
		}

		$select.on('change', function () {
			$form.trigger('submit');
		});
	});
})(jQuery);
