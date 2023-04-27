<div class="panel box">
<h1 class="panel-heading"><?=lang('assign_to_members')?></h1>

<?=form_open($base_url, ' class="panel-body settings"', (isset($form_hidden)) ? $form_hidden : array())?>


<?=ee('CP/Alert')->get('member_categories')?>
    
    <fieldset class="col-group">
		<div class="setting-txt col w-8">
        <?php foreach ($members as $member): ?>
            <h3><?=$member->username?></h3>
			<em><?=$member->email?></em>
            <em><?=version_compare(APP_VER, '6.0', '>=') ? $member->PrimaryRole->name : $member->MemberGroup->group_title?></em>
            <?=form_hidden('member_id[]', $member->member_id)?>
            <br />
        <?php endforeach ?>
		</div>
		<div class="setting-field col w-8 last">
    
    <div class="col w-16 relate-wrap">

<?php foreach ($categories_set as $i => $categories): ?>

		<div class="tbl-list-wrap">

			<div class="nestable">
				<ul class="tbl-list">
					<?php foreach ($categories->children() as $category): ?>
						<?php $this->embed('_category', array('category' => $category)); ?>
					<?php endforeach ?>
					<?php if (count($categories->children()) == 0): ?>
						<li>
							<div class="tbl-row no-results">
								<div class="none">
									<p><?=lang('categories_not_found')?> <a class="btn action" href="<?=ee('CP/URL')->make('channels/cat/create-cat/'.$cat_group_id[$i])?>"><?=lang('create_category_btn')?></a></p>
								</div>
							</div>
						</li>
					<?php endif ?>
				</ul>
			</div>
		</div>

<?php endforeach ?>

</div>
        
        </div>
     </fieldset>
     <fieldset class="form-ctrls ">   
<?=cp_form_submit($save_btn_text, $save_btn_text_working, 'submit', (isset($errors) && $errors->isNotValid()))?>
    
	</fieldset>

</form>
</div>
