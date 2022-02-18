<?php namespace ProcessWire;

/**
 * _main.php
 *
 * Please integrate common template elements here
 *
 */

$navItems = $pageHome->children;

$lblSearch = __('Search');
$ariaSearchToggle = __('Click to open search field');
$iconSearch = renderIcon('search');

$urlImages = "{$urls->templates}img/";

$pageMember = $pages->get(1064);

$banner = $page->hasField('banner') && $page->banner->count ? $page->banner->first : null;
$bannerClass = ['banner', 'uk-position-relative'];
$bannerWrapClass = ['uk-container'];
if($banner) {
	$bannerWrapClass[] = 'uk-container-expand-right';
	if($page->isHome) $banner = $banner->size($banner->height, $banner->height);
} else {
	$bannerClass[] = 'no-image';
	$bannerWrapClass[] = "uk-container-$nb->ukContainer";
}
if(!$page->isHome) $bannerClass[] = 'banner-inner-page';

include '_head.php';

?><body<?= $banner ? ' class="has-banner-image"' : '' ?>>

	<div class="page-wrapper" id="wrapper">

		<div id="top"></div>

		<?php if($pageHome->body && !$page->isOrg): ?>
		<div class="uk-alert uk-alert-warning uk-margin-remove top-alert" data-uk-alert hidden>
			<?= $pageHome->body ?>
			<a class="uk-alert-close" data-uk-close></a>
		</div>
		<?php endif; ?>

		<header class="header">
			<nav class="uk-navbar-container uk-navbar<?= $banner ? ' uk-navbar-transparent' : '' ?>" data-uk-navbar>

				<div class="uk-navbar-left">
					<?php if($page->isOrg && (!$page->organisation->url || $page->isRoot)): ?>

					<?php else: ?>
					<a href="<?= $page->isOrg ? $page->organisation->url : $urls->root ?>" class="uk-navbar-item uk-logo">
						<img src="<?= $urls->templates ?>img/logo-bg.png" alt="">
						<?= (
							$page->isOrg && $page->organisation->hasField('logo') && $page->organisation->logo->count ?
								$page->organisation->logo :
								$pageHome->logo
						)->first->render([
							'alt' => sprintf(__('%s Logo'), $page->organisation->title),
						]) ?>
					</a>
					<?php endif; ?>
				</div>

			<?php if(!$page->isOrg): ?>
				<div class="uk-navbar-right">
					<div id="nav" class="uk-visible@m">
						<?= renderNavigation($navItems) ?>
					</div>

					<div class="uk-navbar-item tools uk-visible@m">
						<a href="#modal-search" class="search-toggler" aria-label="<?= $ariaSearchToggle ?>" data-uk-toggle>
							<?= $iconSearch ?>
						</a>
						<?= ukButtonLink($pageMember->title, $pageMember->url) ?>
					</div>

					<div class="uk-flex uk-flex-center uk-hidden@m">
						<a href="#modal-search" class="uk-navbar-item search-toggler" aria-label="<?= $ariaSearchToggle ?>" data-uk-toggle>
							<?= $iconSearch ?>
						</a>
						<a href="#" class="uk-navbar-item uk-navbar-toggle uk-hidden@m uk-margin-small-right" aria-label="<?= __("Toggle Navigation") ?>" data-uk-toggle="target: #mmenu; cls: menu-hidden;">
							<span class="outer">
								<span></span>
							</span>
						</a>
					</div>
				</div>
			<?php endif; ?>

			</nav>
		</header>

		<?= $prepend ?>

		<main class="uk-margin-bottom">

			<div class="<?= $nb->attrValue($bannerClass) ?>">
				<div class="<?= $nb->attrValue($bannerWrapClass) ?>">
					<div class="uk-grid uk-flex-bottom" data-uk-grid>
						<div class="<?= $banner ? 'uk-width-content@l' : 'uk-width-1-1' ?>">
							<div class="text">
								<h1 class="banner-heading uk-light"><?= $page->h1 ?></h1>
								<?php if($page->isHome && !$page->isOrg): ?>
								<p class="banner-summary uk-light"><?= nl2br($page->intro) ?></p>
								<div class="uk-margin-top">
									<?= ukButtonLink($pageMember->title, $pageMember->url, 'primary', [
										'size' => 'large',
										'icon' => 'arrow-right'
									]) ?>
								</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="uk-width-expand uk-container-item-padding-remove-right">
							<?php if($banner): ?>
								<div class="hero-media">
									<div class="clipped square">
										<?= $banner->render() ?>
									</div>

									<?php if($page->isHome): ?>
									<img class="top-image" src="<?= $urlImages ?>hero-logo.png">
									<?php endif; ?>
									<img class="waves" src="<?= $urlImages ?>wave-green.svg">
								</div>
							<?php else: ?>
								<img class="banner-hex" src="<?= $urlImages ?>banner-hex.svg">
								<img class="waves" src="<?= $urlImages ?>wave-green.svg">
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>

			<?= !$page->isHome ? ukContainer(ukBreadcrumb() . $before, $nb->ukContainer) : '' ?>

		<?php if($page->pwReplace): ?>

			<div id="page-<?= $page->template->name ?>"></div>

		<?php else: ?>

			<?php

				if($page->isOrg && !$page->isHome && $page->organisation->numChildren) {
					echo $nb->wrap(
						$nb->wrap(
							renderHeading($nb->link($page->organisation->url, $page->organisation->title), 4) .
							$nb->wrap($page->organisation->children->each($tplLink), 'ul'),
							'<div class="widget">'
						),
						'<aside class="sidebar">'
					);
				}

			?>

			<?php if($content || $page->intro): ?>
			<div class="uk-margin-medium-top main-body">
				<?= renderIntro($page->intro) ?>
				<?= ukGrid($content, 'uk-child-width-1-1') ?>
			</div>
			<?php endif; ?>

		<?php endif; ?>

			<?= $after ?>

			<?php if(in_array($page->template->name, [
				'default',
				'organisation',
				'post',
			])): ?>
			<div class="share-box">
				<h6>Share page on:</h6>
				<ul><?php

					$links = [];
					$shareUrl = urlencode($page->httpUrl);
					$shareTitle = urlencode($page->get('meta_title|og_title|title'));
					foreach([
						'facebook' => "https://www.facebook.com/sharer.php?u=$shareUrl",
						'twitter' => "https://twitter.com/share?url=$shareUrl&text=$shareTitle",
						'envelope' => "mailto:?subject=$shareTitle"
					] as $icon => $url) {
						$links[] = $nb->link($url, renderIcon($icon), ['class' => 'uk-icon-button']);
					}

					echo $nb->wrap($links, 'li');

				?></ul>
			</div>
			<?php endif; ?>

		</main>

		<?= $append ?>

		<?php if(!$page->isOrg): ?>

		<section class="uk-section section-subscribe">
			<div class="uk-container">
				<div class="section-bg"></div>
				<div class="uk-grid" data-uk-grid>
					<div class="uk-width-1-1 uk-width-40@m">
						<div class="uk-flex uk-flex-column uk-flex-between">
							<div class="section-header">
								<h2 class="section-title">Subscribe to our newsletter and keep up with the latest news from the Third Sector</h2>
								<a href="https://nbcommunication.us5.list-manage.com/subscribe?u=e899af7830d90885df72dfb01&id=d7c58f7a5c" class="uk-button uk-button-primary uk-button-large" target="_blank"><span>Subscribe</span><?= renderIcon('arrow-right') ?><span></span>
								</a>
							</div>

							<div class="uk-margin-medium-top uk-flex uk-flex-between uk-flex-bottom">
								<?= $pageHome->funders->find('logo!=')->each(function($item) use ($nb) {
									return $nb->wrap(
										$item->logo->first->render(['srcset' => false]),
										[
											'href' => $item->link ?: false,
											'target' => $item->link ? '_blank' : false,
											'class' => 'partner',
										],
										$item->link ? 'a' : 'span'
									);
								}) ?>
							</div>
						</div>
					</div>
					<div class="uk-width-expand">
						<img src="<?= $urlImages ?>newsletter-graphic.svg" class="infographic">
					</div>
				</div>
			</div>
		</section>

		<?php else: ?>

		<div class="section-bg"></div>

		<?php endif;

		$legalLinks = $pages->find([
			'template' => 'legal',
			'include' => 'hidden',
			'sort' => 'sort',
			'parent_id' => $page->isOrg ? $page->rootParent->id : 1,
		])->each($tplLink);

		$legalInfo = $nb->clientData('Legal')['info'];

		$thisYear = date('Y');
		$year = $nb->launchDate ? date('Y', $nb->launchDate) : $thisYear;

		$copyright = implode(' ', [
			__('Copyright') . ' &copy;',
			(date('Y') !== $year ? "$year - " : '') . $thisYear,
			"{$nb->clientName}.",
			__('All rights reserved.')
		]);

		$credit = $nb->link(
				'https://www.nbcommunication.com/',
				$nb->wrap(__('Website by NB'), 'small') .
				$nb->attr([
					'src' => "{$urls->templates}img/nb.png",
					'alt' => 'NB Communication Ltd Logo',
					'width' => 16,
					'height' => 16,
					'loading' => 'lazy',
				], 'img'),
				[
					'title' => 'NB Communication - Digital Marketing Agency',
					'target' => '_blank',
					'rel' => 'noopener',
					'class' => 'nb',
				]
			);

		?>

		<footer class="footer uk-padding-large uk-padding-remove-horizontal">

		<?php if(!$page->isOrg): ?>
			<div class="uk-container uk-container-expand-left">
				<div class="uk-grid uk-child-width-1-2@l" data-uk-grid>
					<div>
						<div class="uk-grid uk-grid-match h-100@l" data-uk-grid>
							<div class="uk-width-1-3@m uk-width-1-2@s">
								<div class="white-logo">
									<img src="<?= $urlImages ?>footer-logo.png">
									<small>Voluntary Action Shetland is committed to equal opportunities.</small>
								</div>
							</div>
							<div class="uk-width-2-3@m uk-width-1-2@s">
								<div class="widget widget-contact">
									<div>

										<div class="uk-flex uk-flex-column uk-flex-between">
											<div class="uk-child-width-1-2@m" data-uk-grid>
												<div class="uk-width-1-1"><h4>Get in touch</h4></div>
												<div>
													<p>
														<?= $nb->clientName ?><br>
														<?= nl2br($nb->clientAddress) ?>
													</p>
												</div>
												<div>
													<p>
														<?= nbTel($nb->clientTel, ['class' => 'uk-display-block']) ?>
														<?= nbMailto($nb->clientEmail, ['class' => ['uk-display-block', 'mail']]) ?>
													</p>

													<?php $social = $nb->clientData('Social'); ?>
													<?php if($social->count): ?>
														<ul class="social-channels">
															<?= $social->each(function($icon, $url) use ($nb) {
																return $nb->wrap($nb->link($url, renderIcon($icon), [
																	'title' => sprintf(__('Find us on %s'), ucfirst($icon)),
																	'class' => ['uk-icon-button'],
																]), 'li');
															}) ?>
														</ul>
													<?php endif; ?>
												</div>
											</div>
											<div class="uk-visible@m">
												<small class="legal uk-display-block"><?= $legalInfo ?></small>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

					</div>
					<div>

						<div class="uk-grid centered-mobile" data-uk-grid><?=

						$nb->wrap(
							$pages->find([1072, 1075])->explode(function($parent) use ($nb, $tplLink) {
								return renderHeading($parent->title, 4, ['uk-h5']) .
									$nb->wrap(
										$parent->children('limit=4')->each($tplLink),
										'<ul class="list-alt">'
									);
							}),
							'div'
						);

						?>
							<div>
								<ul class="legal"><?= $legalLinks ?></ul>
							</div>
						</div>

						<div class="footer-bottom">

							<div class="uk-hidden@m">
								<small><?= $legalInfo ?></small>
							</div>

							<div class="legal">
								<small><?= $copyright ?></small>
							</div>
							<div><?= $credit ?></div>
						</div>

					</div>
				</div>
			</div>
		<?php else: ?>
			<div class="uk-container">
				<div class="uk-grid uk-child-width-1-2@m" data-uk-grid>
					<div class="legal">
						<small><?= $copyright ?></small>
					</div>
					<div>
						<ul class="legal">
							<?= $legalLinks ?>
							<li><?= $credit ?></li>
						</ul>
					</div>
				</div>
			</div>
		<?php endif; ?>

		<a href="#top" class="goto-top" data-uk-totop data-uk-scroll><?= renderIcon('angle-up') ?></a>
	</footer>

		<?php if(!$page->isOrg): ?>
		<div class="mmenu-overlay uk-hidden">
			<div id="mmenu">
				<?= renderNavigation($navItems, true) ?>
			</div>
			<div class="mmenu-bg"></div>
		</div>

		<div id="modal-search" class="uk-modal-full" data-uk-modal>
			<div class="uk-modal-dialog uk-background-primary">
				<button class="uk-modal-close-full uk-close-large uk-background-primary uk-light" type="button" data-uk-close></button>
				<div class="uk-flex uk-flex-middle uk-flex-center" data-uk-height-viewport>
					<div class="uk-width-4-5 uk-width-1-2@s uk-width-1-3@l">
						<form method="get" action="<?= $pages->get('template=search')->url ?>" id="search-form">
							<label class="uk-form-label uk-light"><?= "$lblSearch $nb->siteName" ?>:</label>
							<div class="uk-grid-small uk-flex-middle" data-uk-grid>
								<div class="uk-width-expand@s">
									<input name="q" type="text" maxlength="2048" placeholder="<?= __('Enter keyword') ?>..." aria-invalid="false" title="<?= __('Please enter a keyword') ?>" required="required" value="" class="uk-input" placeholder="<?= $lblSearch ?>...">
								</div>
								<div class="uk-width-auto@s">
									<button type="submit" name="submit" class="uk-button uk-button-primary" aria-label="Search">
										<span><?= $lblSearch ?></span><?= $iconSearch ?><span></span>
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>

		<?php endif; ?>

		<svg class="svg-defs" style="position: absolute;width: 1px; height: 1px;bottom: 0;right:0; opacity: 0">
			<defs>

				<clipPath id="clipping-hex-2" clipPathUnits="objectBoundingBox">
					<path d="M0.469,0.008 C0.489,-0.002,0.514,-0.002,0.534,0.008 L0.969,0.229 C0.989,0.239,1,0.258,1,0.279 V0.722 C1,0.742,0.989,0.761,0.969,0.771 L0.534,0.993 C0.514,1,0.489,1,0.469,0.993 L0.034,0.771 C0.014,0.761,0.002,0.742,0.002,0.722 V0.279 C0.002,0.258,0.014,0.239,0.034,0.229 L0.469,0.008"></path>
				</clipPath>

				<clipPath id="clipping-hex" clipPathUnits="objectBoundingBox">
					<path d="M0.488,0.003 C0.496,-0.001,0.506,-0.001,0.514,0.003 L0.988,0.242 C0.996,0.246,1,0.254,1,0.262 V0.739 C1,0.747,0.996,0.755,0.988,0.759 L0.514,0.997 C0.506,1,0.496,1,0.488,0.997 L0.014,0.759 C0.006,0.755,0.001,0.747,0.001,0.739 V0.262 C0.001,0.254,0.006,0.246,0.014,0.242 L0.488,0.003"></path></clipPath>

				<clipPath id="clipping-hex-1-2" clipPathUnits="objectBoundingBox">
					<path d="m0.488,0.008 c0.008,-0.008,0.018,-0.008,0.026,0 L0.988,0.51 c0.008,0.009,0.013,0.024,0.013,0.042 L1,1,0.001,0.998,0.001,0.552 C0.001,0.535,0.006,0.519,0.014,0.51"></path>
				</clipPath>
			</defs>
		</svg>
	</div>
	<?php include '_foot.php'; ?>
</body>
</html>
