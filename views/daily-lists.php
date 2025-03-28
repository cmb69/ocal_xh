<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $occupancyName
 * @var string $modeLink
 * @var string $statusbar
 * @var list<string> $monthLists
 * @var string $monthPagination
 * @var array<string,mixed> $js_config
 * @var string $js_script
 */
?>

<script type="module" src="<?=$this->esc($js_script)?>"></script>
<div class="ocal_lists" data-name="<?=$this->esc($occupancyName)?>" data-ocal-config='<?=$this->json($js_config)?>'>
  <?=$this->raw($modeLink)?>
  <?=$this->raw($statusbar)?>
<?foreach ($monthLists as $monthList):?>
  <?=$this->raw($monthList)?>
<?endforeach?>
  <?=$this->raw($monthPagination)?>
</div>
