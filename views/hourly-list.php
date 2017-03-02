<?php foreach ($this->dates as $weekday => $date):?>
<?php   $listOfDay = $this->listOfDay($weekday)?>
<?php   if (!empty($listOfDay)):?>
<dt><?=$this->dayLabel($date)?></dt>
<dd>
    <dl>
<?php       foreach ($listOfDay as $range => $label):?>
        <dt><?=$this->escape($range)?></dt>
        <dd><?=$this->escape($label)?></dd>
<?php       endforeach?>
    </dl>
</dd>
<?php   endif?>
<?php endforeach?>
