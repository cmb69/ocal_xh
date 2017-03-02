<div data-name="<?=$this->occupancyName?>">
    <?=$this->modeLinkView?>
    <?=$this->statusbarView?>
    <dl class="ocal_list">
<?php foreach ($this->months as $month):?>
    <?=$this->monthList($month)?>
<?php endforeach?>
    </dl>
    <?=$this->monthPagination?>
</div>
