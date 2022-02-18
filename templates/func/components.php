<?php namespace ProcessWire;

/**
 * Component Functions
 *
 * @copyright 2021 NB Communication Ltd
 *
 */

/**
 * Render content
 *
 * ~~~~~
 * // Render the page content
 * echo content($page->body);
 * ~~~~~
 *
 * @param string $content
 * @param string|false $container The `uk-container` class.
 * @return string
 *
 */
function content($content, $container = '') {
	if($container !== false) $content = ukContainer($content, $container ?: nb()->ukContainer);
	return nb()->wrap($content, '<div class="content">');
}

/**
 * Render a gallery
 *
 * This function evalues the number of images and the perRow number
 * specified, to return the 'best fit'. For example, if there are
 * 4 images, and perRow is set to 3, the first row will be a single image,
 * resized by width. The next row will be the remaining 3, resized by
 * height, allowing them to be displayed side-by-side without unnecessary whitespace.
 *
 * ~~~~~
 * // Display a gallery of square images, four per row
 * echo gallery($page->gallery, [
 *     'height' => 480,
 *     'width' => 480,
 *     'perRow' => 4,
 * ]);
 * ~~~~~
 *
 * @param Pageimages $images The images to be rendered.
 * @param array|int $options Options to modify behaviour:
 * - `perRow` (int): Number of images per row (default=3).
 * - `width` (int): Crop width (default=`$nb->width`).
 * - `height` (int): Crop height (default=`$nb->height`).
 * - `ukLightbox` (array|true): UIkit Lightbox options: https://getuikit.com/docs/lightbox#component-options.
 * - `ukScrollspy` (array|false): UIkit Scrollspy options: https://getuikit.com/docs/scrollspy#component-options.
 * @return string
 *
 */
function gallery(Pageimages $images, $options = []) {

	$nb = $images->wire('nb');

	$_profiler = $nb->profilerStart('gallery');

	if(!is_array($options)) $options = ['perRow' => (int) $options];

	// Set default options
	$options = array_merge([
		'perRow' => 3,
		'width' => $nb->width,
		'height' => $nb->height,
		'ukLightbox' => [],
		'ukScrollspy' => [
			'cls' => 'uk-animation-fade',
			'delay' => 64,
			'target' => '> .gallery-row > .gallery-image',
		],
	], $options);

	$c = $images->count();
	$out = '';
	if($c) {

		// Get our increment value
		$remainder = $c % $options['perRow'];
		$increment = $remainder ? $remainder : $options['perRow'];

		// Cycle through images and create our gallery rows
		for($y = 0; $y < $c; $y += $increment) {

			if($y === $increment) $increment = $options['perRow'];

			$items = '';
			for($x = 0; $x < $increment; $x++) {

				$index = $x + $y;
				$image = $images->eq($index);

				if(isset($image)) {

					// Get thumbnail
					// Resize by width if the first/single image else height
					$thumb = $remainder === 1 && $y === 0 ? $image : $image->height($options['height']);

					$alt = getImageAlt($image);
					$caption = getImageCaption($image);

					// If a single image and a description has been specified
					// If the image doesn't already have a description
					// Set the image description to the specified description
					if(isset($options['caption']) && ($y + $x + $c) === 1 && empty($caption)) {
						$caption = $options['caption'];
					}

					$items .= $nb->wrap(
						$thumb->render($nb->attr([
							'src' => '{url}',
							'alt' => $alt,
							'width' => $thumb->width,
							'height' => $thumb->height,
							'title' => $image->headline,
						], 'img'), [
							'sizes' => round(100 / ($increment ?: 1), 2) . 'vw',
						]),
						[
							'href' => $image->url,
							'class' => 'gallery-image',
							'dataAlt' => $alt ?: false,
							// UIkit caption needs a div wrap to work
							'dataCaption' => !empty($caption) ? $nb->wrap($caption, 'div') : false,
						],
						'a'
					);
				}
			}

			$out .= $nb->wrap($items, '<div class="gallery-row">');
		}

		// Render the gallery
		$out = $nb->wrap($out, [
			'class' => 'gallery-wrap',
			'dataUkLightbox' => is_array($options['ukLightbox']) ?
				array_merge(['animation' => 'fade'], $options['ukLightbox']) : true,
			'dataUkScrollspy' => $options['ukScrollspy'],
		], 'div');
	}

	$nb->profilerStop($_profiler);

	return $out;
}

