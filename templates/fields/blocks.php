<?php namespace ProcessWire;

/**
 * Render blocks
 *
 */

foreach($page->blocks as $block) {
	echo $block->render();
}
