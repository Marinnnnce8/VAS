<?php namespace ProcessWire;

/**
 * UIkit Functions
 *
 * Based on https://github.com/processwire/processwire/blob/master/site-regular/templates/_uikit.php
 *
 */

/**
 * Render a UIkit Accordion
 *
 * https://getuikit.com/docs/accordion
 *
 * ~~~~~
 * // Display an accordion with the 4th item open and a slower animation
 * echo ukAccordion($page->items, [
 *     'active' => 3,
 *     'duration' => 512,
 * ]);
 * ~~~~~
 *
 * @param array|PageArray $items The items to display in the accordion.
 * @param array|int $options Options to modify behaviour. The values set by default are:
 * - `active` (int): The index of the open item (default=0).
 * - `duration` (int): The open/close animation duration in milliseconds (default=256).
 * @return string
 *
 */
function ukAccordion($items, $options = []) {

	$nb = nb();

	if(!is_array($options)) $options = ['duration' => (int) $options];

	// Set default options
	$options = array_merge([
		'active' => 0,
		'duration' => 256,
	], $options);

	// Convert to array if PageArray
	if($items instanceof PageArray) {
		$items = $items->explode('body', ['key' => 'title']);
	}

	$out = '';
	foreach($items as $title => $body) {
		$out .= $nb->wrap(
			$nb->wrap($title, ['class' => 'uk-accordion-title', 'href' => '#'], 'a') .
			$nb->wrap(content($body), 'uk-accordion-content'),
			'li'
		);
	}

	return $nb->wrap($out, ['dataUkAccordion' => $options], 'ul');
}

/**
 * Render a UIkit Alert
 *
 * https://getuikit.com/docs/alert
 *
 * ~~~~~
 * // Display a 'danger' message with a close button
 * echo ukAlert("I'm sorry Dave, I'm afraid I can't do that", 'danger', [
 *     'close' => true,
 * ]);
 * ~~~~~
 *
 * @param string $message Text/html to display in the alert box.
 * @param string $type The UIkit style: `primary | success | warning | danger`.
 * @param array|bool $options Options to modify behaviour. The values set by default are:
 * - `animation` (bool|string): Fade out or use the Animation component (default=true).
 * - `close` (bool): Should a close button be displayed? (default=false).
 * - `duration` (int): Animation duration in milliseconds (default=256).
 * @return string
 *
 */
function ukAlert($message, $type = 'success', $options = []) {
	return nb()->ukAlert($message, $type, $options);
}

/**
 * Render a UIkit breadcrumb list from the given Page or PageArray
 *
 * @param Page|PageArray|null $page
 * @param array $options Additional options to modify default behaviour:
 * - `attrs` (array): Additional attributes to apply to the `<ul.uk-breadcrumb>`.
 * - `appendCurrent` (bool): Append current page as non-linked item at the end? (default=true).
 * @return string
 *
 */
function ukBreadcrumb($page = null, array $options = []) {

	if(is_array($page)) $options = $page;
	if(is_null($page) || is_array($page)) $page = page();

	if($page instanceof Page) {
		$items = $page->breadCrumbs ?: $page->parents;
		if($page->isOrg) {
			$items->remove($page->wire('pages')->get(1));
			$page->rootParent->title = __('Home');
		}
	} else {
		$items = $page;
		$page = $items->last;
		$items->remove($page);
	}

	$options = array_merge([
		'attrs' => [],
		'appendCurrent' => true,
	], $options);

	$options['attrs'] = array_merge([
		'class' => [],
	], $options['attrs']);

	$options['attrs']['class'] = array_merge($options['attrs']['class'], [
		'uk-breadcrumb',
	]);

	if($page->rootParent->template->name === 'organisations') {
		$items->remove($items->first);
	}

	return nb()->wrap(
		($items->count ?
			$items->each('<li><a href="{url}">{title}</a></li>') .
			($options['appendCurrent'] ? "<li><span>$page->title</span></li>" : '') :
			'<li>&nbsp;</li>'
		),
		$options['attrs'],
		'ul'
	);
}

