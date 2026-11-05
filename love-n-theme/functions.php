<?php
/**
 * Love, N theme bootstrap.
 *
 * @package love-n
 */

defined( 'ABSPATH' ) || exit;

define( 'LOVE_N_VERSION', '1.7.0' );

require_once get_theme_file_path( 'inc/blocks.php' );
require_once get_theme_file_path( 'inc/forms.php' );
require_once get_theme_file_path( 'inc/content.php' );
require_once get_theme_file_path( 'inc/setup.php' );

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
		add_editor_style( 'style.css' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'love-n', get_stylesheet_uri(), array(), LOVE_N_VERSION );
		wp_enqueue_script( 'love-n-protect', get_theme_file_uri( 'assets/js/protect.js' ), array(), LOVE_N_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_enqueue_script( 'love-n-reveal', get_theme_file_uri( 'assets/js/reveal.js' ), array(), LOVE_N_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_enqueue_script( 'love-n-nav', get_theme_file_uri( 'assets/js/nav.js' ), array(), LOVE_N_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_enqueue_script( 'love-n-tabs', get_theme_file_uri( 'assets/js/tabs.js' ), array(), LOVE_N_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
);

/* Show four products per row in classic WooCommerce loops. */
add_filter( 'loop_shop_columns', fn() => 4 );

/*
 * Underline the menu item for the page you're on. The header menu uses plain
 * links, which WordPress doesn't mark as current, so compare paths here.
 */
add_filter(
	'render_block_core/navigation-link',
	function ( $html, $block ) {
		$url = $block['attrs']['url'] ?? '';
		if ( ! $url ) {
			return $html;
		}
		$here   = trailingslashit( wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH ) ?: '/' );
		$target = trailingslashit( wp_parse_url( $url, PHP_URL_PATH ) ?: '/' );
		$match  = '/' === $target ? '/' === $here : 0 === strpos( $here, $target );
		// The shop's category and product pages count as "Shop".
		if ( ! $match && '/shop/' === $target && function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
			$match = true;
		}
		if ( $match ) {
			$html = preg_replace( '/class="wp-block-navigation-item /', 'class="wp-block-navigation-item current-menu-item ', $html, 1 );
			$html = preg_replace( '/<a /', '<a aria-current="page" ', $html, 1 );
		}
		return $html;
	},
	10,
	2
);

/* Use the device's own emoji instead of WordPress's emoji images. */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/* Pages that don't show the "Letters from the studio" sign-up band. */
add_filter(
	'render_block_core/group',
	function ( $html, $block ) {
		$class = $block['attrs']['className'] ?? '';
		if ( false !== strpos( $class, 'ln-signup-band' ) && is_page( array( 'work' ) ) ) {
			return '';
		}
		return $html;
	},
	10,
	2
);

/*
 * While WooCommerce's "coming soon" mode hides the store from visitors, it also
 * removes the cart icon from the header. Show a plain cart icon in its place so
 * the header looks the same for everyone; it links to the cart page.
 */
function ln_cart_fallback_icon() {
	$url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	return '<a class="ln-cart-fallback" href="' . esc_url( $url ) . '" aria-label="' . esc_attr__( 'Cart', 'love-n' ) . '">'
		. '<svg viewBox="0 0 32 32" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
		. '<path d="M4 6h3.5l3 14h13.5l3-10H9.2"/><circle cx="12.5" cy="25" r="1.6"/><circle cx="22.5" cy="25" r="1.6"/></svg></a>';
}
add_filter(
	'render_block_woocommerce/mini-cart',
	function ( $html ) {
		return '' === trim( (string) $html ) ? ln_cart_fallback_icon() : $html;
	},
	PHP_INT_MAX
);
add_filter(
	'render_block_core/group',
	function ( $html, $block ) {
		// If WooCommerce skipped the cart block entirely, add the icon to the header icons group.
		$class = $block['attrs']['className'] ?? '';
		if ( false !== strpos( $class, 'ln-icons' ) && false === strpos( $html, 'wc-block-mini-cart' ) && false === strpos( $html, 'ln-cart-fallback' ) ) {
			$html = preg_replace( '#</div>\s*$#', ln_cart_fallback_icon() . '</div>', $html, 1 );
		}
		return $html;
	},
	PHP_INT_MAX,
	2
);

/* "← Back" link under the header on every page except the home page. */
add_filter(
	'render_block_core/template-part',
	function ( $html, $block ) {
		if ( 'header' !== ( $block['attrs']['slug'] ?? '' ) || is_front_page() || is_admin() ) {
			return $html;
		}
		$in_shop  = function_exists( 'is_product' ) && ( is_product() || is_product_category() || is_cart() || is_checkout() );
		$fallback = $in_shop && function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
		return $html . '<nav class="ln-back" aria-label="' . esc_attr__( 'Back', 'love-n' ) . '"><a href="' . esc_url( $fallback ) . '" data-ln-back>'
			. '<span class="ln-back__arrow" aria-hidden="true">←</span> ' . esc_html__( 'Back', 'love-n' ) . '</a></nav>';
	},
	10,
	2
);
