<div data-name="<?=$this->occupancyName?>">
    <?php $this->modeLinkView->render()?>
    <?php $this->statusbarView->render()?>
<?php foreach ($this->months as $month):?>
    <?php $this->monthListView($month)->render()?>
<?php endforeach?>
    <?php $this->monthPaginationView->render()?>
</div>
