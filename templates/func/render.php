<?php namespace ProcessWire;

/**
 * Render Functions
 *
 * @copyright 2021 NB Communication Ltd
 *
 */

/**
 * Render an HTML heading
 *
 * ~~~~~
 * // Output `<h3>Heading</h3>`
 * echo renderHeading('Heading'); // h3 is the default
 * ~~~~~
 *
 * @param string $title
 * @return string
 *
 */
function renderHeading($title) {

	if(empty($title)) return '';

	$nb = nb();

	$heading = 2;
	$attrs = [];

	$args = func_get_args();
	$num = func_num_args();
	for($i = 1; $i < $num; $i++) {
		$arg = $args[$i];
		if(is_numeric($arg)) {
			$heading = (int) $arg;
		} else if(is_string($arg) && $arg) {
			if(substr($arg, 0, 1) === '#') {
				$attrs['id'] = substr($arg, 1);
			} else {
				$attrs['class'] = explode(' ', $arg);
			}
		} else if(is_array($arg) && count($arg)) {
			if($nb->isSeq($arg)) {
				$attrs['class'] = $arg;
			} else {
				$attrs = array_merge($attrs, $arg);
			}
		} else if($arg === true && !isset($attrs['id'])) {
			$attrs['id'] = sanitizer()->pageName($title);
		}
	}

	if(count($attrs) && $nb->isSeq($attrs)) $attrs = ['class' => $attrs];
	return $nb->wrap($title, $attrs, "h$heading");
}

/**
 * Render an icon
 *
 * ~~~~~
 * // Output the download icon
 * echo renderIcon('download');
 * ~~~~~
 *
 * @param string $name The icon name.
 * @param array|string $class Any additional classes.
 * @param string $title The title of the icon.
 * @return string
 *
 */
function renderIcon($name, $class = [], $title = '') {
	if(!is_array($class)) $class = [$class];
	return nb()->wrap(
		'<title>' . ($title ?: $name) . '</title>' .
		'<use xlink:href="' . config()->urls->templates . 'symbol/icons.svg#' . $name . '"></use>',
		'<svg viewBox="0 0 512 512" class="' . nb()->attrValue(array_merge([
			'uk-svg',
			'icon',
			"icon-$name",
		], $class)) . '" role="img">'
	);
}

/**
 * Render a page introduction
 *
 * ~~~~~
 * // Render the intro
 * echo renderIntro($page->intro);
 * ~~~~~
 *
 * @param string $intro The intro.
 * @return string
 *
 */
function renderIntro($intro = '') {
	return $intro ? ukContainer(
		nb()->wrap(
			nb()->wrap(nl2br($intro), '<p class="uk-text-lead">'),
			'div'
		),
		nb()->ukContainer
	) : '';
}

/**
 * Render items
 *
 * ~~~~~
 * // Render items
 * echo renderItems($items);
 * ~~~~~
 *
 * @param PageArray $items The items to render.
 * @return string
 *
 */
function renderItems(PageArray $items) {

	$nb = $items->wire('nb');

	$out = '';
	foreach($items as $item) {

		// Details
		/*$details = [];
		foreach(['date_pub', 'date_start', 'location'] as $f) {
			$v = $item->get($f);
			if($v) {
				switch($f) {
					case 'date_pub':
						$v = date('F jS, Y', $v);
						break;
					case 'date_start':
						$v = $item->wire('datetime')->dates($v, $item->get('date_end'));
						break;
				}
				$details[] = $v;
			}
		}

		// Tags
		$tags = [];
		if($items->wire('page')->template->name === 'search') {
			$tags[] = $item->template->label;
		}
		if($item->hasField('tags') && $item->tags->count) {
			foreach($item->tags as $tag) {
				$tags[] = $tag->title;
			}
		}

		$thumb = $item->getImage();*/

		$day = '';
		$month = '';
		$year = '';
		if($item->date_pub) {
			switch($item->template->name) {
				case 'post':
					$day = date('d', $item->date_pub);
				case 'document':
					$month = date('M', $item->date_pub);
					break;
				case 'product':
					if($item->price) $month = price($item->price);
					break;
				default:
					break;
			}

			$year = date('Y', $item->date_pub);
		}

		$out .= $nb->wrap(
			$nb->wrap(
				// Image
				/*($thumb ? $nb->wrap(
					$nb->link(
						$item->url,
						$thumb->render([
							'sizes' => implode(', ', [
								'(min-width: 640px) 50.00vw',
								'(min-width: 960px) 33.33vw',
							])
						])
					),
					'uk-card-media-top'
				) : '') .*/
				// Title / Details / Tags
				$nb->wrap(
					//(count($details) ? $nb->wrap($details, 'uk-text-meta') : '') .
					//(count($tags) ? $nb->wrap($tags, 'uk-label uk-label-primary uk-margin-small-right') : '') .
					$nb->wrap(
						($day ? $nb->wrap($day, '<span class="day">') : '') .
						($month ? $nb->wrap($month, '<span class="month uk-text-uppercase">') : '') .
						($year ? $nb->wrap($year, '<span class="year">') : '') .
						renderIcon("hex-rounded"),
						'<div class="date">'
					) .
					renderHeading($item->title, 3, ['uk-article-title']),
					'header'
				) .
				// Summary
				$nb->wrap(
					($item->getSummary ? $nb->wrap($item->getSummary, 'p') : '') .
					($item->template->name === 'product' && forSale($item) ?
						$nb->wrap(paypalAdd($item), 'uk-margin-bottom read-more-top') : '') .
					$nb->wrap($nb->wrap(__('Find out more'), 'span') . renderIcon('caret-right'), '<span class="uk-button-text uk-button">'),
					'<div class="entry-body">'
				) .
				$nb->link($item->url, '', [
					'class' => 'read-more',
					'ariaLabel' => sprintf(__('Find out more about %s'), $item->title),
				]),
				'<article class="uk-article">'
			),
			'div'
		);
	}

	return $out;
}

