<dt><?=$this->heading?></dt>
<dd>
    <dl>
<?php foreach ($this->monthList as $item):?>
        <dt><?=$this->escape($item->range)?></dt>
        <dd><span data-ocal_state="<?=$this->escape($item->state)?>"><?=$this->escape($item->label)?></span></dd>
<?php endforeach?>
    </dl>
</dd>
