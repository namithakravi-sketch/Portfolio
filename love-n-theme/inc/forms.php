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
	echo '<div class="wrap"><h1>Export subscribers</h1><p>Download every email sign-up (newsletter and Mail Club waitlist) as a CSV file you can import into your email tool.</p>';
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
					'mail-club' === $src ? 'New Mail Club waitlist sign-up' : 'New email sign-up',
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
				'ok'        => array( 'ok', 'mail-club' === $src ? 'You’re on the list! I’ll email you as soon as the next Mail Club spots open.' : 'Thank you! You’re signed up. Look out for some colour in your inbox.' ),
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
				<label class="ln-check"><input type="checkbox" name="ln_consent" value="1" required> <span>Yes, email me <?php echo 'mail-club' === $src ? 'when Mail Club spots open' : 'new art and shop news'; ?>. I can unsubscribe any time.</span></label>
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

/* ---------- [ln_mail_club_plans] ---------- */
add_shortcode(
	'ln_mail_club_plans',
	function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '<p class="ln-plans-empty">Mail Club plans appear here once the shop is switched on.</p>';
		}
		$products = wc_get_products(
			array(
				'category' => array( 'mail-club' ),
				'status'   => 'publish',
				'orderby'  => 'menu_order',
				'order'    => 'ASC',
				'limit'    => 6,
			)
		);
		if ( ! $products ) {
			return '<p class="ln-plans-empty">New Mail Club plans are coming soon. Join the waitlist below and you’ll be first to know.</p>';
		}
		$checkout = wc_get_checkout_url();
		ob_start();
		echo '<div class="ln-plans">';
		foreach ( $products as $i => $product ) {
			$featured = 1 === $i;
			$url      = add_query_arg( 'add-to-cart', $product->get_id(), $checkout );
			?>
			<div class="ln-plan<?php echo $featured ? ' ln-plan--featured' : ''; ?>">
				<?php if ( $featured ) : ?><span class="ln-plan__badge">Most loved</span><?php endif; ?>
				<p class="ln-plan__name"><?php echo esc_html( $product->get_name() ); ?></p>
				<p class="ln-plan__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
				<div class="ln-plan__desc"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
				<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
					<a class="wp-element-button" href="<?php echo esc_url( $url ); ?>">Join with this plan</a>
				<?php else : ?>
					<span class="ln-plan__desc"><strong>Full for now.</strong> Join the waitlist below.</span>
				<?php endif; ?>
			</div>
			<?php
		}
		echo '</div>';
		return ob_get_clean();
	}
);
