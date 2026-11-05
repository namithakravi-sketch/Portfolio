<?php
/**
 * Love, N theme bootstrap.
 *
 * @package love-n
 */

defined( 'ABSPATH' ) || exit;

define( 'LOVE_N_VERSION', '1.0.0' );

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
	}
);

/* Show four products per row in classic WooCommerce loops. */
add_filter( 'loop_shop_columns', fn() => 4 );
