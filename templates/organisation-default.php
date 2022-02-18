<?php namespace ProcessWire;

/**
 * Organisation - Default Page
 *
 */

foreach($nb->_subsites as $domain => $id) {
	$p = $page->closest('template=organisation');
	if($id === $p->id) {
		if($config->httpHost !== $domain) {
			$session->redirect("https://$domain/" . ltrim(str_replace($p->url, '', $page->url), '/'));
		}
	}
}

include 'tpl/page.php';
