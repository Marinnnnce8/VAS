<?php namespace ProcessWire;

/**
 * Redirect
 *
 */

$nb->cspExtend('script-src', "widget.search.volunteerscotland.net 'unsafe-eval'");
$nb->cspExtend('connect-src', 'search.volunteerscotland.net');

include 'tpl/page.php';

$content .= $nb->wrap(
	ukContainer(
		'<script src="https://widget.search.volunteerscotland.net/widget.js" data-url="https://search.volunteerscotland.net/widget/opportunities" data-tsi="shetland" data-colour-primary="#0369c7" data-colour-secondary="#1f1447"></script>',
		$nb->ukContainer
	),
	'div'
);
