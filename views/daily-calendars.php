<?php

use Ocal\HtmlString;
use Ocal\View;

/**
 * @var View $this
 * @var string $occupancyName
 * @var bool $isEditable
 * @var HtmlString $modeLink
 * @var HtmlString $csrfTokenInput
 * @var HtmlString $toolbar
 * @var HtmlString $statusbar
 * @var list<HtmlString> $monthCalendars
 * @var HtmlString $monthPagination
 */
?>
<div class="ocal_calendars" data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->esc($modeLink)?>
<?php if ($isEditable):?>
  <?=$this->esc($csrfTokenInput)?>
  <?=$this->esc($toolbar)?>
<?php endif?>
  <?=$this->esc($statusbar)?>
<?php foreach ($monthCalendars as $monthCalendar):?>
  <?=$this->esc($monthCalendar)?>
<?php endforeach?>
  <?=$this->esc($monthPagination)?>
</div>
