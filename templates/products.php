<?php namespace ProcessWire;

/**
 * Products
 *
 */

$selectors = ['limit' => 12];

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

if($page->checkbox) {
	$page->body .= paypal([
		'cmd' => '_donations',
		'item_name' => 'New Shetlander Invoice Payment',
		'no_shipping' => 1,
		'cn' => 'Enter Invoice Number',
		'tax' => '0',
		'bn' => 'PP-DonationsBF',
	], __('Pay invoice'));
}

$content .= getContent($page);

include 'tpl/items.php';
