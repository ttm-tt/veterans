<?php
use Cake\Routing\Router;
use Cake\Utility\Hash;
?>

<?php
	$hideVariants = true;
	foreach ($articles as $article) {
		$hideVariants &= count($article->article_variants) == 0;
	}
	
	$variantClass = $hideVariants ? 'class="conceal"' : '';
?>

<?php $this->Html->scriptStart(array('block' => true)); ?>

var sold = {
	<?php foreach ($sold as $k => $v) { ?>
		<?php echo $k; ?> : <?php echo $v; ?>,
	<?php } ?>
};

function onAddItem(key) {
	var variant = null;
	if ($('tr#k' + key + ' td#variant select').length > 0) {
		variant = $('tr#k' + key + ' td#variant select').val();

		if (variant === undefined || variant === "") {
			alert('<?php echo __d('user', 'You must select a variant');?>');
			return;
		}
	}

	$.ajax({
		'type' : 'POST',
		'dataType' : 'json',
		'url' : '<?php echo Router::url(array('action' => 'onAddItem'));?>',
		'data' : {'key' : key, 'variant' : variant},
		'success' : function(items) {
			updateTable(items);
		}
	});
}

function onChangeQuantity(key, quantity) {
	$.ajax({
		'type' : 'POST',
		'dataType' : 'json',
		'url'  : '<?php echo Router::url(array('action' => 'onChangeQuantity')); ?>',
		'data' : {'key' : key, 'quantity' : quantity},
		'success' : function(items) {
			updateTable(items);
		}
	});

}

function onChangeVariant(key) {
	// TODO
}


function onRemoveItem(key) {
	$.ajax({
		'type' : 'POST',
		'dataType' : 'json',
		'url' : '<?php echo Router::url(array('action' => 'onRemoveItem')); ?>',
		'data' : {'key' : key},
		'success' : function(items) {
			updateTable(items);
		}
	});
}


function updateTable(items) {
	var changed = false;
	var total = 0;
	var i = 0;
	for (var idx in items) { 
		var item = items[idx];

		if ( !item['visible'] || item['visible'] === "0" )
			continue;

		total += item['total'];

		if ($('#items tbody tr#k' + item['key']).length == 0) {
			var row = '<tr id="k' + item['key'] + '"></tr>';

			$('#items tbody').append(row)

			changed = true;
		}

		var tr = $('#items tbody tr#k' + item['key']);

		row = '';
		row += '<td id="description">' + item['description'] + '</td>';
		if (item['article_variants'] !== undefined && item['article_variants'][0] !== undefined)
			row += '<td id="variant" <?= $variantClass ?> >' + item['article_variants'][0]['description'] + '</td>';
		else
			row += '<td id="variant" <?= $variantClass ?> ></td>';
		row += '<td id="price" class="currency">' + item['price'] + ' ' + "<?php echo $shopSettings['currency'];?>" + '</td>';
		row += '<td id="quantity">';
		row +=
			<?php
				$select = $this->Form->select('quantity', array_combine(range(1, max(1, count($people))), range(1, max(1, count($people)))), array(
					'empty' => false, 
					'style' => 'width: 6ex;',
				));
				echo "'" . implode('', explode("\n", $select)) . "'";
			?>
			;

		row += '</td>';

		row += '<td id="total" class="currency">' + item['total'] + ' ' + "<?php echo $shopSettings['currency'];?>" + '</td>';

		row += '<td class="actions">';
		row += '<?php echo $this->Html->link(__d('user', 'Remove'), '#', array()); ?>';
		row += '</td>';

		tr.html(row);

		// Need anonymous function here so that the function does not capture the latest value of item
		(function(key, quantity) {
			var cbQuantity = $('#items tbody tr#k' + key + ' #quantity select');
			while (cbQuantity.find('option').length < quantity) {
				var val = cbQuantity.find('option').length + 1;
				cbQuantity.prepend('<option value="' + val + '">' + val + '</option>');
			}
			cbQuantity.val(quantity);
			cbQuantity.attr('onchange', 'onChangeQuantity(' + key + ', $(this).val()); return false;');
			

			var linkRemove = $('#items tbody tr#k' + key + ' td.actions a');
			linkRemove.attr('onclick', 'onRemoveItem(' + key + '); return false;');
		})(item['key'], item['quantity']);
	}

	row = '';
	row += '<td id="total" colspan="4">' + '<?php echo __d('user', 'Total');?>' + '</td>';
	row += '<td id="variant" class="hide"></td>';
	row += '<td id="total" class="currency">' + total + ' ' + "<?php echo $shopSettings['currency'];?>" + '</td>';
	row += '<td class="actions"></td>';

	row += '</td>'; 

	$('#items tr#rowtotal').html(row);

	$('#items tbody tr').each(function(i, tr) {
		var id = $(tr).attr('id');

		if (items[id.substr(1)] != undefined)
			return true;

		$('#items tbody tr#' + id).remove();
		changed = true;
	});
}

