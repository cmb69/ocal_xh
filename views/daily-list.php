<dl class="ocal_list">
    <dt><?=$this->heading?></dt>
<?php if (!empty($this->monthList)):?>
    <dd>
        <dl>
<?php   foreach ($this->monthList as $item):?>
            <dt><?=$this->escape($item->range)?></dt>
            <dd><span data-ocal_state="<?=$this->escape($item->state)?>"><?=$this->escape($item->label)?></span></dd>
<?php   endforeach?>
        </dl>
    </dd>
<?php else:?>
    <dd class="ocal_noentry"><?=$this->text('message_no_entry')?></dd>
<?php endif?>
</dl>
