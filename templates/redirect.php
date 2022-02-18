<?php namespace ProcessWire;

/**
 * Redirect
 *
 */

$link = $page->link_page ? $page->link_page->url : $page->link;
if($link) {
	$session->redirect($link);
} else {
	throw new Wire404Exception();
}
