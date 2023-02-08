<?php

use Ocal\HtmlString;
use Ocal\View;

/**
 * @var View $this
 * @var string $occupancyName
 * @var HtmlString $modeLink
 * @var HtmlString $statusbar
 * @var list<HtmlString> $monthLists
 * @var HtmlString $monthPagination
 */
?>
<div data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->esc($modeLink)?>
  <?=$this->esc($statusbar)?>
<?php foreach ($monthLists as $monthList):?>
  <?=$this->esc($monthList)?>
<?php endforeach?>
  <?=$this->esc($monthPagination)?>
</div>