/**
 * Render a UIkit button
 *
 * @param string $label
 * @param string $style
 * @param string|array $options Additional options to modify default behaviour:
 * - `attrs` (array): Additional attributes to apply to the element.
 * - `size` (string): Add a size modifier, either 'large' or 'small' (default='').
 * - `tag` (string): The HTML tag that should be used to render the button (default='button').
 *
 * Shortcut: If `options` is not an `array`, the `size` option value is set.
 * @return string
 *
 */
function ukButton($label, $style = 'primary', $options = []) {

	// Shortcut
	if(!is_array($options)) $options = ['size' => $options];

	// Set default options
	$options = array_merge([
		'attrs' => [],
		'size' => '',
		'tag' => 'button',
		'icon' => '',
	], $options);

	$attrs = $options['attrs'];
	if(!isset($attrs['class'])) $attrs['class'] = [];
	if(!is_array($attrs['class'])) $attrs['class'] = [$attrs['class']];

	// Add uk-button classes
	$attrs['class'] = array_merge(['uk-button', "uk-button-$style"], $attrs['class']);
	if($options['size']) $attrs['class'][] = "uk-button-$options[size]";

	if(isset($attrs['href'])) {
		// If href attribute, make tag an anchor
		$options['tag'] = 'a';
	} else if(!isset($attrs['type']) && $options['tag'] === 'button') {
		// If a button, add type attribute
		$attrs['type'] = 'button';
	}

	return nb()->wrap(
		nb()->wrap($label, 'span') .
		($options['icon'] ? renderIcon($options['icon']) : '') .
		'<span></span>',
		$attrs,
		$options['tag']
	);
}

/**
 * Render a UIkit button link
 *
 * @param string $label
 * @param string $link
 * @param string $style
 * @param string|array $options Additional options to modify default behaviour
 * @return string
 * @see ukButton()
 *
 */
function ukButtonLink($label, $link, $style = 'primary', $options = []) {

	// Shortcut
	if(!is_array($options)) $options = ['size' => $options];

	// Set default options
	$options = array_merge([
		'attrs' => [],
		'size' => '',
		'tag' => 'a',
	], $options);

	$options['attrs']['href'] = $link;

	return ukButton($label, $style, $options);
}

/**
 * Wrap HTML in a UIkit Container
 *
 * https://getuikit.com/docs/container
 *
 * ~~~~~
 * // Wrap a heading in the default uk-container
 * echo ukContainer('<h3>A Heading</h3>');
 * ~~~~~
 *
 * @param string $str The HTML to wrap.
 * @param string $size The UIkit container size: `xsmall | small | large | xlarge | expand`.
 * @param string $class Any additional classes.
 * @return string
 *
 */
function ukContainer($str, $size = '', $class = '') {
	return nb()->wrap(
		$str,
		'uk-container' . ($size ? " uk-container-$size" : '') . ($class ? " $class" : '')
	);
}

/**
 * Render a UIkit Dotnav
 *
 * https://getuikit.com/docs/dotnav
 *
 * ~~~~~
 * // Render a uk-dotnav
 * echo ukDotnav();
 * ~~~~~
 *
 * @param array $options
 * @return string
 * @see _ukItemnav()
 *
 */
function ukDotnav(array $options = []) {
	return _ukItemnav('dot', $options);
}

/**
 * Render a UIkit Grid
 *
 * https://getuikit.com/docs/grid
 *
 * ~~~~~
 * // Display two items side-by-side using the small grid size
 * $items = ['Item 1', 'Item 2'];
 * echo ukGrid($items, ['uk-grid-small', 'uk-child-width-1-2']);
 * ~~~~~
 *
 * @param array|string $items The items to display in the grid.
 * @param array|string $attrs The attributes for the grid element.
 * @param bool|array|string $options Component options for data-uk-grid (default=true).
 * @param string $tag The HTML element tag used for the grid element (default='div').
 * @param string $wrap The HTML element tag or UIkit class name to wrap items with (default='div').
 * @return string
 *
 */
