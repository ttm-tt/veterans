<div class="articles view">
<h2><?php echo __('Article');?></h2>
	<?php $i = 0; $class = ' class="altrow"';?>
	<dl style="width:60%;">
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Name'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['name']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Description'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['description']; ?>
			&nbsp;
		</dd>
		<?php 
			foreach ($article->_translations as $lang) {
				echo '<dt class="detail">' . $lang['locale'] . '</dt>';
				echo '<dd>' . ($article->_translations[$lang['locale']]['description'] ?? '&nbsp;') . '</dd>';
			}
		?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Text'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['article_description']; ?>
			&nbsp;
		</dd>
		<?php 
			foreach ($article->_translations as $lang) {
				echo '<dt class="detail">' . $lang['locale'] . '</dt>';
				echo '<dd>' . ($article->_translations[$lang['locale']]['article_description'] ?? '&nbsp;') . '</dd>';
			}
		?>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Price'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['price'] . '&nbsp;' . $shopSettings['currency'] ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Tax'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['tax'] . '&nbsp;%' ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Available'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['available']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Available From'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['available_from']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Available Until'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['available_until']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Limit Waiting List'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['waitinglist_limit_enabled'] ? $article['waitinglist_limit_max'] : ''; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Visible'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['visible']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Sort Order'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['sort_order']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Updated At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['modified']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Created At'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php echo $article['created']; ?>
			&nbsp;
		</dd>
		<dt<?php if ($i % 2 == 0) echo $class;?>><?php echo __('Photo'); ?></dt>
		<dd<?php if ($i++ % 2 == 0) echo $class;?>>
			<?php 
				echo $this->Html->image(
					$article->article_image === null ?
					'comingsoon.png' :
					'data:image/png;base64,' . base64_encode(stream_get_contents($article->article_image)),
					['width' => '100%']
				);
			?>
			&nbsp;
		</dd>
	</dl>
<?php if (count($article['article_variants']) > 0) { ?>
	<p></p>
	<p></p>
	<h2><?php echo __('Variants');?></h2>
	<table style="width:60%;">
		<tr>
			<th><?php echo __('Name');?></th>
			<th><?php echo __('Description');?></th>
			<th><?php echo __('Sort Order');?></th>
			<th><?php echo __('Price') . '&nbsp;(' . $shopSettings['currency'] . ')';?></th>
		</tr>
	<?php 
		foreach ($article['article_variants'] as $variant) {
			echo '<tr>';
			echo '<td>' . $variant['name'] . '</td>';
			echo '<td>' . $variant['description'] . '</td>';
			echo '<td>' . $variant['sort_order'] . '</td>';
			echo '<td>' . $variant['price'] . '</td>';
			echo '</tr>';
		}
	?>
	</table>
	
<?php } ?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Article'), array('action' => 'edit', $article['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('Delete Article'), array('action' => 'delete', $article['id']), ['confirm' => sprintf(__('Are you sure you want to delete %s?'), $article['description'])]); ?> </li>
		<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Article'), array('action' => 'add')); ?> </li>
	</ul>
<?php $this->end(); ?>
