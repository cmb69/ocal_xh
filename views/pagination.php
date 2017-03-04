<p class="ocal_pagination">
<?php foreach ($this->items as $item):?>
    <a class="ocal_button" href="<?=$this->escape($item->url)?>"><?=$this->text($item->label)?></a>
<?php endforeach?>
</p>
