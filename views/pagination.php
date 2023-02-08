<?php

use Ocal\View;

/**
 * @var View $this
 * @var list<stdClass> $items
 */
?>
<p class="ocal_pagination">
<?php foreach ($items as $item):?>
  <a class="ocal_button" href="<?=$this->esc($item->url)?>"><?=$this->text($item->label)?></a>
<?php endforeach?>
</p>