function opportunity() {

	$page = pages()->get('template=opportunities');
	$opportunity = null;
	foreach($page->opportunities->find('date_pub<now') as $item) {
		if(!$item->date_unpub || $item->date_unpub > time()) {
			$opportunity = $item;
			break;
		}
	}
	if(is_null($opportunity) || !$opportunity->id) return '';

	$nb = $opportunity->wire('nb');

	$label = __('Highlighted Volunteering Opportunity');

	$button = function($label, $url, $style) {
		return ukButtonLink(
			$label,
			$url,
			$style,
			['size' => 'large', 'icon' => 'arrow-right']
		);
	};

	return $nb->wrap(
		'<div class="section-bg"></div>' .
		ukContainer(
			$nb->wrap(
				$nb->wrap(
					$nb->wrap($label, 'uk-text-uppercase section-label uk-light') .
					($opportunity->headline ?
						$nb->wrap($opportunity->headline, '<div class="section-label section-label-small uk-light">') : '') .
					renderHeading($opportunity->title, ['section-title', 'section-title-small', 'uk-light']) .
					($opportunity->og_title ? $nb->wrap(renderIcon('info') . " $opportunity->og_title", 'uk-small section-info uk-light') : '') .
					($opportunity->body ? $nb->wrap($opportunity->body, '<div class="section-summary uk-light">') : '') .
					$nb->wrap(
						($opportunity->link ?
							$button(
								__('Find out more'),
								$page->url . (
									strpos($opportunity->link, '#') === false ? $opportunity->link :
									'#' . explode('#', $opportunity->link)[1]
								),
								'primary uk-margin-right',
							) :
							''
						) .
						$button(
							__('View all opportunities'),
							$page->url,
							'secondary'
						),
						[
							'class' => 'uk-margin-medium-top',
							'dataUkScrollspy' => [
								'target' => 'a',
								'delay' => 128,
								'cls' => 'uk-animation-fade',
							],
						],
						'div'
					),
					'<div class="section-header">'
				),
				'uk-flex uk-flex-center'
			)
		),
		'<section class="uk-section uk-background-primary section-opportunities">'
	);;

	/*return $nb->wrap(
		'<div class="section-bg"></div>' .
		ukContainer(
			ukGrid(
				$nb->wrap(
					$nb->wrap($label, 'uk-hidden@m uk-text-uppercase section-label') .
					$nb->attr([
						'src' => $opportunity->wire('config')->urls->templates . 'img/opport.svg',
						'alt' => '',
						'class' => 'infographic',
					], 'img'),
					'uk-width-expand'
				) .
				$nb->wrap(
					$nb->wrap(
						$nb->wrap($label, 'uk-visible@m uk-text-uppercase section-label') .
						($opportunity->headline ?
							$nb->wrap($opportunity->headline, '<div class="section-label section-label-small">') : '') .
						renderHeading($opportunity->title, ['section-title', 'section-title-small']) .
						($opportunity->og_title ? $nb->wrap(renderIcon('info') . " $opportunity->og_title", 'uk-small section-info') : '') .
						($opportunity->body ? $nb->wrap($opportunity->body, '<div class="section-summary">') : '') .
						($opportunity->link ?
							$nb->wrap(
								ukButtonLink(
									__('Find out more'),
									$page->url . (
										strpos($opportunity->link, '#') === false ? $opportunity->link :
										'#' . explode('#', $opportunity->link)[1]
									),
									'primary',
									['size' => 'large', 'icon' => 'arrow-right']
								),
								'uk-margin-medium-top'
							) : '') .
						$nb->wrap(
							ukButtonLink(
								__('View all opportunities'),
								$page->url,
								'text',
								['icon' => 'caret-right']
							),
							'uk-margin-medium-top'
						),
						'<div class="section-header">'
					),
					'uk-width-40@m'
				)
			)
		),

		'<section class="uk-section section-opportunities">'
	);*/
}

function video(Page $page, $data = []) {

	$nb = $page->wire('nb');

	$thumb = videoPoster($page, $data);

	return $nb->wrap(
		$thumb .
		$nb->wrap(
			'<span class="play"></span>' .
			($page->meta_title ? $nb->wrap($page->meta_title, '<span class="label uk-text-uppercase">') : '') .
			renderHeading($page->headline ?: ($data['title'] ?? ''), 3),
			'uk-overlay uk-position-bottom-center uk-text-center'
		) .
		linkCoverVideo($page),
		[
			'class' => 'video-popup',
			'dataUkLightbox' => [
				'video-autoplay' => true,
			],
		],
		'div'
	);
}
