<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $image
 */
?>

<div class="ocal_loaderbar"><img src="<?=$this->esc($image)?>" alt="loading"></div>
<div class="ocal_statusbar"></div>
