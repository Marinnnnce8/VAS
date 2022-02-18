<?php namespace ProcessWire;

/**
 * Organisation
 *
 */

foreach($nb->_subsites as $domain => $id) {
	if($id === $page->id) {
		if($config->httpHost !== $domain) {
			$session->redirect("https://$domain/");
		}
	}
}

$contact = [];
foreach([
	'title',
	'address',
	'tel',
	'email',
	'link',
	'social',
] as $key) {
	$value = $page->get($key);
	if($value) {
		switch($key) {
			case 'address':
				$value = str_replace("\n", ', ', $value);
				break;
			case 'tel':
				$value = nbTel($value);
				break;
			case 'email':
				$value = nbMailto($value);
				break;
			case 'link':
				$value = $nb->link($value, nbUrl($value));
				break;
			case 'social':
				$links = '';
				foreach(explode("\n", $value) as $link) {
					$icon = 'globe';
					foreach([
						'facebook',
						'twitter',
						'instagram',
						'youtube',
						'linkedin',
					] as $i) {
						if(strpos($link, $i) !== false) {
							$icon = $i;
							break;
						}
					}
					$links .= $nb->link($link, renderIcon($icon), [
						'class' => 'uk-icon-button',
						'ariaLabel' => sprintf(__('Find us on %s'), $icon),
					]);
				}
				$value = $links;
				break;
		}
		$contact[$page->getField($key)->label] = $value;
	}
}

$content .= $nb->wrap(
	ukContainer($nb->wrap(
		$nb->wrap(
			$nb->wrap(array_map(function($key, $value) use ($nb) {
				return $nb->wrap($key, 'strong') . $value;
			}, array_keys($contact), array_values($contact)), 'li'), 'ul'),
			'<div class="contact-info">'
		),
		$nb->ukContainer
	),
	'uk-margin-medium-bottom'
);

$content .= getContent($page);
