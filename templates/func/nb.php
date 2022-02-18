<?php namespace ProcessWire;

/**
 * NB Functions
 *
 * @copyright 2021 NB Communication Ltd
 *
 */

/**
 * Render a form
 *
 * @param string $form The form.
 * @param string|false $container The uk-container to use.
 * @return string
 *
 */
function nbForm($form, $container = '') {
	if($container !== false) $form = ukContainer($form, $container ?: nb()->ukContainer);
	return nb()->wrap($form, 'nb-form');
}

/**
 * Return a telephone number for the href attribute
 *
 * ~~~~~
 * // Output NB Communication's telephone number as a link
 * $tel = '+44 01595 696155';
 * echo "<a href='tel:" . formatTelHref($tel) . "'>$tel</a>";
 * ~~~~~
 *
 * @param string $tel The telephone number to be formatted.
 * @return string
 *
 */
function nbFormatTelHref($tel) {
	// If in international format, remove the bracketed zero
	if(strpos($tel, '(0)') !== false && strpos($tel, '+') !== false) {
		$tel = str_replace('(0)', '', $tel);
	}
	return trim(preg_replace('/[^0-9]/', '', $tel));
}

/**
 * Render items
 *
 * @param PageArray $items
 * @param array $options Options to modify behaviour:
 * - `container` (array): An array of classes for the uk-grid container.
 * - `filter` (string): An HTML string of rendered filters (default="").
 * - `loadMore` (bool): Should the (default=false).
 * - `message` (string): The query to display in a message (default="").
 * - `noResults` (string): Output if there are no items to display (default="").
 * - `ukContainer` (string): The uk-container class to use (default="large").
 * @return string
 *
 */
function nbItems(PageArray $items, array $options = []) {

	$nb = $items->wire('nb');

	// Set the default options
	$options = array_merge([
		'container' => [
			'uk-child-width-1-2@s',
			'uk-child-width-1-3@m',
			'uk-grid-match',
		],
		'filter' => '',
		'loadMore' => false,
		'message' => '',
		'noResults' => '',
		'ukContainer' => 'default',
		'hex' => false,
	], $options);

	$count = $items->count;
	$total = $items->getTotal();

	$options['container'][] = 'nb-items-container';

	if($options['hex'] === true) $options['hex'] = 'uk-child-width-1-2@s uk-child-width-1-3@l';

	return $nb->wrap(
		($options['hex'] ?
			ukGrid(
				$items->explode(function($item) use ($nb) {
					/*$url = $item->url;
					if($nb->wire('page')->template->name === 'organisations') {
						foreach($nb->_subsites as $domain => $id) {
							if($item->id === $id) {
								$url = "https://$domain/";
							}
						}
					}*/
					return $nb->wrap(
						$nb->wrap(
							$nb->wrap(
								$nb->link($item->url, $item->getImage()->render()),
								'<div class="media">'
							) .
							$nb->wrap(
								$nb->link(
									$item->url,
									renderHeading($item->title, 3) .
									$nb->wrap(
										__('Find out more') . renderIcon('caret-right'),
										'<span class="uk-button uk-button-text">'
									) .
									'<span class="circle"></span>'
								),

								'<div class="entry-body">'
							),
							'div'
						) .
						$nb->attr([
							'src' => $nb->wire('config')->urls->templates . 'img/cell.svg',
							'alt' => '',
							'dataUkSvg' => true,
						], 'img'),
						'<div class="entry-hex">'
					);
				}),
				$options['hex']
			) :
			ukContainer(
				($options['filter'] ? $nb->wrap($options['filter'], 'nb-items-filter uk-margin-bottom') : '') .
				($count ?
					($options['message'] ?
						$nb->wrap(
							ukAlert(sprintf(
								__('Showing %1$s of %2$s found for %3$s:'),
								$nb->wrap($count, '<span data-cursor>'),
								$nb->wrap($total, '<span data-total>'),
								$nb->wrap($options['message'], 'strong')
							), 'primary'),
							'nb-items-message uk-margin-bottom'
						) :
						''
					) .
					ukGrid(
						renderItems($items),
						[
							'class' => $options['container'],
							'dataUkGrid' => true,
						]
					) .
					($options['loadMore'] ?
						$nb->wrap(
							ukButton(is_string($options['loadMore']) ? $options['loadMore'] : __('Load More'), 'primary'),
							'uk-text-center uk-margin-large-top nb-items-more' .
								($total > $count ? '' : ' uk-hidden')
						) :
						''
					) :
					$options['noResults']
				),
				$options['ukContainer']
			)
		),
		[
			'dataUkNbItems' => true,
		],
		'div'
	);
}

