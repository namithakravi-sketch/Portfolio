<?php
/**
 * Contact form, email sign-up and Mail Club plan cards.
 *
 * Messages and sign-ups are saved in WordPress (Love, N menu in the admin)
 * as well as emailed, so nothing is lost if the server's mail fails.
 *
 * @package love-n
 */

defined( 'ABSPATH' ) || exit;

/* ---------- Storage ---------- */
add_action(
	'init',
	function () {
		$common = array(
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'love-n',
			'exclude_from_search' => true,
			'supports'            => array( 'title', 'custom-fields' ),
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
		);
		register_post_type(
			'ln_subscriber',
			$common + array(
				'labels' => array(
					'name'          => 'Subscribers',
					'singular_name' => 'Subscriber',
					'all_items'     => 'Subscribers',
				),
			)
		);
		register_post_type(
			'ln_message',
			$common + array(
				'labels' => array(
					'name'          => 'Messages',
					'singular_name' => 'Message',
					'all_items'     => 'Messages',
				),
				'supports' => array( 'title', 'editor' ),
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_menu_page( 'Love, N', 'Love, N', 'edit_posts', 'love-n', '__return_null', 'dashicons-heart', 26 );
		add_submenu_page( 'love-n', 'Export subscribers', 'Export subscribers (CSV)', 'manage_options', 'love-n-export', 'ln_export_page' );
	}
);

/* Show where each subscriber signed up. */
add_filter(
	'manage_ln_subscriber_posts_columns',
	function ( $cols ) {
		$cols['ln_source'] = 'Signed up from';
		return $cols;
	}
);
add_action(
	'manage_ln_subscriber_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'ln_source' === $col ) {
			echo esc_html( get_post_meta( $post_id, 'ln_source', true ) );
		}
	},
	10,
	2
);

function ln_export_page() {
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=ln_export' ), 'ln_export' );
	echo '<div class="wrap"><h1>Export subscribers</h1><p>Download every email sign-up (newsletter and Mail Club mail list) as a CSV file you can import into your email tool.</p>';
	echo '<p><a class="button button-primary" href="' . esc_url( $url ) . '">Download CSV</a></p></div>';
}

add_action(
	'admin_post_ln_export',
	function () {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'ln_export' ) ) {
			wp_die( 'Not allowed.' );
		}
		$subs = get_posts(
			array(
				'post_type'      => 'ln_subscriber',
				'posts_per_page' => -1,
				'post_status'    => 'private',
			)
		);
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=love-n-subscribers.csv' );
		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'email', 'name', 'source', 'signed_up' ) );
		foreach ( $subs as $s ) {
			fputcsv( $out, array( $s->post_title, get_post_meta( $s->ID, 'ln_name', true ), get_post_meta( $s->ID, 'ln_source', true ), $s->post_date ) );
		}
		fclose( $out );
		exit;
	}
);

/* ---------- Helpers ---------- */
function ln_redirect_back( $args, $anchor ) {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'ln_signup', 'ln_contact', 'ln_src' ), $back );
	$back = preg_replace( '/#.*$/', '', $back );
	wp_safe_redirect( add_query_arg( $args, $back ) . '#' . $anchor );
	exit;
}

function ln_is_spam() {
	// Honeypot field must stay empty; humans take more than 3 seconds.
	if ( ! empty( $_POST['ln_website'] ) ) {
		return true;
	}
	$t = isset( $_POST['ln_t'] ) ? (int) $_POST['ln_t'] : 0;
	return $t && ( time() - $t ) < 3;
}

function ln_spam_fields() {
	return '<div class="ln-hp" aria-hidden="true"><label>Leave this empty<input type="text" name="ln_website" tabindex="-1" autocomplete="off"></label></div>'
		. '<input type="hidden" name="ln_t" value="' . esc_attr( time() ) . '">';
}

