<h1>Ocal</h1>
<img src="<?=$this->logo()?>" class="ocal_logo" alt="<?=$this->text('alt_logo')?>">
<p>
    Version: <?=$this->version()?>
</p>
<p>
    Copyright &copy; 2014-2017 <a href="http://3-magi.net/"
    target="_blank">Christoph M. Becker</a>
</p>
<p class="ocal_license">
    This program is free software: you can redistribute it and/or modify it
    under the terms of the GNU General Public License as published by the Free
    Software Foundation, either version 3 of the License, or (at your option)
    any later version.
</p>
<p class="ocal_license">
    This program is distributed in the hope that it will be useful, but
    <em>without any warranty</em>; without even the implied warranty of
    <em>merchantability</em> or <em>fitness for a particular purpose</em>. See
    the GNU General Public License for more details.
</p>
<p class="ocal_license">
    You should have received a copy of the GNU General Public License along with
    this program. If not, see <a href="http://www.gnu.org/licenses/"
    target="_blank">http://www.gnu.org/licenses/</a>.
</p>
<h2><?=$this->text('syscheck_title')?></h2>
<ul class="ocal_syscheck">
<?php foreach ($this->checks as $check):?>
    <li class="xh_<?=$this->escape($check->state)?>"><?=$this->text('syscheck_message', $check->label, $check->stateLabel)?></li>
<?php endforeach?>
</ul>