function ukGrid($items, $attrs = [], $options = true, $tag = 'div', $wrap = 'div') {

	// Shortcut
	if(!is_array($attrs) || nb()->isSeq($attrs)) {
		$attrs = ['class' => $attrs];
	}

	// If the items are an array, wrap them
	if(is_array($items)) {
		$items = nb()->wrap($items, $wrap);
	}

	return nb()->wrap($items, array_merge([
		'dataUkGrid' => $options,
	], $attrs), $tag);
}

/**
 * Render a UIkit Nav
 *
 * https://getuikit.com/docs/nav
 *
 * ~~~~~
 * // Render the section navigation for the page
 * echo ukNav($page);
 * ~~~~~
 *
 * @param Page|PageArray $items
 * @param array $options Options to modify behaviour:
 * - `attrs` (array): An array of attributes rendered on the main <ul> element.
 * - `exclude` (array): An array of template names that should be excluded from the navigation.
 * - `prependParent` (bool): When rendering children, should the parent be prepended? (default=false).
 * @return string
 *
 */
function ukNav($items, array $options = []) {

	// Set default options
	$options = array_merge([
		'attrs' => [],
		'attrsSub' => [
			'class' => [
				'uk-nav-sub',
			],
		],
		'attrsSubItems' => [
			'class' => [
				'uk-nav-sub-items',
			],
		],
		'exclude' => [],
		'prependParent' => false,
	], $options);

	// Set default attributes
	$attrs = array_merge([
		'class' => [
			'uk-nav',
			'uk-nav-default',
			'uk-nav-parent-icon',
		],
		'dataUkNav' => true,
	], $options['attrs']);

	if($items instanceof Page) {
		$page = $items;
		$items = $page->id === 1 ? $page->wire('pages')->find('id=1') : $page->rootParent->children;
	} else if($items instanceof PageArray && $items->count()) {
		$page = page();
	} else {
		return '';
	}

	// Return blank if a nav cannot or should not be rendered
	if(!$items->count()) return '';

	$out = '';
	foreach($items as $item) {
		$out .= _ukNavItem($item, $page, $options);
	}

	return $page->wire('nb')->wrap($out, $attrs, 'ul');
}

/**
 * Render a Prev/Next Page navigation
 *
 * https://getuikit.com/docs/pagination#previous-and-next
 *
 * @param Page $page
 * @param array|bool $options
 * - `attrs` (array): Array of attributes for <ul.uk-pagination>.
 * - `prevClass` (array|string): Class attributes for the previous arrow (default='uk-margin-small-right').
 * - `nextClass` (array|string): Class attributes for the next arrow (default='uk-margin-small-left').
 * - `switch` (bool): Should the next item be the previous and vice versa?
 * Set to `false` by default unless the parent's child pages are sorted in reverse.
 * @return string
 *
 */
function ukPrevNext(Page $page, array $options = []) {

	// If a single child page, return nothing
	if(!$page->prev->id && !$page->next->id) return '';

	$nb = $page->wire('nb');

	$label = $page->template->label;
	if(!$label || in_array($page->template->name, ['default'])) $label = __('Page');

	// Default options
	$options = array_merge([
		'attrs' => [],
		'prevClass' => 'uk-margin-small-right',
		'nextClass' => 'uk-margin-small-left',
		'switch' => substr($page->parent->template->sortfield, 0, 1) === '-',
	], $options);

	if(!isset($options['attrs']['class'])) {
		$options['attrs']['class'] = [];
	} else if(!is_array($options['attrs']['class'])) {
		$options['attrs']['class'] = [$options['attrs']['class']];
	}

	$options['attrs']['class'][] = 'uk-pagination';

	$prev = $options['switch'] ? $page->next : $page->prev;
	$next = $options['switch'] ? $page->prev : $page->next;

	return $nb->wrap(
		$nb->wrap(($prev->id ? $nb->wrap(
			$nb->attr([
				'class' => $options['prevClass'],
				'dataUkPaginationPrevious' => true,
			], 'span', true) . sprintf(__('Previous %s'), $label),
			[
				'href' => $prev->url,
				'dataUkTooltip' => $prev->title,
			],
			'a'
		) : ''), 'li') .
		$nb->wrap(($next->id ? $nb->wrap(
			sprintf(__('Next %s'), $label) . $nb->attr([
				'class' => $options['nextClass'],
				'dataUkPaginationNext' => true,
			], 'span', true),
			[
				'href' => $next->url,
				'dataUkTooltip' => $next->title,
			],
			'a'
		) : ''), ['class' => ['uk-margin-auto-left']], 'li'),
		$options['attrs'],
		'ul'
	);
}

