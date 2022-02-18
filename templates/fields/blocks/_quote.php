<?php namespace ProcessWire;

/**
 * Quote Block
 *
 */

if($page->summary) {

	$footer = $page->title;
	if($page->headline) $footer .= $nb->wrap(", $page->headline", 'span');

	echo $nb->wrap(
		ukContainer(
			$nb->wrap(
				renderSummary($page->summary) .
				($footer ? $nb->wrap($footer, 'footer') : ''),
				'blockquote'
			),
			$nb->ukContainer
		),
		'<div class="quote">'
	);
}
