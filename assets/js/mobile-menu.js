/**
 * Mobile Menu Toggle
 *
 * Handles the hamburger menu functionality for mobile devices.
 * When the hamburger icon is clicked, the menu slides in from left to right.
 *
 * Features:
 * - Toggle menu open/close
 * - Animate hamburger icon (hamburger to X)
 * - Prevent body scroll when menu is open
 * - Close menu when clicking overlay
 * - Close menu when clicking menu links
 * - Keyboard accessibility (ESC key to close)
 */

(function($) {
	'use strict';

	/**
	 * Mobile Menu Controller
	 * Manages all mobile menu interactions
	 */
	var MobileMenu = {
		/**
		 * Initialize mobile menu functionality
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind event handlers
		 */
		bindEvents: function() {
			var self = this;

			// Toggle menu when hamburger button is clicked
			$(document).on('click', '#menu-toggle', function(e) {
				e.preventDefault();
				e.stopPropagation();
				self.toggleMenu();
			});

			// Close menu when clicking overlay/backdrop
			$(document).on('click', '#menu-overlay', function() {
				self.closeMenu();
			});

			// Close menu when clicking a menu link (mobile only)
			$(document).on('click', '#primary-menu a', function() {
				if ($(window).width() <= 767) {
					self.closeMenu();
				}
			});

			// Close menu on ESC key press
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' || e.keyCode === 27) {
					if ($('.main-navigation').hasClass('menu-open')) {
						self.closeMenu();
						$('#menu-toggle').focus(); // Return focus to toggle button
					}
				}
			});

			// Close menu when window is resized to desktop size
			$(window).on('resize', function() {
				if ($(window).width() > 767) {
					self.closeMenu();
				}
			});

			// Prevent menu from closing when clicking inside menu
			$(document).on('click', '.main-navigation', function(e) {
				e.stopPropagation();
			});
		},

		/**
		 * Toggle menu open/close state
		 */
		toggleMenu: function() {
			var $menu = $('.main-navigation');
			var $toggle = $('#menu-toggle');
			var $body = $('body');

			if ($menu.hasClass('menu-open')) {
				this.closeMenu();
			} else {
				this.openMenu();
			}
		},

		/**
		 * Open mobile menu
		 */
		openMenu: function() {
			var $menu = $('.main-navigation');
			var $toggle = $('#menu-toggle');
			var $body = $('body');
			var $overlay = $('#menu-overlay');

			$menu.addClass('menu-open');
			$overlay.addClass('active');
			$toggle.attr('aria-expanded', 'true');
			$body.addClass('menu-open');

			// Focus first menu item for accessibility
			setTimeout(function() {
				$('#primary-menu a').first().focus();
			}, 300);
		},

		/**
		 * Close mobile menu
		 */
		closeMenu: function() {
			var $menu = $('.main-navigation');
			var $toggle = $('#menu-toggle');
			var $body = $('body');
			var $overlay = $('#menu-overlay');

			$menu.removeClass('menu-open');
			$overlay.removeClass('active');
			$toggle.attr('aria-expanded', 'false');
			$body.removeClass('menu-open');
		}
	};

	/**
	 * Initialize when DOM is ready
	 */
	$(document).ready(function() {
		MobileMenu.init();
	});

})(jQuery);

