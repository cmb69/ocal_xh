<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var string $from
 * @var string $to
 */
?>

<dl class="ocal_list">
<?if (empty($weekList)):?>
  <dt><?=$this->esc($from)?>–<?=$this->esc($to)?></dt>
  <dd class="ocal_noentry"><?=$this->text('message_no_entry')?></dd>
<?else:?>
<?  foreach ($weekList as $day):?>
  <dt><?=$this->esc($day->label)?></dt>
  <dd>
    <dl>
<?    foreach ($day->list as $item):?>
      <dt><?=$this->esc($item->range)?></dt>
      <dd><span data-ocal_state="<?=$this->esc($item->state)?>"><?=$this->esc($item->label)?></span></dd>
<?    endforeach?>
    </dl>
  </dd>
<?  endforeach?>
<?endif?>
</dl>
