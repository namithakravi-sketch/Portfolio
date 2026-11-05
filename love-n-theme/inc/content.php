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
	$hero = ln_columns(
		array(
			array(
				ln_p( 'Designer &amp; digital artist', 'ln-kicker' )
				. ln_heading( 'Drawing joy into <em class="ln-accent">every</em> little thing.', 1 )
				. ln_p( 'Hi, I’m Namitha. I make illustrations, animations and brand worlds full of colour, and I turn my favourite pieces into the Love, N collection of prints, stationery and happy mail.', 'ln-lede' )
				. ln_buttons(
					array(
						array( 'See my work', '/work/' ),
						array( 'Shop Love, N', '/shop/', true ),
						array( 'Join the Mail Club', '/mail-club/', true ),
					)
				),
				'55%',
			),
			array( ln_image( 'hero.jpg', 'Featured artwork by Namitha Raveendranathan', 'ln-hero-art', '', false ), '45%' ),
		),
		'',
		'center'
	);

	$marquee = ln_group( ln_p( 'Illustration ✶ Animation ✶ Branding ✶ Love, N prints ✶ Bookmarks ✶ Stickers ✶ Postcards ✶ Art Mail Club ✶' ), 'ln-marquee', 'div', true );

	$door = function ( $band, $img, $alt, $title, $text, $label, $url ) {
		return ln_group(
			ln_image( $img, $alt, '', '', false )
			. ln_heading( $title, 3 )
			. ln_p( $text )
			. ln_buttons( array( array( $label, $url ) ) ),
			"ln-card {$band}"
		);
	};
	$doors = ln_section(
		ln_group( ln_p( 'Start here', 'ln-kicker' ) . ln_heading( 'Three ways <em class="ln-accent">in</em>' ), 'ln-section-head' )
		. ln_columns(
			array(
				$door( 'ln-band--sun', 'illustration-2.jpg', 'Illustration from the portfolio', 'The portfolio', 'Illustrations, animations and branding projects, from first sketch to finished piece.', 'Explore the work', '/work/' ),
				$door( 'ln-band--pink', 'shop-prints.jpg', 'Love, N art prints', 'The Love, N shop', 'Art prints, bookmarks, stickers and postcards, made to brighten desks, walls and letters.', 'Visit the shop', '/shop/' ),
				$door( 'ln-band--leaf', 'shop-mail-club.jpg', 'A Love, N Art Mail Club envelope', 'The Art Mail Club', 'A little parcel of original art and stationery, sent through the post to your door.', 'Join the club', '/mail-club/' ),
			),
			'ln-doors'
		)
	);

	$work = ln_section(
		ln_columns(
			array(
				ln_p( 'Selected work', 'ln-kicker' ) . ln_heading( 'Fresh off the <em class="ln-accent">drawing board</em>' ),
				ln_p( 'A few recent favourites. Tap any piece to see it bigger, or head to the portfolio for the full stories behind each project.' ) . ln_buttons( array( array( 'View the full portfolio', '/work/' ) ) ),
			),
			'ln-section-head',
			'bottom'
		)
		. ln_gallery(
			array(
				array( 'illustration-1.jpg', '[Describe this illustration]', '' ),
				array( 'branding-2.jpg', '[Describe this branding project]', '' ),
				array( 'illustration-3.jpg', '[Describe this illustration]', '' ),
				array( 'animation-1.jpg', '[Describe this animation still]', '' ),
			),
			4
		),
		'ln-band--ink'
	);

	$mags = '';
	foreach ( array( 1, 2, 3, 4 ) as $n ) {
		$mags .= ln_group( ln_image( "magazine-{$n}.jpg", "[Magazine name] cover featuring Namitha’s work", '', '', false ) . ln_heading( '[Magazine name]', 3 ) . ln_p( '[Issue · Year]' ), 'ln-mag' );
	}
	$features = ln_section(
		ln_group( ln_p( 'As seen in', 'ln-kicker' ) . ln_heading( 'In the <em class="ln-accent">magazines</em>' ) . ln_p( 'Some lovely publications have shared my work. Have a flick through.', 'ln-lede' ), 'ln-section-head' )
		. ln_group( $mags, 'ln-mags' )
		. ln_buttons( array( array( 'See all features', '/features/', true ) ) ),
		'ln-band--sun'
	);

	$cat = function ( $img, $alt, $label, $slug ) {
		return ln_group( ln_image( $img, $alt, '', '', false ) . ln_buttons( array( array( $label, "/product-category/{$slug}/" ) ) ), 'ln-cat' );
	};
	$shop = ln_section(
		ln_group( ln_p( 'The Love, N shop', 'ln-kicker' ) . ln_heading( 'Take a little <em class="ln-accent">art</em> home' ) . ln_p( 'Every piece starts as one of my drawings, then gets printed with care on lovely paper and packed by hand.', 'ln-lede' ), 'ln-section-head' )
		. ln_group(
			$cat( 'shop-prints.jpg', 'Love, N art prints', 'Art prints', 'prints' )
			. $cat( 'shop-bookmarks.jpg', 'Love, N bookmarks', 'Bookmarks', 'bookmarks' )
			. $cat( 'shop-stickers.jpg', 'Love, N stickers', 'Stickers', 'stickers' )
			. $cat( 'shop-postcards.jpg', 'Love, N postcards', 'Postcards', 'postcards' ),
			'ln-cats'
		)
		. ln_buttons( array( array( 'Shop everything', '/shop/' ) ) )
	);

	$club = ln_section(
		ln_columns(
			array(
				ln_p( 'Love, N Art Mail Club', 'ln-kicker' )
				. ln_heading( 'Happy mail, <em class="ln-accent">made by hand</em>' )
				. ln_p( 'Join the club and get an envelope of original art, stickers and stationery through the post, designed just for members.', 'ln-lede' )
				. ln_buttons( array( array( 'See plans &amp; join', '/mail-club/' ), array( 'How it works', '/mail-club/#how-it-works', true ) ) ),
				ln_image( 'shop-mail-club.jpg', 'What arrives in a Love, N Art Mail Club envelope', 'ln-hero-art', '', false ),
			),
			'',
			'center'
		),
		'ln-band--cobalt'
	);

	$about = ln_section(
		ln_columns(
			array(
				array( ln_image( 'about.jpg', 'Portrait of Namitha Raveendranathan', 'ln-portrait', '', false ), '40%' ),
				array(
					ln_p( 'About me', 'ln-kicker' )
					. ln_heading( 'Hello, <em class="ln-accent">I’m Namitha</em>' )
					. ln_p( '[Write a few warm sentences about yourself: where you work from, what you love to draw, and the kind of projects that light you up.]' )
					. ln_p( 'I work with brands, publishers and studios on illustration, animation and identity design, and I always love hearing about new ideas.' )
					. ln_buttons( array( array( 'Work with me', '/contact/' ), array( 'Read the features', '/features/', true ) ) ),
					'60%',
				),
			),
			'',
			'center'
		)
	);

	return ln_section( $hero, 'ln-hero' ) . $marquee . $doors . $work . $features . $shop . $club . $about;
}

