<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	
	<?php
	/**
	 * wp_head() is a critical WordPress function that outputs:
	 * - The <title> tag (if title-tag support is enabled)
	 * - Enqueued stylesheets and scripts
	 * - Meta tags, RSS feeds, and other head content
	 * - Plugins can also hook into this to add their own content
	 */
	wp_head();
	?>
</head>

<body <?php body_class(); ?>>
<?php
/**
 * wp_body_open() allows plugins and themes to inject content
 * right after the opening <body> tag.
 * This is a WordPress 5.2+ feature.
 */
wp_body_open();
?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary">
		<?php esc_html_e( 'Skip to content', 'woocommerce' ); ?>
	</a>

	<header id="masthead" class="site-header" role="banner">
		<div class="site-branding">
			<?php
			// Display site title or logo
			if ( has_custom_logo() ) {
				// If a custom logo is set, display it
				the_custom_logo();
			} else {
				// Otherwise, display the site title as a link
				?>
				<h1 class="site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<?php bloginfo( 'name' ); ?>
					</a>
				</h1>
				<?php
				// Display site tagline if available
				$description = get_bloginfo( 'description', 'display' );
				if ( $description || is_customize_preview() ) {
					?>
					<p class="site-description"><?php echo $description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<?php
				}
			}
			?>
		</div><!-- .site-branding -->

		<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'woocommerce' ); ?>">
			<?php
			/**
			 * Display the primary menu
			 * wp_nav_menu() outputs the menu assigned to the 'primary' location
			 * If no menu is assigned, it falls back to wp_list_pages()
			 */
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_id'        => 'primary-menu',
				'container'      => false,
				'fallback_cb'    => false, // Don't show fallback menu if none is set
			) );
			?>
		</nav><!-- #site-navigation -->
	</header><!-- #masthead -->

	<main id="primary" class="site-main">