<?php $this->Html->scriptEnd(); ?>

<div class="order register form">
	<?php echo $this->Wizard->create(null);?>
	<?php echo $this->element('shop_header'); ?>
	<h2><?php echo __d('user', 'Buy Additional Articles');?></h2>
<?php if (count($articles)) { ?>	
	<div class="hint cell">
	<?php
		echo '<p>';
		echo __d('user', 'Here you may buy additional items, e.g. tickets for the gala dinner.');
		echo '</p>';
	?>
	</div>
	<div id="stock">
		<h3><?php echo __d('user', 'Available Articles');?></h3>
		<div class="grid-container show-for-medium">
			<div class="grid-x grid-padding-x small-up-2 medium-up-3" data-equalizer data-equalizer-by-row="true">
				<?php 
					foreach ($articles as $article) { 
						if (empty($article->article_description))
							continue;
				?>
				<div class="cell">
					<div class="card" data-equalizer-watch>
						<div class="card-divider">
							<h5><?= $article->description; ?></h5>
						</div>
						<a href="<?= $article->article_url ?: '#'; ?>" target="_blank">
						<?= $this->Html->image(
								$article->article_image === null ?
								'comingsoon.png' :
								['plugin' => 'Shop', 'controller' => 'Shops', 'action' => 'article_image', $article->id],
								['width' => '100%']
							); ?>
						</a>
						<div class="card-section">
							<?= $article->article_description; ?>
						</div>
						<?=
							$this->Html->link(__d('user', 'Add'), '#', array(
								'class' => 'button',
								'onclick' => 'onAddItem(' . $article['id'] . '); return false;'
							));
						?>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
		<table id="articles" class="ttm-table">
		<thead>
			<tr>
				<th><?php echo __d('user', 'Article');?></th>
				<th <?= $variantClass ?>><?php echo __d('user', 'Variant');?></th>
				<th class="hide-for-medium"><?= __d('user', 'Description'); ?></th>
				<th class="hide-for-medium"><?= __d('user', 'Photo'); ?></th>
				<th class="currency"><?php echo __d('user', 'Price');?></th>
				<th class="actions"><?php echo __d('user', 'Actions');?></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$i = 0;
				foreach ($articles as $article) {
					if (!$article['visible'])
						continue;
			?>
			<?php /* id prefix 'k' is by intention, add-/removeItem rely on it, or? */ ?>
			<tr id="k<?php echo $article['id']?>">
				<td id="description"><?php echo $article['description'];?></td>
				<td id="variant" <?= $variantClass ?> ><?php
					if (!empty($article['article_variants'])) {
						$variants = Hash::combine($article->article_variants, '{n}.id', '{n}.description', '{n}.variant_type');
						foreach ($variants as $label => $options) {
							echo $this->Form->select(
								'variant', 
								$options,
								array(
									'empty' => __d('user', 'Select') . ' ' . __d('user', $label),
									'onchange' => 'onChangeVariant(' . $article['id'] . '); return false'
								)
							);
						}
					}
					?>
				</td>
				<td id="description" class="hide-for-medium"><?= $article->article_description ?: '&nbsp;';?></td>
				<td id="photo" class="hide-for-medium">
					<?= $article->article_url === null ? 
						'&nbsp;' : $this->Html->image(['plugin' => 'Shop', 'controller' => 'Shops', 'action' => 'article_image', $article->id]); 
					?>
				</td>
				<td id="price" class="currency"><?php echo $article['price'] . ' ' . $shopSettings['currency'];?></td>
				<td class="actions">
					<?php
						$aid = $article['id'];
						$ava = $article['available'];
						if ($ava > 0 && !empty($sold[$aid]) && $ava <= $sold[$aid])
							echo __d('user', 'Sold out');
						else
							echo $this->Html->link(__d('user', 'Add'), '#', array(
								'onclick' => 'onAddItem(' . $article['id'] . '); return false;'
							));
					?>
				</td>
			</tr>
			<?php 
				}
			?>
		</tbody>
		</table>
	</div>
	<br>
	<div id="cart">
		<h3><?php echo __d('user', 'Shopping Cart');?></h3>
		<table id="items" class="ttm-table vertical-align-middle">
		<thead>
			<tr>
				<th><?php echo __d('user', 'Article');?></th>
				<th <?= $variantClass ?>><?php echo __d('user', 'Variant');?></th>
				<th class="currency"><?php echo __d('user', 'Price');?></th>
				<th><?php echo __d('user', 'Quantity');?></th>
				<th class="currency"><?php echo __d('user', 'Total');?></th>
				<th class="actions"><?php echo __d('user', 'Actions');?></th>
			</tr>
		</thead>
		<tbody>
			<?php 
				$total = 0;
				$i = 0;
				foreach ($items as $item) {
					if (!$item['visible'])
						continue;

					$total += $item['total'];

					echo '<tr id="k' . $item['key'] . '">';
						echo '<td id="description">' . $item['description'] . '</td>';
						if (!empty($item['article_variants']))
							echo '<td id="variant"' . $variantClass . '>' . $item['article_variants'][0]['description'] . '</td>';
						else
							echo '<td id="variant" ' . $variantClass . '></td>';
						echo '<td id="price" class="currency">' . $item['price'] . ' ' . $shopSettings['currency'] . '</td>';
						if ($item['visible']) {
							$count = max(count($people), $item['quantity']);
							echo '<td id="quantity">' . $this->Form->select(
								'quantity', array_combine(range(1, $count), range(1, $count)), array(
									'empty' => false, 
									'value' => $item['quantity'], 
									'style' => 'width: 6ex;',
									'onchange' => 'onChangeQuantity(String(' . $item['key'] . '), $(this).val());'
							)) . '</td>';
						} else {
							echo '<td id="quantity">' . $item['quantity'] . '</td>';
						} 
						echo '<td id="total" class="currency">' . $item['total'] . ' ' . $shopSettings['currency'] . '</td>';
						echo '<td class="actions">';
							echo $this->Html->link(__d('user', 'Remove'), '#', array(
								'onclick' => 'onRemoveItem("' . $item['key'] . '"); return false;'
							));
						echo '</td>';
					echo '</tr>';
				}
			?>
		</tbody>
		<tfoot>
			<?php

				echo '<tr id="rowtotal" class="total currency">';
				echo '<td colspan="4">' . __d('user', 'Total') . '</td>';
				echo '<td id="total" class="currency">' . $total . ' ' . $shopSettings['currency'] . '</td>';
				echo '<td class="actions"></td>';
				echo '</tr>';
			?>
		</tfoot>
		</table>
	</div>
<?php } else { ?>	
	<div class="hint cell">
	<?php
		echo '<p>';
		echo __d('user', 'Please visit our shop');
		echo '</p>';
	?>
	</div>	
<?php } ?>
<?php echo $this->element('shop_footer'); ?>
<?php echo $this->Form->end(); ?>
</div>
