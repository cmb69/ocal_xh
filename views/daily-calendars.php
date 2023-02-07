<div class="ocal_calendars" data-name="<?=$this->occupancyName()?>">
  <?=$this->modeLink()?>
<?php if ($this->isEditable):?>
  <?=$this->csrfTokenInput()?>
  <?=$this->toolbar()?>
<?php endif?>
  <?=$this->statusbar()?>
<?php foreach ($this->monthCalendars as $monthCalendar):?>
  <?=$this->escape($monthCalendar)?>
<?php endforeach?>
  <?=$this->monthPagination()?>
</div>
