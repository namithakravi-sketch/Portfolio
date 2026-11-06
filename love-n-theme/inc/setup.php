<?php
/**
 * Site setup, run when the theme is first active and again after a theme update:
 *  1. Creates the Home, Work, Features, Mail Club, Contact and policy pages and sets Home as the front page.
 *  2. Once WooCommerce is active, creates the shop categories and starter products (once only).
 *
 * After a theme update, a generated page is refreshed with the new design only
 * if nobody has edited it yet. Edited pages are never overwritten.
 *
 * @package love-n
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'ln_maybe_setup', 99 );

function ln_maybe_setup() {
	if ( wp_installing() ) {
		return;
	}
	if ( LOVE_N_VERSION !== get_option( 'ln_pages_done' ) && add_option( 'ln_pages_lock', time(), '', false ) ) {
		ln_setup_pages();
		update_option( 'ln_pages_done', LOVE_N_VERSION, false );
		delete_option( 'ln_pages_lock' );
	}
	if ( class_exists( 'WooCommerce' ) && ! get_option( 'ln_shop_done' ) && add_option( 'ln_shop_lock', time(), '', false ) ) {
		ln_setup_shop();
		update_option( 'ln_shop_done', LOVE_N_VERSION, false );
		delete_option( 'ln_shop_lock' );
	}
}

/**
 * Create a page, or refresh one the theme made earlier if it is still untouched.
 */
function ln_upsert_page( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	if ( ! $existing ) {
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $slug,
				'post_title'   => $title,
				'post_content' => wp_slash( $content ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			ln_store_hash( $id );
			update_post_meta( $id, '_ln_source', md5( $content ) );
		}
		return $id;
	}

	$hash      = get_post_meta( $existing->ID, '_ln_hash', true );
	$untouched = $hash
		? md5( $existing->post_content ) === $hash
		// Pages from theme 1.0 have no hash: treat them as untouched if never modified.
		: $existing->post_modified_gmt === $existing->post_date_gmt;

	if ( $untouched && md5( $content ) !== get_post_meta( $existing->ID, '_ln_source', true ) ) {
		wp_update_post(
			array(
				'ID'           => $existing->ID,
				'post_content' => wp_slash( $content ),
			)
		);
		ln_store_hash( $existing->ID );
		update_post_meta( $existing->ID, '_ln_source', md5( $content ) );
	}
	// Never change a page's status here: if someone unpublished it, that stays.
	return $existing->ID;
}

/**
 * Fingerprint the page exactly as WordPress saved it, so "untouched" checks
 * compare like with like.
 */
function ln_store_hash( $id ) {
	clean_post_cache( $id );
	update_post_meta( $id, '_ln_hash', md5( get_post_field( 'post_content', $id, 'raw' ) ) );
}

function ln_setup_pages() {
	// Setup can run during a visitor's page load; save theme pages without the
	// visitor-level HTML filter so the blocks are stored exactly as written.
	$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
	if ( $kses ) {
		kses_remove_filters();
	}
	ln_setup_pages_inner();
	if ( $kses ) {
		kses_init_filters();
	}
}

function ln_setup_pages_inner() {
	if ( ! get_option( 'ln_pages_done' ) ) {
		update_option( 'blogname', 'Namitha Raveendranathan' );
		update_option( 'blogdescription', 'Designer & digital artist · Love, N' );
	}

	$home = ln_upsert_page( 'home', 'Home', ln_page_home() );
	ln_upsert_page( 'work', 'Work', ln_page_work() );
	ln_upsert_page( 'features', 'Features', ln_page_features() );
	ln_upsert_page( 'mail-club', 'Love, N Art Mail Club', ln_page_mail_club() );
	ln_upsert_page( 'contact', 'Contact', ln_page_contact() );
	ln_upsert_page( 'about', 'About', ln_page_about() );
	foreach ( array( 'shipping-policy' => 'Shipping policy', 'refund-policy' => 'Refund policy', 'terms-of-service' => 'Terms of service' ) as $slug => $t ) {
		ln_upsert_page( $slug, $t, ln_page_policy( $t ) );
	}
	// WordPress makes a draft privacy policy on install; publish it so the footer link works.
	$privacy = (int) get_option( 'wp_page_for_privacy_policy' );
	if ( $privacy && 'draft' === get_post_status( $privacy ) ) {
		wp_update_post( array( 'ID' => $privacy, 'post_status' => 'publish' ) );
	}

	if ( $home && ! is_wp_error( $home ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
	}

	// Remove the default sample content from a fresh install.
	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $slug => $type ) {
		$p = get_page_by_path( $slug, OBJECT, $type );
		if ( $p ) {
			wp_trash_post( $p->ID );
		}
	}

	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	flush_rewrite_rules( false );
}

