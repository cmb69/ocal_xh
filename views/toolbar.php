<?php

use Plib\View;

if (!defined("CMSIMPLE_XH_VERSION")) {http_response_code(403); exit;}

/**
 * @var View $this
 * @var list<int> $states
 */
?>

<div class="ocal_toolbar">
<?foreach ($states as $state):?>
  <span class="ocal_state" data-ocal_state="<?=$state?>" title="<?=$this->text("label_state_$state")?>"></span>
<?endforeach?>
  <button type="button" class="ocal_save" disabled="disabled"><?=$this->text('label_save')?></button>
</div>