/**
 * Render navigation
 *
 * ~~~~~
 * // Render the navigation
 * echo renderNavigation($items);
 * ~~~~~
 *
 * @param PageArray $items The top level navigation items to render.
 * @param bool $mobile Should mobile wrapping be used (default=false).
 * @param int $depth The maximum navigation depth (default=3).
 * @param int $level The level of the current navigation (default=0).
 * @return string
 *
 */
function renderNavigation(PageArray $items, $mobile = false, int $depth = 3, int $level = 0) {

	$nb = $items->wire('nb');
	$page = $items->wire('page');

	$out = '';
	foreach($items as $item) {

		// Should subnavigation be rendered?
		$hasChildren = $item->hasChildren && !in_array($item->template->name, [
			'home',
			'documents',
			'events',
			'projects',
			'posts',
			'studies',
			'products',
		]);

		// Is this item active, either as the active page or parent of it?
		$isActive = $item->id === $page->id;
		if(!$isActive && $item->id !== 1) {
			if($item->id === $item->rootParent->id) {
				// Top-level item
				// The item is the rootParent of the active page
				$isActive = $item->id === $page->rootParent->id;
			}
			if(!$isActive) {
				// The active page has the item as a parent
				$isActive = $page->parents->has($item);
			}
		}

		$cls = [];
		if($isActive) $cls[] = 'uk-active';
		if($hasChildren && $level <= $depth) $cls[] = 'uk-parent';

		$out .= $nb->wrap(
			$nb->wrap(
				$item->title,
				['href' => $item->url],
				'a'
			) .
			($hasChildren && $level <= $depth ? renderNavigation($item->children, $mobile, $depth, $level + 1) : ''),
			$cls,
			'li'
		);
	}

	return $nb->wrap(
		$out,
		$mobile ?
			'ul' :
			($level ?
				$nb->wrap(
					$nb->attr(['uk-nav', 'uk-navbar-dropdown-nav'], 'ul', true),
					['uk-navbar-dropdown'],
					'div'
				) :
				$nb->attr(['uk-navbar-nav'], 'ul')
			)
	);
}

/**
 * Render child pages or siblings
 *
 * @param Page $page
 * @param array $selectors A array of selectors.
 * @param bool $siblings Should siblings be rendered if the page has no children?
 * @return string
 *
 */
function renderRelated(Page $page, $selectors = [], $siblings = false) {

	// Shortcuts
	if(is_bool($selectors)) {
		$siblings = $selectors;
		$selectors = [];
	}
	if(!is_array($selectors)) $selectors = [];

	// Render siblings if requested and no children present
	if(!$page->hasChildren) {
		if($page->isRoot || !$siblings) return '';
		$selectors = array_merge($selectors, ['id!=' => $page->id]);
		return renderRelated($page->parent, $selectors);
	}

	$items = getItems($page, $selectors);

	return $items->count ? nbItems($items, ['hex' => $page->template->name === 'organisation-default' ? 'uk-child-width-1-2@s' : true]) : '';
}

/**
 * Render a summary
 *
 * ~~~~~
 * // Render the page's summary
 * echo renderSummary($page);
 * ~~~~~
 *
 * @param Page|string $summary The summary text to render, or a `Page` get the text from.
 * @return string
 *
 */
function renderSummary($summary) {
	if($summary instanceof Page) $summary = $summary->get('summary|getSummary');
	return $summary ? nb()->wrap(nl2br($summary), 'p') : '';
}
