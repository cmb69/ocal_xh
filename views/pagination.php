<p class="ocal_pagination">
<?php foreach ($this->items as $item):?>
    <a href="<?=$this->url($item->year, $item->monthOrWeek)?>"><?=$this->text($item->label)?></a>
<?php endforeach?>
</p>