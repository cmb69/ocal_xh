<?php

use Plib\View;

/**
 * @var View $this
 * @var string $from
 * @var string $to
 */
?>

<dl class="ocal_list">
<?php if (empty($weekList)):?>
  <dt><?=$this->esc($from)?>–<?=$this->esc($to)?></dt>
  <dd class="ocal_noentry"><?=$this->text('message_no_entry')?></dd>
<?php else:?>
<?php   foreach ($weekList as $day):?>
  <dt><?=$this->esc($day->label)?></dt>
  <dd>
    <dl>
<?php       foreach ($day->list as $item):?>
      <dt><?=$this->esc($item->range)?></dt>
      <dd><span data-ocal_state="<?=$this->esc($item->state)?>"><?=$this->esc($item->label)?></span></dd>
<?php       endforeach?>
    </dl>
  </dd>
<?php   endforeach?>
<?php endif?>
</dl>