/** Copy a bundled image into the media library. */
function ln_import_image( $file, $title ) {
	$path = get_theme_file_path( 'assets/img/' . $file );
	if ( ! file_exists( $path ) ) {
		return 0;
	}
	$up = wp_upload_bits( 'love-n-' . $file, null, file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! empty( $up['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/jpeg',
			'post_title'     => $title,
			'post_status'    => 'inherit',
		),
		$up['file']
	);
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $up['file'] ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	return $id;
}

function ln_setup_shop() {
	$cats = array(
		'prints'    => array( 'Art prints', 'Love, N art prints on thick, textured paper, ready to frame.', 'shop-prints.jpg' ),
		'bookmarks' => array( 'Bookmarks', 'Bright illustrated bookmarks for every reader.', 'shop-bookmarks.jpg' ),
		'stickers'  => array( 'Stickers', 'Sticker sheets and die-cuts for journals, laptops and letters.', 'shop-stickers.jpg' ),
		'postcards' => array( 'Postcards', 'Postcards to send, pin up or keep.', 'shop-postcards.jpg' ),
		'mail-club' => array( 'Art Mail Club', 'Plans for the Love, N Art Mail Club.', 'shop-mail-club.jpg' ),
	);
	$ids    = array();
	$images = array();
	foreach ( $cats as $slug => $c ) {
		$term = term_exists( $slug, 'product_cat' );
		if ( ! $term ) {
			$term = wp_insert_term( $c[0], 'product_cat', array( 'slug' => $slug, 'description' => $c[1] ) );
		}
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$ids[ $slug ]    = (int) $term['term_id'];
		$images[ $slug ] = ln_import_image( $c[2], $c[0] );
		if ( $images[ $slug ] ) {
			update_term_meta( $ids[ $slug ], 'thumbnail_id', $images[ $slug ] );
		}
	}

	// Starter products. Names, prices and descriptions are placeholders to edit.
	$products = array(
		array( 'prints', 'Love, N Art Print (A4)', 'A bold, colourful art print of one of my favourite illustrations, printed on thick textured paper. Unframed.', 499 ),
		array( 'prints', 'Love, N Art Print (A5)', 'The same joyful artwork in a smaller size, perfect for desks and shelves. Unframed.', 299 ),
		array( 'bookmarks', 'Love, N Bookmark Set', 'A set of three illustrated bookmarks to keep your place in style.', 199 ),
		array( 'stickers', 'Love, N Sticker Sheet', 'A sheet of glossy illustrated stickers for journals, laptops and happy mail.', 149 ),
		array( 'postcards', 'Love, N Postcard Pack', 'A pack of five illustrated postcards to send or display.', 249 ),
		array( 'mail-club', 'Mail Club (India) · 1 month', 'One envelope of original artworks, postcards, stickers and little paper surprises. A lovely way to try the club, or to send as a gift.', 499 ),
		array( 'mail-club', 'Mail Club (India) · 3 months', 'Three monthly envelopes of original artworks, postcards, stickers and little paper surprises.', 1497 ),
		array( 'mail-club', 'Mail Club (India) · 6 months', 'Six monthly envelopes of original artworks, postcards, stickers and little paper surprises.', 2994 ),
		array( 'mail-club', 'Mail Club (International) · 1 month', 'One envelope of original artworks, postcards, stickers and little paper surprises, posted to you outside India.', 1760 ),
		array( 'mail-club', 'Mail Club (International) · 3 months', 'Three monthly envelopes of original artworks, postcards, stickers and little paper surprises, posted to you outside India.', 5280 ),
		array( 'mail-club', 'Mail Club (International) · 6 months', 'Six monthly envelopes of original artworks, postcards, stickers and little paper surprises, posted to you outside India.', 10560 ),
	);
	foreach ( $products as $order => $p ) {
		list( $cat, $name, $desc, $price ) = $p;
		$exists = get_posts(
			array(
				'post_type'   => 'product',
				'post_status' => 'any',
				'title'       => $name,
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
		if ( empty( $ids[ $cat ] ) || $exists ) {
			continue;
		}
		$product = new WC_Product_Simple();
		$product->set_name( $name );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'mail-club' === $cat ? 'hidden' : 'visible' );
		$product->set_regular_price( (string) $price );
		$product->set_short_description( $desc );
		$product->set_description( $desc . "\n\n[Add size, paper, finish and shipping details here.]" );
		$product->set_category_ids( array( $ids[ $cat ] ) );
		$product->set_menu_order( $order );
		if ( 'mail-club' === $cat ) {
			$product->update_meta_data( '_ln_region', false !== strpos( $name, 'International' ) ? 'international' : 'india' );
		}
		if ( ! empty( $images[ $cat ] ) ) {
			$product->set_image_id( $images[ $cat ] );
		}
		$product->save();
	}
}

/**
 * One-time fix (1.14.2) for a Work page already edited in WordPress.
 * In each tab (Artworks, Animation, Branding): galleries that sit next to each
 * other are merged into one, it is set to 3 columns, and placeholder tiles are
 * added until it holds 18 works (6 rows of 3). Real images are kept, in order.
 * Runs once; the previous version stays in the page's Revisions.
 */
add_action( 'init', 'ln_fill_work_galleries', 100 );

function ln_fill_work_galleries() {
	if ( wp_installing() || get_option( 'ln_work_galleries_done' ) || ! add_option( 'ln_work_galleries_lock', time(), '', false ) ) {
		return;
	}
	$page = get_page_by_path( 'work', OBJECT, 'page' );
	if ( $page ) {
		$placeholders = array(
			'artworks'  => array( array( 'illustration-1.jpg', 'illustration-2.jpg', 'illustration-3.jpg', 'illustration-4.jpg' ), '[Describe this artwork]', '[Artwork title] · [Client or personal]' ),
			'animation' => array( array( 'animation-1.jpg', 'animation-2.jpg', 'animation-3.jpg' ), '[Describe this animation still]', '[Film title] · [Client]' ),
			'branding'  => array( array( 'branding-2.jpg', 'branding-3.jpg', 'branding-1.jpg' ), '[Describe this branding image]', '[Brand name] · [Logo, illustration, packaging]' ),
		);
		$blocks  = parse_blocks( $page->post_content );
		$changed = false;
		foreach ( $blocks as &$section ) {
			$tab = $section['attrs']['anchor'] ?? '';
			if ( 'core/group' === $section['blockName'] && isset( $placeholders[ $tab ] ) ) {
				$changed = ln_fill_tab_gallery( $section, $placeholders[ $tab ], 18 ) || $changed;
			}
		}
		unset( $section );
		if ( $changed ) {
			$kses = has_filter( 'content_save_pre', 'wp_filter_post_kses' );
			if ( $kses ) {
				kses_remove_filters();
			}
			wp_update_post( array( 'ID' => $page->ID, 'post_content' => wp_slash( serialize_blocks( $blocks ) ) ) );
			if ( $kses ) {
				kses_init_filters();
			}
		}
	}
	update_option( 'ln_work_galleries_done', LOVE_N_VERSION, false );
	delete_option( 'ln_work_galleries_lock' );
}

/** Rebuild a block's markup slots: its own opening and closing HTML, one slot per inner block. */
function ln_rebuild_inner_content( array &$block, $open ) {
	$close = end( $block['innerContent'] );
	$inner = array( $open );
	foreach ( array_keys( $block['innerBlocks'] ) as $i ) {
		if ( $i ) {
			$inner[] = "\n\n";
		}
		$inner[] = null;
	}
	$inner[]               = $close;
	$block['innerContent'] = $inner;
}

/**
 * In one tab section: merge neighbouring galleries into the first, set it to
 * 3 columns and pad it with placeholder images up to $total. Returns true if
 * anything changed. A tab with no gallery is left alone.
 */
function ln_fill_tab_gallery( array &$section, array $ph, $total ) {
	$kept    = array();
	$merged  = false;
	foreach ( $section['innerBlocks'] as $block ) {
		$last = count( $kept ) - 1;
		if ( 'core/gallery' === $block['blockName'] && $last >= 0 && 'core/gallery' === $kept[ $last ]['blockName'] ) {
			$kept[ $last ]['innerBlocks'] = array_merge( $kept[ $last ]['innerBlocks'], $block['innerBlocks'] );
			$merged                       = true;
			continue;
		}
		$kept[] = $block;
	}

	$changed = $merged;
	foreach ( $kept as &$block ) {
		if ( 'core/gallery' !== $block['blockName'] ) {
			continue;
		}
		$n = count( $block['innerBlocks'] );
		for ( $i = $n; $i < $total; $i++ ) {
			$file   = $ph[0][ ( $i - $n ) % count( $ph[0] ) ];
			$parsed = parse_blocks( ln_image( $file, $ph[1], '', $ph[2], true ) );
			foreach ( $parsed as $img ) {
				if ( 'core/image' === $img['blockName'] ) {
					$block['innerBlocks'][] = $img;
				}
			}
		}
		$cols = (int) ( $block['attrs']['columns'] ?? 0 );
		if ( $merged || $n < $total || 3 !== $cols ) {
			$block['attrs']['columns'] = 3;
			ln_rebuild_inner_content( $block, preg_replace( '/\bcolumns-\d+\b/', 'columns-3', $block['innerContent'][0] ) );
			$changed = true;
		}
		break; // only the first gallery in a tab
	}
	unset( $block );

	if ( $merged ) {
		$section['innerBlocks'] = $kept;
		ln_rebuild_inner_content( $section, $section['innerContent'][0] );
	} elseif ( $changed ) {
		$section['innerBlocks'] = $kept;
	}
	return $changed;
}
