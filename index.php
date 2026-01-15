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

/**
 * Load the header template file
 * 
 * This function includes header.php, which contains:
 * - HTML document structure (<html>, <head>)
 * - Site branding and navigation
 * - WooCommerce mini-cart (if WooCommerce is active)
 * 
 * In a WooCommerce theme, the header is shared across all pages including
 * shop, product, cart, and checkout pages, ensuring consistent navigation.
 */
get_header();
?>

<?php
/**
 * The WordPress Loop
 * 
 * This is the core mechanism WordPress uses to display content. The Loop
 * iterates through posts/pages retrieved by the main query and displays
 * each one. In a WooCommerce theme, this file serves as a fallback for
 * blog posts and pages when WooCommerce-specific templates are not needed.
 * 
 * Execution flow:
 * 1. WordPress queries the database based on the current page request
 * 2. have_posts() checks if any posts were found
 * 3. If posts exist, the while loop displays each post
 * 4. If no posts exist, the else block displays a "not found" message
 */
if ( have_posts() ) :
	?>
	<div class="posts-container">
		<?php
		/**
		 * Start the WordPress Loop
		 * 
		 * The while loop iterates through each post in the query results.
		 * Each iteration calls the_post() which sets up global post data,
		 * making functions like the_title() and the_content() work correctly.
		 * 
		 * In a WooCommerce theme context:
		 * - This loop displays blog posts, not products
		 * - WooCommerce product pages use single-product.php template
		 * - Shop archives use archive-product.php template
		 * - This file only runs when no more specific template exists
		 */
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<header class="entry-header">
					<?php
					/**
					 * Display the post title
					 * 
					 * Conditional logic determines title markup:
					 * - is_singular() returns true on single post/page views
					 * - On singular pages: uses <h1> (main heading for SEO)
					 * - On archive pages: uses <h2> with link (secondary heading)
					 * 
					 * esc_url() sanitizes the permalink URL to prevent XSS attacks.
					 * This is a WordPress security best practice.
					 */
					if ( is_singular() ) {
						the_title( '<h1 class="entry-title">', '</h1>' );
					} else {
						the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
					}

					/**
					 * Display post meta information (date, author)
					 * 
					 * This section only displays for 'post' post type, not pages.
					 * In a WooCommerce theme, this provides blog functionality
					 * alongside the e-commerce features. The meta information
					 * helps users understand when content was published and by whom.
					 */
					if ( 'post' === get_post_type() ) {
						?>
						<div class="entry-meta">
							<span class="posted-on">
								<?php
								/**
								 * Display publication date
								 * 
								 * get_the_date('c') returns ISO 8601 format for datetime attribute
								 * get_the_date() returns formatted date for display
								 * esc_attr() and esc_html() sanitize output for security
								 */
								/* translators: %s: post date */
								printf( esc_html__( 'Published on %s', 'woocommerce' ), '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date() ) . '</time>' );
								?>
							</span>
							<span class="byline">
								<?php
								/**
								 * Display author information
								 * 
								 * get_the_author_meta('ID') gets the author's user ID
								 * get_author_posts_url() generates URL to author's archive page
								 * get_the_author() returns the author's display name
								 * All output is escaped for security
								 */
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
				/**
				 * Display featured image (post thumbnail)
				 * 
				 * has_post_thumbnail() checks if a featured image is set
				 * the_post_thumbnail() outputs the image with specified size
				 * 'large' is a WordPress default image size
				 * 
				 * In WooCommerce themes, featured images are important for
				 * blog posts, though product images use different WooCommerce
				 * functions and templates.
				 */
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'large', array( 'class' => 'aligncenter' ) );
				}
				?>

				<div class="entry-content">
					<?php
					/**
					 * Display post content
					 * 
					 * Conditional logic determines what content to show:
					 * - is_singular() checks if viewing single post/page
					 * - On singular: the_content() shows full content with formatting
					 * - On archives: the_excerpt() shows shortened preview
					 * 
					 * This separation improves user experience by showing
					 * previews on archive pages and full content on single views.
					 */
					if ( is_singular() ) {
						/**
						 * Display full post content
						 * 
						 * the_content() outputs the post content with:
						 * - Paragraph tags automatically added
						 * - Shortcodes processed
						 * - "Read more" tag support
						 * - All formatting preserved
						 */
						the_content();

						/**
						 * Display pagination for multi-page posts
						 * 
						 * wp_link_pages() creates pagination links when a post
						 * uses the <!--nextpage--> quicktag to split content.
						 * This allows long posts to be divided into multiple pages.
						 */
						wp_link_pages( array(
							'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'woocommerce' ),
							'after'  => '</div>',
						) );
					} else {
						/**
						 * Display post excerpt on archive pages
						 * 
						 * the_excerpt() shows a shortened version of the content.
						 * If no excerpt is manually set, WordPress automatically
						 * generates one from the first 55 words of the content.
						 */
						the_excerpt();
					}
					?>
				</div><!-- .entry-content -->

				<footer class="entry-footer">
					<?php
					/**
					 * Display categories and tags
					 * 
					 * This section only displays for 'post' post type.
					 * Categories and tags help organize blog content and improve
					 * navigation. In a WooCommerce theme, this provides blog
					 * functionality separate from product categories and tags.
					 */
					if ( 'post' === get_post_type() ) {
						/**
						 * Display category list
						 * 
						 * get_the_category_list() returns HTML links to category archive pages.
						 * The output is not escaped because it contains HTML links that are
						 * safe (generated by WordPress core function).
						 */
						$categories_list = get_the_category_list( esc_html__( ', ', 'woocommerce' ) );
						if ( $categories_list ) {
							/* translators: 1: list of categories */
							printf( '<span class="cat-links">' . esc_html__( 'Posted in %1$s', 'woocommerce' ) . '</span>', $categories_list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}

						/**
						 * Display tag list
						 * 
						 * get_the_tag_list() returns HTML links to tag archive pages.
						 * Similar to categories, the output contains safe HTML from WordPress core.
						 */
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
		/**
		 * Display pagination navigation for archive pages
		 * 
		 * the_posts_navigation() creates "Previous" and "Next" links for
		 * navigating between pages of posts on archive pages (blog index,
		 * category archives, etc.). This helps users browse through
		 * multiple pages of content.
		 */
		the_posts_navigation( array(
			'prev_text' => esc_html__( '&laquo; Older posts', 'woocommerce' ),
			'next_text' => esc_html__( 'Newer posts &raquo;', 'woocommerce' ),
		) );
		?>
	</div><!-- .posts-container -->
	<?php
else :
	/**
	 * No posts found - display "not found" message
	 * 
	 * This section displays when the query returns no results.
	 * In a WooCommerce theme, this could occur on:
	 * - Empty blog archive pages
	 * - Search results with no matches
	 * - Category/tag archives with no posts
	 * 
	 * Note: WooCommerce shop pages have their own "no products found" template.
	 */
	?>
	<section class="no-results not-found">
		<header class="page-header">
			<h1 class="page-title"><?php esc_html_e( 'Nothing here', 'woocommerce' ); ?></h1>
		</header><!-- .page-header -->

		<div class="page-content">
			<?php
			/**
			 * Conditional message based on context
			 * 
			 * If on the blog homepage and user can publish posts, show
			 * a helpful message with link to create first post.
			 * Otherwise, show generic "not found" message with search form.
			 */
			if ( is_home() && current_user_can( 'publish_posts' ) ) {
				/**
				 * Display message for site administrators
				 * 
				 * wp_kses() sanitizes HTML, allowing only specified tags/attributes.
				 * This prevents XSS attacks while allowing safe HTML links.
				 * esc_url() sanitizes the admin URL.
				 */
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
				/**
				 * Display generic "not found" message with search form
				 * 
				 * esc_html_e() translates and escapes text for safe output.
				 * get_search_form() displays the WordPress search form widget.
				 */
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
/**
 * Load the footer template file
 * 
 * This function includes footer.php, which contains:
 * - Footer widgets and content
 * - Site information and copyright
 * - wp_footer() hook (critical for scripts and plugins)
 * - Closing HTML tags
 * 
 * In a WooCommerce theme, the footer is shared across all pages,
 * ensuring consistent site structure and proper script loading.
 */
get_footer();
?>

