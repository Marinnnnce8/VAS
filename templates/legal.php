<?php namespace ProcessWire;

/**
 * Legal
 *
 * @todo Legal text needs to be reviewed
 *
 */

if($nb->siteLive && !$page->body) {

	// If the site is live,
	// Generate legal text if not already generated.

	$type = explode('-', $page->name)[0];
	$file = $config->paths->assets . "nb/legal/$type.md";
	if(file_exists($file)) {

		$text = file_get_contents($file);

		// Replace common tags
		$text = str_replace('[[clientName]]', $nb->clientName, $text);
		$text = str_replace('[[siteUrl]]', $nb->wrap(
			str_replace('//', '', $input->httpHostUrl('')),
			['href' => $config->urls->root],
			'a'
		), $text);

		// Link checker
		$pageContact = $pages->get('template=contact');
		$pageTerms = $pages->get(1037);
		$pagePrivacy = $pages->get(1038);
		$pageDisclaimer = $pages->get(1039);

		if(!$pageContact->id || $pageContact->isUnpublished()) {
			$text = str_replace('(/contact)', "(mailto:{$nb->clientEmail})", $text);
		} else if($pageContact->name !== 'contact') {
			str_replace('(/contact)', "($pageContact->url)", $text);
		}

		if($pageTerms->name !== 'terms') str_replace('(/terms)', "($pageTerms->url)", $text);
		if($pagePrivacy->name !== 'privacy') str_replace('(/privacy)', "($pagePrivacy->url)", $text);
		if($pageDisclaimer->name !== 'disclaimer') str_replace('(/disclaimer)', "($pageDisclaimer->url)", $text);

		$text = $sanitizer->entitiesMarkdown($text, true);

		if($type == 'privacy') {

			// Contact Details
			$rows = [['Legal name', $nb->clientName]];
			if($nb->clientEmail) $rows[] = ['Email address', nbMailto($nb->clientEmail)];
			if($nb->clientAddress) $rows[] = ['Postal address', str_replace("\n", ', ', $nb->clientAddress)];
			$bit = '<h3>3.';
			$text = str_replace($bit, $nb->table([], $rows) . $bit, $text);

			// Cookies
			$rows = [
				[
					'Content Management System',
					'wires<br>wires_challenge',
					'This website uses a content management system, ' .
						'and uses cookies that are required for aspects of this website to work.',
				],
			];
			if($modules->isInstalled('MarkupCookieConsentOsano')) {
				$rows[] = [
					'Cookie Consent',
					'cookieconsent_status',
					'This cookie stores the status of the cookie consent ' .
						'message that appears when you first use this website.',
				];
			}
			if($nb->googleAnalytics) {
				$rows[] = [
					'Google Analytics',
					'_ga<br>_gat<br>_gid<br>_utma<br>_utmb<br>_utmc<br>_utmt<br>_utmz',
					'This website uses Google Analytics, a web analytics service provided by Google Inc. ("Google"). ' .
						'Google Analytics uses cookies to help collect data on how users use this website. ' .
						'Information about the cookies set by Google, and their purpose, can be found at: ' .
						"<a href='https://developers.google.com/analytics/devguides/collection/analyticsjs/cookie-usage'>" .
							'https://developers.google.com/analytics/devguides/collection/analyticsjs/cookie-usage. ' .
						'</a>' .
						"Google's Privacy Policy can be found at: " .
						"<a href='https://policies.google.com/privacy'>https://policies.google.com/privacy</a><br>" .
						'To opt-out of analysis by Google Analytics, both on this website and other websites, ' .
						"please see <a href='https://tools.google.com/dlpage/gaoptout'>https://tools.google.com/dlpage/gaoptout</a>.",
				];
			}

			$bit = '<p>Your web browser should';
			$text = str_replace($bit, $nb->table(['Cookie / Local Storage', 'Example Name(s)', 'Purpose'], $rows) . $bit, $text);

			// Providers
			$bit = '<h3>15.';
			$text = str_replace(
				$bit,
				$nb->table(
					['Provider', 'Description'],
					[
						[
							$nb->wrap('Google Inc.', 'strong'),
							'We may use services from Google Inc. ' .
								'to provide various services on this website, including "Google Analytics" ' .
								'to help collect data on how users use this website, and "Google Fonts" ' .
								'to provide hosting for font files. Their privacy policy can be found here: ' .
								"<a href='https://policies.google.com/privacy'>https://policies.google.com/privacy.</a>",
						],
						[
							$nb->wrap('NB Communication Ltd', 'strong'),
							'We use NB Communication Ltd - ' .
								"<a href='https://www.nbcommunication.com/'>https://www.nbcommunication.com/</a> - " .
								'to provide website hosting and marketing services on our behalf.',
						],
						[
							$nb->wrap('Node4 Ltd', 'strong'),
							'We use Node4 Ltd - ' .
								"<a href='https://www.node4.co.uk/'>" .
								'https://www.node4.co.uk/</a> - to provide hosting services for this website.',
						],
						[
							$nb->wrap('cdnjs.com', 'strong'),
							'We use cdnjs - ' .
								"<a href='https://cdnjs.com/'>https://cdnjs.com/</a> - " .
								'to provide hosting for some code libraries used by this website.',
						],
					]
				) . $bit,
				$text
			);

			$text .= $nb->wrap(
				$nb->wrap($nb->wrap('This privacy policy was last updated on ' . date('F jS, Y'), 'em') . '.', 'small'),
				'p'
			);
		}

		$page->setAndSave('body', $text);
	}

} else if(!$page->body && strpos($config->httpHost, 'nbcom.co.uk') !== false) {

	$page->body = ukAlert('If no content is specified for this page it will be generated when the site is launched.', 'warning uk-text-center');
}

$content .= getContent($page);
