<div class="ocal_week_calendars" data-name="<?=$this->occupancyName?>">
    <?php $this->modeLinkView->render()?>
<?php if ($this->isEditable):?>
    <?=$this->csrfTokenInput?>
    <?php $this->toolbarView->render()?>
<?php endif?>
    <?php $this->statusbarView->render()?>
<?php foreach ($this->weeks as $week):?>
    <?php $this->weekCalendarView($week)->render()?>
<?php endforeach?>
    <?php $this->weekPaginationView->render()?>
</div>
