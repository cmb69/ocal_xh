<?php

use Ocal\HtmlString;
use Ocal\View;

/**
 * @var View $this
 * @var string $occupancyName
 * @var HtmlString $modeLink
 * @var HtmlString $statusbar
 * @var list<HtmlString> $weekLists
 * @var HtmlString $weekPagination
 */
?>
<div data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->esc($modeLink)?>
  <?=$this->esc($statusbar)?>
<?php foreach ($weekLists as $weekList):?>
  <?=$this->esc($weekList)?>
<?php endforeach?>
  <?=$this->esc($weekPagination)?>
</div>
