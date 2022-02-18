<?php namespace ProcessWire;

/**
 * XML Sitemap
 *
 */

/**
 * Returns a single <url> node for XML sitemap
 *
 * @param Page $page
 * @return string
 *
 */
function renderSitemapPage(Page $page) {
	$launchDate = nb()->launchDate;
	return "\n<url>" .
		"\n\t<loc>{$page->httpUrl}</loc>" .
		"\n\t<lastmod>" . date('Y-m-d', ($page->modified > $launchDate ? $page->modified : $launchDate)) . '</lastmod>' .
	"\n</url>";
}

/**
 * Returns all children of a given Page as <url> nodes for XML sitemap
 *
 * @param Page $page
 * @return string
 *
 */
function renderSitemapChildren(Page $page) {

	$out = '';
	$pages = pages();
	$newParents = $pages->newPageArray();
	$children = $page->children;

	foreach($children as $child) {
		$out .= renderSitemapPage($child);
		if($child->numChildren) $newParents->add($child);
			else $pages->uncache($child);
	}

	foreach($newParents as $newParent) {
		$out .= renderSitemapChildren($newParent);
		$pages->uncache($newParent);
	}

	return $out;
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
	'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' .
		renderSitemapPage($pageHome) .
		renderSitemapChildren($pageHome) .
	"\n</urlset>";
