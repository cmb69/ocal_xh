<div class="ocal_calendars" data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->esc($modeLink)?>
<?php if ($isEditable):?>
  <?=$this->esc($csrfTokenInput)?>
  <?=$this->esc($toolbar)?>
<?php endif?>
  <?=$this->esc($statusbar)?>
<?php foreach ($monthCalendars as $monthCalendar):?>
  <?=$this->esc($monthCalendar)?>
<?php endforeach?>
  <?=$this->esc($monthPagination)?>
</div>
