<div class="ocal_calendars" data-name="<?=$this->occupancyName?>">
    <?php $this->modeLinkView->render()?>
<?php if ($this->isEditable):?>
    <?=$this->csrfTokenInput?>
    <?php $this->toolbarView->render()?>
<?php endif?>
    <?php $this->statusbarView->render()?>
<?php foreach ($this->months as $month):?>
    <?php $this->monthCalendarView($month)->render()?>
<?php endforeach?>
    <?php $this->monthPaginationView->render()?>
</div>
