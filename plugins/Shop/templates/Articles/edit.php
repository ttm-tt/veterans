<?php
	$this->Html->scriptStart(array('block' => true));
?>
// IDX in the template is a placeholder to be replace with the actual value
var variantTemplate =
'<?php 
echo htmlspecialchars_decode('<fieldset class="fieldset">');

echo htmlspecialchars_decode($this->Form->control('article_variants.IDX.name', array(
		'label' => __('Name'),
	)));
echo htmlspecialchars_decode($this->Form->control('article_variants.IDX.description', array(
		'label' => __('Description'),
		'type' => 'translate'
	)));
echo htmlspecialchars_decode($this->Form->control('article_variants.IDX.variant_type', array(
		'label' => __('Category')
	)));
echo htmlspecialchars_decode($this->Form->control('article_variants.IDX.visible', array(
		'label' => __('Visible'),
		'type' => 'chekbox'
	)));
echo htmlspecialchars_decode($this->Form->control('article_variants.IDX.sort_order', array(
		'label' => __('Sort Order')
	)));
echo htmlspecialchars_decode($this->Form->control('article_variants.IDX.price', array(
		'label' => __('Price')
	)));
echo htmlspecialchars_decode('</fieldset>');
?>';	

function addVariant() {
	// Next index is the current number of <fieldset> items in the variants <div>
	// We start counting with 0
	var idx = $('#article-variants div fieldset').length;
	// replace 'IDX' in template with actual index and add it after the last <fieldset> element
	$(variantTemplate.replace(/IDX/g, idx)).insertAfter($('#article-variants div fieldset').last());
}

<?php
	$this->Html->scriptEnd();
?>

