	</main><!-- #primary -->

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="footer-widgets">
			<div class="footer-widget-area">
				<div class="footer-column">
					<h3 class="footer-title"><?php esc_html_e( 'About Us', 'woocommerce' ); ?></h3>
					<p><?php esc_html_e( 'We are committed to providing quality products and excellent customer service. Your satisfaction is our priority.', 'woocommerce' ); ?></p>
					<p>
						<a href="<?php echo esc_url( home_url( '/privacy-policy' ) ); ?>">
							<?php esc_html_e( 'Privacy Policy', 'woocommerce' ); ?>
						</a> | 
						<a href="<?php echo esc_url( home_url( '/terms-conditions' ) ); ?>">
							<?php esc_html_e( 'Terms & Conditions', 'woocommerce' ); ?>
						</a>
					</p>
				</div>

				<div class="footer-column">
					<h3 class="footer-title"><?php esc_html_e( 'Quick Links', 'woocommerce' ); ?></h3>
					<?php
					/**
					 * Footer Navigation Menu
					 * 
					 * wp_nav_menu() displays the menu assigned to the 'footer' theme location.
					 * This menu is typically used for secondary navigation links that don't
					 * belong in the primary header menu.
					 * 
					 * In WooCommerce themes, footer menus commonly include:
					 * - Legal pages (Privacy Policy, Terms & Conditions)
					 * - Customer service links
					 * - Account-related pages
					 * - Site information pages
					 * 
					 * The menu location 'footer' must be registered in functions.php via
					 * register_nav_menus() for this to work.
					 */
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'fallback_cb'    => false,
					) );
					?>
					<?php
					/**
					 * Fallback Footer Links
					 * 
					 * If no footer menu is assigned, display default WooCommerce links.
					 * This ensures users always have access to essential e-commerce pages
					 * even if the footer menu hasn't been configured.
					 * 
					 * has_nav_menu() checks if a menu is assigned to the 'footer' location.
					 * If false, we display fallback links.
					 */
					if ( ! has_nav_menu( 'footer' ) ) :
						?>
						<ul class="footer-menu">
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'woocommerce' ); ?></a></li>
							<?php
							/**
							 * WooCommerce Page Links
							 * 
							 * wc_get_page_permalink() retrieves the permalink for WooCommerce
							 * core pages (shop, cart, checkout, myaccount). These functions
							 * only work when WooCommerce is active, so we check with
							 * class_exists('WooCommerce') before using them.
							 * 
							 * Why these links exist:
							 * - Provides quick access to essential e-commerce pages
							 * - Improves site navigation and user experience
							 * - Helps with SEO by creating internal link structure
							 */
							if ( class_exists( 'WooCommerce' ) ) :
								?>
								<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop', 'woocommerce' ); ?></a></li>
								<li><a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>"><?php esc_html_e( 'Cart', 'woocommerce' ); ?></a></li>
								<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My Account', 'woocommerce' ); ?></a></li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
				</div>

				<div class="footer-column">
					<h3 class="footer-title"><?php esc_html_e( 'Contact Us', 'woocommerce' ); ?></h3>
					<?php
					/**
					 * Contact Information Section
					 * 
					 * This section displays business contact details. In a WooCommerce theme,
					 * contact information is important for:
					 * - Building customer trust
					 * - Providing support channels
					 * - Meeting legal requirements (some jurisdictions require business contact info)
					 * - Improving SEO with local business information
					 * 
					 * Note: The contact details shown here are hardcoded. In a production
					 * theme, these would typically be managed via WordPress Customizer or
					 * theme options panel for easier maintenance.
					 */
					?>
					<p>
						<strong><?php esc_html_e( 'Email:', 'woocommerce' ); ?></strong><br>
						<a href="mailto:chamikashashipriya3@gmail.com">chamikashashipriya3@gmail.com</a>
					</p>
					<p>
						<strong><?php esc_html_e( 'Phone:', 'woocommerce' ); ?></strong><br>
						<a href="tel:+94704120358">+94704120358</a>
					</p>
					<p>
						<strong><?php esc_html_e( 'Address:', 'woocommerce' ); ?></strong><br>
						<?php esc_html_e( 'Malabe, Colombo, Sri Lanka', 'woocommerce' ); ?>
					</p>
				</div>

				<div class="footer-column">
					<h3 class="footer-title"><?php esc_html_e( 'Follow Us', 'woocommerce' ); ?></h3>
					<p><?php esc_html_e( 'Stay connected with us on social media for the latest updates and offers.', 'woocommerce' ); ?></p>
					<div class="social-links">
						<a href="#" class="social-link" aria-label="<?php esc_attr_e( 'Facebook', 'woocommerce' ); ?>"><?php esc_html_e( 'Facebook', 'woocommerce' ); ?></a>
						<a href="#" class="social-link" aria-label="<?php esc_attr_e( 'Twitter', 'woocommerce' ); ?>"><?php esc_html_e( 'Twitter', 'woocommerce' ); ?></a>
						<a href="#" class="social-link" aria-label="<?php esc_attr_e( 'Instagram', 'woocommerce' ); ?>"><?php esc_html_e( 'Instagram', 'woocommerce' ); ?></a>
						<a href="#" class="social-link" aria-label="<?php esc_attr_e( 'LinkedIn', 'woocommerce' ); ?>"><?php esc_html_e( 'LinkedIn', 'woocommerce' ); ?></a>
					</div>
				</div>
			</div>
		</div>

		<div class="site-info">
			<?php
			/**
			 * Site Information and Copyright Section
			 * 
			 * This section displays copyright notice and site credits. In WooCommerce themes,
			 * this typically appears at the bottom of every page and includes:
			 * - Copyright year (dynamically generated)
			 * - Site name (from WordPress settings)
			 * - Theme credits (optional)
			 * 
			 * Why this exists:
			 * - Legal requirement in many jurisdictions
			 * - Professional appearance
			 * - Attribution for theme developers
			 * 
			 * date('Y') returns the current 4-digit year, ensuring copyright year
			 * is always current without manual updates.
			 */
			?>
			<p>
				&copy; <?php echo esc_html( date( 'Y' ) ); ?> 
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php bloginfo( 'name' ); ?>
				</a>
				<?php esc_html_e( '. All rights reserved.', 'woocommerce' ); ?>
			</p>
			<?php esc_html_e( 'Made By Chamika Shashipriya At DoAcademy', 'woocommerce' ); ?>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php
