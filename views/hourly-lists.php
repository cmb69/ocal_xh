<div data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->esc($modeLink)?>
  <?=$this->esc($statusbar)?>
<?php foreach ($weekLists as $weekList):?>
  <?=$this->esc($weekList)?>
<?php endforeach?>
  <?=$this->esc($weekPagination)?>
</div>
