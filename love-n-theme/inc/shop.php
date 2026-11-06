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
		'all' => array( 'Shop all', 'Everything in the shop, illustrated and packed by hand in my studio, with new little things added along the way.' ),
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
			// Heading and intro share one width, so the intro lines up with the end of the heading.
			. ln_group(
				ln_heading( 'Little things, <em class="ln-accent">made with love</em>', 1 )
				. ln_p( 'Art prints, postcards, bookmarks, stickers and many more little paper treasures, made to keep, frame or send to someone you love.', 'ln-lede' ),
				'ln-fit'
			)
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

/* ---------- Product page: [ln_product] ----------
 * Same layout as the Mail Club page: photos on one side (main photo plus two
 * more beneath it), title, price, quantity, Add to cart and the description
 * on the other, then related products.
 */
function ln_product_thumbs( $product ) {
	$ids    = array_slice( $product->get_gallery_image_ids(), 0, 2 );
	$thumbs = '';
	foreach ( $ids as $id ) {
		$thumbs .= wp_get_attachment_image( $id, 'large', false, array( 'loading' => 'lazy' ) );
	}
	// Placeholders until two more photos are added under "Product gallery".
	$fallback = array( 'illustration-1.jpg', 'illustration-2.jpg' );
	for ( $i = count( $ids ); $i < 2; $i++ ) {
		$thumbs .= '<img src="' . esc_url( ln_img_url( $fallback[ $i ] ) ) . '" alt="" loading="lazy">';
	}
	return $thumbs;
}

add_shortcode(
	'ln_product',
	function () {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return '';
		}
		$product = wc_get_product( get_queried_object_id() );
		if ( ! $product ) {
			return '';
		}
		$GLOBALS['product'] = $product; // WooCommerce's add-to-cart and related products read this.
		$id    = $product->get_id();
		$main  = $product->get_image_id()
			? wp_get_attachment_image( $product->get_image_id(), 'large', false, array( 'alt' => $product->get_name() ) )
			: '<img src="' . esc_url( wc_placeholder_img_src( 'large' ) ) . '" alt="">';
		$media = '<div class="ln-club-product__media">' . $main . '<div class="ln-club-product__thumbs">' . ln_product_thumbs( $product ) . '</div></div>';

		ob_start();
		?>
		<div class="ln-club-product ln-product">
			<?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="ln-club-product__info">
				<h1 class="ln-club-product__title"><?php echo esc_html( $product->get_name() ); ?></h1>
				<p class="ln-club-product__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
				<?php if ( $product->get_short_description() ) : ?>
					<div class="ln-plan-desc"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
				<?php endif; ?>

				<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) : ?>
					<form class="ln-product-form" method="get" action="<?php echo esc_url( wc_get_checkout_url() ); ?>">
						<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $id ); ?>">
						<div class="ln-qty">
							<label for="ln-qty-<?php echo esc_attr( $id ); ?>">Quantity</label>
							<div class="ln-qty__box">
								<button type="button" class="ln-qty__btn" data-step="-1" aria-label="Decrease quantity">−</button>
								<input id="ln-qty-<?php echo esc_attr( $id ); ?>" type="number" name="quantity" value="1" min="1" max="<?php echo esc_attr( $product->get_max_purchase_quantity() > 0 ? $product->get_max_purchase_quantity() : 99 ); ?>" step="1" inputmode="numeric">
								<button type="button" class="ln-qty__btn" data-step="1" aria-label="Increase quantity">+</button>
							</div>
						</div>
						<div class="ln-club-actions">
							<button type="submit" class="wp-element-button ln-btn-outline" data-ln-add="<?php echo esc_attr( $id ); ?>" formaction="<?php echo esc_url( wc_get_cart_url() ); ?>">Add to cart</button>
							<button type="submit" class="wp-element-button">Buy it now</button>
						</div>
					</form>
				<?php elseif ( ! $product->is_in_stock() ) : ?>
					<p class="ln-note">Sold out for now. More are on the way.</p>
				<?php else : ?>
					<div class="ln-product-cart"><?php woocommerce_template_single_add_to_cart(); ?></div>
				<?php endif; ?>

				<?php if ( $product->get_description() ) : ?>
					<div class="ln-product-desc">
						<h2 class="ln-product-desc__title">Description</h2>
						<?php echo wp_kses_post( wpautop( do_shortcode( $product->get_description() ) ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
		$out = ob_get_clean();

		$related = do_shortcode( '[related_products limit="4" columns="4"]' );
		if ( false !== strpos( $related, 'type-product' ) ) {
			$out .= '<div class="ln-related">' . $related . '</div>';
		}
		return $out;
	}
);

