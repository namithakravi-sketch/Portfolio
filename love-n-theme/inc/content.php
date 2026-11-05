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
		. ln_heading( 'Little artworks for <em class="ln-accent">big</em> feelings', 1 )
		. ln_p( 'Illustration, animation and branding by Namitha Raveendranathan, plus the Love, N collection of prints and paper goods.', 'ln-lede' )
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
				. ln_p( 'A hand-packed envelope of brand-new art, made only for club members and posted to your door.', 'ln-lede' )
				. "<!-- wp:list {\"className\":\"ln-ticks\"} -->\n<ul class=\"wp-block-list ln-ticks\">"
				. "<!-- wp:list-item -->\n<li>An exclusive print you won’t find in the shop</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>Stickers, postcards and little paper surprises</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>3-month, 6-month and 1-year plans</li>\n<!-- /wp:list-item -->\n\n"
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
			. ln_photo_card( 'shop-stickers.jpg', 'Love, N stickers', 'Stickers', '/product-category/stickers/', 'ln-tile ln-tile--half' )
			. ln_photo_card( 'shop-mail-club.jpg', 'Love, N Art Mail Club', 'Art Mail Club', '/mail-club/', 'ln-tile ln-tile--half' ),
			'ln-grid'
		)
		. ln_group( ln_buttons( array( array( 'Shop everything', '/shop/', true ) ) ), 'ln-center' )
	);

	// 4. Selected work + 5. Features + 6. About (kept short).
	$work = ln_section(
		ln_group( ln_p( 'Portfolio', 'ln-kicker' ) . ln_heading( 'From the <em class="ln-accent">sketchbook</em>' ) . ln_p( 'Illustration, animation and branding. Tap a piece to see it bigger.', 'ln-lede' ), 'ln-section-head ln-center' )
		. ln_gallery(
			array(
				array( 'illustration-1.jpg', '[Describe this illustration]', '' ),
				array( 'illustration-2.jpg', '[Describe this illustration]', '' ),
				array( 'branding-1.jpg', '[Describe this branding project]', '' ),
				array( 'illustration-3.jpg', '[Describe this illustration]', '' ),
			),
			4
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
					. ln_p( '[Write a few warm sentences about yourself: where you draw from, what you love to make, and the stories behind Love, N.]' )
					. ln_p( 'I work with brands, publishers and studios on illustration, animation and identity design, and I always love hearing new ideas.' )
					. ln_buttons( array( array( 'Work with me', '/contact/' ), array( 'View works', '/work/', true ) ) ),
					'60%',
				),
			),
			'',
			'center'
		),
		'ln-band--cream'
	);

	return $hero . $club . $shop . $work . $features . $about;
}

