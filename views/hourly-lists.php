<div data-name="<?=$this->occupancyName?>">
    <?php $this->modeLinkView->render()?>
    <?php $this->statusbarView->render()?>
    <dl class="ocal_list">
<?php foreach ($this->weeks as $week):?>
        <?php $this->weekListView($week)->render()?>
<?php endforeach?>
    </dl>
    <?php $this->weekPaginationView->render()?>
</div>