function ln_page_work() {
	$hero = ln_section(
		ln_p( 'Portfolio', 'ln-kicker' )
		. ln_heading( 'Work that’s <em class="ln-accent">loud,</em> warm and full of colour.', 1 )
		. ln_p( 'Illustration, animation and branding projects for clients and for the love of it. Tap any image to see it full size.', 'ln-lede' )
		. ln_buttons(
			array(
				array( 'Illustration', '#illustration', true ),
				array( 'Animation', '#animation', true ),
				array( 'Branding', '#branding', true ),
			),
			'ln-jump'
		),
		'ln-page-hero ln-band--sun'
	);

	$meta = function ( $client, $role, $year ) {
		return ln_p( "<strong>Client</strong>{$client}", 'ln-project-meta' ) . ln_p( "<strong>What I did</strong>{$role}", 'ln-project-meta' ) . ln_p( "<strong>Year</strong>{$year}", 'ln-project-meta' );
	};

	$illustration = ln_section(
		'<!-- wp:html -->' . "\n" . '<span id="illustration"></span>' . "\n" . '<!-- /wp:html -->' . "\n\n"
		. ln_columns(
			array(
				ln_p( '01 · Illustration', 'ln-kicker' ) . ln_heading( 'Illustration' ),
				ln_p( 'Editorial pieces, book covers, personal series and everything in between. Bold shapes, bright colour and a lot of heart.', 'ln-lede' ),
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
		'ln-project'
	);

	$animation = ln_section(
		'<!-- wp:html -->' . "\n" . '<span id="animation"></span>' . "\n" . '<!-- /wp:html -->' . "\n\n"
		. ln_columns(
			array(
				ln_p( '02 · Animation', 'ln-kicker' ) . ln_heading( 'Animation' ),
				ln_p( 'Looping GIFs, explainer films and little moving moments that make people smile.', 'ln-lede' ),
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
		'ln-project ln-band--ink'
	);

	$branding = ln_section(
		'<!-- wp:html -->' . "\n" . '<span id="branding"></span>' . "\n" . '<!-- /wp:html -->' . "\n\n"
		. ln_columns(
			array(
				ln_p( '03 · Branding', 'ln-kicker' ) . ln_heading( 'Branding' ),
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
		ln_heading( 'Got a project in mind? <em class="ln-accent">Let’s make it colourful.</em>' )
		. ln_buttons( array( array( 'Start a conversation', '/contact/' ), array( 'Shop the prints', '/shop/', true ) ) ),
		'ln-band--pink'
	);

	return $hero . $illustration . $animation . $branding . $cta;
}

function ln_page_features() {
	$hero = ln_section(
		ln_p( 'Features &amp; press', 'ln-kicker' )
		. ln_heading( 'As seen <em class="ln-accent">in print</em>', 1 )
		. ln_p( 'Magazines and publications that have featured my illustration and design work. Thank you for the love!', 'ln-lede' ),
		'ln-page-hero ln-band--pink'
	);
	$mags = '';
	foreach ( array( 1, 2, 3, 4 ) as $n ) {
		$mags .= ln_group(
			ln_image( "magazine-{$n}.jpg", '[Magazine name] cover', '', '', true )
			. ln_heading( '[Magazine name]', 3 )
			. ln_p( '[Issue · Month Year]' )
			. ln_p( '[One line on what the feature was about.]' )
			. ln_p( '<a href="#">Read the feature →</a>' ),
			'ln-mag'
		);
	}
	$grid = ln_section( ln_group( $mags, 'ln-mags' ) );
	$cta  = ln_section(
		ln_columns(
			array(
				ln_heading( 'Writing about illustration? <em class="ln-accent">Say hello.</em>' ),
				ln_p( 'For interviews, features and press images, get in touch and I’ll send what you need.' ) . ln_buttons( array( array( 'Press enquiries', '/contact/' ) ) ),
			),
			'',
			'center'
		),
		'ln-band--ink'
	);
	return $hero . $grid . $cta;
}

function ln_page_mail_club() {
	$hero = ln_section(
		ln_columns(
			array(
				ln_p( 'Love, N Art Mail Club', 'ln-kicker' )
				. ln_heading( 'Art that arrives <em class="ln-accent">by post.</em>', 1 )
				. ln_p( 'Every month, members get an envelope of brand-new art made just for the club. It’s a little bit of joy in your letterbox.', 'ln-lede' )
				. ln_buttons( array( array( 'Choose a plan', '#plans' ), array( 'How it works', '#how-it-works', true ) ) ),
				ln_image( 'shop-mail-club.jpg', 'A Love, N Art Mail Club envelope and its contents', 'ln-hero-art', '', false ),
			),
			'',
			'center'
		),
		'ln-page-hero ln-band--leaf'
	);

	$inside = ln_section(
		ln_columns(
			array(
				ln_p( 'In every envelope', 'ln-kicker' ) . ln_heading( 'What’s <em class="ln-accent">inside</em>' ),
				ln_list(
					array(
						'An exclusive art print you won’t find in the shop',
						'A sticker or two, made to match the theme',
						'A postcard or bookmark to keep or send on',
						'A handwritten note and a peek at what’s next in the studio',
					)
				) . ln_p( '[Edit this list so it matches exactly what members receive.]', 'ln-note' ),
			)
		)
	);

	$how = ln_section(
		'<!-- wp:html -->' . "\n" . '<span id="how-it-works"></span>' . "\n" . '<!-- /wp:html -->' . "\n\n"
		. ln_group( ln_p( 'How it works', 'ln-kicker' ) . ln_heading( 'Joining is <em class="ln-accent">easy</em>' ), 'ln-section-head' )
		. ln_columns(
			array(
				ln_group( ln_heading( 'Pick a plan', 3 ) . ln_p( 'Choose how many months of happy mail you’d like below.' ), 'ln-step' ),
				ln_group( ln_heading( 'Check out securely', 3 ) . ln_p( 'Pay online and add the address your envelopes should go to.' ), 'ln-step' ),
				ln_group( ln_heading( 'Watch the letterbox', 3 ) . ln_p( 'Your first envelope ships with the next club mailing. [Add your posting date.]' ), 'ln-step' ),
			),
			'ln-steps'
		),
		'ln-band--sun'
	);

	$plans = ln_section(
		'<!-- wp:html -->' . "\n" . '<span id="plans"></span>' . "\n" . '<!-- /wp:html -->' . "\n\n"
		. ln_group( ln_p( 'Plans', 'ln-kicker' ) . ln_heading( 'Choose your <em class="ln-accent">club plan</em>' ) . ln_p( 'Plans are paid up front for the months you choose. No automatic renewals, so there are no surprises.', 'ln-lede' ), 'ln-section-head' )
		. ln_shortcode( '[ln_mail_club_plans]' ),
		'ln-band--ink'
	);

	$faq = ln_section(
		ln_columns(
			array(
				ln_p( 'Questions', 'ln-kicker' ) . ln_heading( 'Good to <em class="ln-accent">know</em>' ),
				ln_details( 'When do envelopes ship?', '[Add the date or week each month that club mail is posted.]' )
				. ln_details( 'Where do you ship to?', '[List the countries you send club mail to and any extra postage.]' )
				. ln_details( 'Can I give the Mail Club as a gift?', 'Yes! Just add the lucky person’s address as the shipping address at checkout, and leave a note if you’d like me to include a gift message.' )
				. ln_details( 'What happens when my plan ends?', 'Your envelopes stop after the last month you paid for. I’ll email you before then, so you can rejoin if you’d like to keep the happy mail coming.' ),
			)
		)
	);

	$wait = ln_section(
		ln_columns(
			array(
				ln_heading( 'Not ready yet? <em class="ln-accent">Get on the list.</em>' ) . ln_p( 'Leave your email and I’ll let you know when new club spots and themes open.' ),
				ln_shortcode( '[ln_signup source="mail-club" button="Join the waitlist"]' ),
			),
			'',
			'center'
		),
		'ln-band--orange'
	);

	return $hero . $inside . $how . $plans . $faq . $wait;
}

function ln_page_contact() {
	$hero = ln_section(
		ln_columns(
			array(
				ln_p( 'Contact', 'ln-kicker' )
				. ln_heading( 'Let’s make something <em class="ln-accent">wonderful.</em>', 1 )
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
		'ln-page-hero ln-band--cobalt'
	);
	return $hero;
}
