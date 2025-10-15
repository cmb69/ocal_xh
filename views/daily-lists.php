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
 */
?>

<div class="ocal_container" data-name="<?=$this->esc($occupancyName)?>">
  <!--AJAX START-->
  <div class="ocal_lists" data-ocal-config='<?=$this->json($js_config)?>'>
    <?=$this->raw($modeLink)?>
    <?=$this->raw($statusbar)?>
<?foreach ($monthLists as $monthList):?>
    <?=$this->raw($monthList)?>
<?endforeach?>
    <?=$this->raw($monthPagination)?>
  </div>
  <!--AJAX END-->
</div>
