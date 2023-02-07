<table class="ocal_calendar" data-ocal_date="<?=$this->date()?>">
  <thead>
    <tr>
      <th colspan="7"><?=$this->from()?>–<?=$this->to()?></th>
    </tr>
    <tr>
<?php foreach ($this->daynames as $dayname):?>
      <th><?=$this->escape($dayname)?></th>
<?php endforeach?>
    </tr>
  </thead>
  <tbody>
<?php foreach ($this->hours as $hour):?>
    <tr>
<?php   foreach ($hour as $day):?>
      <td class="ocal_state" data-ocal_state="<?=$this->escape($day->state)?>" title="<?=$this->text("label_state_{$day->state}")?>"><?=$this->escape($day->hour)?></td>
<?php   endforeach?>
    </tr>
<?php endforeach?>
  </tbody>
</table>
