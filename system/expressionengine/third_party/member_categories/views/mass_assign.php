<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=member_categories'.AMP.'method=do_mass_assign', array('id'=>'member_categories_mass_assign_form'));?>

<ul class="tab_menu" id="tab_menu_tabs">
<li class="content_tab "> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=member_categories'.AMP.'method=index'?>"><?=lang('members')?></a>  </li> 
<li class="content_tab current"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=member_categories'.AMP.'method=mass_assign'?>"><?=lang('mass_assign')?></a>  </li> 
<li class="content_tab"> <a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=member_categories'.AMP.'method=settings'?>"><?=lang('settings')?></a>  </li> 
</ul> 
<div class="clear_left shun"></div> 

<h2><?=lang('select_members')?></h2>

<?=$fields['members']?>

<h2><?=lang('select_categories')?></h2>

<?=$fields['categories']?>

<p><?=form_submit('submit', lang('submit'), 'class="submit"')?></p>

<?php
form_close();

