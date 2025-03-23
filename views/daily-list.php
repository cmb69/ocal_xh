<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $heading
 */
?>

<dl class="ocal_list">
  <dt><?=$this->esc($heading)?></dt>
<?if (empty($monthList)):?>
  <dd class="ocal_noentry"><?=$this->text('message_no_entry')?></dd>
<?else:?>
  <dd>
    <dl>
<?  foreach ($monthList as $item):?>
      <dt><?=$this->esc($item->range)?></dt>
      <dd><span data-ocal_state="<?=$this->esc($item->state)?>"><?=$this->esc($item->label)?></span></dd>
<?  endforeach?>
    </dl>
  </dd>
<?endif?>
</dl>
