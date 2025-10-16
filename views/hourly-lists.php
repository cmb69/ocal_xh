<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

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

<div class="ocal_container" data-name="<?=$this->esc($occupancyName)?>">
  <!--AJAX START-->
  <div class="ocal_week_lists" data-ocal-config='<?=$this->json($js_config)?>'>
    <?=$this->raw($modeLink)?>
    <script type="text/x-template">
      <?=$this->raw($statusbar)?>
    </script>
<?foreach ($weekLists as $weekList):?>
    <?=$this->raw($weekList)?>
<?endforeach?>
    <?=$this->raw($weekPagination)?>
  </div>
  <!--AJAX END-->
</div>
