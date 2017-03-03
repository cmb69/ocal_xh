<div data-name="<?=$this->occupancyName?>">
    <?php $this->modeLinkView->render()?>
    <?php $this->statusbarView->render()?>
<?php foreach ($this->weeks as $week):?>
        <?php $this->weekListView($week)->render()?>
<?php endforeach?>
    <?php $this->weekPaginationView->render()?>
</div>
