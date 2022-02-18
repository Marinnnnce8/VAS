<?php namespace ProcessWire;

/**
 * Site Functions
 *
 * @copyright 2021 NB Communication Ltd
 *
 */

/**
 * Return the Page content
 *
 * This method assumes that a Page will have either
 * a 'blocks' or 'body' field present. It isn't looking anywhere else.
 *
 * ~~~~~
 * // Output the page content
 * echo getContent($page);
 * ~~~~~
 *
 * @param Page $page The `Page` to be queried.
 * @return string
 *
 */
function getContent(Page $page) {
	$out = '';
	if($page->hasField('blocks') && $page->blocks->count()) {
		$out .= $page->render->blocks;
	} else if($page->hasField('body') && $page->body) {
		$out .= content($page->body);
	}
	return $out;
}

/**
 * Return the image alt tag
 *
 * ~~~
 * // Get the image's alt tag
 * $alt = getImageAlt($image);
 * ~~~
 *
 * @param Pageimage $image
 * @param string $field
 * @return string
 *
 */
function getImageAlt(Pageimage $image, $field = 'title') {
	return $image->get($field) ?: ($image->description ?: '');
}

/**
 * Return the image caption
 *
 * Converts simple markdown to HTML.
 *
 * ~~~
 * // Get the image's caption
 * $caption = getImageCaption($image);
 * ~~~
 *
 * @param Pageimage $image
 * @return string
 *
 */
function getImageCaption(Pageimage $image) {
	return $image->wire('sanitizer')->entitiesMarkdown(getImageAlt($image, 'headline'), [
		'doubleEncode' => false,
	]);
}

/**
 * Get items
 *
 * Used primarily for lazy loading.
 *
 * ~~~~~
 * // Output the page content
 * $items = getItems($page);
 * ~~~~~
 *
 * @param Page $page The `Page` to be queried.
 * @param array $selectors
 * @return PageArray
 *
 */
function getItems(Page $page, array $selectors = []) {

	$pages = $page->wire('pages');

	if($page->template->name === 'search') {

		$selectors['template'] = [];
		foreach($page->wire('templates')->find('name!=admin|home') as $template) {
			if(
				($template->hasField('body') || $template->hasField('blocks')) &&
				$template->hasRole('guest') &&
				$template->filenameExists() &&
				!in_array($template->name, [
					'organisations',
					'organisation',
					'organisation-default',
				])
			) {
				$selectors['template'][] = $template->name;
			}
		}

		$matches = $pages->newPageArray();
		if(!empty($selectors['q'])) {

			$start = $selectors['start'];
			$limit = $selectors['limit'];
			unset($selectors['start']);
			unset($selectors['limit']);

			$q = $page->wire('sanitizer')->selectorValue($selectors['q']);
			unset($selectors['q']);

			// Exact Matches
			$matches->import($pages->find(array_merge($selectors, [
				'title|headline%=' => $q,
			])));

			// Content Matches
			foreach($pages->find(array_merge($selectors, [
				'intro|summary|body|blocks.body%=' => $q,
			])) as $item) {
				if(!$matches->has($item)) $matches->import($item);
			}

			$matches = $matches->find([
				'start' => $start,
				'limit' => $limit,
			]);
		}

		return $matches;
	}

	return $page->children($selectors);
}

/**
 * Return the active tags for child pages
 *
 * ~~~
 * // Get the tags
 * $tags = getTags($page);
 * ~~~
 *
 * @param Page $parent The parent of the pages to check for tags.
 * @return PageArray
 *
 */
function getTags(Page $parent) {
	$tags = $parent->wire('pages')->newPageArray();
	foreach($parent->wire('pages')->find([
		'template' => 'tag',
		'parent.template' => 'tags',
	]) as $tag) {
		if($parent->hasChildren(['tags' => $tag])) {
			$tags->add($tag);
		}
	}
	$tags->sort($parent->wire('templates')->get('tags')->sortfield);
	return $tags;
}

function linkCover($link, $label = '') {
	$defaultLabel = __('Read more about %s');
	$defaultItem = 'this page';
	if($link instanceof Page) {
		if(!$label) $defaultItem = $link->title;
		$link = $link->url;
	}
	return nb()->attr([
		'href' => $link,
		'ariaLabel' => sprintf($defaultLabel, $label ?: $defaultItem),
		'class' => 'read-more',
	], 'a', true);
}

function linkCoverVideo($link) {
	if($link instanceof Page) $link = $link->getUnformatted('link_video');
	return linkCover($link, __('Play video'));
}

function videoPoster(Page $page, $data = []) {
	$tpl = '<img src="{src}" alt="{alt}" width="{width}" height="{height}">';
	if($page->thumb->count) {
		return $page->thumb->first->render($tpl);
	} else if(isset($data['thumbnail_url'])) {
		$tpl = str_replace('{src}', $data['thumbnail_url'], $tpl);
		$tpl = str_replace('{alt}', $data['title'], $tpl);
		$tpl = str_replace('>', ' loading="lazy">', $tpl);
		return $tpl;
	}
	return '';
}

function forSale(Page $page) {
	return $page->checkbox && $page->price;
}

function price($value) {
	return htmlentities('£') . str_replace(".00", "", (string) number_format($value, 2));
}

function paypal($config, $label, $buy = true) {

	$nb = nb();

	if(empty($config['cmd'])) $config['cmd'] = '_cart';
	$config['business'] = 'vas@shetland.org';

	if($buy) {
		$config['currency_code'] = 'GBP';
		$config['lc'] = 'GB';
	}

	$inputs = '';
	foreach($config as $name => $value) {
		$inputs .= $nb->attr([
			'type' => 'hidden',
			'name' => $name,
			'value' => $value,
		], 'input');
	}

	$style = 'primary';
	$attrs = ['type' => 'submit'];
	if(is_array($label)) {
		if(isset($label['attrs'])) $attrs = array_merge($attrs, $label['attrs']);
		if(isset($label['style'])) $style = $label['style'];
		$label = $label['label'];
	}

	return $nb->wrap(
		$inputs .
		ukButton($label, 'primary', ['attrs' => $attrs]),
		[
			'action' => 'https://www.paypal.com/cgi-bin/webscr',
			'method' => 'post',
			'target' => 'paypal',
		],
		'form'
	);
}

function paypalAdd(Page $page) {
	return forSale($page) ? paypal([
		'add' => 1,
		'item_name' => "{$page->parent->title} - $page->title",
		'amount' => $page->price,
		'no_shipping' => 2,
		'no_note' => 1,
		'bn' => 'PP-ShopCartBF',
	], __('Add to Cart')) : '';
}

function paypalView() {
	return paypal([
		'display' => 1,
	], __('View Cart'), false);
}
