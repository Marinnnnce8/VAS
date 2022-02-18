<?php namespace ProcessWire;

/**
 * Embed Block
 *
 */

if($page->html) {
	if(!$nb->cspIsOpen) {
		$open = "'self' https:";
		$nb->cspExtend([
			'script-src' => $open,
			'style-src' => $open,
			'connect-src' => $open,
			'media-src' => $open,
			'frame-src' => $open,
			'form-action' => $open,
		]);
		$nb->set('cspIsOpen', true);
	}
	echo $nb->wrap(
		ukContainer(
			renderHeading($page->title) .
			$page->html,
			$nb->ukContainer
		),
		'<div class="embed">'
	);
}
