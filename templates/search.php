<?php namespace ProcessWire;

/**
 * Search
 *
 */

$selectors = ['limit' => 12];

$q = $input->get->text('q');
if($q) $selectors['q'] = $q;

$options = [
	'message' => $q,
	'noResults' => ukAlert($q ?
		sprintf(__('Sorry no results were found for %s.'), $nb->wrap($q, 'strong')) :
		__('Please enter a search query.'),
		'danger'
	),
];

include 'tpl/items.php';
