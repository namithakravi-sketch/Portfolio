<?php
/**
 * Starting content for each page. Everything here becomes normal, editable
 * blocks once the pages are created. Text in [square brackets] is a
 * placeholder for Namitha to replace.
 *
 * @package love-n
 */

defined( 'ABSPATH' ) || exit;

function ln_page_home() {
	// 1. Hero video with "View works".
	$hero = ln_cover_video(
		'hero.mp4',
		ln_p( 'Designer &amp; digital artist', 'ln-kicker' )
		. ln_heading( 'Finding <em class="ln-accent">magic</em> in the ordinary, one colour at a time', 1 )
		. ln_buttons( array( array( 'View works', '/work/' ), array( 'Visit the shop', '/shop/', true ) ) ),
		'ln-hero'
	);

	// 2. Snail mail club with "View details".
	$club = ln_section(
		ln_columns(
			array(
				ln_image( 'shop-mail-club.jpg', 'What arrives in a Love, N Art Mail Club envelope', '', '', false ),
				ln_p( 'Love, N Art Mail Club 💌', 'ln-kicker' )
				. ln_heading( 'Happy mail, <em class="ln-accent">every month</em>' )
				. ln_p( 'A happy little surprise waiting for you every month! A hand-packed envelope filled with colourful illustrations, postcards, stickers, and paper goodies, all made with a little extra love.', 'ln-lede' )
				. "<!-- wp:list {\"className\":\"ln-ticks\"} -->\n<ul class=\"wp-block-list ln-ticks\">"
				. "<!-- wp:list-item -->\n<li>A monthly selection of original artworks and postcards</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>Stickers, bookmarks and other little paper surprises to collect, share or gift</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>1-month, 3-month and 6-month subscriptions</li>\n<!-- /wp:list-item -->\n\n"
				. "</ul>\n<!-- /wp:list -->\n\n"
				. ln_buttons( array( array( 'View details', '/mail-club/' ) ) ),
			),
			'',
			'center'
		),
		'ln-club-feature ln-band--cream'
	);

	// 3. Shop: editorial cards for artworks, postcards, bookmarks, stickers.
	$shop = ln_section(
		ln_group( ln_p( 'The Love, N shop', 'ln-kicker' ) . ln_heading( 'New in' ), 'ln-section-head' )
		. ln_group(
			ln_photo_card( 'shop-prints.jpg', 'Love, N art prints', 'Artworks &amp; prints', '/product-category/prints/', 'ln-tile ln-tile--big' )
			. ln_photo_card( 'shop-postcards.jpg', 'Love, N postcards', 'Postcards', '/product-category/postcards/', 'ln-tile' )
			. ln_photo_card( 'shop-bookmarks.jpg', 'Love, N bookmarks', 'Bookmarks', '/product-category/bookmarks/', 'ln-tile' )
			. ln_photo_card( 'shop-stickers.jpg', 'Love, N stickers', 'Stickers', '/product-category/stickers/', 'ln-tile' ),
			'ln-grid'
		)
		. ln_group( ln_buttons( array( array( 'Shop now', '/shop/', true ) ) ), 'ln-center' )
	);

	// 4. Selected work, 5. About, 6. Featured in.
	$work = ln_section(
		ln_group( ln_p( 'Portfolio', 'ln-kicker' ) . ln_heading( 'From the <em class="ln-accent">sketchbook</em>' ) . ln_p( 'Illustration, animation and branding. Tap a piece to see it bigger.', 'ln-lede' ), 'ln-section-head ln-center' )
		. ln_gallery(
			array(
				array( 'illustration-1.jpg', '[Describe this illustration]', '' ),
				array( 'illustration-2.jpg', '[Describe this illustration]', '' ),
				array( 'illustration-3.jpg', '[Describe this illustration]', '' ),
				array( 'branding-1.jpg', '[Describe this branding project]', '' ),
				array( 'animation-1.jpg', '[Describe this animation still]', '' ),
			),
			3
		)
		. ln_group( ln_buttons( array( array( 'View works', '/work/' ) ) ), 'ln-center' ),
		'ln-band--sage'
	);

	$mags = '';
	foreach ( array( 1, 2, 3, 4 ) as $n ) {
		$mags .= ln_group( ln_image( "magazine-{$n}.jpg", '[Magazine name] cover', '', '', false ) . ln_heading( '[Magazine name]', 3 ) . ln_p( '[Issue · Year]' ), 'ln-mag' );
	}
	$features = ln_section(
		ln_group( ln_p( 'As seen in', 'ln-kicker' ) . ln_heading( 'Featured in' ), 'ln-section-head ln-center' )
		. ln_group( $mags, 'ln-mags' )
		. ln_group( ln_p( '<a href="/features/">See all features</a>', 'ln-arrow' ), 'ln-center' )
	);

	$about = ln_section(
		ln_columns(
			array(
				array( ln_image( 'about.jpg', 'Namitha Raveendranathan in the studio', 'ln-portrait', '', false ), '40%' ),
				array(
					ln_p( 'Hello', 'ln-kicker' )
					. ln_heading( 'I’m <em class="ln-accent">Namitha</em>' )
					. ln_p( 'An artist and designer with a love for colour, creativity and all things a little whimsical. I create vibrant digital artworks, illustrations and visual identities, bringing together my love for art, design and storytelling.', 'ln-lede' )
					. ln_buttons( array( array( 'Work with me', '/contact/' ) ) ),
					'60%',
				),
			),
			'',
			'center'
		),
		'ln-band--cream'
	);

	// 7. Instagram strip (latest posts once the Instagram Feed plugin is connected).
	$insta = ln_section(
		ln_group( ln_p( '@namitha_raveendranathan', 'ln-kicker' ) . ln_heading( 'Follow along <em class="ln-accent">on Instagram</em>' ) . ln_p( 'Sketches, works in progress and little studio moments.', 'ln-lede' ), 'ln-section-head ln-center' )
		. ln_shortcode( '[ln_instagram url="https://www.instagram.com/namitha_raveendranathan/"]' )
		. ln_group( ln_buttons( array( array( 'Follow on Instagram', 'https://www.instagram.com/namitha_raveendranathan/', true ) ) ), 'ln-center' ),
		'ln-band--cream ln-insta-section'
	);

	return $hero . $club . $shop . $work . $about . $features . $insta;
}