function ln_page_work() {
	$hero = ln_section(
		ln_p( 'Portfolio', 'ln-kicker' )
		. ln_heading( 'Selected <em class="ln-accent">works</em>', 1 )
		. ln_p( 'Illustration, animation and branding projects, for clients and for the love of it. Tap any image to see it full size.', 'ln-lede' )
		. ln_buttons(
			array(
				array( 'Illustration', '#illustration', true ),
				array( 'Animation', '#animation', true ),
				array( 'Branding', '#branding', true ),
			),
			'ln-jump'
		),
		'ln-page-hero'
	);

	$meta = function ( $client, $role, $year ) {
		return ln_p( "<strong>Client</strong>{$client}", 'ln-project-meta' ) . ln_p( "<strong>What I did</strong>{$role}", 'ln-project-meta' ) . ln_p( "<strong>Year</strong>{$year}", 'ln-project-meta' );
	};
	$anchor = function ( $id ) {
		return "<!-- wp:html -->\n<span id=\"{$id}\"></span>\n<!-- /wp:html -->\n\n";
	};

	$illustration = ln_section(
		$anchor( 'illustration' )
		. ln_columns(
			array(
				ln_p( '01', 'ln-kicker' ) . ln_heading( 'Illustration' ),
				ln_p( 'Editorial pieces, book covers, personal series and everything in between: soft colour, small details and a lot of heart.', 'ln-lede' ),
			),
			'ln-section-head',
			'bottom'
		)
		. ln_gallery(
			array(
				array( 'illustration-1.jpg', '[Describe this illustration]', '[Project title] · [Client or personal]' ),
				array( 'illustration-2.jpg', '[Describe this illustration]', '[Project title] · [Client or personal]' ),
				array( 'illustration-3.jpg', '[Describe this illustration]', '[Project title] · [Client or personal]' ),
				array( 'illustration-4.jpg', '[Describe this illustration]', '[Project title] · [Client or personal]' ),
			),
			2
		),
		'ln-project ln-band--cream'
	);

	$animation = ln_section(
		$anchor( 'animation' )
		. ln_columns(
			array(
				ln_p( '02', 'ln-kicker' ) . ln_heading( 'Animation' ),
				ln_p( 'Looping moments, explainer films and little moving stories that make people smile.', 'ln-lede' ),
			),
			'ln-section-head',
			'bottom'
		)
		. ln_gallery(
			array(
				array( 'animation-1.jpg', '[Describe this animation still]', '[Film title] · [Client]' ),
				array( 'animation-2.jpg', '[Describe this animation still]', '[Film title] · [Client]' ),
				array( 'animation-3.jpg', '[Describe this animation still]', '[Film title] · [Client]' ),
			),
			3
		)
		. ln_p( 'To show a film here, paste its YouTube or Vimeo link on its own line in the editor and it becomes a video player.', 'ln-note' ),
		'ln-project ln-band--forest'
	);

	$branding = ln_section(
		$anchor( 'branding' )
		. ln_columns(
			array(
				ln_p( '03', 'ln-kicker' ) . ln_heading( 'Branding' ),
				ln_p( 'Logos, illustrated identities and packaging for small businesses with big personalities.', 'ln-lede' ),
			),
			'ln-section-head',
			'bottom'
		)
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
		'ln-project'
	);

	$cta = ln_section(
		ln_heading( 'Have a project in mind? <em class="ln-accent">Let’s talk.</em>' )
		. ln_buttons( array( array( 'Get in touch', '/contact/' ), array( 'Shop the prints', '/shop/', true ) ) ),
		'ln-band--rose ln-center'
	);

	return $hero . $illustration . $animation . $branding . $cta;
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
				. "<!-- wp:list-item -->\n<li>An exclusive art print you won’t find in the shop</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>A sticker or two, made to match the month’s theme</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>A postcard or bookmark to keep or send on</li>\n<!-- /wp:list-item -->\n\n"
				. "<!-- wp:list-item -->\n<li>A handwritten note and a peek at what’s next in the studio</li>\n<!-- /wp:list-item -->\n\n"
				. "</ul>\n<!-- /wp:list -->\n\n"
				. ln_p( '[Edit this list so it matches exactly what members receive.]', 'ln-note' ),
			)
		),
		'ln-band--cream'
	);

	$how = ln_section(
		"<!-- wp:html -->\n<span id=\"how-it-works\"></span>\n<!-- /wp:html -->\n\n"
		. ln_group( ln_p( 'How it works', 'ln-kicker' ) . ln_heading( 'Joining is <em class="ln-accent">easy</em>' ), 'ln-section-head' )
		. ln_columns(
			array(
				ln_group( ln_heading( 'Pick a plan', 3 ) . ln_p( '3 months, 6 months or a whole year of happy mail.' ), 'ln-step' ),
				ln_group( ln_heading( 'Check out securely', 3 ) . ln_p( 'Pay online and add the address your envelopes should go to.' ), 'ln-step' ),
				ln_group( ln_heading( 'Watch the letterbox', 3 ) . ln_p( 'Your first envelope ships with the next club mailing. [Add your posting date.]' ), 'ln-step' ),
			),
			'ln-steps'
		),
		'ln-band--sage'
	);

	$faq = ln_section(
		ln_columns(
			array(
				ln_p( 'Questions', 'ln-kicker' ) . ln_heading( 'Good to <em class="ln-accent">know</em>' ),
				ln_details( 'When do envelopes ship?', '[Add the date or week each month that club mail is posted.]' )
				. ln_details( 'Where do you ship to?', '[List the countries you send club mail to, and how international members pay.]' )
				. ln_details( 'Can I give the Mail Club as a gift?', 'Yes! Add the lucky person’s address as the shipping address at checkout, and leave a note if you’d like a gift message included.' )
				. ln_details( 'What happens when my plan ends?', 'Your envelopes stop after the last month you paid for. I’ll email you before then, so you can rejoin if you’d like to keep the happy mail coming.' ),
			)
		)
	);

	$wait = ln_section(
		ln_columns(
			array(
				ln_heading( 'Not ready yet? <em class="ln-accent">Join the waitlist.</em>' ) . ln_p( 'Leave your email and I’ll let you know when new club themes and spots open.' ),
				ln_shortcode( '[ln_signup source="mail-club" button="Join the waitlist"]' ),
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
