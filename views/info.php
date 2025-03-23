<?php

use Plib\View;

/**
 * @var View $this
 * @var string $version
 * @var list<stdClass> $checks
 */
?>

<h1>Ocal <?=$this->esc($version)?></h1>
<h2><?=$this->text('syscheck_title')?></h2>
<ul class="ocal_syscheck">
<?foreach ($checks as $check):?>
  <li class="xh_<?=$this->esc($check->state)?>"><?=$this->text('syscheck_message', $check->label, $check->stateLabel)?></li>
<?endforeach?>
</ul>