function ln_page_work() {
	// Tabs: each button links to a panel id; assets/js/tabs.js shows one panel at a time.
	$hero = ln_section(
		ln_p( 'Portfolio', 'ln-kicker' )
		. ln_heading( 'The creative <em class="ln-accent">collection</em>', 1 )
		. ln_p( 'Artworks, animation and branding projects, for clients and for the love of it.', 'ln-lede' )
		. ln_buttons(
			array(
				array( 'Artworks', '#artworks', true ),
				array( 'Animation', '#animation', true ),
				array( 'Branding', '#branding', true ),
			),
			'ln-tabs'
		),
		'ln-page-hero'
	);

	$meta = function ( $client, $role, $year ) {
		return ln_p( "<strong>Client</strong>{$client}", 'ln-project-meta' ) . ln_p( "<strong>What I did</strong>{$role}", 'ln-project-meta' ) . ln_p( "<strong>Year</strong>{$year}", 'ln-project-meta' );
	};
	$head = function ( $title, $text ) {
		return ln_columns( array( ln_heading( $title ), ln_p( $text, 'ln-lede' ) ), 'ln-section-head', 'bottom' );
	};

	$artworks = ln_section(
		$head( 'Artworks', 'Personal series, editorial pieces, book covers and everything in between.' )
		. ln_gallery(
			array(
				array( 'illustration-1.jpg', '[Describe this artwork]', '[Artwork title] · [Client or personal]' ),
				array( 'illustration-2.jpg', '[Describe this artwork]', '[Artwork title] · [Client or personal]' ),
				array( 'illustration-3.jpg', '[Describe this artwork]', '[Artwork title] · [Client or personal]' ),
				array( 'illustration-4.jpg', '[Describe this artwork]', '[Artwork title] · [Client or personal]' ),
			),
			2
		),
		'ln-project ln-tab-panel ln-band--cream',
		'artworks'
	);

	$animation = ln_section(
		$head( 'Animation', 'Looping moments and little moving stories.' )
		. ln_gallery(
			array(
				array( 'animation-1.jpg', '[Describe this animation still]', '[Film title] · [Client]' ),
				array( 'animation-2.jpg', '[Describe this animation still]', '[Film title] · [Client]' ),
				array( 'animation-3.jpg', '[Describe this animation still]', '[Film title] · [Client]' ),
			),
			3
		)
		. ln_p( 'To show a film here, paste its YouTube or Vimeo link on its own line in the editor and it becomes a video player.', 'ln-note' ),
		'ln-project ln-tab-panel ln-band--cream',
		'animation'
	);

	$branding = ln_section(
		$head( 'Branding', 'Logos, illustrated identities and packaging for small businesses with big personalities.' )
		. ln_columns(
			array(
				array( ln_image( 'branding-1.jpg', '[Describe the main brand image]' ), '60%' ),
				array(
					ln_heading( '[Brand name]', 3 )
					. ln_p( '[Two or three sentences on the brief: who the client is, what they needed and the idea behind the identity.]' )
					. $meta( '[Client name]', '[Logo, illustration, packaging]', '[Year]' ),
					'40%',
				),
			),
			'',
			'center'
		)
		. ln_gallery(
			array(
				array( 'branding-2.jpg', '[Describe this branding image]', '' ),
				array( 'branding-3.jpg', '[Describe this branding image]', '' ),
			),
			2
		),
		'ln-project ln-tab-panel ln-band--cream',
		'branding'
	);

	$cta = ln_section(
		ln_heading( 'Have a project in mind? <em class="ln-accent">Let’s talk.</em>' )
		. ln_buttons( array( array( 'Get in touch', '/contact/', true ), array( 'Shop the prints', '/shop/', true ) ) ),
		'ln-band--rose ln-center'
	);

	return $hero . $artworks . $animation . $branding . $cta;
}

