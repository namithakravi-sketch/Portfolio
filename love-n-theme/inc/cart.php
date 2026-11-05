<?php
/**
 * Cart drawer and local-currency price hints.
 *
 * The header cart icon opens a drawer built by the theme on top of the
 * WooCommerce Store API. WooCommerce's own mini-cart block is not used: it is
 * left out of the page while the store is in "coming soon" mode, which left
 * the icon as a plain link with no drawer.
 *
 * Prices are charged in INR. Visitors outside India also see an approximate
 * amount in their own currency, using daily exchange rates cached here.
 *
 * @package love-n
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Exchange rates (1 INR = x) ---------- */

/** Currencies a visitor can choose to see prices in. */
function ln_fx_currencies() {
	return array( 'INR', 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'NZD', 'SGD', 'AED', 'SAR', 'QAR', 'KWD', 'OMR', 'BHD', 'JPY', 'CHF', 'SEK', 'NOK', 'DKK', 'PLN', 'CZK', 'HUF', 'RON', 'TRY', 'MYR', 'HKD', 'THB', 'IDR', 'PHP', 'KRW', 'CNY', 'TWD', 'LKR', 'NPR', 'BDT', 'PKR', 'ILS', 'ZAR', 'NGN', 'KES', 'EGP', 'MXN', 'BRL' );
}

/**
 * Rates from INR, refreshed twice a day. Falls back to the last good set if
 * the rate services can't be reached, and to nothing (no hint shown) if there
 * has never been one.
 */
function ln_fx_rates() {
	$cached = get_transient( 'ln_fx_rates' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$sources = array(
		'https://open.er-api.com/v6/latest/INR',      // Broad list, incl. Gulf currencies.
		'https://api.frankfurter.app/latest?from=INR', // ECB rates, fewer currencies.
	);
	$rates = array();
	foreach ( $sources as $url ) {
		$res = wp_remote_get( $url, array( 'timeout' => 5 ) );
		if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
			continue;
		}
		$data = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( ! empty( $data['rates'] ) && is_array( $data['rates'] ) ) {
			$rates = $data['rates'];
			break;
		}
	}
	$keep = array();
	foreach ( ln_fx_currencies() as $code ) {
		if ( 'INR' === $code ) {
			$keep['INR'] = 1;
		} elseif ( isset( $rates[ $code ] ) && is_numeric( $rates[ $code ] ) && $rates[ $code ] > 0 ) {
			$keep[ $code ] = (float) $rates[ $code ];
		}
	}
	if ( count( $keep ) > 1 ) {
		set_transient( 'ln_fx_rates', $keep, 12 * HOUR_IN_SECONDS );
		update_option( 'ln_fx_rates_last', $keep, false );
		return $keep;
	}
	$last = get_option( 'ln_fx_rates_last' );
	$last = is_array( $last ) ? $last : array();
	set_transient( 'ln_fx_rates', $last, HOUR_IN_SECONDS ); // Try again in an hour.
	return $last;
}

/* ---------- Scripts ---------- */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_script( 'love-n-fx', get_theme_file_uri( 'assets/js/fx.js' ), array(), LOVE_N_VERSION, array( 'in_footer' => true ) );
		wp_add_inline_script( 'love-n-fx', 'window.lnFxRates = ' . wp_json_encode( (object) ln_fx_rates() ) . ';', 'before' );
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		wp_enqueue_script( 'love-n-cart', get_theme_file_uri( 'assets/js/cart-drawer.js' ), array( 'love-n-fx' ), LOVE_N_VERSION, array( 'in_footer' => true ) );
		wp_localize_script(
			'love-n-cart',
			'lnCartConfig',
			array(
				'api'         => esc_url_raw( rest_url( 'wc/store/v1/' ) ),
				'cartUrl'     => wc_get_cart_url(),
				'checkoutUrl' => wc_get_checkout_url(),
			)
		);
	}
);

/* ---------- Header icon ---------- */
function ln_cart_icon() {
	$url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
	return '<a class="ln-cart-fallback" href="' . esc_url( $url ) . '" data-ln-cart-open aria-label="' . esc_attr__( 'Cart', 'love-n' ) . '" aria-controls="ln-cart-drawer" aria-expanded="false">'
		. '<svg viewBox="0 0 32 32" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
		. '<path d="M4 6h3.5l3 14h13.5l3-10H9.2"/><circle cx="12.5" cy="25" r="1.6"/><circle cx="22.5" cy="25" r="1.6"/></svg>'
		. '<span class="ln-cart-count" data-ln-cart-count hidden></span></a>';
}

// Always use the theme's icon in place of WooCommerce's mini-cart block.
add_filter( 'render_block_woocommerce/mini-cart', 'ln_cart_icon', PHP_INT_MAX );

add_filter(
	'render_block_core/group',
	function ( $html, $block ) {
		// If WooCommerce skipped the cart block entirely, add the icon to the header icons group.
		$class = $block['attrs']['className'] ?? '';
		if ( false !== strpos( $class, 'ln-icons' ) && false === strpos( $html, 'ln-cart-fallback' ) ) {
			$html = preg_replace( '#</div>\s*$#', ln_cart_icon() . '</div>', $html, 1 );
		}
		return $html;
	},
	PHP_INT_MAX,
	2
);

/* ---------- Drawer ---------- */
add_action(
	'wp_footer',
	function () {
		if ( ! class_exists( 'WooCommerce' ) || is_cart() || is_checkout() ) {
			return;
		}
		?>
		<div class="ln-drawer" id="ln-cart-drawer" hidden>
			<div class="ln-drawer__backdrop" data-ln-cart-close></div>
			<aside class="ln-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="ln-drawer-title" tabindex="-1">
				<div class="ln-drawer__head">
					<h2 class="ln-drawer__title" id="ln-drawer-title"><?php esc_html_e( 'Your cart', 'love-n' ); ?></h2>
					<button type="button" class="ln-drawer__close" data-ln-cart-close aria-label="<?php esc_attr_e( 'Close cart', 'love-n' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>
					</button>
				</div>
				<p class="ln-drawer__msg" data-ln-cart-msg role="status" hidden></p>
				<div class="ln-drawer__body" data-ln-cart-body></div>
				<div class="ln-drawer__foot" data-ln-cart-foot hidden>
					<p class="ln-drawer__total"><span><?php esc_html_e( 'Subtotal', 'love-n' ); ?></span><strong data-ln-cart-total></strong></p>
					<p class="ln-drawer__fx" data-ln-cart-fx hidden></p>
					<p class="ln-drawer__note"><?php esc_html_e( 'Tracked post and any taxes are shown at checkout.', 'love-n' ); ?></p>
					<div class="ln-drawer__actions">
						<a class="wp-element-button ln-drawer__btn ln-drawer__btn--outline" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View cart', 'love-n' ); ?></a>
						<a class="wp-element-button ln-drawer__btn" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'love-n' ); ?></a>
					</div>
				</div>
			</aside>
		</div>
		<?php
	}
);
