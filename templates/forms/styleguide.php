<?php namespace ProcessWire;

/**
 * Styleguide Form
 *
 * @copyright 2021 NB Communication Ltd
 *
 */

// Set the `to` email address
$to = 'administrator@nbcommunication.com'; //$nb->clientEmail;

// Create the form
$form = $nb->form([
	'class' => [
		'uk-form-stacked',
		'uk-width-2-3@s',
		'uk-width-3-5@m',
	],
	'fields' => [
		[
			'name' => 'name',
			'label' => __('Your Name'),
			'icon' => 'user-circle',
			'required' => true,
			'requiredLabel' => __('Please enter your name'),
			'description' => __('A description in **Markdown**'),
			'notes' => __('Some notes *markdown*'),
			'prependMarkup' => $nb->wrap(__('Some prepended text.'), 'uk-text-small'),
			'appendMarkup' => $nb->wrap(__('Some appended text.'), 'uk-text-small'),
		],
		[
			'type' => 'email',
			'name' => 'email',
			'label' => __('Email Address'),
			'icon' => 'envelope',
			'required' => true,
			'requiredLabel' => __('Please enter a valid email address'),
		],
		[
			'name' => 'tel',
			'label' => __('Telephone'),
			'icon' => 'phone',
			'attr' => ['type' => 'tel'],
			'placeholder' => __('Optional'),
		],
		[
			'type' => 'textarea',
			'name' => 'enquiry',
			'label' => __('Your Enquiry'),
			'icon' => 'pencil-alt',
			'required' => true,
			'requiredLabel' => __('Please enter your enquiry'),
			'rows' => 9,
		],
		[
			'name' => 'collapsed',
			'label' => __('A collapsed field'),
			'collapsed' => 1,
		],
		[
			'type' => 'checkbox',
			'name' => 'checkbox_test',
			'label' => __('Test Checkbox'),
			'value' => 1,
		],
		[
			'type' => 'select',
			'name' => 'select_test_1',
			'label' => __('Test Select'),
			'options' => [
				'1' => __('Option 1'),
				'2' => __('Option 2'),
				'3' => __('Option 3'),
			],
		],
		[
			'type' => 'select',
			'name' => 'select_test_2',
			'label' => __('Test Select 2'),
			'options' => [
				__('Option 1'),
				__('Option 2'),
				__('Option 3'),
			],
		],
		[
			'type' => 'radios',
			'name' => 'radios_test',
			'label' => __('Test Radios'),
			'notes' => __('3 column layout'),
			'options' => [
				'1' => __('Option 1'),
				'2' => __('Option 2'),
				'3' => __('Option 3'),
			],
			'optionColumns' => 3,
		],
		[
			'type' => 'checkboxes',
			'name' => 'checkboxes_test',
			'label' => __('Test Checkboxes'),
			'notes' => __('Single column layout'),
			'options' => [
				'1' => __('Option 1'),
				'2' => __('Option 2'),
				'3' => __('Option 3'),
				'4' => __('Option 4'),
			],
		],
		[
			'type' => 'markup',
			'name' => 'markup',
			'value' => $nb->wrap(
				$nb->wrap(__('InputfieldMarkup'), '<strong><em></em></strong>') . ', ' .
					__('for displaying HTML markup.'),
				'p'
			),
		],
		[
			'type' => 'submit',
			'name' => 'submit',
			'class' => 'uk-button uk-button-primary',
			'value' => __('Send'),
			'textClass' => '',
			'prependMarkup' => isset($nb->captcha) ? $nb->captcha->render() : '',
		],
	],
]);

// Process submission
if($config->ajax) {

	$status = 400;
	$message = __('Sorry, the message could not be sent. Please refresh the page to try again.');

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
						->setTestMode(true) // This form is for testing
						->addTag($input->httpUrl())
						->send();

					$status = $mg->getHttpCode();
					if($sent) $message = __('Thank you, your message has been sent. We will be in touch soon.');
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

$form->prependMarkup = renderHeading(__('Form Styles'));
