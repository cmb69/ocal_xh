<?php

use Ocal\View;

/**
 * @var View $this
 * @var string $heading
 */
?>
<dl class="ocal_list">
  <dt><?=$this->esc($heading)?></dt>
<?php if (empty($monthList)):?>
  <dd class="ocal_noentry"><?=$this->text('message_no_entry')?></dd>
<?php else:?>
  <dd>
    <dl>
<?php   foreach ($monthList as $item):?>
      <dt><?=$this->esc($item->range)?></dt>
      <dd><span data-ocal_state="<?=$this->esc($item->state)?>"><?=$this->esc($item->label)?></span></dd>
<?php   endforeach?>
    </dl>
  </dd>
<?php endif?>
</dl>
