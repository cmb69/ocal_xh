<div class="ocal_week_calendars" data-name="<?=$this->occupancyName()?>">
    <?=$this->modeLink()?>
<?php if ($this->isEditable):?>
    <?=$this->csrfTokenInput()?>
    <?=$this->toolbar()?>
<?php endif?>
    <?=$this->statusbar()?>
<?php foreach ($this->weekCalendars as $weekCalendar):?>
    <?=$this->escape($weekCalendar)?>
<?php endforeach?>
    <?=$this->weekPagination()?>
</div>
