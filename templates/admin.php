<?php namespace ProcessWire;

/**
 * Admin template just loads the admin application controller,
 * and admin is just an application built on top of ProcessWire.
 *
 * This demonstrates how you can use ProcessWire as a front-end
 * to another application.
 *
 * Feel free to hook admin-specific functionality from this file,
 * but remember to leave the require() statement below at the end.
 *
 */

$isOrganisation = function(User $user) {
	return $user->hasRole('organisation');
};

$getOrganisation = function(User $user) {
	return $user->wire('pages')->get(1158)->child("include=all,owner={$user->name}");
};

$redirectOrganisation = function(Page $page, User $user) use ($getOrganisation) {
	$redirect = null;
	$org = $getOrganisation($user);
	if(!$org->id) {
		$redirect = $page->wire('config')->urls->admin;
	} else if($page !== $org && !$page->parents->has($org)) {
		$redirect = $org->editURL;
	}
	if($redirect) $page->wire('session')->redirect($redirect);
};

// If the user is an organisation find their page and redirect to the editor
$session->addHook('loginSuccess', function(HookEvent $event) use ($isOrganisation, $getOrganisation) {
	$user = $event->arguments(0);
	if($isOrganisation($user)) {
		$org = $getOrganisation($user);
		if($org->id) {
			$event->object->redirect($org->editURL);
		} else {
			$event->object->error(sprintf(__('Sorry, an organisation page could not be found for your user account. Please contact %s to resolve this.'), $event->wire('nb')->siteName));
		}
	}
});

$wire->addHookAfter('PagePermissions::pageEditable()', function(HookEvent $event) use ($isOrganisation, $getOrganisation) {
	$page = $event->arguments(0);
	$user = $event->wire('user');
	if($isOrganisation($user)) {
		$org = $getOrganisation($user);
		$event->return = $page === $org || $page->parents->has($org);
	}
});

// If the user is an organisation and tries to edit a page that doesn't belong to them
$wire->addHookAfter('ProcessPageEdit::loadPage', function(HookEvent $event) use ($isOrganisation, $redirectOrganisation) {
	$user = $event->wire('user');
	if($isOrganisation($user) && !$event->wire('input')->get->bool('InputfieldFileAjax')) {
		$redirectOrganisation($event->return, $user);
	}
});

// If the user is an organisation and tries to add a page that doesn't belong to them
$wire->addHookAfter('ProcessPageAdd::execute', function(HookEvent $event) use ($isOrganisation, $redirectOrganisation) {
	$user = $event->wire('user');
	if($isOrganisation($user)) {
		$redirectOrganisation($event->wire('pages')->get($event->wire('input')->get('parent_id')), $user);
	}
});

// Make sure images from the Page are selectable by default on repeater items
if($page->process === 'ProcessPageEditImageSelect') {
	$editorPageId = (int) $input->get('edit_page_id');
	$imagesPageId = (int) $input->get('id');
	if($editorPageId && $imagesPageId && $editorPageId !== $imagesPageId) {
		$imagesPage = $pages->get($imagesPageId);
		if($imagesPage instanceof RepeaterPage) {
			$input->get->id = $editorPageId;
		}
	}
}

// Place recently modified unpublished posts to the top of the list so they are easier to find
$wire->addHookBefore('ProcessPageList::find', function(HookEvent $event) {
	$selectorString = $event->arguments(0);
	$page = $event->arguments(1);
	if($page->sortfield() === '-date_pub') {
		$recent = $page->children('date_pub=,include=all,sort=modified,modified>' . strtotime('-1 month'));
		$all = $page->children("id!=$recent,status<" . Page::statusMax);
		foreach($recent as $p) $all->prepend($p);
		$event->replace = true;
		$event->return = $all->find($selectorString);
	}
});

$pages->addHook('saveReady', function(HookEvent $event) {

	$page = $event->arguments('page');
	$nb = $event->wire('nb');

	// If the page has a publish date field, is published and has no value
	// Set publish date
	foreach($nb->fieldsPublish as $field) {
		if($page->hasField($field) && !$page->get($field) && !$page->isUnpublished()) {
			$page->set($field, time());
		}
	}

	// Hide any pages that should be hidden
	if(in_array($page->template->name, $nb->templatesHidden) && !$page->isHidden()) {
		$page->addStatus(Page::statusHidden);
	}

	// While site is in development, if the title changes warn the user about the page name
	if(!$nb->siteLive && $page->isChanged('title') && $page->rootParent->id !== 2) {
		if($page->name !== $event->wire('sanitizer')->pageName($page->title) && !$page->isChanged('name')) {
			$event->wire('session')->warning(__('Please update the page name in the Settings tab.'));
		}
	}

});

require($config->paths->adminTemplates . 'controller.php');