<div class="articles form">
<?php echo $this->Form->create($article, ['type' => 'file']);?>
	<fieldset class="has-tabs">
 		<legend><?php echo __('Edit Article'); ?></legend>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('tournament_id', array('type' => 'hidden'));
	?>
	
	<ul class="tabs" data-tabs id="edit-article">
		<li class="tabs-title is-active">
			<a href="#article-general" aria-selected="true"><?= __('General') ?></a>
		</li>
		<li class="tabs-title">
			<a href="#article-presentation" aria-selected="true"><?= __('Presentation') ?></a>
		</li>
		<li class="tabs-title">
			<a href="#article-cost" aria-selected="true"><?= __('Cost') ?></a>
		</li>
		<li class="tabs-title">
			<a href="#article-available" aria-selected="true"><?= __('Available') ?></a>
		</li>
		<li class="tabs-title">
			<a href="#article-variants" aria-selected="true"><?= __('Variants') ?></a>
		</li>
	</ul>
		
	<div class="tabs-content" data-tabs-content="edit-article">
		<div class="tabs-panel is-active" id="article-general">
			<div>
				<?php
					echo $this->Form->control('name');
					echo $this->Form->control('description', ['type' => 'translate']);
					echo $this->Form->control('visible', array('type' => 'checkbox'));
					echo $this->Form->control('sort_order',array('label' =>  __('Sort Order')));
				?>
			</div>
		</div>
		
		<div class="tabs-panel" id="article-presentation">
			<div>
				<?php
					echo $this->Form->control('article_description', ['label' => __('Text'), 'type' => 'translate']);
					echo $this->Form->control('photo_upload', ['label' => __('Image'), 'type' => 'file']);
					echo $this->Form->control('photo_remove', ['label' => __('Remove Photo'), 'type' => 'checkbox']);
					echo $this->Form->control('article_url', ['label' => __('Url')]);
				?>
			</div>
		</div>
		
		<div class="tabs-panel" id="article-cost">
			<div>
				<?php
					echo $this->Form->control('price', array('after' => '&nbsp;' . $shopSettings['currency']));
					echo $this->Form->control('tax', array('after' => '&nbsp;' . '%'));
				?>
			</div>
		</div>
		
		<div class="tabs-panel" id="article-available">
			<div>
				<?php
					echo $this->Form->control('available');
					echo $this->Form->control('available_from', array(
						'type' => 'date',
						'empty' => [
							'year' => __('Year'), 
							'month' => __('Month'), 
							'day' => __('Day')
						],
						'label' => __('Available From')			
					));
					echo $this->Form->control('available_until', array(
						'type' => 'date',
						'empty' => [
							'year' => __('Year'), 
							'month' => __('Month'), 
							'day' => __('Day')
						],
						'label' => __('Available Until')			
					));

					// Limit size of waiting list
					echo $this->Form->control('waitinglist_limit_enabled', array(
						'type' => 'checkbox',
						'label' => __('Waiting List Enabled')
					));
					echo $this->Form->control('waitinglist_limit_max', array(
						'label' => __('Waiting List Limit')
					));
				?>
			</div>	
		</div>
		
		<div class="tabs-panel" id="article-variants">
			<div class="input">
				<?php 
					foreach ($article->article_variants as $k => $variant) {
						echo '<fieldset class="fieldset">';

						echo $this->Form->control('article_variants.' . $k . '.id', array(
							'value' => $variant['id'],
							'type' => 'hidden'
						));

						echo $this->Form->control('article_variants.' . $k . '.name', array(
								'label' => __('Name'),
								'value' => $variant['name']
							));

						echo $this->Form->control('article_variants.' . $k . '.description', array(
								'label' => __('Description'),
								'value' => $variant['description'],
								'type' => 'translate'
							));

						echo $this->Form->control('article_variants.' . $k . '.variant_type', array(
								'label' => __('Category'),
							));

						echo $this->Form->control('article_variants.' . $k . '.visible', array(
								'label' => __('Visible'),
								'type' => 'checkbox'
							));
						
						echo $this->Form->control('article_variants.' . $k . '.available', array(
								'label' => __('Available')
							));
						
						echo $this->Form->control('article_variants.' . $k . '.available_from', array(
							'type' => 'date',
							'empty' => [
								'year' => __('Year'), 
								'month' => __('Month'), 
								'day' => __('Day')
							],
							'label' => __('Available From')			
						));
						echo $this->Form->control('article_variants.' . $k . '.available_until', array(
							'type' => 'date',
							'empty' => [
								'year' => __('Year'), 
								'month' => __('Month'), 
								'day' => __('Day')
							],
							'label' => __('Available Until')			
						));
						
						echo $this->Form->control('article_variants.' . $k . '.sort_order', array(
								'label' => __('Sort Order'),
								'value' => $variant['sort_order']
							));

						echo $this->Form->control('article_variants.' . $k . '.price', array(
								'label' => __('Price'),
								'value' => $variant['price']
							));

						echo '</fieldset>';
					}

					$count = count($article->article_variants);
					for ($k = $count; $k < $count + 1; $k++) {
						echo '<fieldset class="fieldset">';

						echo $this->Form->control('article_variants.' . $k . '.name', array(
								'label' => __('Name'),
							));
						echo $this->Form->control('article_variants.' . $k . '.description', array(
								'label' => __('Description'),
								'type' => 'translate'
							));
						echo $this->Form->control('article_variants.' . $k . '.variant_type', array(
								'label' => __('Category')
							));
						echo $this->Form->control('article_variants.' . $k . '.visible', array(
								'label' => __('Visible'),
								'type' => 'checkbox',
								'checked' => true
							));
						echo $this->Form->control('article_variants.' . $k . '.sort_order', array(
								'label' => __('Sort Order')
							));
						echo $this->Form->control('article_variants.' . $k . '.price', array(
								'label' => __('Price')
							));
						echo '</fieldset>';
					}
				?>	
				<a href='#' onclick='addVariant(); return false;'><?=__('Add Variant')?></a>
			</div>
		</div>
	</div>
	<?php 
		echo $this->element('savecancel');
		echo $this->Form->end();
	?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Articles'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
