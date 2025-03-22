<?php

use Plib\View;

/**
 * @var View $this
 * @var string $isoDate
 * @var string $monthname
 * @var int $year
 * @var list<string> $daynames
 * @var list<list<stdClass|null>> $weeks
 */
?>

<table class="ocal_calendar" data-ocal_date="<?=$this->esc($isoDate)?>">
  <thead>
    <tr>
      <th colspan="7"><?=$this->esc($monthname)?> <?=$year?></th>
    </tr>
    <tr>
<?php foreach ($daynames as $dayname):?>
      <th><?=$this->esc($dayname)?></th>
<?php endforeach?>
    </tr>
  </thead>
  <tbody>
<?php foreach ($weeks as $week):?>
    <tr>
<?php   foreach ($week as $day):?>
<?php       if (empty($day)):?>
      <td>&nbsp;</td>
<?php       else:?>
      <td class="ocal_state <?=$this->esc($day->todayClass)?>" data-ocal_state="<?=$this->esc($day->state)?>" title="<?=$this->text($day->titleKey)?>"><?=$this->esc($day->day)?></td>
<?php       endif?>
<?php   endforeach?>
    </tr>
<?php endforeach?>
  </tbody>
</table>
