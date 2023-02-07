<table class="ocal_calendar" data-ocal_date="<?=$this->isoDate()?>">
  <thead>
    <tr>
      <th colspan="7"><?=$this->monthname()?> <?=$this->year()?></th>
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
<?php       if (empty($day)):?>
      <td>&nbsp;</td>
<?php       else:?>
      <td class="ocal_state <?=$this->escape($day->todayClass)?>" data-ocal_state="<?=$this->escape($day->state)?>" title="<?=$this->text($day->titleKey)?>"><?=$this->escape($day->day)?></td>
<?php       endif?>
<?php   endforeach?>
    </tr>
<?php endforeach?>
  </tbody>
</table>