/**
 * Render a UIkit Section
 *
 * https://getuikit.com/docs/section
 *
 * ~~~~~
 * // Render a uk-section
 * echo ukSection($str);
 * ~~~~~
 *
 * @param string $html
 * @param array $attrs
 * @param string $tag
 * @return string
 *
 */
function ukSection($html, $attrs = [], $tag = 'div') {
	if(!is_array($attrs)) $attrs = ['id' => $attrs];
	if(!isset($attrs['class'])) $attrs['class'] = [];
	$attrs['class'][] = 'uk-section';
	return nb()->wrap($html, $attrs, $tag);
}

/**
 * Render a UIkit Slidenav
 *
 * https://getuikit.com/docs/slidenav
 *
 * ~~~~~
 * // Render a uk-slidenav
 * echo ukSlidenav();
 * ~~~~~
 *
 * @param array $options
 * - `previous` (array|bool): Attributes for the previous button. Pass `false` to disable.
 * - `next` (array|bool): Attributes for the next button. Pass `false` to disable.
 * - `large` (bool): Should uk-slidenav-large class be used? (default=false).
 * - `wrap` (string): A wrap element for the uk-slidenav.
 * @return string
 *
 */
function ukSlidenav(array $options = []) {

	$nb = nb();

	// Set default options
	$options = array_merge([
		'previous' => [],
		'next' => [],
		'large' => false,
		'wrap' => '',
	], $options);

	// Assign common classes
	if(isset($options['class'])) {

		// Make sure the common classes are an array
		$options = $nb->keyArray('class', $options);

		if(is_array($options['previous'])) {
			// Make sure the class attribute exists
			$options['previous'] = $nb->keyArray('class', $options['previous']);
			// Add the common classes
			$options['previous']['class'] = array_merge($options['class'], $options['previous']['class']);
		}
		if(is_array($options['next'])) {
			// Make sure the class attribute exists
			$options['next'] = $nb->keyArray('class', $options['next']);
			// Add the common classes
			$options['next']['class'] = array_merge($options['class'], $options['next']['class']);
		}
	}

	return $nb->wrap(
		(is_array($options['previous']) ?
			ukSlidenavButton('previous', $options['previous'], $options['large']) : '') .
		(is_array($options['next']) ?
			ukSlidenavButton('next', $options['next'], $options['large']) : ''),
		$options['wrap']
	);
}

/**
 * Render a UIkit Slidenav button
 *
 * ~~~~~
 * // Render a 'previous' uk-slidenav button
 * echo ukSlidenavButton('previous', ['class' => 'uk-position-center-left']);
 * ~~~~~
 *
 * @param string $direction previous/next.
 * @param array $attrs
 * @param bool $large
 * @return string
 *
 */
function ukSlidenavButton($direction = 'previous', array $attrs = [], $large = false) {

	$attrs = array_merge([
		'href' => '#',
		"dataUkSlidenav-$direction" => true,
		'ariaLabel' => $direction,
	], $attrs);

	if($large) {
		$attrs = nb()->keyArray('class', $attrs);
		$attrs['class'][] = 'uk-slidenav-large';
	}

	return nb()->attr($attrs, 'a', true);
}