// Related products: fill up to four with other shop products when a category has fewer.
add_filter(
	'woocommerce_related_products',
	function ( $ids, $product_id, $args ) {
		$want = 4;
		if ( count( $ids ) >= $want ) {
			return $ids;
		}
		$more = wc_get_products(
			array(
				'status'     => 'publish',
				'visibility' => 'catalog',
				'limit'      => $want + 1,
				'orderby'    => 'menu_order',
				'order'      => 'ASC',
				'exclude'    => array_merge( array( $product_id ), $ids ),
				'return'     => 'ids',
			)
		);
		return array_slice( array_values( array_unique( array_merge( $ids, $more ) ) ), 0, $want );
	},
	10,
	3
);
add_filter( 'woocommerce_output_related_products_args', fn( $a ) => array_merge( $a, array( 'posts_per_page' => 4, 'columns' => 4 ) ) );
add_filter( 'woocommerce_product_related_products_heading', fn() => 'You may also like' );

/*
 * Starter product descriptions: replace the theme's placeholder text (only if it
 * hasn't been edited) with a description in the shop's format. Runs once.
 */
add_action(
	'init',
	function () {
		if ( ! function_exists( 'wc_get_products' ) || get_option( 'ln_desc_done' ) ) {
			return;
		}
		update_option( 'ln_desc_done', 1, false );
		$row  = fn( $label, $text ) => "<p><strong>{$label}:</strong> {$text}</p>\n";
		$desc = array(
			'prints'    => "<p><strong>Each art print includes:</strong></p>\n"
				. $row( 'Quality paper', 'Thick [300 GSM], acid-free textured paper that holds colour beautifully, so every detail looks just like the original artwork.' )
				. $row( 'Size', '5 × 7 inches, a standard frame size, so it’s easy to find the perfect frame.' )
				. $row( 'Ready to frame', 'Sent unframed, packed flat with a sturdy backing board so it reaches you in perfect condition.' )
				. $row( 'Made to last', 'Printed with archival inks that won’t fade, for a little colour on your wall for years to come.' ),
			'postcards' => "<p><strong>Each set of postcards includes:</strong></p>\n"
				. $row( 'Quality paper', 'Thick [350 GSM], acid-free card that absorbs colour beautifully, for vivid colours and fine details without warping or bleeding.' )
				. $row( 'Size', '5 × 7 inches, fitting neatly into a standard envelope.' )
				. $row( 'Pre-printed backs', 'Each postcard has a printed back with space for a message, address and stamp, so it’s easy to send to your near and dear ones.' )
				. $row( 'Durable and sturdy', 'Designed to travel through the post without getting damaged.' ),
			'bookmarks' => "<p><strong>Each bookmark set includes:</strong></p>\n"
				. $row( 'Quality card', 'Thick [350 GSM] card with a smooth finish that feels lovely in your hand.' )
				. $row( 'Size', '[2 × 7] inches, slim enough for any book.' )
				. $row( 'Illustrated on both sides', 'A little piece of art to keep your place, in every book you read.' )
				. $row( 'Made to last', 'Sturdy enough to be used again and again.' ),
			'stickers'  => "<p><strong>Each sticker sheet includes:</strong></p>\n"
				. $row( 'Quality stickers', '[Matte] vinyl stickers with bright, crisp colours.' )
				. $row( 'Size', '5 × 7 inch sheet.' )
				. $row( 'Easy to peel', 'Kiss-cut around each design, so they lift off cleanly.' )
				. $row( 'For everywhere', 'Journals, laptops, letters and happy mail.' ),
		);
		foreach ( $desc as $cat => $html ) {
			foreach ( wc_get_products( array( 'category' => array( $cat ), 'limit' => -1, 'status' => array( 'publish', 'draft', 'private' ) ) ) as $p ) {
				if ( false !== strpos( $p->get_description(), '[Add size, paper, finish and shipping details here.]' ) ) {
					$p->set_description( $html );
					$p->save();
				}
			}
		}
	},
	20
);
