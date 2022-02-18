<?php namespace ProcessWire;

/**
 * Items
 *
 */

// Set the canonical URL (adds ?displayall=1)
$page->urlCanonicalAll();

// The init value
$init = (int) $nb->getRequestHeader('NB-Items-Init');

// The key for the request cursor (unique cursor for each url query)
$cursorKey = 'itemsCursor_' . md5(rtrim(str_replace('nc=1', '', $input->url(true)), '?'));

// Get the number of items loaded already this session for this request
$cursor = (int) $session->get($cursorKey);

// Make sure that $selectors is an array
$selectors = $selectors ?? [];
if(!is_array($selectors)) $selectors = [];

// Make sure that a limit is set
$selectors['limit'] = $selectors['limit'] ?? 0;
// If displayall, set the limit to 0 to show all items
if($input->get->bool($nb::displayAll)) $selectors['limit'] = 0;

// Make sure that a start is set (should be 0 in almost all cases)
$selectors['start'] = $selectors['start'] ?? 0;

// Get base64 encoded selectors
// This is a way of passing more complex selectors in the query string
// e.g. date_start<=now, date_end>=now, location%=example
$getSelectors = $input->get->text('selectors');
if($getSelectors) {
	$getSelectors = base64_decode($getSelectors);
	if($getSelectors) {
		$getSelectors = json_decode($getSelectors, 1);
		if(is_array($getSelectors)) {
			$selectors = array_merge($selectors, $getSelectors);
		}
	}
}

// Set the default options
$options = array_merge([
	'filter' => '',
	'loadMore' => (bool) $selectors['limit'],
	'message' => false,
	'noResults' => ukAlert(sprintf(__('Sorry no %s could be found.'), $page->template->name), 'danger'),
], isset($options) && is_array($options) ? $options : []);

// If there is a limit
if($selectors['limit']) {

	if($init) {
		if($cursor && $init < $cursor) {
			$selectors['start'] = $init;
			$selectors['limit'] = $cursor - $init;
		} else {
			if(!$cursor) $session->set($cursorKey, $init);
			$nb->respond('');
		}
	} else if($config->ajax && $nb->getRequestHeader('NB-Items-LoadMore')) {
		// If a load more request, nudge the start value
		$selectors['start'] = $cursor;
	} else if($cursor) {
		// Else if items have already been loaded in this session,
		// set the initial limit to the number already loaded
		$selectors['limit'] = $cursor;
	} else if(!$config->ajax) {
		// If not an ajax request, set the cursor to the limit
		// This is the number of items loaded on load
		$cursor = $selectors['limit'];
		$session->set($cursorKey, $cursor);
	}
}

if($config->ajax) {

	$response = $nb->wrap($options['noResults'], 'uk-width-1-1');

	// Get the items
	$items = getItems($page, $selectors);
	if($items->count) {
		// Render and update the cursor
		$cursor = $selectors['start'] + $items->count;
		$response = renderItems($items);
		$session->set($cursorKey, $cursor);
	}

	// Respond
	$nb->respond($response, [
		'NB-Items-Cursor' => $cursor,
		'NB-Items-Total' => $items->getTotal(),
	]);
}

// If there is a $form array
// Process as a form and pass to nbItems
if(isset($form) && is_array($form)) {
	// Prepend filters
	$options['filter'] = nbForm($nb->form(array_merge([
		'method' => 'get',
		'class' => 'uk-form-stacked uk-margin-bottom',
	], $form))->render(), 'default');
}

$content .= $nb->wrap(nbItems(getItems($page, $selectors), $options), 'uk-margin-medium-top');
