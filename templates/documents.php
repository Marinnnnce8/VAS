<?php namespace ProcessWire;

/**
 * Documents
 *
 */

$render = function($items) use ($nb) {
	return $nb->table(
		[
			__('Title'),
			__('Size'),
		],
		$items->explode(function($item) {
			$nb = $item->wire('nb');
			return [
				$nb->link($item->httpUrl, $item->title),
				$nb->wrap($item->files->count ? $item->files->first->filesizeStr : '-', 'small'),
			];
		}),
		[
			'attrs' => [
				'class' => [
					'uk-table',
					'uk-table-small',
				],
			],
		]
	);
};

$items = [];
foreach($page->tags as $tag) {
	$tagged = $page->children("tags=$tag");
	if($tagged->count) {
		if($tag->id === 1254) $tagged->sort('-date_pub');
		$items[$tag->title] = $render($tagged);
	}
}
$misc = $page->children("tags!=$page->tags");
if($misc->count) $items[__('Miscellaneous')] = $render($misc);

$content .= content(count($items) ? ukAccordion($items, ['active' => -1]) : ukAlert(__('Sorry, there are currently no documents available for download.'), 'danger'));
