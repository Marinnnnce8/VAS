<?php namespace ProcessWire;

/**
 * Homepage
 *
 */

$who = explode("\n", $page->address, 2);

$label = function($key) use ($page) {
	return $page->getField($key)->label;
};

$url = function(int $id) use ($pages) {
	return $pages->get($id)->url;
};

?><div pw-replace='page-<?= $page->template ?>'>

	<section class="uk-section section-who-we-are">
		<div class="uk-container">
			<div class="uk-grid uk-child-width-1-2@m" data-uk-grid>
				<div>
					<div class="section-header">
						<div class="section-label uk-text-uppercase"><?= $label('address') ?></div>
						<h2 class="section-title"><?= $who[0] ?></h2>
						<div class="section-summary">
							<p><?= $who[1] ?? '' ?></p>
						</div>
						<a href="<?= $url(1057) ?>" class="uk-button uk-button-text">Find out more about us<?= renderIcon('caret-right') ?></a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="uk-section section-cta uk-padding-remove" data-uk-scrollspy="cls: uk-animation-fade;">
		<div class="image-frame" data-uk-img="<?= $page->banner->count > 1 ? $page->banner->eq(1)->size(1920, 830)->url : "{$urls->templates}img/group.jpg" ?>">
			<div class="clipped-button">
				<a href="<?= $url(1054) ?>" class="uk-button uk-button-text">View all our services <?= renderIcon('arrow-right') ?></a>
				<img src="<?= $urls->templates ?>img/cell.svg" data-uk-svg>
			</div>
		</div>
	</section>

	<section class="uk-section section-keys uk-section-large">
		<div class="uk-container">
			<div class="section-header uk-text-center">
				<h2 class="section-title"><?= $label('items') ?></h2>
			</div>
			<?= ukGrid(
				$page->items->explode(function($item, $index) use ($nb) {
					$i = $index ? $index % 4 : $index;
					return $nb->wrap(
						$nb->wrap(
							$nb->wrap(
								renderIcon([
									'book',
									'voice',
									'user-group',
									'communities',
								][$i]) .
								renderIcon('hex-rounded', 'inner') .
								renderIcon('hex-rounded', 'outer'),
								'<div class="hex">'
							),
							'uk-card-media-top'
						) .
						renderHeading($item->title, 3) .
						str_replace('<ul>', '<ul class="list-checked">', $item->body),
						[
							'entry',
							'entry-' . [
								'green',
								'blue',
								'pink',
								'purple',
							][$i]
						],
						'div'
					);
				}),
				[
					'class' => [
						'uk-child-width-1-2@s',
						'uk-child-width-1-4@l',
					],
					'dataUkScrollspy' => [
						'target' => '> div',
						'delay' => 128,
						'cls' => 'uk-animation-fade'
					],
				]

			) ?>
		</div>
		<div class="section-bg" data-uk-img="<?= $urls->templates ?>img/key-aim-bg.svg"></div>
	</section>

	<section class="uk-section">
		<div class="uk-container uk-container-large">

			<div class="honeycomb2" data-uk-scrollspy="target: .row > .w; delay: 128;cls: uk-animation-fade;">

				<div class="row">
					<div class="w">
						<div class="cell uk-light">
							<div class="inner bg-dark-blue">
								<?= renderIcon('s-sun') ?>
								<h3><?= $label('activities') ?></h3>
							</div>
						</div>
					</div>
				</div><?php

				$j = 0;
				for($i = 0; $i < $page->activities->count; $i += 4) {

					$start = $i;
					$limit = 4;
					switch($j) {
						case 2:
							$limit = 3;
							$i--;
							break;
						case 3:
							$limit = 2;
							break;
					}

					$activities = $page->activities->find("start=$start,limit=$limit");
					$c = $activities->count;

					echo $nb->wrap(
						$activities->each(function($item) use ($nb) {
							$colour = $item->colour ? $item->colour->name : 'dark-blue';
							return $nb->wrap(
								$nb->wrap(
									($item->icon ? renderIcon("s-{$item->icon->name}", ($colour === 'yellow' ? 'color-pink' : [])) : '') .
									renderHeading($item->title, 3) .
									$nb->wrap($nb->wrap(explode("\n", $item->summary), 'li'), 'ul'),
									[
										'inner',
										"bg-$colour",
									],
									'div'
								),
								'<div class="w"><div class="cell"></div></div>'
							);
						}) . str_repeat('<div class="w blank"><div class="cell"></div></div>', $limit - $c),
						'<div class="row c'. $c .'">'
					);

					$j++;
				}

			?></div>
		</div>
	</section>

	<?= opportunity() ?>

	<?php $pageNews = $pages->get('template=posts'); ?>
	<?php if($pageNews->numChildren): ?>
	<section class="uk-section section-latest">
		<div class="section-bg"></div>
		<div class="uk-container">
			<div class="section-header">
				<div class="section-label uk-text-uppercase"><?= $pageNews->title ?></div>
				<div class="uk-flex uk-flex-between uk-flex-wrap">
					<h2 class="section-title"><?= $pageNews->headline ?></h2>
					<div class="uk-visible@s"><a href="<?= $pageNews->url ?>" class="uk-button uk-button-text">View all
						news<?= renderIcon('caret-right' ) ?></a></div>
				</div>
			</div>
			<div class="uk-grid uk-grid-match uk-child-width-1-3@m" data-uk-scrollspy="target: > div; delay: 128;cls: uk-animation-fade;" data-uk-grid>
				<?= renderItems($pageNews->children('limit=3')) ?>
			</div>
			<div class="uk-hidden@s uk-margin-medium-top">
				<a href="<?= $pageNews->url ?>" class="uk-button uk-button-text">View all news<?= renderIcon('caret-right' ) ?></a>
			</div>
		</div>
	</section>
	<?php endif; ?>
</div>
