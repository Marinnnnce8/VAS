<?php namespace ProcessWire;

/**
 * Page
 *
 */

$content .= getContent($page);

if($page->hasChildren || $page->siblings(false)->count) {

	$selectors = [];
	if(!in_array($page->template->name, [
		'default',
		'organisations',
		'organisation',
		'organisation-default',
	])) {
		$selectors['limit'] = 3;
		if($page->hasField('tags') && $page->tags->count) {
			$selectors['tags'] = $page->tags;
		}
	}

	$isDefaultOrg = $page->template->name === 'organisation-default';

	$after .= ukSection(
		'<div class="section-bg"></div>' .
		ukContainer(renderRelated($page, $selectors, !$isDefaultOrg), $isDefaultOrg ? $nb->ukContainer : 'default'),
		['class' => ['section-gateway']]
	);
}
