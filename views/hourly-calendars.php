<div class="ocal_week_calendars" data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->esc($modeLink)?>
<?php if ($isEditable):?>
  <?=$this->esc($csrfTokenInput)?>
  <?=$this->esc($toolbar)?>
<?php endif?>
  <?=$this->esc($statusbar)?>
<?php foreach ($weekCalendars as $weekCalendar):?>
  <?=$this->esc($weekCalendar)?>
<?php endforeach?>
  <?=$this->esc($weekPagination)?>
</div>
