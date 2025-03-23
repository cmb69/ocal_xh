<?php

use Plib\View;

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
