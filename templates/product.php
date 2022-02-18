<?php namespace ProcessWire;

/**
 * Product
 *
 */

if($page->thumb->count) {
	$content .= $nb->wrap(ukContainer(gallery($page->thumb), $nb->ukContainer), '<div class="gallery">');
}

include 'tpl/page.php';

$after .= ukContainer(
	$nb->wrap(
		(forSale($page) ?
			$nb->wrap(
				$nb->wrap(price($page->price), '<span class="uk-text-large">') .
				$nb->wrap(' including postage', '<span class="uk-text-small">'),
				'uk-margin-small-bottom'
			) .
			$nb->wrap(
				paypalAdd($page) . paypalView(),
				'uk-flex uk-flex-between uk-flex-middle'
			) :
			''
		) .
		ukSection(
			ukButtonLink(sprintf(__('Back to %s'), $page->parent->title), $page->parent->url, 'default', 'small')
		),
		'uk-margin-medium-top'
	),
	$nb->ukContainer
);
