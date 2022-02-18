<?php namespace ProcessWire;

/**
 * foot.php
 *
 * Please retain this code, integrating as necessary
 *
 */

?>
<script src="<?= $urlScripts ?>"></script>
<?php

if(isset($nb->captcha) && isset($form)) {
	echo $nb->captcha->getScript();
}