/**
 * Render a UIkit Slider
 *
 * https://getuikit.com/docs/slider
 *
 * ~~~~~
 * // Render a slider, resize images to 512px in width
 * echo ukSlider($page->images, [
 *     'width' => 512,
 * ]);
 * ~~~~~
 *
 * @param Pageimages|array $items Images to render and display or an array of items to display.
 * @param array $options Options to modify behaviour:
 * - `ukSlider` (array): UIkit Slider options https://getuikit.com/docs/slider#component-options.
 * - `class` (array): Classes for the slider wrapper.
 * - `ukLightbox` (array|bool): UIkit Lightbox options https://getuikit.com/docs/lightbox#component-options.
 * - `ukScrollspy` (array|false): UIkit Scrollspy options: https://getuikit.com/docs/scrollspy#component-options.
 * - `ukSlidenav` (array|bool): UIkit Slidenav options. Pass `false` to disable.
 * - `caption` (array|bool): An array of attributes for captions. Pass `false` to disable captions.
 * - `width` (int): The width of the thumbnail image (default=`$nb->width`).
 * - `height` (int): The height of the thumbnail image (default=`$nb->height`).
 * @return string
 *
 */
function ukSlider($items, array $options = []) {

	$nb = nb();

	// Set default options
	$options = array_merge([
		'ukSlider' => [],
		'class' => [
			'uk-grid',
			'uk-grid-small',
			'uk-child-width-1-2',
			'uk-child-width-1-3@s',
		],
		'ukLightbox' => [],
		'ukScrollspy' => [
			'cls' => 'uk-animation-fade',
			'delay' => 128,
		],
		'ukSlidenav' => [],
		'width' => $nb->width ?: 910,
		'height' => $nb->height ?: 512,
		'wrap' => 'uk-position-relative uk-visible-toggle uk-light',
	], $options);

	// Set default uk-slider options
	$ukSlider = array_merge([
		'sets' => true,
	], $options['ukSlider']);

	// Lightbox only used for Pageimages
	$ukLightbox = false;

	// Set default uk-slidenav options
	$ukSlidenav = is_array($options['ukSlidenav']) ? array_merge([
		'class' => [
			'uk-position-small',
			'uk-hidden-hover',
		],
		'previous' => [
			'dataUkSliderItem' => 'previous',
			'class' => 'uk-position-center-left',
		],
		'next' => [
			'dataUkSliderItem' => 'next',
			'class' => 'uk-position-center-right',
		],
	], $options['ukSlidenav']) : false;

	// If uk-dotnav should be used
	$ukDotnav = false;
	if(isset($options['ukDotnav']) && $options['ukDotnav'] !== false) {
		// Set default uk-dotnav options
		if(!is_array($options['ukDotnav'])) $options['ukDotnav'] = [];
		$ukDotnav = array_merge([
			'class' => [
				'uk-slider-nav',
				'uk-flex-center',
				'uk-margin',
			],
		], $options['ukDotnav']);
	}

	if($items instanceof Pageimages) {

		$images = $items;
		$items = [];

		// Set default uk-lightbox options
		$ukLightbox = is_array($options['ukLightbox']) ? array_merge([
			'animation' => 'fade',
		], $options['ukLightbox']) : $options['ukLightbox'];

		// Automatically assign wrap ID if not passed
		if(!isset($options['id'])) {
			$options['id'] = implode('_', [
				'uk-slider',
				$images->getField()->name,
				$images->getPage()->id,
			]);
		}

		// Render the Pageimages
		foreach($images as $image) {

			$alt = getImageAlt($image);
			$caption = getImageCaption($image);

			$thumb = $image->size($options['width'], $options['height']);

			$items[] = $nb->wrap(
				$thumb->render($nb->attr([
					'src' => '{url}',
					'alt' => $alt,
					'width' => $thumb->width,
					'height' => $thumb->height,
					'title' => $image->headline,
				], 'img')),
				($ukLightbox ? $nb->attr([
					'href' => $image->url,
					'dataAlt' => $alt ?: false,
					'dataCaption' => !empty($caption) ? $nb->wrap($caption, 'div') : false,
				], 'a') : '')
			);
		}

	} else if(!is_array($items)) {

		// No renderable items passed, return nothing
		return '';
	}

	return $nb->wrap(
		$nb->wrap(
			$nb->wrap(
				$nb->wrap(
					$nb->wrap($items, 'li'),
					[
						'class' => array_merge(['uk-slider-items'], $options['class']),
						'dataUkLightbox' => $ukLightbox,
						'dataUkScrollspy' => $options['ukScrollspy'],
					],
					'ul'
				),
				'uk-slider-container'
			) .
			($ukSlidenav ? ukSlidenav($ukSlidenav) : '') .
			($ukDotnav ? ukDotnav($ukDotnav) : ''),
			$options['wrap']
		),
		[
			'dataUkSlider' => $ukSlider,
			'id' => $options['id'] ?? 'uk-slider',
		],
		'div'
	);
}

