<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 *
 * Template Hierarchy:
 * - index.php (this file) - fallback for all pages
 * - WooCommerce templates override this for shop/product pages
 * - WordPress will use archive.php, single.php, etc. if they exist
 *
 * @package WooCommerce
 */

get_header(); // Load header.php
?>

<?php
/**
 * The WordPress Loop
 * This is the core of WordPress - it displays posts, pages, and other content
 */
if ( have_posts() ) :
	?>
	<div class="posts-container">
		<?php
		// Start the Loop
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php
					// Display the post title
					if ( is_singular() ) {
						the_title( '<h1 class="entry-title">', '</h1>' );
					} else {
						the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
					}

					// Display post meta (date, author, etc.)
					if ( 'post' === get_post_type() ) {
						?>
						<div class="entry-meta">
							<span class="posted-on">
								<?php
								/* translators: %s: post date */
								printf( esc_html__( 'Published on %s', 'woocommerce' ), '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>' );
								?>
							</span>
							<span class="byline">
								<?php
								/* translators: %s: post author */
								printf( esc_html__( 'by %s', 'woocommerce' ), '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>' );
								?>
							</span>
						</div><!-- .entry-meta -->
						<?php
					}
					?>
				</header><!-- .entry-header -->

				<?php
				// Display featured image if available
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'large', array( 'class' => 'aligncenter' ) );
				}
				?>

				<div class="entry-content">
					<?php
					// Display post content
					if ( is_singular() ) {
						// On single posts/pages, show full content
						the_content();

						// Display pagination for posts with <!--nextpage--> tag
						wp_link_pages( array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'woocommerce' ),
							'after'  => '</div>',
						) );
					} else {
						// On archive pages, show excerpt
						the_excerpt();
					}
					?>
				</div><!-- .entry-content -->

				<footer class="entry-footer">
					<?php
					// Display categories and tags
					if ( 'post' === get_post_type() ) {
						$categories_list = get_the_category_list( esc_html__( ', ', 'woocommerce' ) );
						if ( $categories_list ) {
							/* translators: 1: list of categories */
							printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'woocommerce' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}

						$tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'woocommerce' ) );
						if ( $tags_list ) {
							/* translators: 1: list of tags */
							printf( '<span class="tags-links">' . esc_html__( 'Tagged %1$s', 'woocommerce' ) . '</span>', $tags_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
					}
					?>
				</footer><!-- .entry-footer -->
			</article><!-- #post-<?php the_ID(); ?> -->
			<?php
		endwhile; // End of the loop
		?>

		<?php
		// Display pagination for archive pages
		the_posts_navigation( array(
			'prev_text' => esc_html__( '&laquo; Older posts', 'woocommerce' ),
			'next_text' => esc_html__( 'Newer posts &raquo;', 'woocommerce' ),
		) );
		?>
	</div><!-- .posts-container -->
	<?php
else :
	// If no content is found, display a message
	?>
	<section class="no-results not-found">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Nothing here', 'woocommerce' ); ?></h1>
		</header><!-- .page-header -->

		<div class="page-content">
			<?php
			if ( is_home() && current_user_can( 'publish_posts' ) ) {
				?>
				<p>
					<?php
					printf(
						wp_kses(
							/* translators: 1: link to WP admin new post page */
							__( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'woocommerce' ),
							array(
								'a' => array(
									'href' => array(),
								),
							)
						),
						esc_url( admin_url( 'post-new.php' ) )
					);
					?>
				</p>
				<?php
			} else {
				?>
				<p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'woocommerce' ); ?></p>
				<?php
				get_search_form();
			}
			?>
		</div><!-- .page-content -->
	</section><!-- .no-results -->
	<?php
endif;
?>

<?php
get_footer(); // Load footer.php
?>

