<?php namespace ProcessWire;

/**
 * Video Block
 *
 */

if($page->link_video) {
	echo content($page->link_video);
}
