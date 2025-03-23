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
 * @var array<string,mixed> $js_config
 * @var string $js_script
 */
?>

<script type="module" src="<?=$this->esc($js_script)?>"></script>
<div class="ocal_calendars" data-name="<?=$this->esc($occupancyName)?>" data-ocal-config='<?=$this->json($js_config)?>'>
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
