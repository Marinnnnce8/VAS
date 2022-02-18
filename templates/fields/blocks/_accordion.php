<?php namespace ProcessWire;

/**
 * Accordion Block
 *
 */

if($page->items->count) {
	echo $nb->wrap(
		ukContainer(
			renderHeading($page->title) . ukAccordion($page->items),
			$nb->ukContainer
		),
		'<div class="accordion">'
	);
}
