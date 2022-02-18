<?php namespace ProcessWire;

/**
 * Sitemap
 *
 */

$content .= content(ukNav($pageHome, [
	'attrs' => [
		'class' => 'nb-sitemap',
		'dataUkNav' => false,
	],
]));
