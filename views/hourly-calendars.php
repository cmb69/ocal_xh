<?php

use Plib\View;

/**
 * @var View $this
 * @var string $occupancyName
 * @var bool $isEditable
 * @var string $modeLink
 * @var string $csrfTokenInput
 * @var string $toolbar
 * @var list<string> $weekCalendars
 * @var string $statusbar
 * @var string $weekPagination
 * @var array<string,mixed> $js_config
 */
?>

<div class="ocal_week_calendars" data-name="<?=$this->esc($occupancyName)?>" data-ocal-config='<?=$this->json($js_config)?>'>
  <?=$this->raw($modeLink)?>
<?php if ($isEditable):?>
  <?=$this->raw($csrfTokenInput)?>
  <?=$this->raw($toolbar)?>
<?php endif?>
  <?=$this->raw($statusbar)?>
<?php foreach ($weekCalendars as $weekCalendar):?>
  <?=$this->raw($weekCalendar)?>
<?php endforeach?>
  <?=$this->raw($weekPagination)?>
</div>
