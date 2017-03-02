<?php foreach ($this->dates as $weekday => $date):?>
<?php   $listOfDay = $this->listOfDay($weekday)?>
<?php   if (!empty($listOfDay)):?>
<dt><?=$this->dayLabel($date)?></dt>
<dd>
    <dl>
<?php       foreach ($listOfDay as $item):?>
        <dt><?=$this->escape($item->range)?></dt>
        <dd><span data-ocal_state="<?=$this->escape($item->state)?>"><?=$this->escape($item->label)?></span></dd>
<?php       endforeach?>
    </dl>
</dd>
<?php   endif?>
<?php endforeach?>
