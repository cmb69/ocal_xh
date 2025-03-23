<?php

use Plib\View;

/**
 * @var View $this
 * @var string $date
 * @var string $from
 * @var string $to
 * @var list<string> $daynames
 * @var list<list<stdClass>> $hours
 */
?>

<table class="ocal_calendar" data-ocal_date="<?=$this->esc($date)?>">
  <thead>
    <tr>
      <th colspan="7"><?=$this->esc($from)?>–<?=$this->esc($to)?></th>
    </tr>
    <tr>
<?foreach ($daynames as $dayname):?>
      <th><?=$this->esc($dayname)?></th>
<?endforeach?>
    </tr>
  </thead>
  <tbody>
<?foreach ($hours as $hour):?>
    <tr>
<?  foreach ($hour as $day):?>
      <td class="ocal_state" data-ocal_state="<?=$this->esc($day->state)?>" title="<?=$this->text("label_state_{$day->state}")?>"><?=$this->esc($day->hour)?></td>
<?  endforeach?>
    </tr>
<?endforeach?>
  </tbody>
</table>