/* ---------- Form handling ---------- */
add_action(
	'init',
	function () {
		if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || empty( $_POST['ln_form'] ) ) {
			return;
		}
		$form = sanitize_key( wp_unslash( $_POST['ln_form'] ) );

		if ( 'signup' === $form ) {
			$src = sanitize_key( wp_unslash( $_POST['ln_source'] ?? 'newsletter' ) );
			if ( ! wp_verify_nonce( $_POST['_ln_nonce'] ?? '', 'ln_signup' ) ) {
				ln_redirect_back( array( 'ln_signup' => 'expired', 'ln_src' => $src ), 'ln-signup-' . $src );
			}
			if ( ln_is_spam() ) {
				ln_redirect_back( array( 'ln_signup' => 'ok', 'ln_src' => $src ), 'ln-signup-' . $src );
			}
			$email = sanitize_email( wp_unslash( $_POST['ln_email'] ?? '' ) );
			$name  = sanitize_text_field( wp_unslash( $_POST['ln_name'] ?? '' ) );
			if ( ! is_email( $email ) ) {
				ln_redirect_back( array( 'ln_signup' => 'bad_email', 'ln_src' => $src ), 'ln-signup-' . $src );
			}
			if ( empty( $_POST['ln_consent'] ) ) {
				ln_redirect_back( array( 'ln_signup' => 'consent', 'ln_src' => $src ), 'ln-signup-' . $src );
			}
			$exists = get_posts(
				array(
					'post_type'   => 'ln_subscriber',
					'post_status' => 'private',
					'title'       => $email,
					'meta_key'    => 'ln_source',
					'meta_value'  => $src,
					'fields'      => 'ids',
					'numberposts' => 1,
				)
			);
			if ( ! $exists ) {
				$id = wp_insert_post(
					array(
						'post_type'   => 'ln_subscriber',
						'post_status' => 'private',
						'post_title'  => $email,
					)
				);
				if ( $id && ! is_wp_error( $id ) ) {
					update_post_meta( $id, 'ln_source', $src );
					update_post_meta( $id, 'ln_name', $name );
				}
				wp_mail(
					get_option( 'admin_email' ),
					'mail-club' === $src ? 'New Mail Club mail list sign-up' : 'New email sign-up',
					sprintf( "%s%s signed up from: %s\n\nSee all sign-ups: %s", $email, $name ? " ({$name})" : '', $src, admin_url( 'edit.php?post_type=ln_subscriber' ) )
				);
			}
			ln_redirect_back( array( 'ln_signup' => 'ok', 'ln_src' => $src ), 'ln-signup-' . $src );
		}

		if ( 'contact' === $form ) {
			if ( ! wp_verify_nonce( $_POST['_ln_nonce'] ?? '', 'ln_contact' ) ) {
				ln_redirect_back( array( 'ln_contact' => 'expired' ), 'ln-contact' );
			}
			if ( ln_is_spam() ) {
				ln_redirect_back( array( 'ln_contact' => 'ok' ), 'ln-contact' );
			}
			$name    = sanitize_text_field( wp_unslash( $_POST['ln_name'] ?? '' ) );
			$email   = sanitize_email( wp_unslash( $_POST['ln_email'] ?? '' ) );
			$topic   = sanitize_text_field( wp_unslash( $_POST['ln_topic'] ?? '' ) );
			$message = sanitize_textarea_field( wp_unslash( $_POST['ln_message'] ?? '' ) );
			if ( ! $name || ! is_email( $email ) || ! $message ) {
				ln_redirect_back( array( 'ln_contact' => 'missing' ), 'ln-contact' );
			}
			wp_insert_post(
				array(
					'post_type'    => 'ln_message',
					'post_status'  => 'private',
					'post_title'   => sprintf( '%s · %s', $topic ? $topic : 'Message', $name ),
					'post_content' => sprintf( "From: %s <%s>\nTopic: %s\n\n%s", $name, $email, $topic, $message ),
				)
			);
			wp_mail(
				get_option( 'admin_email' ),
				sprintf( '[%s] %s from %s', get_bloginfo( 'name' ), $topic ? $topic : 'Message', $name ),
				sprintf( "From: %s <%s>\nTopic: %s\n\n%s", $name, $email, $topic, $message ),
				array( 'Reply-To: ' . $name . ' <' . $email . '>' )
			);
			ln_redirect_back( array( 'ln_contact' => 'ok' ), 'ln-contact' );
		}
	}
);

