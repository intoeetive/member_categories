<?php
echo ee('CP/Alert')->get('member_categories');
echo form_open($filter_url);
echo $filters;
echo form_close();
echo form_open($edit_url);
$this->embed('ee:_shared/table', $table); 
?>

<?php if ( ! empty($table['columns']) && ! empty($table['data'])): ?>
<fieldset class="tbl-bulk-act hidden">
	<select name="bulk_action">
		<option value="">-- <?=lang('with_selected')?> --</option>
		<option value="remove" data-confirm-trigger="selected" rel="modal-confirm-remove"><?=lang('assign')?></option>
	</select>
	<input class="btn submit" data-conditional-modal="confirm-trigger" type="submit" value="<?=lang('submit')?>">
</fieldset>
<?php endif; ?>
</form>
<?=$pagination?>