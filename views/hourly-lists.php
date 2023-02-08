<div data-name="<?=$this->occupancyName()?>">
  <?=$this->modeLink()?>
  <?=$this->statusbar()?>
<?php foreach ($this->weekLists as $weekList):?>
  <?=$this->esc($weekList)?>
<?php endforeach?>
  <?=$this->weekPagination()?>
</div>
