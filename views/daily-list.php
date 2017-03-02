<dt><?=$this->heading?></dt>
<dd>
    <dl>
<?php foreach ($this->monthList as $range => $label):?>
        <dt><?=$this->escape($range)?></dt>
        <dd><?=$this->escape($label)?></dd>
<?php endforeach?>
    </dl>
</dd>