/**
 * Render the email address as an obfuscated nb-mailto link
 *
 * ~~~~~
 * // Output a mailto link
 * echo nbMailto('tester@nbcommunication.com', 'Email us', [
 *     'class' => 'email-link',
 * ]);
 * ~~~~~
 *
 * @param string $email The email address.
 * @param string $text The value to display.
 * @param array $attrs Other attributes to be rendered.
 * @return string
 *
 */
function nbMailto($email = '', $text = '', array $attrs = []) {

	// Shortcut
	if(is_array($text)) {
		$attrs = $text;
		$text = '';
	}

	// Add a label if it does not already exist
	if(!isset($attrs['ariaLabel'])) $attrs['ariaLabel'] = __('Send an email');

	$email = sanitizer()->email($email);
	return $email ? nbObfuscate([
		'href' => "mailto:$email",
		'text' => $text ? str_replace('{email}', $email, $text) : $email,
	], $attrs) : '';
}

/**
 * Render using javascript obfuscation
 *
 * ~~~~~
 * // Obfuscate text to be rendered by Javascript
 * echo nbObfuscate('Testing 123');
 * ~~~~~
 *
 * @param string|array $data The data to obfuscate.
 * @param array $attrs Other attributes to be rendered on the element.
 * @param string $tag The html tag to use for the element (default='span').
 * @return string
 *
 */
function nbObfuscate($data, $attrs = [], $tag = 'span') {

	// Shortcuts
	if(is_string($attrs)) {
		$tag = $attrs;
		$attrs = [];
	}

	if(is_array($data)) {
		if(isset($data['href'])) $tag = 'a';
		$data = json_encode($data);
	}

	return nb()->attr(array_merge($attrs, [
		'dataUkNbObfuscate' => base64_encode($data),
	]), $tag, true);
}

/**
 * Render a phone number as a nb-tel link
 *
 * It doesn't actually return a 'href' link, but a data-nb-tel one, which
 * the `$nb` javascript turns into a `href='tel:{tel}'` link. This is to prevent
 * scrapers from harvesting numbers from tel: href values.
 *
 * ~~~~~
 * // Output the client's telephone number as a tel link
 * echo nbTel($nb->clientTel, 'Telephone us');
 * ~~~~~
 *
 * @param string $tel The telephone number.
 * @param string $text The value to display.
 * @param array $attrs Other attributes to be rendered.
 * @return string
 *
 */
function nbTel($tel = '', $text = '', array $attrs = []) {

	// Shortcut
	if(is_array($text)) {
		$attrs = $text;
		$text = '';
	}

	// Add a label if it does not already exist
	if(!isset($attrs['ariaLabel'])) $attrs['ariaLabel'] = __('Make a call');

	return !empty($tel) ? nbObfuscate([
		'href' => 'tel:' . nbFormatTelHref($tel),
		'text' => $text ? str_replace('{tel}', $tel, $text) : $tel,
	], $attrs) : '';
}

/**
 * Return a url with or without the protocol
 *
 * This is primarily for returning urls without http:// or https://.
 * If protocol is set to true, then the url is only run through $sanitizer->url().
 *
 * ~~~~~
 * // Output NB Communication's URL without the protocol
 * echo nbUrl('https://www.nbcommunication.com/');
 * ~~~~~
 *
 * @param string $url The URL to be processed.
 * @param bool $protocol Should the protocol be displayed?
 * @return string
 *
 */
function nbUrl($url = '', $protocol = false) {
	$url = sanitizer()->url($url);
	return $protocol ? $url : trim(str_replace([
		'https',
		'http',
	], '', str_replace('://', '', $url)), '/');
}
