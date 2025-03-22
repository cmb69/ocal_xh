<?php

use Plib\View;

/**
 * @var View $this
 * @var string $occupancyName
 * @var bool $isEditable
 * @var string $modeLink
 * @var string $csrfTokenInput
 * @var string $toolbar
 * @var string $statusbar
 * @var list<string> $monthCalendars
 * @var string $monthPagination
 */
?>

<div class="ocal_calendars" data-name="<?=$this->esc($occupancyName)?>">
  <?=$this->raw($modeLink)?>
<?php if ($isEditable):?>
  <?=$this->raw($csrfTokenInput)?>
  <?=$this->raw($toolbar)?>
<?php endif?>
  <?=$this->raw($statusbar)?>
<?php foreach ($monthCalendars as $monthCalendar):?>
  <?=$this->raw($monthCalendar)?>
<?php endforeach?>
  <?=$this->raw($monthPagination)?>
</div>
