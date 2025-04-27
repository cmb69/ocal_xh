<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $occupancyName
 * @var bool $isEditable
 * @var string $modeLink
 * @var string $csrf_token
 * @var string $toolbar
 * @var string $statusbar
 * @var list<string> $monthCalendars
 * @var string $monthPagination
 * @var array<string,mixed> $js_config
 * @var string $js_script
 * @var string $checksum
 */
?>

<script type="module" src="<?=$this->esc($js_script)?>"></script>
<div class="ocal_calendars" data-name="<?=$this->esc($occupancyName)?>" data-ocal-config='<?=$this->json($js_config)?>'>
  <?=$this->raw($modeLink)?>
<?if ($isEditable):?>
  <input type="hidden" name="ocal_token" value="<?=$this->esc($csrf_token)?>">
  <input type="hidden" name="ocal_checksum" value="<?=$this->esc($checksum)?>">
  <?=$this->raw($toolbar)?>
<?endif?>
  <?=$this->raw($statusbar)?>
<?foreach ($monthCalendars as $monthCalendar):?>
  <?=$this->raw($monthCalendar)?>
<?endforeach?>
  <?=$this->raw($monthPagination)?>
</div>
