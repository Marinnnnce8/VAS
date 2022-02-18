<?php namespace ProcessWire;

/**
 * Initialize
 *
 * Set up site functions, defaults, styles and scripts
 *
 */

/**
 * Site functions
 *
 */

include_once 'func/nb.php';
include_once 'func/uikit.php';
include_once 'func/site.php';
include_once 'func/render.php';
include_once 'func/components.php';

/**
 * Add stylesheets/scripts
 *
 */

$nb->styles->import([
	'css/uikit-theme.min.css',
	'css/mmenu-theme.min.css',
	'css/main.scss',
]);

$nb->scripts->import([
	'js/uikit.min.js',
	'js/nbkit.min.js',
	'js/mmenu.js',
	'js/main.js',
]);

/**
 * Homepage
 *
 */

$pageHome = $pages->get(1);

/**
 * Page
 *
 */

// Is this?
$page->set('isHome', $page->id === $pageHome->id || $page->id === 1158);
$page->set('isRoot', $page->rootParent->id === $page->id);
$page->set('isArticle', $page->template->name === 'post');
$page->set('isOrg', $page->rootParent->id === 1158 || array_key_exists($config->httpHost, $nb->_subsites));

// Breadcrumbs
$page->set('breadCrumbs', $page->parents);

// Page Title
$page->set('h1', $page->get('headline|title'));

// JSON-LD
$page->jsonld($nb->jsonldOrg, 'Organization');
$page->jsonld([
	'name' => $page->h1,
	'url' => $page->httpUrl,
	'description' => $page->getSummary,
	'image' => $page->getImage(false)->httpUrl,
	'mainEntityOfPage' => true,
]);

// Is this template using markup regions?
$page->set('pwReplace', in_array($page->template->name, [
	'home',
]));

$page->set('organisation', $page->isOrg ?
	(in_array($page->template->name, ['organisations', 'organisation']) ?
		$page : $page->closest('template=organisation')) :
	WireArray::new(['title' => $nb->siteName])
);

/**
 * Variables
 *
 */

// Default uk-container
$nb->set('ukContainer', 'xsmall');

// The page content variables
$prepend = ''; // Before main
$before = ''; // Before content, after title
$content = ''; // Page content
$after = ''; // After the content
$append = ''; // After main


$tplLink = '<li><a href="{url}">{title}</a></li>';
