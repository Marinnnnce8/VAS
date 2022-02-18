<?php namespace ProcessWire;

/**
 * Styleguide
 *
 */

include 'forms/styleguide.php';
include 'tpl/page.php';

$renderButton = function($style = 'primary', $size = '', $attrs = []) use ($nb) {
	$label = ucfirst($style);
	return $nb->wrap(
		$size ? "$label " . ucfirst($size) : $label,
		'uk-text-meta uk-form-label' . ($size ? ' uk-margin-top' : '')
	) . ukButton(__('Find Out More'), $style, count($attrs) ? $attrs : $size);
};

$renderButtons = function($style = 'primary') use ($renderButton) {
	$out = $renderButton($style);
	foreach(['small', 'large'] as $size) {
		$out .= $renderButton($style, $size);
	}
	return $out;
};

$buttonText = '';
$labelClass = 'uk-text-meta uk-form-label';
$labelClassMargin = "$labelClass uk-margin-top";
$content .= content(
	renderHeading(__('Buttons')) .
	ukGrid([
		// Primary
		$renderButtons(),
		// Secondary
		$renderButtons('secondary'),
		// Default
		$renderButtons('default'),
	], ['uk-grid-medium', 'uk-child-width-1-3@s']) .

	renderHeading(__('Other Button Styles'), 3) .
	ukGrid([
		// Danger
		$renderButton('danger'),
		// Text Normal
		$renderButton('text'),
		// Text Large
		$renderButton('text', 'large', ['attrs' => ['class' => 'uk-text-large']]),
	], ['uk-grid-medium', 'uk-child-width-1-3@s'])
);

$content .= nbForm($form->render());
