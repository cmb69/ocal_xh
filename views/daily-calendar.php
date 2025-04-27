<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $isoDate
 * @var string $monthname
 * @var int $year
 * @var list<string> $daynames
 * @var list<list<?object{day:?int,state:int,todayClass:string,titleKey:string}>> $weeks
 */
?>

<table class="ocal_calendar" data-ocal_date="<?=$this->esc($isoDate)?>">
  <thead>
    <tr>
      <th colspan="7"><?=$this->esc($monthname)?> <?=$year?></th>
    </tr>
    <tr>
<?foreach ($daynames as $dayname):?>
      <th><?=$this->esc($dayname)?></th>
<?endforeach?>
    </tr>
  </thead>
  <tbody>
<?foreach ($weeks as $week):?>
    <tr>
<?  foreach ($week as $day):?>
<?    if (empty($day)):?>
      <td>&nbsp;</td>
<?    else:?>
      <td class="ocal_state <?=$this->esc($day->todayClass)?>" data-ocal_state="<?=$day->state?>" title="<?=$this->text($day->titleKey)?>"><?=$day->day?></td>
<?    endif?>
<?  endforeach?>
    </tr>
<?endforeach?>
  </tbody>
</table>
