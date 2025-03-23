<?php

use Plib\View;

/**
 * @var View $this
 * @var string $occupancyName
 * @var string $modeLink
 * @var string $statusbar
 * @var list<string> $weekLists
 * @var string $weekPagination
 * @var array<string,mixed> $js_config
 */
?>

<div class="ocal_week_lists" data-name="<?=$this->esc($occupancyName)?>" data-ocal-config='<?=$this->json($js_config)?>'>
  <?=$this->raw($modeLink)?>
  <?=$this->raw($statusbar)?>
<?php foreach ($weekLists as $weekList):?>
  <?=$this->raw($weekList)?>
<?php endforeach?>
  <?=$this->raw($weekPagination)?>
</div>
