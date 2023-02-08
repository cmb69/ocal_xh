<div data-name="<?=$this->occupancyName()?>">
  <?=$this->modeLink()?>
  <?=$this->statusbar()?>
<?php foreach ($this->monthLists as $monthList):?>
  <?=$this->esc($monthList)?>
<?php endforeach?>
  <?=$this->monthPagination()?>
</div>
