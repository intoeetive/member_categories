<li class="tbl-list-item" data-id="<?=$category->data->cat_id?>">
	<div class="tbl-row">

		<div class="txt">
			<div class="main">
				<b><?=$category->data->cat_name?></b>
			</div>
			<div class="secondary">
				<span class="faded">ID#</span> <?=$category->data->cat_id?> <span class="faded">/</span> <?=$category->data->cat_url_title?>
			</div>
		</div>


		<div class="check-ctrl"><input type="checkbox" name="categories[]" value="<?=$category->data->cat_id?>"<?php if (in_array($category->data->cat_id, $selected)): ?> checked="checked"<?php endif ?>></div>

	</div>
	<?php if (count($category->children())): ?>
		<ul class="tbl-list">
			<?php foreach ($category->children() as $child): ?>
				<?php $this->embed('frontend/_category', array('category' => $child)); ?>
			<?php endforeach ?>
		</ul>
	<?php endif ?>
</li>
