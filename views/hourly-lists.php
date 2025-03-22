<?php

use Plib\View;

/**
 * @var View $this
 * @var string $occupancyName
 * @var string $modeLink
 * @var string $statusbar
 * @var list<string> $weekLists
 * @var string $weekPagination
 */
?>

<div data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->raw($modeLink)?>
  <?=$this->raw($statusbar)?>
<?php foreach ($weekLists as $weekList):?>
  <?=$this->raw($weekList)?>
<?php endforeach?>
  <?=$this->raw($weekPagination)?>
</div>
