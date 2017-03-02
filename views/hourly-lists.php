<div data-name="<?=$this->occupancyName?>">
    <?=$this->modeLink?>
    <?=$this->statusbar?>
    <dl class="ocal_list">
<?php foreach ($this->weeks as $week):?>
        <?=$this->weekList($week)?>
<?php endforeach?>
    </dl>
    <?=$this->weekPagination?>
</div>
