	</main><!-- #primary -->

	<footer id="colophon" class="site-footer" role="contentinfo">
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
			</p>
			<p>
				<?php
				/* translators: %s: Theme name */
				printf( esc_html__( 'Theme: %s', 'woocommerce' ), 'WooCommerce' );
				?>
			</p>
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