function ln_page_features() {
	$hero = ln_section(
		ln_p( 'Features &amp; press', 'ln-kicker' )
		. ln_heading( 'As seen <em class="ln-accent">in print</em>', 1 )
		. ln_p( 'Magazines and publications that have featured my illustration and design work. Thank you for the love!', 'ln-lede' ),
		'ln-page-hero ln-center'
	);
	$mags = '';
	foreach ( array( 1, 2, 3, 4 ) as $n ) {
		$mags .= ln_group(
			ln_image( "magazine-{$n}.jpg", '[Magazine name] cover', '', '', true )
			. ln_heading( '[Magazine name]', 3 )
			. ln_p( '[Issue · Month Year]' )
			. ln_p( '[One line on what the feature was about.]' )
			. ln_p( '<a href="#">Read the feature</a>', 'ln-arrow' ),
			'ln-mag'
		);
	}
	$grid = ln_section( ln_group( $mags, 'ln-mags' ), 'ln-band--cream' );
	$cta  = ln_section(
		ln_columns(
			array(
				ln_heading( 'Writing about illustration? <em class="ln-accent">Say hello.</em>' ),
				ln_p( 'For interviews, features and press images, get in touch and I’ll send what you need.' ) . ln_buttons( array( array( 'Press enquiries', '/contact/' ) ) ),
			),
			'',
			'center'
		),
		'ln-band--forest'
	);
	return $hero . $grid . $cta;
}

function ln_page_mail_club() {
	// Product-style block: photo left, plan picker right.
	$top = ln_section( ln_shortcode( '[ln_mail_club_plans]' ) );

	$inside = ln_section(
		ln_columns(
			array(
				ln_p( 'In every envelope', 'ln-kicker' ) . ln_heading( 'What’s <em class="ln-accent">inside</em>' ),
				"<!-- wp:list {\"className\":\"ln-ticks\"} -->\n<ul class=\"wp-block-list ln-ticks\">"
				. "<!-- wp:list-item -->\n<li>A monthly selection of original artworks and postcards</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>Stickers, bookmarks and other little paper surprises to collect, share or gift</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>1-month, 3-month and 6-month subscriptions</li>\n<!-- /wp:list-item -->\n\n"
				. "</ul>\n<!-- /wp:list -->\n\n",
			)
		),
		'ln-band--cream'
	);

	$how = ln_section(
		"<!-- wp:html -->\n<span id=\"how-it-works\"></span>\n<!-- /wp:html -->\n\n"
		. ln_group( ln_p( 'How it works', 'ln-kicker' ) . ln_heading( 'Joining is <em class="ln-accent">easy</em>' ), 'ln-section-head' )
		. ln_columns(
			array(
				ln_group( ln_heading( 'Pick a plan', 3 ) . ln_p( '1 month, 3 months or 6 months of happy mail.' ), 'ln-step' ),
				ln_group( ln_heading( 'Check out securely', 3 ) . ln_p( 'Pay online and add the address your envelopes should go to.' ), 'ln-step' ),
				ln_group( ln_heading( 'Watch the letterbox', 3 ) . ln_p( 'Subscribe before the end of the month and your first envelope arrives with the next month’s mailing.' ), 'ln-step' ),
			),
			'ln-steps'
		),
		'ln-band--sage'
	);

	$faq = ln_section(
		ln_columns(
			array(
				ln_p( 'Questions', 'ln-kicker' ) . ln_heading( 'Good to <em class="ln-accent">know</em>' ),
				ln_details( 'When do envelopes ship?', 'Subscribe before the end of each month to receive the next month’s mail. Envelopes go out by India Post and usually arrive within 3–10 days in India and 7–21 days abroad.' )
				. ln_details( 'Where do you ship to?', 'Across India and worldwide. Postage is included in the price: choose “International” on this page for addresses outside India. Tracked post is available at checkout for a small extra charge.' )
				. ln_details( 'Can I give the Mail Club as a gift?', 'Yes! Add the lucky person’s address as the shipping address at checkout, and leave a note if you’d like a gift message included.' )
				. ln_details( 'What happens when my plan ends?', 'Your envelopes stop after the last month you paid for. I’ll email you before then, so you can rejoin if you’d like to keep the happy mail coming.' ),
			)
		)
	);

	$wait = ln_section(
		ln_columns(
			array(
				ln_heading( 'Not ready yet? <em class="ln-accent">Join the mail list.</em>' ) . ln_p( 'Leave your email and I’ll let you know about new club themes.' ),
				ln_shortcode( '[ln_signup source="mail-club" button="Join the mail list"]' ),
			),
			'',
			'center'
		),
		'ln-band--butter'
	);

	return $top . $inside . $how . $faq . $wait;
}

