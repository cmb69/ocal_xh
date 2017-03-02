<div class="ocal_week_calendars" data-name="<?=$this->occupancyName?>">
    <?=$this->modeLinkView?>
<?php if ($this->isEditable):?>
    <?=$this->csrfTokenInput?>
    <?=$this->toolbarView?>
<?php endif?>
    <?=$this->statusbarView?>
<?php foreach ($this->weeks as $week):?>
    <?=$this->weekCalendarView($week)?>
<?php endforeach?>
    <?=$this->weekPagination?>
</div>
