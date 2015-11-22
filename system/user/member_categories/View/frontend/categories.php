<?php foreach ($categories_set as $categories): ?>

		<div class="tbl-list-wrap">

			<div class="nestable">
				<ul class="tbl-list">
					<?php foreach ($categories->children() as $category): ?>
						<?php $this->embed('frontend/_category', array('category' => $category)); ?>
					<?php endforeach ?>
				</ul>
			</div>
		</div>

<?php endforeach ?>