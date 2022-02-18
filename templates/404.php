<?php namespace ProcessWire;

/**
 * 404
 *
 */

if(array_key_exists($config->httpHost, $nb->_subsites)) {
	$p = $pages->get("/$nb->_it");
	if($p->id) {
		$session->redirect("https://{$config->httpHosts[1]}{$p->url}");
	}
}

$content .= getContent($page);
