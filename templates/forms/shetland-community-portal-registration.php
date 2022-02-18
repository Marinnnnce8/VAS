<?php namespace ProcessWire;

/**
 * Registration Form
 *
 * @copyright 2021 NB Communication Ltd
 *
 */

// Set the `to` email address
$to = $nb->clientEmail;

$button = __('Submit Registration Details');

// Create the form
$form = $nb->form([
	'class' => [
		'uk-form-stacked',
		'uk-width-2-3@s',
		'uk-width-3-5@m',
	],
	'fields' => [
		[
			'type' => 'markup',
			'name' => 'org_heading',
			'value' => renderHeading(__('Organisation')),
		],
		[
			'name' => 'org',
			'label' => __('Name of Group or Organisation'),
			'required' => true,
			'requiredLabel' => __('Please enter the name of the group/organisation.'),
		],
		[
			'type' => 'textarea',
			'name' => 'description',
			'label' => __('Description of Group or Organisation'),
			'required' => true,
			'requiredLabel' => __('Please enter a description.'),
			'rows' => 9,
		],
		[
			'name' => 'org_no',
			'label' => __('Number of members'),
			'attr' => ['type' => 'number', 'min' => 1],
		],
		[
			'name' => 'org_add1',
			'label' => __('Organisation Address line 1'),
		],
		[
			'name' => 'org_add2',
			'label' => __('Organisation Address line 2'),
		],
		[
			'name' => 'org_town',
			'label' => __('Organisation Town'),
		],
		[
			'name' => 'org_region',
			'label' => __('Organisation Region'),
		],
		[
			'name' => 'org_postcode',
			'label' => __('Organisation Postcode'),
		],
		[
			'type' => 'markup',
			'name' => 'contact_heading',
			'value' => renderHeading(__('Main Contact')),
		],
		[
			'name' => 'name',
			'label' => __('Contact name'),
			'required' => true,
			'requiredLabel' => __('Please enter a contact name.'),
		],
		[
			'type' => 'email',
			'name' => 'email',
			'label' => __('Email address'),
			'required' => true,
		],
		[
			'name' => 'tel',
			'label' => __('Telephone'),
			'attr' => ['type' => 'tel'],
			'required' => true,
		],
		[
			'name' => 'fax',
			'label' => __('Fax'),
			'attr' => ['type' => 'tel'],
		],
		[
			'name' => 'contact_add1',
			'label' => __('Address line 1'),
		],
		[
			'name' => 'contact_add2',
			'label' => __('Address line 2'),
		],
		[
			'name' => 'contact_town',
			'label' => __('Town'),
		],
		[
			'name' => 'contact_region',
			'label' => __('Region'),
		],
		[
			'name' => 'contact_postcode',
			'label' => __('Postcode'),
		],
		[
			'type' => 'markup',
			'name' => 'reg_heading',
			'value' => renderHeading(__('Your Registration')),
		],
		[
			'type' => 'radios',
			'name' => 'reg_training',
			'label' => __('Do you require training for website design?'),
			'required' => true,
			'options' => [
				'No' => 'No',
				'Yes - during normal office hours' => 'Yes - during normal office hours',
				'Yes - in the evening' => 'Yes - in the evening',
			],
		],
		[
			'type' => 'checkbox',
			'name' => 'reg_fee',
			'label' => __('Please confirm that you accept the £50 annual fee'),
			'checkboxLabel' => __('I Accept'),
			'value' => __('I Accept'),
			'required' => true,
		],
		[
			'type' => 'checkbox',
			'name' => 'reg_terms',
			'label' => __('Terms and conditions'),
			'skipLabel' => 2,
			'checkboxLabel' => __('I Accept'),
			'description' => sprintf(
				__('Terms and conditions of use for organisations creating sub-sites within the Shetland Community Portal are published %s.'),
				'[' . __('here') . '](' . $pages->get(1786)->url . ')'
			),
			'value' => __('I Accept'),
			'notes' => __('You must agree to and accept our Terms & Conditions by checking this box'),
			'required' => true,
		],
		[
			'type' => 'markup',
			'name' => 'reg_dpa',
			'value' => $nb->wrap('Data Protection Act 1998 - The information provided by you will be used solely for the purpose of the community portal. Should you have any objections to this please confirm your objection in writing and these details will not be included. Shetland Community Portal, Shetland Council of Social Service, Market House, 14 Market Street, Lerwick, Shetland, ZE1 0JP', 'div'),
		],
		[
			'type' => 'checkboxes',
			'name' => 'reg_ml',
			'label' => __('Mailing Lists'),
			'skipLabel' => 2,
			'description' => __('If you want to receive the latest news please select the Mailing Lists you want to subscribe to.'),
			'options' => [
				'Shetland Community Portal' => 'Shetland Community Portal'
			],
		],
		[
			'type' => 'submit',
			'name' => 'submit',
			'class' => 'uk-button uk-button-primary',
			'value' => $button,
			'html' => $nb->wrap([$button, ''], 'span'),
			'textClass' => '',
			'prependMarkup' => isset($nb->captcha) ? $nb->captcha->render() : '',
		],
	],
]);

// Process submission
if($config->ajax) {

	$status = 400;
	$message = __('Sorry, the registration could not be sent. Please refresh the page to try again.');

	// If the form has been submitted
	if($input->post()->count()) {

		// Check reCAPTCHA
		if(isset($nb->captcha) && $nb->captcha->verifyResponse() !== true) {
			$status = 401; // Unauthorized
			$message = __('Please ensure the reCAPTCHA is checked.');
		}

		if($status !== 401) {

			try {

				// Check form
				$form->processInput($input->post);
				$errors = $form->getErrors();

				if(count($errors)) {

					// Return errors
					$status = 412; // Precondition failed
					$message = implode('<br>', $errors);

				} else {

					// Create and Send Email
					$subject = sprintf(
						__('%1$s Form Submission from %2$s'),
						$page->getUnformatted('title'),
						nbUrl($input->httpHostUrl())
					);
					$bodyHTML = $nb->formEmail($form, [
						'subject' => $subject,
						'prepend' => $nb->wrap(
							sprintf(
								__('This is a response sent using the %s form on your website:'),
								$page->title
							),
							'p'
						),
					]);

					$mg = $mail->new();
					$sent = $mg->to($to)
						->replyTo($form->get('email')->value, $form->get('name')->value)
						->subject($subject)
						->bodyHTML($bodyHTML)
						->addTag($input->httpUrl())
						->send();

					$status = $mg->getHttpCode();
					if($sent) $message = __('Thank you, your registration has been sent. We will be in touch soon.');
				}

			} catch(WireException $e) {

				// CSRF Exception
				$message = sprintf(__('%s Please refresh the page and try again.'), $e->getMessage());
			}
		}
	}

	// Respond
	$nb->respond($message, [
		'NB-Form-State' => $status,
	]);
}
