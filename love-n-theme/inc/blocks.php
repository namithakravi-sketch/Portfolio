<?php
/**
 * Small helpers that write valid block markup, so the generated pages
 * stay editable in the block editor.
 *
 * @package love-n
 */

defined( 'ABSPATH' ) || exit;

/** URL of an image bundled with the theme. */
function ln_img_url( $file ) {
	// Root-relative, so the pages keep working when the site moves to the real domain.
	return wp_make_link_relative( get_theme_file_uri( 'assets/img/' . $file ) );
}

function ln_attrs( array $attrs ) {
	return $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) : '';
}

function ln_heading( $html, $level = 2, $class = '' ) {
	$attrs = array();
	if ( 2 !== $level ) {
		$attrs['level'] = $level;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$cls = trim( 'wp-block-heading ' . $class );
	return "<!-- wp:heading" . ln_attrs( $attrs ) . " -->\n<h{$level} class=\"{$cls}\">{$html}</h{$level}>\n<!-- /wp:heading -->\n\n";
}

function ln_p( $html, $class = '' ) {
	$attrs = $class ? array( 'className' => $class ) : array();
	$cls   = $class ? " class=\"{$class}\"" : '';
	return "<!-- wp:paragraph" . ln_attrs( $attrs ) . " -->\n<p{$cls}>{$html}</p>\n<!-- /wp:paragraph -->\n\n";
}

function ln_image( $file, $alt, $class = '', $caption = '', $lightbox = true ) {
	$attrs = array(
		'sizeSlug'        => 'large',
		'linkDestination' => 'none',
	);
	if ( $lightbox ) {
		$attrs['lightbox'] = array( 'enabled' => true );
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$cls = trim( 'wp-block-image size-large ' . $class );
	$src = esc_url( ln_img_url( $file ) );
	$alt = esc_attr( $alt );
	$cap = $caption ? "<figcaption class=\"wp-element-caption\">{$caption}</figcaption>" : '';
	return "<!-- wp:image" . ln_attrs( $attrs ) . " -->\n<figure class=\"{$cls}\"><img src=\"{$src}\" alt=\"{$alt}\"/>{$cap}</figure>\n<!-- /wp:image -->\n\n";
}

/**
 * @param array $items Each: array( file, alt, caption ).
 */
function ln_gallery( array $items, $columns = 3, $class = 'ln-gallery' ) {
	$inner = '';
	foreach ( $items as $it ) {
		$inner .= ln_image( $it[0], $it[1], '', $it[2] ?? '', true );
	}
	$attrs = array(
		'columns'   => $columns,
		'linkTo'    => 'none',
		'className' => $class,
	);
	return "<!-- wp:gallery" . ln_attrs( $attrs ) . " -->\n<figure class=\"wp-block-gallery has-nested-images columns-{$columns} is-cropped {$class}\">{$inner}</figure>\n<!-- /wp:gallery -->\n\n";
}

/**
 * A 3-column grid (Group block, grid layout) for media that a gallery can't
 * hold: each tile can be an Image, Video, or YouTube/Vimeo embed block.
 *
 * @param array $items Each: array( file, alt, caption ) for a placeholder image.
 */
function ln_media_grid( array $items, $class = 'ln-media-grid' ) {
	$inner = '';
	foreach ( $items as $it ) {
		$inner .= ln_image( $it[0], $it[1], '', $it[2] ?? '', true );
	}
	$attrs = array(
		'className' => $class,
		'layout'    => array( 'type' => 'grid', 'columnCount' => 3 ),
	);
	return "<!-- wp:group" . ln_attrs( $attrs ) . " -->\n<div class=\"wp-block-group {$class}\">{$inner}</div>\n<!-- /wp:group -->\n\n";
}

/**
 * @param array $buttons Each: array( label, url, outline? ).
 */
function ln_buttons( array $buttons, $class = '' ) {
	$inner = '';
	foreach ( $buttons as $b ) {
		$outline = ! empty( $b[2] );
		$attrs   = $outline ? array( 'className' => 'is-style-outline' ) : array();
		$cls     = $outline ? 'wp-block-button is-style-outline' : 'wp-block-button';
		$url     = esc_url( $b[1] );
		$inner  .= "<!-- wp:button" . ln_attrs( $attrs ) . " -->\n<div class=\"{$cls}\"><a class=\"wp-block-button__link wp-element-button\" href=\"{$url}\">{$b[0]}</a></div>\n<!-- /wp:button -->\n\n";
	}
	$attrs = $class ? array( 'className' => $class ) : array();
	$cls   = trim( 'wp-block-buttons ' . $class );
	return "<!-- wp:buttons" . ln_attrs( $attrs ) . " -->\n<div class=\"{$cls}\">{$inner}</div>\n<!-- /wp:buttons -->\n\n";
}

/**
 * Group block. $tag is the HTML element; $full makes it edge to edge.
 */
function ln_group( $inner, $class = '', $tag = 'div', $full = false, $content_size = '', $anchor = '' ) {
	$attrs = array();
	if ( $anchor ) {
		$attrs['anchor'] = $anchor;
	}
	if ( 'div' !== $tag ) {
		$attrs['tagName'] = $tag;
	}
	if ( $full ) {
		$attrs['align'] = 'full';
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$attrs['layout'] = $content_size ? array( 'type' => 'constrained', 'contentSize' => $content_size ) : array( 'type' => 'default' );
	$cls             = trim( 'wp-block-group ' . ( $full ? 'alignfull ' : '' ) . $class );
	$id_attr         = $anchor ? ' id="' . esc_attr( $anchor ) . '"' : '';
	return "<!-- wp:group" . ln_attrs( $attrs ) . " -->\n<{$tag}{$id_attr} class=\"{$cls}\">{$inner}</{$tag}>\n<!-- /wp:group -->\n\n";
}

/** A full-width page section with a 1320px content area. */
function ln_section( $inner, $class = '', $anchor = '' ) {
	return ln_group( $inner, trim( 'ln-section ' . $class ), 'section', true, '1320px', $anchor );
}

/**
 * @param array $cols Each: inner markup, or array( inner, width ).
 */
function ln_columns( array $cols, $class = '', $valign = '' ) {
	$inner = '';
	foreach ( $cols as $c ) {
		$html  = is_array( $c ) ? $c[0] : $c;
		$width = is_array( $c ) ? $c[1] : '';
		$attrs = array();
		$cls   = 'wp-block-column';
		$style = '';
		if ( $valign ) {
			$attrs['verticalAlignment'] = $valign;
			$cls                       .= " is-vertically-aligned-{$valign}";
		}
		if ( $width ) {
			$attrs['width'] = $width;
			$style          = " style=\"flex-basis:{$width}\"";
		}
		$inner .= "<!-- wp:column" . ln_attrs( $attrs ) . " -->\n<div class=\"{$cls}\"{$style}>{$html}</div>\n<!-- /wp:column -->\n\n";
	}
	$attrs = array();
	$cls   = 'wp-block-columns';
	if ( $valign ) {
		$attrs['verticalAlignment'] = $valign;
		$cls                       .= " are-vertically-aligned-{$valign}";
	}
	if ( $class ) {
		$attrs['className'] = $class;
		$cls               .= ' ' . $class;
	}
	return "<!-- wp:columns" . ln_attrs( $attrs ) . " -->\n<div class=\"{$cls}\">{$inner}</div>\n<!-- /wp:columns -->\n\n";
}

function ln_list( array $items ) {
	$inner = '';
	foreach ( $items as $i ) {
		$inner .= "<!-- wp:list-item -->\n<li>{$i}</li>\n<!-- /wp:list-item -->\n\n";
	}
	return "<!-- wp:list -->\n<ul class=\"wp-block-list\">{$inner}</ul>\n<!-- /wp:list -->\n\n";
}

function ln_details( $summary, $answer ) {
	return "<!-- wp:details -->\n<details class=\"wp-block-details\"><summary>{$summary}</summary>" . ln_p( $answer ) . "</details>\n<!-- /wp:details -->\n\n";
}

function ln_shortcode( $code ) {
	return "<!-- wp:shortcode -->\n{$code}\n<!-- /wp:shortcode -->\n\n";
}

/** Full-width cover block with a looping background video (or image). */
function ln_cover_video( $video, $inner, $class = '' ) {
	$url   = esc_url( wp_make_link_relative( get_theme_file_uri( 'assets/video/' . $video ) ) );
	$attrs = array(
		'url'            => $url,
		'dimRatio'       => 40,
		'backgroundType' => 'video',
		'minHeight'      => 88,
		'minHeightUnit'  => 'vh',
		'align'          => 'full',
		'className'      => $class,
		'layout'         => array( 'type' => 'constrained', 'contentSize' => '1240px' ),
	);
	$cls   = trim( 'wp-block-cover alignfull ' . $class );
	return "<!-- wp:cover" . ln_attrs( $attrs ) . " -->\n<div class=\"{$cls}\" style=\"min-height:88vh\"><span aria-hidden=\"true\" class=\"wp-block-cover__background has-background-dim-40 has-background-dim\"></span><video class=\"wp-block-cover__video-background intrinsic-ignore\" autoplay muted loop playsinline src=\"{$url}\" data-object-fit=\"cover\"></video><div class=\"wp-block-cover__inner-container\">{$inner}</div></div>\n<!-- /wp:cover -->\n\n";
}

/** A captioned photo card: image + "Title →" link, like a collection tile. */
function ln_photo_card( $file, $alt, $title, $url, $class = 'ln-tile', $sub = '' ) {
	$link = '<a href="' . esc_url( $url ) . '">' . $title . '</a>';
	return ln_group(
		ln_image( $file, $alt, '', '', false )
		. ln_p( $link, 'ln-arrow' )
		. ( $sub ? ln_p( $sub, 'ln-card-sub' ) : '' ),
		trim( 'ln-card-photo ' . $class )
	);
}
