<div class="ocal_calendars" data-name="<?=$this->occupancyName?>">
    <?=$this->modeLinkView?>
<?php if ($this->isEditable):?>
    <?=$this->csrfTokenInput?>
    <?=$this->toolbarView?>
<?php endif?>
    <?=$this->statusbarView?>
<?php foreach ($this->months as $month):?>
    <?=$this->monthCalendarView($month)?>
<?php endforeach?>
    <?=$this->monthPagination?>
</div>
