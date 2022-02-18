<?php namespace ProcessWire;

/**
 * Posts
 *
 */

$selectors = ['limit' => 12];

if($fields->get('tags')->id) { // If tags in use

	$tag = $input->get->pageName('tag');
	if($tag) $selectors['tags'] = $tag;

	$date = $input->get->selectorValue('date');
	if($date) {
		$start = strtotime("$date-01-01");
		$selectors['date_pub>='] = $start;
		$selectors['date_pub<'] = strtotime('+1 year', $start);
	};

	$dates = [];
	foreach($page->children as $p) {
		$year = $datetime->date('Y', $p->date_pub);
		if(!$year) continue;
		$dates[$year] = $year;
	}
	krsort($dates);

	$selectorDateValues = [];
	foreach(array_keys($dates) as $key) {
		if(!$key) continue;
		$dateStart = strtotime("$key-01-01");
		$selectorDateValues[$key] = [$dateStart, strtotime('+1 year', $dateStart)];
	}

	$form = [
		'fields' => [
			[
				'type' => 'select',
				'name' => 'tag',
				'value' => $tag,
				'skipLabel' => 4,
				'options' => array_merge(
					['' => __('Select a Tag')],
					($page->tags->count ? $page->tags : getTags($page))->explode('title', ['key' => 'name'])
				),
				'wrapClass' => 'uk-width-auto@m',
			],
			[
				'type' => 'select',
				'name' => 'date',
				'value' => $date,
				'skipLabel' => 4,
				'options' => ['' => __('All Dates')] + $dates,
				'attr' => [
					'dataSelectors' => [
						'keys' => [
							'date_pub>=',
							'date_pub<',
						],
						'values' => $selectorDateValues,
					],
				],
				'wrapClass' => 'uk-width-auto@m',
			],
		],
	];
}

include 'tpl/items.php';
