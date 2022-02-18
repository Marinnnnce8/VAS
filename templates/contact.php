<?php namespace ProcessWire;

/**
 * Contact
 *
 */

include 'forms/contact.php';
include 'tpl/page.php';

if($page->checkbox) {

	$address = WireArray::new();
	if($nb->clientName) $address->add($nb->wrap($nb->clientName, 'strong'));
	if($nb->clientAddress) $address->add(nl2br($nb->clientAddress));

	$contact = WireArray::new();
	// Email
	if($nb->clientEmail) $contact->add($nb->wrap(__('Email'), 'strong') . ': ' . nbMailto($nb->clientEmail));
	if($nb->clientEmails) {
		$contact->add($nb->clientData('Emails')->implode('<br>', function($value, $key) {
			return nb()->wrap($key, 'strong') . ': ' . nbMailto($value);
		}));
	}

	// Telephone
	if($nb->clientTel) $contact->add($nb->wrap(__('Telephone'), 'strong') . ': ' . nbTel($nb->clientTel));
	if($nb->clientTels) {
		$contact->add($nb->clientData('Tels')->implode('<br>', function($value, $key) {
			return nb()->wrap($key, 'strong') . ': ' . nbTel($value);
		}));
	}

	// Social
	/*if($nb->clientSocial) {
		$contact->add($nb->clientData('Social')->each($nb->wrap(
			renderIcon('{key}'),
			[
				'href' => '{value}',
				'class' => [
					'uk-display-inline-block',
					'uk-margin-small-right',
					'uk-margin-small-top',
				],
				'target' => '_blank',
				'rel' => 'noopener',
			],
			'a'
		)));
	}*/

	$content .= content(ukGrid([
		$nb->wrap($address->implode('<br>'), 'p'),
		$nb->wrap($contact->implode('<br>'), 'p'),
	], ['uk-grid-small', 'uk-child-width-1-2@s']));
}

$content .= nbForm($form->render());
