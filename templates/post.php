<?php namespace ProcessWire;

/**
 * Post
 *
 */

$published = $page->getUnformatted('date_pub|published');
$page->jsonld([
	'@type' => 'Article', //https://jsonld.com/article/
	'headline' => $page->h1,
	'author' => !$page->createdUser->id || $page->createdUser->isSuperUser() ? $nb->jsonldPub : [
		'@type' => 'Person',
		'name' => $page->createdUser->get('title|name'),
	],
	'publisher' => $nb->jsonldPub,
	'mainEntityOfPage' => true,
	'datePublished' => $published ? $datetime->date('c', $published) : '',
	'dateModified' => $datetime->date('c', $page->modified),
]);

if($published) $before .= $nb->wrap($datetime->date('F jS Y', $published), 'uk-text-meta');
include 'tpl/page.php';
$after .= ukSection(ukContainer(ukPrevNext($page), $nb->ukContainer));