/**
 * Render a UIkit Slideshow
 *
 * https://getuikit.com/docs/slideshow
 *
 * ~~~~~
 * // Render a slideshow, resize images to 512px in width
 * echo ukSlideshow($page->images, [
 *     'width' => 512,
 * ]);
 * ~~~~~
 *
 * @param Pageimages|array $items Images to render and display or an array of items to display.
 * @param array $options Options to modify behaviour:
 * - `ukSlideshow` (array): UIkit Slideshow options https://getuikit.com/docs/slideshow#component-options.
 * - `class` (array): Classes for the slideshow wrapper.
 * - `ukLightbox` (array|bool): UIkit Lightbox options https://getuikit.com/docs/lightbox#component-options.
 * - `ukScrollspy` (array|false): UIkit Scrollspy options: https://getuikit.com/docs/scrollspy#component-options.
 * - `ukSlidenav` (array|bool): UIkit Slidenav options. Pass `false` to disable.
 * - `caption` (array|bool): An array of attributes for captions. Pass `false` to disable captions.
 * - `width` (int): The width of the thumbnail image (default=`$nb->width`).
 * @return string
 *
 */
function ukSlideshow($items, array $options = []) {

	$nb = nb();

	// Set default options
	$defaultWidth = 1200;
	$options = array_merge([
		'ukSlideshow' => [],
		'class' => [
			'uk-position-relative',
			'uk-visible-toggle',
			'uk-light',
		],
		'ukLightbox' => [],
		'ukScrollspy' => [
			'cls' => 'uk-animation-slide-bottom-small',
		],
		'ukSlidenav' => [],
		'caption' => [
			'class' => [
				'uk-overlay',
				'uk-overlay-primary',
				'uk-position-bottom',
				'uk-padding-small',
				'uk-text-center',
				'uk-transition-slide-bottom',
			],
		],
		'width' => $nb->width ?: $defaultWidth,
		'height' => $nb->height ?: ($defaultWidth * 0.5625),
	], $options);

	// Set default uk-slideshow options
	$ukSlideshow = array_merge([
		'animation' => 'fade',
		'autoplay' => true,
		'autoplay-interval' => 4096,
		'ratio' => '16:9',
	], $options['ukSlideshow']);

	// Make sure width is wide enough
	if($options['width'] < $defaultWidth) $options['width'] = $defaultWidth;

	// Set height from slideshow ratio
	if($ukSlideshow['ratio'] && strpos($ukSlideshow['ratio'], ':') !== false) {
		$ratio = explode(':', $ukSlideshow['ratio']);
		$options['height'] = ($ratio[1] / $ratio[0]) * $options['width'];
	}

	// Set default uk-slidenav options
	$ukSlidenav = is_array($options['ukSlidenav']) ? array_merge([
		'class' => [
			'uk-position-small',
			'uk-hidden-hover',
		],
		'previous' => [
			'dataUkSlideshowItem' => 'previous',
			'class' => 'uk-position-center-left',
		],
		'next' => [
			'dataUkSlideshowItem' => 'next',
			'class' => 'uk-position-center-right',
		],
	], $options['ukSlidenav']) : false;

	// Lightbox only used for Pageimages
	$ukLightbox = false;

	// Default itemnav options
	$ukItemnav = false;
	$ukItemnavOptions = [
		'class' => [
			'uk-slideshow-nav',
			'uk-flex-center',
			'uk-margin',
		],
	];

	// If uk-dotnav should be used
	if(isset($options['ukDotnav']) && $options['ukDotnav'] !== false) {
		// Set default uk-dotnav options
		$ukItemnav = 'ukDotnav';
		if(!is_array($options['ukDotnav'])) $options['ukDotnav'] = [];
		$options['ukDotnav'] = array_merge($ukItemnavOptions, $options['ukDotnav']);
	}

	if($items instanceof Pageimages) {

		$images = $items;
		$items = [];

		// Set default uk-lightbox options
		$ukLightbox = is_array($options['ukLightbox']) ? array_merge([
			'animation' => 'fade',
		], $options['ukLightbox']) : $options['ukLightbox'];

		// If uk-thumbnav should be used
		if(isset($options['ukThumbnav']) && $options['ukThumbnav'] !== false) {

			$ukItemnav = 'ukThumbnav';

			// Set default uk-thumbnav options
			if(!is_array($options['ukThumbnav'])) $options['ukThumbnav'] = [];
			$options['ukThumbnav'] = array_merge($ukItemnavOptions, $options['ukThumbnav']);
			$options['ukThumbnav']['items'] = '';

			// Set height for uk-thumbnav images
			if(!isset($options['ukThumbnav']['height'])) $options['ukThumbnav']['height'] = 72;
		}

		// Automatically assign wrap ID if not passed
		if(!isset($options['id'])) {
			$options['id'] = implode('_', [
				'uk-slideshow',
				$images->getField()->name,
				$images->getPage()->id,
			]);
		}

		// Render the Pageimages
		$c = $images->count();
		for($i = 0; $i < $c; $i++) {

			$image = $images->eq($i);

			$alt = getImageAlt($image);
			$caption = getImageCaption($image);

			$thumb = $image->size($options['width'], $options['height']);

			$items[] = $nb->wrap(

				$thumb->render($nb->attr([
					'src' => '{url}',
					'alt' => $alt,
					'width' => $thumb->width,
					'height' => $thumb->height,
					'title' => $image->headline,
				], 'img')),

				($ukLightbox ? $nb->attr([
					'href' => $image->url,
					'class' => ['uk-position-center'],
					'dataAlt' => $alt ?: false,
					// UIkit caption needs a div wrap to work
					'dataCaption' => !empty($caption) ? $nb->wrap($caption, 'div') : false,
				], 'a') : '')
			) .

			($caption && $options['caption'] ? $nb->wrap(
				$caption,
				$options['caption'],
				'div'
			) : '');

			if($ukItemnav === 'ukThumbnav') {

				$options['ukThumbnav']['items'] .= $nb->wrap(
					$nb->wrap(
						$image->height($options['ukThumbnav']['height'])->render($nb->attr([
							'src' => '{url}',
							'alt' => $alt,
							'height' => $options['ukThumbnav']['height'],
						], 'img')),
						['href' => '#'],
						'a'
					),
					['dataUkSlideshowItem' => "$i"],
					'li'
				);
			}
		}

	} else if(!is_array($items)) {

		// No renderable items passed, return nothing
		return '';
	}

	return $nb->wrap(
		$nb->wrap(
			$nb->wrap(
				$nb->wrap($items, 'li'),
				[
					'class' => ['uk-slideshow-items'],
					'dataUkLightbox' => $ukLightbox,
					'dataUkScrollspy' => $options['ukScrollspy'],
				],
				'ul'
			) .
			($ukSlidenav ? ukSlidenav($ukSlidenav) : '') .
			($ukItemnav ? _ukItemnav($ukItemnav, $options[$ukItemnav]) : ''),
			['class' => $options['class']],
			'div'
		),
		[
			'dataUkSlideshow' => $ukSlideshow,
			'id' => $options['id'] ?? 'uk-slideshow',
		],
		'div'
	);
}

