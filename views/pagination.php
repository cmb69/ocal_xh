<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var list<stdClass> $items
 */
?>

<p class="ocal_pagination">
<?foreach ($items as $item):?>
  <a class="ocal_button" href="<?=$this->esc($item->url)?>"><?=$this->text($item->label)?></a>
<?endforeach?>
</p>
