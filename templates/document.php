<?php namespace ProcessWire;

/**
 * Document
 *
 */

if($page->body) {

	include 'tpl/page.php';

} else if($page->files->count) {

	$file = $page->files->first;
	$disposition = 'attachment';
	if($file->ext === 'pdf') {
		$disposition = 'inline';
		header('Content-Type: application/pdf');
	}

	header("Content-Disposition: $disposition; filename=$page->name.$file->ext");
	readfile($file->filename);

	die();

} else if($page->link) {

	$session->redirect($page->link);

} else {

	include "forms/$page->name.php";

	if(isset($form)) {

		$content .= nbForm($form->render());

	} else {
		throw new Wire404Exception();
	}
}
