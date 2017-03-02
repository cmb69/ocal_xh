<table class="ocal_calendar" data-ocal_date="<?=$this->isoDate?>">
    <thead>
        <tr>
            <th colspan="7"><?=$this->monthname?> <?=$this->year?></th>
        </tr>
        <tr>
<?php foreach ($this->daynames as $dayname):?>
            <th><?=$this->escape($dayname)?></th>
<?php endforeach?>
        </tr>
    </thead>
    <tbody>
<?php foreach ($this->weeks as $week):?>
        <tr>
<?php   foreach ($week as $day):?>
<?php       if (isset($day)):?>
            <td class="ocal_state <?=$this->todayClass($day)?>" data-ocal_state="<?=$this->state($day)?>" title="<?=$this->text($this->titleKey($day))?>"><?=$this->escape($day)?></td>
<?php       else:?>
            <td>&nbsp;</td>
<?php       endif?>
<?php   endforeach?>
        </tr>
<?php endforeach?>
    </tbody>
</table>
