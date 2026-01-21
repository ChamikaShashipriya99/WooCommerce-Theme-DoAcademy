/**
 * Back to Top Button Functionality
 * 
 * This script handles the display and functionality of the back-to-top button.
 * - Shows button when user scrolls down 300px
 * - Hides button when at top of page
 * - Smoothly scrolls to top when button is clicked
 */

(function($) {
	'use strict';

	$(document).ready(function() {
		var $backToTop = $('#back-to-top');

		// Show/hide button based on scroll position
		$(window).on('scroll', function() {
			if ($(this).scrollTop() > 300) {
				$backToTop.addClass('is-visible');
			} else {
				$backToTop.removeClass('is-visible');
			}
		});

		// Smooth scroll to top when button is clicked
		$backToTop.on('click', function(e) {
			e.preventDefault();
			$('html, body').animate({
				scrollTop: 0
			}, 600); // 600ms animation duration
		});
	});

})(jQuery);

