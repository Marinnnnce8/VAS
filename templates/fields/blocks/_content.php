<?php namespace ProcessWire;

/**
 * Content Block
 *
 */

if($page->body) {
	echo content($page->body);
}
