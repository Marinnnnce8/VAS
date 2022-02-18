<?php namespace ProcessWire;

/**
 * Organisations
 *
 */

foreach($nb->_subsites as $domain => $id) {
	if($id === $page->id) {
		if($config->httpHost !== $domain) {
			$session->redirect("https://$domain/");
		}
	}
}

include 'tpl/page.php';
