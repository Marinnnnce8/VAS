<?php namespace ProcessWire;

/**
 * Default
 *
 */

if($page->id === 1075) $after .= $nb->wrap(opportunity(), 'uk-margin-large-top');

include 'tpl/page.php';
