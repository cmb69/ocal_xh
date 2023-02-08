<dl class="ocal_list">
  <dt><?=$this->heading()?></dt>
<?php if (empty($this->monthList)):?>
  <dd class="ocal_noentry"><?=$this->text('message_no_entry')?></dd>
<?php else:?>
  <dd>
    <dl>
<?php   foreach ($this->monthList as $item):?>
      <dt><?=$this->esc($item->range)?></dt>
      <dd><span data-ocal_state="<?=$this->esc($item->state)?>"><?=$this->esc($item->label)?></span></dd>
<?php   endforeach?>
    </dl>
  </dd>
<?php endif?>
</dl>
