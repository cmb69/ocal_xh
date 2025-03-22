<?php

use Plib\View;

/**
 * @var View $this
 * @var string $occupancyName
 * @var string $modeLink
 * @var string $statusbar
 * @var list<string> $monthLists
 * @var string $monthPagination
 */
?>

<div data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->raw($modeLink)?>
  <?=$this->raw($statusbar)?>
<?php foreach ($monthLists as $monthList):?>
  <?=$this->raw($monthList)?>
<?php endforeach?>
  <?=$this->raw($monthPagination)?>
</div>