/**
 * Render UIkit Tabs
 *
 * https://getuikit.com/docs/tab
 *
 * ~~~~~
 * // Display tabs with the 5th item active and a quick animation
 * echo ukTabs($page->items, [
 *     'active' => 5,
 *     'duration' => 128
 * ]);
 * ~~~~~
 *
 * @param array|PageArray $items The items to display in tabs.
 * @param array $options Options to modify behaviour. The values set by default are:
 * - `active` (int): The index of the open item (default=0).
 * - `animation` (string): The type of animation used (default='uk-animation-fade').
 * - `duration` (int): The open/close animation duration in milliseconds (default=256).
 * @return string
 *
 */
function ukTabs($items, array $options = []) {

	$nb = nb();

	// Set default options
	$options = array_merge([
		'active' => 0,
		'animation' => 'uk-animation-fade',
		'duration' => 256,
	], $options);

	// Convert to array if PageArray
	if($items instanceof PageArray) {
		$items = $items->explode('body', ['key' => 'title']);
	}

	$tabs = '';
	$contents = '';
	foreach($items as $title => $body) {
		$tabs .= $nb->wrap("<a href='#'>$title</a>", 'li');
		$contents .= $nb->wrap($body, 'li');
	}

	return $nb->wrap($tabs, ['dataUkTab' => $options], 'ul') .
		$nb->wrap($contents, ['class' => 'uk-switcher'], 'ul');
}

