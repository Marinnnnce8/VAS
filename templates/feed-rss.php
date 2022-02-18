<?php namespace ProcessWire;

/**
 * RSS Feed
 *
 * @todo Add thumbnail image to <description>
 *
 */

$posts = $pages->get('template=posts')->children('date_pub!=');
$mostRecent = $posts->first;
$imgRSS = 'img/rss.jpg';

if($posts->count) {

?><?= '<?xml version="1.0" encoding="utf-8"?>' . "\n" ?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">
	<channel>
		<title><?= $nb->siteName ?></title>
		<link><?= $pageHome->httpUrl ?></link>
		<description><?= sprintf(__('The latest news from %s'), $nb->siteName) ?></description>
		<language>en-gb</language>
		<copyright><?= __('Copyright') ?> <?= $nb->clientName ?> <?= date('Y') ?>. <?= __('All rights reserved') ?>.</copyright><?php if($mostRecent->id) { ?>
		<pubDate><?= date('r', $mostRecent->date_pub) ?></pubDate>
		<lastBuildDate><?= date('r', $mostRecent->modified) ?></lastBuildDate>
		<?php } else { echo "\n"; } ?>
		<ttl>60</ttl>
		<?php if(file_exists($config->paths->templates . $imgRSS)): ?>
		<image>
			<url><?= rtrim($pageHome->httpUrl, '/') . $urls->templates . $imgRSS ?></url>
			<link><?= $pageHome->httpUrl ?></link>
			<title><?= $nb->siteName ?></title>
		</image>
		<?php endif; ?>
		<atom:link href="<?= $page->httpUrl ?>" rel="self" type="application/rss+xml" />
		<?php foreach($posts as $post): ?>
		<item>
			<title><?= $post->title ?></title>
			<link><?= $post->httpUrl ?></link>
			<description><![CDATA[<?= renderSummary($post) ?>]]></description>
			<guid><?= $post->httpUrl ?></guid>
			<pubDate><?= date('r', $post->date_pub) ?></pubDate>
		</item>
		<?php endforeach; ?>
	</channel>
</rss><?php

} else if($nb->siteLive) {
	// If no posts, unpublish the feed
	$page->of(false);
	$page->addStatus(Page::statusUnpublished);
	$page->save();
}
