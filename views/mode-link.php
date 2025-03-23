<?php

use Plib\View;

/**
 * @var View $this
 * @var string $mode
 * @var string $url
 */
?>

<p class="ocal_mode">
  <a class="ocal_button" href="<?=$this->esc($url)?>"><?=$this->text("label_{$mode}_view")?></a>
</p>
