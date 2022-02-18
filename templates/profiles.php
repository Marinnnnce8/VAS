<?php namespace ProcessWire;

/**
 * Profiles
 *
 */

include 'tpl/page.php';

$profiles = $page->children('include=hidden');
if($profiles->count) {

	$out = '';
	foreach($profiles as $p) {

		$page->jsonld([
			'@type' => 'Person', // https://jsonld.com/person/
			'name' => $p->title,
			'jobTitle' => $p->headline,
			'image' => $p->thumb->count ? $p->thumb->first->httpUrl : '',
		], "profile$p->id");

		$out .= $nb->wrap(

			// Profile Image
			($p->thumb->count ? $nb->wrap($p->thumb->first->width(280)->render([
				'alt' => $p->title,
			]), '<figure class="align_right">') : '') .

			// Heading
			$nb->wrap($p->title, 'uk-h3 uk-margin-remove-bottom uk-margin-small-top') .

			// Job/Role
			($p->headline ? $nb->wrap($p->headline, 'uk-h5 uk-margin-remove-top') : '') .

			// Short Biography
			$p->body .

			($p->tel ? $nb->wrap(renderIcon('tel') . ' ' . nbTel($p->tel), 'div') : '') .
			($p->email ? $nb->wrap(renderIcon('envelope') . ' ' . nbMailto($p->email), 'div') : ''),

			'uk-margin-medium-bottom profile'
		);
	}

	$content .= content($nb->wrap(
		ukGrid($out, 'uk-child-width-1-1'),
		'<div class="profiles">'
	));
}
