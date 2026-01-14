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
					 * Display footer navigation menu
					 * Note: 'footer' menu location must be registered in functions.php
					 * If not registered, fallback links will be displayed instead
					 */
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'fallback_cb'    => false,
					) );
					?>
					<?php if ( ! has_nav_menu( 'footer' ) ) : ?>
						<ul class="footer-menu">
							<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'woocommerce' ); ?></a></li>
							<?php if ( class_exists( 'WooCommerce' ) ) : ?>
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
					 * Contact information section
					 * TODO: Replace placeholder values with actual contact information
					 * or use WordPress Customizer options for better flexibility
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
			 * Display copyright and theme credit information
			 * You can customize this section as needed
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
 * wp_footer() is a critical WordPress function that outputs:
 * - Enqueued scripts that should load in the footer
 * - Analytics code, tracking pixels, etc.
 * - Plugins can hook into this to add their own content
 */
wp_footer();
?>

</body>
</html>

