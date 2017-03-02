<div data-name="<?=$this->occupancyName?>">
    <?php $this->modeLinkView->render()?>
    <?php $this->statusbarView->render()?>
    <dl class="ocal_list">
<?php foreach ($this->months as $month):?>
    <?php $this->monthListView($month)->render()?>
<?php endforeach?>
    </dl>
    <?php $this->monthPaginationView->render()?>
</div>
