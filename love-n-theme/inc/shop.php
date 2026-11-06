<?php
/**
 * Shop page laid out like the Work page: one tab per product category plus
 * "Shop all", all on the same page (assets/js/tabs.js switches them).
 *
 * Category links such as /product-category/postcards/ go to the matching tab
 * (/shop/#postcards), so the home page cards and any older links land there.
 *
 * @package love-n
 */

defined( 'ABSPATH' ) || exit;

/** Shop tabs: slug => [label, intro]. "all" comes first. */
function ln_shop_tabs() {
	$tabs = array(
		'all' => array( 'Shop all', 'Prints, postcards, bookmarks and stickers, all illustrated and packed by hand in my studio.' ),
	);
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $tabs;
	}
	// Preferred order and wording (matches the home page cards); other categories follow by name.
	$preferred = array(
		'prints'    => 'Artworks &amp; prints',
		'postcards' => 'Postcards',
		'bookmarks' => 'Bookmarks',
		'stickers'  => 'Stickers',
	);
	$skip  = array( 'mail-club', 'uncategorized' );
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);
	if ( is_wp_error( $terms ) ) {
		return $tabs;
	}
	$by_slug = array();
	foreach ( $terms as $t ) {
		if ( ! in_array( $t->slug, $skip, true ) ) {
			$by_slug[ $t->slug ] = $t;
		}
	}
	$add = function ( $t, $label ) use ( &$tabs ) {
		$tabs[ $t->slug ] = array( $label, $t->description ? esc_html( $t->description ) : '' );
	};
	foreach ( $preferred as $slug => $label ) {
		if ( isset( $by_slug[ $slug ] ) ) {
			$add( $by_slug[ $slug ], $label );
			unset( $by_slug[ $slug ] );
		}
	}
	foreach ( $by_slug as $t ) {
		if ( $t->count > 0 ) { // A new category gets its own tab once it has products.
			$add( $t, esc_html( $t->name ) );
		}
	}
	return $tabs;
}

/* ---------- [ln_shop] ---------- */
add_shortcode(
	'ln_shop',
	function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '';
		}
		$tabs    = ln_shop_tabs();
		$buttons = array();
		foreach ( $tabs as $slug => $t ) {
			$buttons[] = array( $t[0], '#' . $slug, true );
		}
		$out = ln_section(
			ln_p( 'The Love, N shop', 'ln-kicker' )
			. ln_heading( 'Little things, <em class="ln-accent">made with love</em>', 1 )
			. ln_p( 'Art prints, postcards, bookmarks and stickers to keep, frame or send to someone you love.', 'ln-lede' )
			. ln_buttons( $buttons, 'ln-tabs' ),
			'ln-page-hero'
		);

		foreach ( $tabs as $slug => $t ) {
			$args  = 'all' === $slug ? '' : ' category="' . esc_attr( $slug ) . '"';
			$grid  = do_shortcode( '[products' . $args . ' columns="4" limit="-1" orderby="menu_order" order="ASC"]' );
			$empty = false === strpos( $grid, 'class="product' ) && false === strpos( $grid, ' product type-product' );
			$body  = $empty
				? ln_p( 'New pieces are on their way. Check back soon, or join the mail list to hear first.', 'ln-note' )
				: '<!-- wp:html -->' . $grid . '<!-- /wp:html -->';
			$out  .= ln_section(
				ln_columns( array( ln_heading( $t[0] ), $t[1] ? ln_p( $t[1], 'ln-lede' ) : '' ), 'ln-section-head', 'bottom' )
				. $body,
				'ln-project ln-tab-panel ln-band--cream',
				$slug
			);
		}
		return do_blocks( $out );
	}
);

/* Category pages go to their tab on the shop page. */
add_action(
	'template_redirect',
	function () {
		if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
			return;
		}
		$term = get_queried_object();
		$slug = $term && ! empty( $term->slug ) ? $term->slug : '';
		$shop = wc_get_page_permalink( 'shop' );
		if ( 'mail-club' === $slug ) {
			$to = home_url( '/mail-club/' );
		} elseif ( $slug && isset( ln_shop_tabs()[ $slug ] ) ) {
			$to = $shop . '#' . $slug;
		} else {
			$to = $shop; // A category with no tab yet: show everything.
		}
		wp_safe_redirect( $to, 302 );
		exit;
	}
);
