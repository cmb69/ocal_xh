<div class="ocal_toolbar">
<?php foreach ($this->states as $state):?>
  <span class="ocal_state" data-ocal_state="<?=$this->esc($state)?>" title="<?=$this->text("label_state_$state")?>"></span>
<?php endforeach?>
  <button type="button" class="ocal_save" disabled="disabled"><?=$this->text('label_save')?></button>
</div>
