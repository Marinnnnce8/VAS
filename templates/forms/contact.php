<?php namespace ProcessWire;

/**
 * Contact Form
 *
 * @copyright 2021 NB Communication Ltd
 *
 */

// Set the `to` email address
$to = $nb->clientEmail;

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
			'required' => true,
			'requiredLabel' => __('Please enter your name.'),
		],
		[
			'type' => 'email',
			'name' => 'email',
			'label' => __('Email Address'),
			'required' => true,
		],
		[
			'name' => 'tel',
			'label' => __('Telephone'),
			'attr' => ['type' => 'tel'],
			'placeholder' => __('Optional'),
		],
		[
			'type' => 'textarea',
			'name' => 'enquiry',
			'label' => __('Your Enquiry'),
			'required' => true,
			'requiredLabel' => __('Please enter your enquiry.'),
			'rows' => 9,
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

$form->prependMarkup = renderHeading(sprintf(__('%s Form'), $page->title));