/* ---------- [ln_signup source="newsletter" button="Sign me up" name="no"] ---------- */
add_shortcode(
	'ln_signup',
	function ( $atts ) {
		$a   = shortcode_atts(
			array(
				'source' => 'newsletter',
				'button' => 'Sign me up',
				'name'   => 'no',
			),
			$atts
		);
		$src = sanitize_key( $a['source'] );
		$msg = '';
		if ( isset( $_GET['ln_signup'], $_GET['ln_src'] ) && sanitize_key( $_GET['ln_src'] ) === $src ) {
			$state = sanitize_key( $_GET['ln_signup'] );
			$map   = array(
				'ok'        => array( 'ok', 'mail-club' === $src ? 'You’re on the Mail Club mail list! I’ll email you when new club themes are on their way.' : 'Thank you! You’re signed up. Look out for some colour in your inbox.' ),
				'bad_email' => array( 'err', 'That email address doesn’t look quite right. Could you check it?' ),
				'consent'   => array( 'err', 'Please tick the box so I know it’s OK to email you.' ),
				'expired'   => array( 'err', 'The form timed out. Please try again.' ),
			);
			if ( isset( $map[ $state ] ) ) {
				$msg = '<p class="ln-alert ln-alert--' . $map[ $state ][0] . '" role="status">' . esc_html( $map[ $state ][1] ) . '</p>';
			}
		}
		$id = 'ln-signup-' . $src;
		ob_start();
		?>
		<div class="ln-signup ln-signup-inline" id="<?php echo esc_attr( $id ); ?>">
			<?php echo $msg; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<form class="ln-form" method="post" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php if ( 'yes' === $a['name'] ) : ?>
					<label>Your first name<input type="text" name="ln_name" autocomplete="given-name"></label>
				<?php endif; ?>
				<label>Email address<input type="email" name="ln_email" required autocomplete="email" placeholder="you@example.com"></label>
				<button class="wp-element-button" type="submit"><?php echo esc_html( $a['button'] ); ?></button>
				<label class="ln-check"><input type="checkbox" name="ln_consent" value="1" required> <span>Yes, email me <?php echo 'mail-club' === $src ? 'about new club themes' : 'new art and shop news'; ?>. I can unsubscribe any time.</span></label>
				<input type="hidden" name="ln_form" value="signup">
				<input type="hidden" name="ln_source" value="<?php echo esc_attr( $src ); ?>">
				<?php wp_nonce_field( 'ln_signup', '_ln_nonce', false ); ?>
				<?php echo ln_spam_fields(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
);

/* ---------- [ln_contact] ---------- */
add_shortcode(
	'ln_contact',
	function () {
		$msg = '';
		if ( isset( $_GET['ln_contact'] ) ) {
			$map   = array(
				'ok'      => array( 'ok', 'Thank you! Your message is on its way. I usually reply within a few days.' ),
				'missing' => array( 'err', 'Please add your name, a valid email and a message.' ),
				'expired' => array( 'err', 'The form timed out. Please try again.' ),
			);
			$state = sanitize_key( $_GET['ln_contact'] );
			if ( isset( $map[ $state ] ) ) {
				$msg = '<p class="ln-alert ln-alert--' . $map[ $state ][0] . '" role="status">' . esc_html( $map[ $state ][1] ) . '</p>';
			}
		}
		$topics = array( 'Commission or illustration project', 'Animation project', 'Branding project', 'Press or feature', 'Shop order', 'Mail Club', 'Something else' );
		ob_start();
		?>
		<div class="ln-contact" id="ln-contact">
			<?php echo $msg; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<form class="ln-form" method="post" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<div class="ln-form__row">
					<label>Your name<input type="text" name="ln_name" required autocomplete="name"></label>
					<label>Email address<input type="email" name="ln_email" required autocomplete="email"></label>
				</div>
				<label>What’s it about?
					<select name="ln_topic">
						<?php foreach ( $topics as $t ) : ?>
							<option><?php echo esc_html( $t ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>Message<textarea name="ln_message" required placeholder="Tell me a little about your idea, timeline and budget."></textarea></label>
				<input type="hidden" name="ln_form" value="contact">
				<?php wp_nonce_field( 'ln_contact', '_ln_nonce', false ); ?>
				<?php echo ln_spam_fields(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<button class="wp-element-button" type="submit">Send message</button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}
);

/* ---------- [ln_mail_club_plans] ----------
 * Product-style picker: photo, title, price, "Delivering to" switch
 * (India / International), plan pills and one "Join" button.
 * Each plan is a simple product in the "Art Mail Club" category with a
 * _ln_region meta of "india" (default) or "international"; the button adds
 * the chosen one to the cart and goes straight to checkout.
 */
function ln_club_region( $product ) {
	$r = $product ? $product->get_meta( '_ln_region' ) : '';
	return 'international' === $r ? 'international' : 'india';
}

add_shortcode(
	'ln_mail_club_plans',
	function () {
		$img   = esc_url( ln_img_url( 'shop-mail-club.jpg' ) );
		$title = 'Love, N Art Mail Club 💌';
		// Main photo plus a 2×2 grid of extra photos (envelope contents) beneath it.
		$thumbs = '';
		foreach ( array( 'shop-prints.jpg' => 'An art print from the club', 'shop-postcards.jpg' => 'Club postcards', 'shop-stickers.jpg' => 'Club stickers', 'shop-bookmarks.jpg' => 'A club bookmark' ) as $file => $alt ) {
			$thumbs .= '<img src="' . esc_url( ln_img_url( $file ) ) . '" alt="' . esc_attr( $alt ) . '" loading="lazy">';
		}
		$media = '<div class="ln-club-product__media"><img src="' . $img . '" alt="What arrives in a Love, N Art Mail Club envelope"><div class="ln-club-product__thumbs">' . $thumbs . '</div></div>';

		$products = class_exists( 'WooCommerce' ) ? wc_get_products(
			array(
				'category' => array( 'mail-club' ),
				'status'   => 'publish',
				'orderby'  => 'menu_order',
				'order'    => 'ASC',
				'limit'    => 12,
			)
		) : array();
		$products = array_values( array_filter( $products, fn( $p ) => $p->is_purchasable() && $p->is_in_stock() ) );

		if ( ! $products ) {
			return '<div class="ln-club-product">' . $media . '<div><h1 class="ln-club-product__title">' . esc_html( $title ) . '</h1>'
				. '<p class="ln-plans-empty">New club plans are coming soon. Join the mail list below and you’ll be the first to know.</p></div></div>';
		}

		$regions = array(
			'india'         => array( 'label' => 'India', 'plans' => array() ),
			'international' => array( 'label' => 'International', 'plans' => array() ),
		);
		foreach ( $products as $p ) {
			$regions[ ln_club_region( $p ) ]['plans'][] = $p;
		}
		$regions = array_filter( $regions, fn( $r ) => $r['plans'] );
		$first   = reset( $regions )['plans'][0];

		// Short labels for the pills: "Mail Club (India) · 3 months" → "3 months".
		$label = function ( $p ) {
			$parts = explode( '·', $p->get_name() );
			return trim( end( $parts ) );
		};

		ob_start();
		?>
		<div class="ln-club-product">
			<?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<form class="ln-club-product__info" method="get" action="<?php echo esc_url( wc_get_checkout_url() ); ?>">
				<h1 class="ln-club-product__title"><?php echo esc_html( $title ); ?></h1>
				<p class="ln-club-product__price" data-ln-price><?php echo wp_kses_post( $first->get_price_html() ); ?></p>
				<p class="ln-club-product__small">Postage included in India and worldwide · Tracked post available at checkout · No automatic renewals.</p>
				<p class="ln-club-product__hint">Choose your location to see subscription plans and shipping-inclusive prices.</p>

				<?php if ( count( $regions ) > 1 ) : ?>
				<fieldset class="ln-region">
					<legend>Delivering to</legend>
					<div class="ln-pills">
						<?php $i = 0; foreach ( $regions as $key => $r ) : ?>
							<label class="ln-pill">
								<input type="radio" name="ln_region" value="<?php echo esc_attr( $key ); ?>" <?php checked( 0, $i++ ); ?>>
								<span><?php echo esc_html( $r['label'] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>
				<?php endif; ?>

				<?php $i = 0; foreach ( $regions as $key => $r ) : ?>
				<fieldset class="ln-plan-set" data-region="<?php echo esc_attr( $key ); ?>"<?php echo $i++ ? ' hidden disabled' : ''; ?>>
					<legend>Subscription</legend>
					<div class="ln-pills">
						<?php foreach ( $r['plans'] as $j => $p ) : ?>
							<label class="ln-pill">
								<input type="radio" name="add-to-cart" value="<?php echo esc_attr( $p->get_id() ); ?>" <?php checked( true, 1 === $i && 0 === $j ); ?>
									data-price="<?php echo esc_attr( wp_strip_all_tags( wc_price( $p->get_price() ) ) ); ?>"
									data-desc="<?php echo esc_attr( wp_strip_all_tags( $p->get_short_description() ) ); ?>">
								<span><?php echo esc_html( $label( $p ) ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>
				<?php endforeach; ?>

				<p class="ln-plan-desc" data-ln-desc><?php echo esc_html( wp_strip_all_tags( $first->get_short_description() ) ); ?></p>
				<div class="ln-qty">
					<label for="ln-club-qty">Quantity</label>
					<div class="ln-qty__box">
						<button type="button" class="ln-qty__btn" data-step="-1" aria-label="Decrease quantity">−</button>
						<input id="ln-club-qty" type="number" name="quantity" value="1" min="1" max="10" step="1" inputmode="numeric">
						<button type="button" class="ln-qty__btn" data-step="1" aria-label="Increase quantity">+</button>
					</div>
				</div>
				<div class="ln-club-actions">
					<button type="submit" class="wp-element-button ln-btn-outline" formaction="<?php echo esc_url( wc_get_cart_url() ); ?>">Add to cart</button>
					<button type="submit" class="wp-element-button">Buy it now</button>
				</div>

				<div class="ln-club-about">
					<article class="ln-letter" aria-label="A note from Namitha">
						<span class="ln-letter__stamp" aria-hidden="true"><svg viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
							<path class="ln-stamp-edge" d="M14 10h60v70H14z" stroke-width="1.4" stroke-dasharray="2.2 3.2"/>
							<rect x="19" y="15" width="50" height="60" stroke-width="1.2"/>
							<g stroke-width="1.4"><circle cx="44" cy="40" r="4"/><path d="M44 36c-2-6 6-8 5-2M48 40c6-2 8 6 2 5M44 44c2 6-6 8-5 2M40 40c-6 2-8-6-2-5M44 47v16M44 56c-4-1-7-4-8-7M44 59c4-1 6-3 7-6"/></g>
							<g stroke-width="1.2" opacity=".75"><circle cx="70" cy="66" r="15"/><circle cx="70" cy="66" r="11"/><path d="M45 80c5-4 10 4 15 0s10 4 15 0 10 4 15 0M47 87c5-4 10 4 15 0s10 4 15 0 10 4 15 0"/></g>
						</svg></span>
						<p class="ln-letter__dear">Dear friend,</p>
						<p>Somewhere between endless scrolling and notifications, I started missing the simple joy of real post: an envelope with your name on it, something you can hold, keep and pin to a wall. So I made a little club for it.</p>
						<p>Every month I pack an envelope just for you, filled with original artworks, postcards, stickers and paper goodies, all illustrated and hand-packed by me in my studio, along with a little note about what I’ve been making, learning and dreaming up.</p>
						<p class="ln-letter__sign"><img src="<?php echo esc_url( ln_img_url( 'love-n-signature.png' ) ); ?>" alt="Love, N" width="900" height="199"></p>
					</article>

					<p class="ln-cutoff"><svg class="ln-cutoff__icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5.5" width="18" height="13" rx="1.5"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5"/></svg> Subscribe by the last day of the month to catch the next month’s mailing.</p>

					<section class="ln-journey" aria-labelledby="ln-journey-title">
						<h3 id="ln-journey-title" class="ln-journey__title">How your envelope travels</h3>
						<ol class="ln-journey__steps">
							<li>
								<span class="ln-journey__icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M5 11h22v15H5z"/><path d="M5 11l11 8 11-8"/><path d="M16 9.5c-1.6-2.4-5-1.4-4.4 1 .4 1.6 2.6 3 4.4 4 1.8-1 4-2.4 4.4-4 .6-2.4-2.8-3.4-4.4-1z"/></svg></span>
								<strong>Hand-packed</strong>
								<span>in the studio, every month</span>
							</li>
							<li>
								<span class="ln-journey__icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M9 28V14a7 7 0 0 1 14 0v14z"/><path d="M12 18h8"/><path d="M16 28v-4"/><path d="M23 9l4-2v5l-4-1"/></svg></span>
								<strong>Posted by India Post</strong>
								<span>postage already included</span>
							</li>
							<li>
								<span class="ln-journey__icon" aria-hidden="true"><svg viewBox="0 0 32 32"><path d="M5 15L16 6l11 9"/><path d="M8 13v14h16V13"/><path d="M13 27v-7h6v7"/></svg></span>
								<strong>In your letterbox</strong>
								<span>3–10 days in India · 7–21 days abroad</span>
							</li>
						</ol>
						<ul class="ln-stamps">
							<li class="ln-stamp ln-stamp--rose"><strong>Tracked post</strong><span>Optional, for a small extra charge at checkout.</span></li>
							<li class="ln-stamp ln-stamp--sage"><strong>Lost on the way?</strong><span>Untracked mail not there in 4 weeks? I’ll send a new one.</span></li>
							<li class="ln-stamp ln-stamp--butter"><strong>Customs</strong><span>Any local taxes or duties aren’t included in the price.</span></li>
							<li class="ln-stamp ln-stamp--sky"><strong>Paid in ₹</strong><span>Payments are processed in Indian rupees.</span></li>
						</ul>
						<p class="ln-journey__note">Times depend on the destination country and customs.</p>
					</section>
					<a class="ln-club-product__more ln-arrow" href="#how-it-works">How it works</a>
				</div>
			</form>
		</div>
		<script>
		document.querySelectorAll('.ln-club-product__info').forEach(function (f) {
			function show(r) {
				f.querySelector('[data-ln-price]').textContent = r.dataset.price;
				f.querySelector('[data-ln-desc]').textContent = r.dataset.desc;
			}
			function setRegion(region) {
				f.querySelectorAll('.ln-plan-set').forEach(function (s) {
					var on = s.dataset.region === region;
					s.hidden = !on;
					s.disabled = !on; // only the visible set is submitted
				});
				// Keep the same plan length when switching region (e.g. 3 months stays 3 months).
				var current = f.querySelector('input[name="add-to-cart"]:checked');
				var idx = current ? Array.prototype.indexOf.call(current.closest('.ln-pills').querySelectorAll('input'), current) : 0;
				var set = f.querySelector('.ln-plan-set[data-region="' + region + '"]');
				var inputs = set ? set.querySelectorAll('input') : [];
				var pick = inputs[idx] || inputs[0];
				if (pick) { pick.checked = true; show(pick); }
				try { localStorage.setItem('lnClubRegion', region); } catch (e) {}
			}
			var qty = f.querySelector('#ln-club-qty');
			f.querySelectorAll('.ln-qty__btn').forEach(function (b) {
				b.addEventListener('click', function () {
					var v = parseInt(qty.value, 10) || 1;
					qty.value = Math.min(10, Math.max(1, v + parseInt(b.dataset.step, 10)));
				});
			});
			// Add to cart without leaving the page, then open the cart drawer.
			var addBtn = f.querySelector('.ln-btn-outline');
			if (addBtn && window.fetch) {
				addBtn.addEventListener('click', function (e) {
					var mini = document.querySelector('.wc-block-mini-cart');
					var pick = f.querySelector('input[name="add-to-cart"]:checked');
					if (!mini || !pick) return; // no drawer available: fall back to the cart page
					e.preventDefault();
					var label = addBtn.textContent;
					addBtn.disabled = true;
					addBtn.textContent = 'Adding…';
					var qtyVal = Math.max(1, parseInt(qty.value, 10) || 1);
					fetch('/wp-json/wc/store/v1/cart', { credentials: 'same-origin' })
						.then(function (r) { return r.headers.get('Nonce'); })
						.then(function (nonce) {
							return fetch('/wp-json/wc/store/v1/cart/add-item', {
								method: 'POST',
								credentials: 'same-origin',
								headers: { 'Content-Type': 'application/json', 'Nonce': nonce },
								body: JSON.stringify({ id: parseInt(pick.value, 10), quantity: qtyVal })
							});
						})
						.then(function (r) { if (!r.ok) throw new Error('add failed'); return r.json(); })
						.then(function () {
							document.body.dispatchEvent(new CustomEvent('wc-blocks_added_to_cart', { bubbles: true, cancelable: true }));
							addBtn.textContent = 'Added ✓';
							setTimeout(function () { addBtn.textContent = label; addBtn.disabled = false; }, 1600);
						})
						.catch(function () {
							addBtn.disabled = false;
							addBtn.textContent = label;
							f.action = addBtn.getAttribute('formaction');
							f.submit();
						});
				});
			}
			f.addEventListener('change', function (e) {
				var r = e.target;
				if (r.name === 'ln_region') setRegion(r.value);
				else if (r.dataset && r.dataset.price) show(r);
			});
			var saved = null;
			try { saved = localStorage.getItem('lnClubRegion'); } catch (e) {}
			var savedInput = saved && f.querySelector('input[name="ln_region"][value="' + saved + '"]');
			if (savedInput) { savedInput.checked = true; setRegion(saved); }
		});
		</script>
		<?php
		return ob_get_clean();
	}
);

/*
 * Checkout check: India plans ship only within India, international plans only
 * outside it. Works with both the block checkout and the classic checkout.
 */
function ln_club_region_error( $country, $items ) {
	if ( ! $country ) {
		return '';
	}
	foreach ( $items as $product ) {
		if ( ! $product || ! has_term( 'mail-club', 'product_cat', $product->get_parent_id() ?: $product->get_id() ) ) {
			continue;
		}
		$region = ln_club_region( $product );
		if ( 'india' === $region && 'IN' !== $country ) {
			return __( 'The Mail Club plan in your basket is for delivery within India. For an address outside India, please go back to the Mail Club page and choose “International”.', 'love-n' );
		}
		if ( 'international' === $region && 'IN' === $country ) {
			return __( 'The Mail Club plan in your basket is for delivery outside India. For an Indian address, please go back to the Mail Club page and choose “India”.', 'love-n' );
		}
	}
	return '';
}

// Block checkout (Store API).
add_action(
	'woocommerce_store_api_checkout_update_order_from_request',
	function ( $order ) {
		$country = $order->get_shipping_country() ?: $order->get_billing_country();
		$items   = array_map( fn( $i ) => $i->get_product(), array_values( $order->get_items() ) );
		$error   = ln_club_region_error( $country, $items );
		if ( $error && class_exists( '\Automattic\WooCommerce\StoreApi\Exceptions\RouteException' ) ) {
			throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'ln_club_region', $error, 400 );
		}
	}
);

// Classic checkout.
add_action(
	'woocommerce_after_checkout_validation',
	function ( $data, $errors ) {
		$country = ! empty( $data['ship_to_different_address'] ) ? ( $data['shipping_country'] ?? '' ) : ( $data['billing_country'] ?? '' );
		$items   = array_map( fn( $i ) => $i['data'] ?? null, WC()->cart ? WC()->cart->get_cart() : array() );
		$error   = ln_club_region_error( $country, $items );
		if ( $error ) {
			$errors->add( 'ln_club_region', $error );
		}
	},
	10,
	2
);

/* ---------- [ln_instagram url="https://instagram.com/handle"] ----------
 * Shows the latest posts from the Smash Balloon "Instagram Feed" plugin once
 * an account is connected there. Until then it shows six placeholder tiles
 * that link to the Instagram profile.
 */
add_shortcode(
	'ln_instagram',
	function ( $atts ) {
		$a = shortcode_atts( array( 'url' => '#' ), $atts );
		if ( shortcode_exists( 'instagram-feed' ) ) {
			$feed = do_shortcode( '[instagram-feed num=6 cols=6 colsmobile=3 colstablet=3 imagepadding=6 imagepaddingunit=px showheader=false showbutton=false showfollow=false]' );
			if ( false !== strpos( $feed, 'sbi_item' ) ) {
				return '<div class="ln-insta ln-insta--live">' . $feed . '</div>';
			}
		}
		$tiles = array( 'illustration-1.jpg', 'shop-postcards.jpg', 'illustration-3.jpg', 'shop-stickers.jpg', 'illustration-2.jpg', 'shop-bookmarks.jpg' );
		$out   = '<div class="ln-insta">';
		foreach ( $tiles as $t ) {
			$out .= '<a class="ln-insta__tile" href="' . esc_url( $a['url'] ) . '" target="_blank" rel="noopener" aria-label="View on Instagram">'
				. '<img src="' . esc_url( ln_img_url( $t ) ) . '" alt="" loading="lazy">'
				. '<span class="ln-insta__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.4" cy="6.6" r="1" fill="currentColor" stroke="none"/></svg></span></a>';
		}
		return $out . '</div>';
	}
);

// Basket stage (block checkout): flags a mismatch as soon as the customer's
// address is entered, and blocks placing the order before payment is attempted.
add_action(
	'woocommerce_store_api_cart_errors',
	function ( $errors, $cart ) {
		if ( ! WC()->customer || ! $cart ) {
			return;
		}
		$country = WC()->customer->get_shipping_country() ?: WC()->customer->get_billing_country();
		$items   = array_map( fn( $i ) => $i['data'] ?? null, $cart->get_cart() );
		$error   = ln_club_region_error( $country, $items );
		if ( $error ) {
			$errors->add( 'ln_club_region', $error );
		}
	},
	10,
	2
);
