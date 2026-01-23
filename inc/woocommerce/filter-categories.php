<?php
/**
 * WooCommerce Filter by Category
 *
 * Adds a category filter dropdown on the shop page. When a category is
 * selected, products are restricted to that category via the product query.
 *
 * ============================================================================
 * FILE RESPONSIBILITIES:
 * ============================================================================
 * - Render "Filter by category" dropdown before the shop loop
 * - Apply tax_query in woocommerce_product_query when filter_cat GET param is set
 *
 * @package    WooCommerce
 * @subpackage Theme/WooCommerce
 * @since      1.0.0
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Filter product query by category when filter_cat is in request
 *
 * @since 1.0.0
 * @param WP_Query $q The product query object.
 */
function woocommerce_theme_filter_products_by_category( $q ) {
	$filter_cat = isset( $_GET['filter_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_cat'] ) ) : '';
	if ( '' === $filter_cat ) {
		return;
	}

	$tax_query = (array) $q->get( 'tax_query' );
	$tax_query[] = array(
		'taxonomy' => 'product_cat',
		'field'    => 'slug',
		'terms'    => $filter_cat,
		'operator' => 'IN',
	);
	$q->set( 'tax_query', $tax_query );
}
add_filter( 'woocommerce_product_query', 'woocommerce_theme_filter_products_by_category', 20 );

/**
 * Output category filter dropdown before the shop loop
 *
 * Displays on the main shop page only. Form submits via GET to preserve
 * filter_cat and optional orderby.
 *
 * @since 1.0.0
 * @return void
 */
function woocommerce_theme_render_category_filter() {
	if ( ! function_exists( 'is_shop' ) || ! is_shop() ) {
		return;
	}

	$categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );

	if ( is_wp_error( $categories ) || empty( $categories ) ) {
		return;
	}

	$current = isset( $_GET['filter_cat'] ) ? sanitize_text_field( wp_unslash( $_GET['filter_cat'] ) ) : '';
	$base    = get_permalink( wc_get_page_id( 'shop' ) );
	$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
	?>
	<div class="shop-category-filter" aria-label="<?php esc_attr_e( 'Filter products by category', 'woocommerce' ); ?>">
		<form method="get" action="<?php echo esc_url( $base ); ?>" class="shop-category-filter__form">
			<label for="shop-filter-category" class="shop-category-filter__label">
				<?php esc_html_e( 'Filter by category', 'woocommerce' ); ?>
			</label>
			<select id="shop-filter-category" name="filter_cat" class="shop-category-filter__select" aria-label="<?php esc_attr_e( 'Filter by category', 'woocommerce' ); ?>">
				<option value=""><?php esc_html_e( 'All categories', 'woocommerce' ); ?></option>
				<?php
				foreach ( $categories as $term ) {
					printf(
						'<option value="%1$s"%2$s>%3$s</option>',
						esc_attr( $term->slug ),
						selected( $current, $term->slug, false ),
						esc_html( $term->name )
					);
				}
				?>
			</select>
			<?php if ( $orderby ) : ?>
				<input type="hidden" name="orderby" value="<?php echo esc_attr( $orderby ); ?>" />
			<?php endif; ?>
			<button type="submit" class="shop-category-filter__submit">
				<?php esc_html_e( 'Filter', 'woocommerce' ); ?>
			</button>
		</form>
	</div>
	<?php
}
add_action( 'woocommerce_before_shop_loop', 'woocommerce_theme_render_category_filter', 8 );