function ln_page_contact() {
	return ln_section(
		ln_columns(
			array(
				ln_p( 'Contact', 'ln-kicker' )
				. ln_heading( 'Let’s make something <em class="ln-accent">lovely</em>', 1 )
				. ln_p( 'Commissions, collaborations, press, or a question about your order. Drop me a note and I’ll get back to you within a few days.', 'ln-lede' )
				. ln_list(
					array(
						'<strong>Commissions:</strong> illustration, animation and branding',
						'<strong>Shop orders:</strong> please include your order number',
						'<strong>Mail Club:</strong> address changes and gifting',
					)
				)
				. ln_p( '[Add your social links or studio email here.]', 'ln-note' ),
				ln_group( ln_shortcode( '[ln_contact]' ), 'ln-contact-card' ),
			)
		),
		'ln-page-hero'
	);
}

/** Simple policy pages linked from the footer. */
function ln_page_policy( $title ) {
	return ln_section(
		ln_heading( $title, 1 )
		. ln_p( "[Write your {$title} here. WooCommerce → Settings → Advanced can link the shop’s checkout to it.]", 'ln-note' )
	);
}

function ln_page_about() {
	$intro = ln_section(
		ln_columns(
			array(
				array( ln_image( 'about.jpg', 'Namitha Raveendranathan in the studio', 'ln-portrait', '', false ), '40%' ),
				array(
					ln_p( 'About', 'ln-kicker' )
					. ln_heading( 'Hello, I’m <em class="ln-accent">Namitha</em>', 1 )
					. ln_p( '[Write a few warm sentences about yourself: where you draw from, what you love to make, and how Love, N began.]', 'ln-lede' )
					. ln_p( '[A second paragraph: your tools and process, the feelings and memories your work returns to, and what you hope people feel when they hold one of your pieces.]' )
					. ln_buttons( array( array( 'View works', '/work/' ), array( 'Get in touch', '/contact/', true ) ) ),
					'60%',
				),
			),
			'',
			'center'
		)
	);

	$card = function ( $title, $text ) {
		return ln_group( ln_heading( $title, 3 ) . ln_p( $text ), 'ln-step' );
	};
	$what = ln_section(
		ln_group( ln_p( 'What I do', 'ln-kicker' ) . ln_heading( 'Ways we can <em class="ln-accent">work together</em>' ), 'ln-section-head' )
		. ln_columns(
			array(
				$card( 'Illustration', 'Editorial pieces, book covers, packaging art and personal commissions.' ),
				$card( 'Animation', 'Looping moments, short films and moving stories for brands and social.' ),
				$card( 'Branding', 'Logos and illustrated identities for small businesses with big hearts.' ),
			),
			'ln-steps'
		),
		'ln-band--sage'
	);

	$press = ln_section(
		ln_columns(
			array(
				ln_heading( 'As seen <em class="ln-accent">in print</em>' ) . ln_p( 'Magazines and publications that have shared my work.' ),
				ln_buttons( array( array( 'See the features', '/features/' ), array( 'Contact me', '/contact/', true ) ) ),
			),
			'',
			'center'
		),
		'ln-band--cream'
	);

	return $intro . $what . $press;
}
