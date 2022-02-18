<?php namespace ProcessWire;

/**
 * head.php
 *
 * Please retain this code, integrating as necessary
 *
 */

$urlStyles = $procache->css($nb->styles->getArray());
$urlScripts = $procache->js($nb->scripts->getArray());

// Meta Title
if(!$page->meta_title && !$config->ajax) {
	$page->meta_title = $sanitizer->unentities($page->isHome ?
		"$nb->siteName | {$page->get("headline|title")}" :
		"$page->title | $nb->siteName"
	);
}

// Open Graph
$ogUpdated = $datetime->date('c', $page->modified);
$ogImage = $page->getImage([
	'height' => 0,
	'width' => 0,
	'fields' => [
		'og_image',
		'thumb',
		'images',
	],
]);

?><!doctype html>
<html lang="en-gb" class="<?= $nb->attrValue([
	"template-{$page->template->name}",
	"section-{$page->rootParent->name}",
	"page-$page->id",
]) ?>">
<head>
<?php if(!$nb->siteLive): ?>
	<meta name="robots" content="noindex, nofollow">
<?php endif; ?>

	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="format-detection" content="telephone=no">

	<title><?= $page->meta_title ?></title>

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link rel="preload" as="style" href="<?= $urlStyles ?>">
	<link rel="preload" as="script" href="<?= $urlScripts ?>">


	<meta name="description" content="<?= $sanitizer->entities1($page->get('meta_desc|getSummary'), true) ?>">
	<meta property="og:title" content="<?= $sanitizer->entities1($page->get('og_title|meta_title'), true) ?>">
	<meta property="og:description" content="<?= $sanitizer->entities1($page->get('og_desc|meta_desc|getSummary'), true) ?>">
	<meta property="og:url" content="<?= $input->httpUrl ?>">
	<meta property="og:site_name" content="<?= $nb->siteName ?>">

<?php if($page->isArticle): ?>
	<meta property="og:type" content="article">
	<meta property="article:published_time" content="<?= $datetime->date('c', $page->getUnformatted('date_pub|published')) ?>">
	<meta property="article:modified_time" content="<?= $ogUpdated ?>">
<?php else: ?>
	<meta property="og:type" content="website">
	<meta property="og:updated_time" content="<?= $ogUpdated ?>">
<?php endif; ?>

<?php if($ogImage->httpUrl): ?>
	<meta property="og:image" content="<?= $ogImage->httpUrl ?>">
	<meta property="og:image:width" content="<?= $ogImage->width ?>">
	<meta property="og:image:height" content="<?= $ogImage->height ?>">
	<?php /*if($page->og_image->count): ?>
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:site" content="@twitterhandle">
	<meta name="twitter:title" content="<?= $sanitizer->entities1($page->get('og_title|meta_title'), true) ?>">
	<meta name="twitter:description" content="<?= $sanitizer->entities1($page->get('og_desc|meta_desc|getSummary'), true) ?>">
	<meta property="twitter:image" content="<?= $ogImage->httpUrl ?>">
	<?php endif;*/ ?>
<?php endif; ?>

	<link rel="canonical" href="<?= $page->urlCanonical ?>">
	<link rel="shortcut icon" href="<?= $urls->root ?>favicon.ico">

<?php
	$pageRSS = $pages->get('template=feed-rss');
	if($pageRSS->id && !$pageRSS->isUnpublished()) {
		echo $nb->attr([
			'href' => $pageRSS->url,
			'rel' => 'alternate',
			'type' => 'application/rss+xml',
			'title' => sprintf(__('%s RSS Feed'), $nb->siteName)
		], 'link');
	}
?>

	<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;700&family=Rowdies:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?= $urlStyles ?>">
	<style>

	.banner .clipped {
		-webkit-clip-path: url('#clipping-hex');
		clip-path: url('#clipping-hex');
	}

	.banner.banner-inner-page:not(.no-image) .clipped {
		-webkit-clip-path: url('#clipping-hex-1-2');
		clip-path: url('#clipping-hex-1-2');
	}

	.cell {
		-webkit-clip-path: url('#clipping-hex-2');
		clip-path: url('#clipping-hex-2');
	}

	.entry-hex > div {
		-webkit-clip-path: url('#clipping-hex-2');
		clip-path: url('#clipping-hex-2');
	}
	</style>
</head>
