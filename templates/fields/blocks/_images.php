<?php namespace ProcessWire;

/**
 * Images Block
 *
 */

$c = $page->gallery->count;
if($c) {

	$slideshow = $page->checkbox;
	$type = $slideshow ? 'slideshow' : 'gallery';

	$out = '';
	$options = [];

	if($c < 4) $options['perRow'] = 2;

	// If a caption is specified and there is only one image
	// Set the image description to the caption
	if($page->headline && $c == 1) $options['caption'] = $page->headline;

	// Add a title if specified
	if($page->title) {
		$out .= renderHeading($page->title);
	}

	// Render the gallery
	$out .= $slideshow ? ukSlideshow($page->gallery) : gallery($page->gallery, $options);

	// Add a caption if specified
	if($page->headline) {
		$out .= $nb->wrap(
			$sanitizer->entitiesMarkdown($page->headline, ['doubleEncode' => false]),
			"uk-text-center uk-margin-small-top $type-caption"
		);
	}

	// Wrap in a block
	echo $nb->wrap(
		ukContainer($out, $nb->ukContainer),
		"<div class='$type'>"
	);
}