/**
 * Render a UIkit Thumbnav
 *
 * https://getuikit.com/docs/thumbnav
 *
 * ~~~~~
 * // Render a uk-thumbnav
 * echo ukThumbnav();
 * ~~~~~
 *
 * @param array $options
 * @return string
 * @see _ukItemnav()
 *
 */
function ukThumbnav(array $options = []) {
	return _ukItemnav('thumb', $options);
}

// Internal

/**
 * Render a UIkit item nav (uk-dotnav/uk-thumbnav)
 *
 * @param string $type dot/thumb.
 * @param array $options
 * - `items` (string): List items to populate.
 * - `class` (array): An array of classes.
 * - `vertical` (bool): Display vertically.
 * - `wrap` (string): A wrap element.
 * @return string
 *
 */
function _ukItemnav($type = 'dot', array $options = []) {

	// Set default options
	$options = array_merge([
		'items' => '',
		'class' => [],
		'vertical' => false,
		'wrap' => '',
	], $options);

	$type = str_replace(['uk-', 'nav'], '', $type);
	$options['class'][] = "uk-{$type}nav";
	if($options['vertical']) $options['class'][] = "uk-{$type}nav-vertical";

	return nb()->wrap(
		nb()->wrap(
			$options['items'],
			['class' => $options['class']],
			'ul'
		),
		$options['wrap']
	);
}

/**
 * Renders a UIkit Nav item
 *
 * @param Page $item The child item being rendered.
 * @param Page $page The page the nav is being rendered on.
 * @param array $options Options to modify behaviour.
 * @param bool $children Should the child pages be rendered?
 * @return string
 * @see ukNav()
 *
 */
function _ukNavItem(Page $item, Page $page, array $options = [], $children = true) {

	$nb = $page->wire('nb');

	$isActive = $item->id === $page->id || ($page->parents->has($item) && $item->id !== 1);

	$attrs = array_merge([
		'class' => [],
	], $options['attrs']);

	if(!is_array($attrs['class'])) $attrs['class'] = [];
	if($isActive) $attrs['class'][] = 'uk-active';

	$out = $nb->wrap($item->title, ['href' => $item->url], 'a');
	if($item->children->count() && !in_array($item->template->name, $options['exclude']) && $children) {

		$attrs['class'][] = 'uk-parent';
		if($isActive) $attrs['class'][] = 'uk-open';

		$subAttr = $attrs;
		foreach($options['attrsSub'] as $key => $value) {
			if(!isset($subAttr[$key])) $subAttr[$key] = [];
			if(!is_array($subAttr[$key])) $subAttr[$key] = [$subAttr[$key]];
			$subAttr[$key] = is_array($value) ? array_merge($subAttr[$key], $value) : $value;
		}

		$subOptions = $options;
		$subOptions['attrs'] = $options['attrsSubItems'];

		$o = $options['prependParent'] ? _ukNavItem($item, $page, $subOptions, false) : '';

		foreach($item->children as $child) {
			$o .= _ukNavItem($child, $page, $subOptions);
		}

		$out .= $nb->wrap($o, $subAttr, 'ul');
	}

	return $nb->wrap($out, $attrs, 'li');
}
