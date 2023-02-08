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
 * @var list<HtmlString> $weekCalendars
 * @var HtmlString $statusbar
 * @var HtmlString $weekPagination
 */
?>
<div class="ocal_week_calendars" data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->esc($modeLink)?>
<?php if ($isEditable):?>
  <?=$this->esc($csrfTokenInput)?>
  <?=$this->esc($toolbar)?>
<?php endif?>
  <?=$this->esc($statusbar)?>
<?php foreach ($weekCalendars as $weekCalendar):?>
  <?=$this->esc($weekCalendar)?>
<?php endforeach?>
  <?=$this->esc($weekPagination)?>
</div>