/**
 * wp_footer() Hook - Critical WordPress Function
 * 
 * This function outputs essential content before the closing </body> tag.
 * It is called automatically by WordPress and must be included in all themes.
 * 
 * What wp_footer() outputs:
 * 1. All enqueued JavaScript files that should load in footer
 *    (scripts enqueued with wp_enqueue_script() with $in_footer = true)
 * 2. Inline scripts added by plugins/themes
 * 3. Analytics tracking codes (Google Analytics, etc.)
 * 4. Plugin-generated JavaScript (e.g., WooCommerce cart fragments)
 * 5. Theme-specific JavaScript (e.g., mini-cart.js)
 * 
 * In WooCommerce themes, wp_footer() outputs:
 * - WooCommerce JavaScript (cart updates, AJAX functionality)
 * - Theme JavaScript (mini-cart interactions, etc.)
 * - Plugin scripts (payment gateways, shipping calculators)
 * 
 * Why scripts load in footer:
 * - Improves page load performance (HTML renders first)
 * - Better user experience (content visible sooner)
 * - Scripts can access fully-rendered DOM elements
 * 
 * Hook execution: Runs on every page load, immediately before </body> tag closes.
 * Plugins can hook into 'wp_footer' action to add custom scripts/content.
 * 
 * IMPORTANT: wp_footer() must be called before </body> tag or many WordPress
 * features (including AJAX) will not work correctly.
 */
wp_footer();
?>

</body>
</html>

