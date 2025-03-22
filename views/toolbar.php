<?php

use Plib\View;

/**
 * @var View $this
 * @var list<int> $states
 */
?>

<div class="ocal_toolbar">
<?php foreach ($states as $state):?>
  <span class="ocal_state" data-ocal_state="<?=$state?>" title="<?=$this->text("label_state_$state")?>"></span>
<?php endforeach?>
  <button type="button" class="ocal_save" disabled="disabled"><?=$this->text('label_save')?></button>
</div>
