<dl class="ocal_list">
<?php if (empty($this->weekList)):?>
  <dt><?=$this->from()?>–<?=$this->to()?></dt>
  <dd class="ocal_noentry"><?=$this->text('message_no_entry')?></dd>
<?php else:?>
<?php   foreach ($this->weekList as $day):?>
  <dt><?=$this->escape($day->label)?></dt>
  <dd>
    <dl>
<?php       foreach ($day->list as $item):?>
      <dt><?=$this->escape($item->range)?></dt>
      <dd><span data-ocal_state="<?=$this->escape($item->state)?>"><?=$this->escape($item->label)?></span></dd>
<?php       endforeach?>
    </dl>
  </dd>
<?php   endforeach?>
<?php endif?>
</dl>
