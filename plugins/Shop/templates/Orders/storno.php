<?php
use Shop\Model\Table\OrderStatusTable;

use Cake\Core\Configure;
?>

<?php 
	function allowRefund() {
		if (Configure::check('Shop.allowRefund'))
			return Configure::read('Shop.allowRefund');
		
		$paymentMethod = $order['payment_method'];

		if ($paymentMethod === null)
			return false;
		
		if (Configure::check('Shop.' . $paymentMethod . '.allowRefund'))
			return Configure::read('Shop.' . $paymentMethod . '.allowRefund');
		
		return false;
	}
?>

<?php
	$isPaid = $order['order_status_id'] == OrderStatusTable::getPaidId();
	$datePaid = $isPaid ? $order['invoice_paid'] : null;
	$mayMail = in_array($order['order_status_id'], [
		OrderStatusTable::getPaidId(),
		OrderStatusTable::getPendingId(),
		OrderStatusTable::getWaitingListId()
	]);
	
	$refund = 0;
	$fee = 0;
	$discount = 0;
	
	$playerArticles = array();
	$accArticles = array();
	$linkedArticles = array();
	$unlinkedArticles = array();

	foreach ($order['order_articles'] as $oa) {
		if ($isPaid && empty($oa['cancelled'])) {
			$refund += $oa['total'];
			$fee += $shopSettings['cancellation_fee'] * $oa['total'] / 100.;
		}

		if ( !empty($oa['detail']) ) {
			$detail = unserialize($oa['detail']);
			
			if (!empty($detail))
				$oa['sortkey'] = $detail['last_name'] . ', ' . $detail['first_name'];
		}

		if ($articles[$oa->article_id]['name'] == 'PLA') {
			$playerArticles[] = $oa;
		} else if ($articles[$oa->article_id]['name'] == 'ACC') {
			$accArticles[] = $oa;
		} else if ($articles[$oa->article_id]['visible'] > 0 && !empty($oa['person_id'])) {
			if (empty($linkedArticles[$oa['person_id']]))
				$linkedArticles[$oa['person_id']] = array();
			
			$linkedArticles[$oa['person_id']][] = $oa;
		} else {
			$unlinkedArticles[] = $oa;
		}
	}
	
	uasort($playerArticles, function($a, $b) {
		if (empty($a['sortkey']))
			return (empty($b['sortkey']) ? 0 : -1);
		
		if (empty($b['sortkey']))
			return +1;
		
		return strcmp($a['sortkey'], $b['sortkey']);
	});
		
	uasort($accArticles, function($a, $b) {
		if (empty($a['sortkey']))
			return (empty($b['sortkey']) ? 0 : -1);
		
		if (empty($b['sortkey']))
			return +1;
		
		return strcmp($a['sortkey'], $b['sortkey']);
	});
?>
<?php
	$this->Html->scriptStart(array('block' => true));
?>

var isPaid = <?php echo $isPaid ? 'true' : 'false';?>;
var refund = <?php echo $refund;?>;
var fee = <?php echo $fee;?>;
var discount = <?php echo $discount;?>;

function parseFloatOrEmpty(val) {
	return isNaN(parseFloat(val)) ? 0. : parseFloat(val);
}

function onCancelItem(tr) {	
	var td = tr.find('td#quantity select');
	var quantity = td.find('option:last-child').val();
	
	if (tr.find('td#storno input').is(':checked')) {
		td.prop('disabled', false);
	} else {
		td.val(quantity);
		td.prop('disabled', true);
	}
	
	updatePartial();
}

function updateAll() {
	refund = <?php echo $refund;?>;
	fee = <?php echo $fee;?>;

	discount = parseFloatOrEmpty($('table#calculation td#discount input').val());
	if (isNaN(discount))
		discount = 0.;
		
	var total = refund - fee + discount;
	
	$('table#calculation td#refund').html(refund);
	$('table#calculation td#fee').html(fee);

	$('table#calculation td#total').html(total);
}


function updatePartial() {
	refund = 0.;
	fee = 0.;

	discount = parseFloatOrEmpty($('table#calculation td#discount input').val());
	if (isNaN(discount))
		discount = 0.;

	$('table#players tbody tr').each(function() {
		if ($(this).find('td').length == 0)
			return;
			
		if (!isPaid)
			return;

		if ($(this).find('td#storno input[type="checkbox"]').is(':checked')) {
			refund += parseFloatOrEmpty($(this).find('td#price').html());
			fee += parseFloatOrEmpty($(this).find('td#fee').html());
		}
	});

	$('table#accompanying tbody tr').each(function(idx, row) {
		if ($(this).find('td').length == 0)
			return;
			
		if (!isPaid)
			return;

		if ($(this).find('td#storno input[type="checkbox"]').is(':checked')) {
			refund += parseFloatOrEmpty($(this).find('td#price').html());
			fee += parseFloatOrEmpty($(this).find('td#fee').html());
		}
	});

	$('table#items tbody tr').each(function(idx, row) {
		if ($(this).find('td').length == 0)
			return;
			
		if (!isPaid)
			return;

		if ($(this).find('td#storno input[type="checkbox"]').is(':checked')) {
			var quantity = 0;
			
			if ( $(this).find('td#quantity select').length > 0 )
				quantity = parseInt($(this).find('td#quantity select').val());
			else
				quantity = parseInt($(this).find('td#quantity input').val());
			refund += parseFloatOrEmpty($(this).find('td#price').html()) * quantity;
			fee += parseFloatOrEmpty($(this).find('td#fee').html()) * quantity;
		}
	});
	
	$('table#calculation td#refund').html(refund);
	$('table#calculation td#fee').html(fee);

	var total = refund - fee + discount;
	$('table#calculation td#total').html(total);
}


function cancelAll() {
	$('div#partial').hide();

	updateAll();
}


function cancelPartial() {
	$('div#partial').show();
	refund = 0.;
	fee = 0.;
	discount = 0.;

	updatePartial();
}


$(document).ready(function() {
	// Hide refund calculation if not yet paid
	if (!isPaid) {
		$('div#refund').hide();
		$('div#partial table .price').hide();
		$('div#partial table .fee').hide();
	}
		
	$('input#cancelAll').is(':checked', true);
	cancelAll();
});

<?php
	$this->Html->scriptEnd();
?>

<div class="order form">
<?php echo $this->Form->create(null);?>
	<fieldset>
		<legend><?php echo sprintf(__('Storno Order %s'), $order['invoice']);?></legend>
		<?php
			echo $this->Form->control('id', array('type' => 'hidden', 'value' => $order['id']));
		?>

		<div id="sendmail">
			<?php 
				echo $this->Form->control('Storno.sendMail', array(
					'type' => 'checkbox',
					'checked' => $mayMail,
					'label' => __('Send Mail'),	
					'disabled' => !$mayMail
				));
			?>
		</div>

		<?php
			$refundAllowed = !empty($orderDetails) && allowRefund();
			if ($refundAllowed) {
				echo '<div id="refund">';
				echo $this->Form->control('Storno.refund', array(
					'type' => 'checkbox',
					'label' => __('Automatic Refund')
				));			
				echo '</div>';
			} else {
				echo $this->Form->control('Storno.refund', array(
					'type' => 'hidden',
					'value' => false
				));
			}
		?>
		<div id="stornotype">
			<div class="grid-x grid-margin-x input radio">
				<?php
					echo $this->Form->radio( 'Storno.all', array(1 => __('Entire Order')), array(
						'onChange' => 'window.cancelAll(); return false;',
						'hiddenField' => false,
						'checked' => 'checked',
						'legend' => false,
						'style' => 'float: inherit'
					));
				?>
			</div>
			<div class="grid-x grid-margin-x input radio">
				<?php
					echo $this->Form->radio( 'Storno.all', array(0 => __('Part of Order')), array(
						'onChange' => 'window.cancelPartial(); return false;',
						'hiddenField' => false,
						'legend' => false,
						'style' => 'float: inherit'
					));
				?>
			</div>
		</div>
		<div id="partial" style="display:none;">
			<?php 
				if (count($playerArticles) > 0) { 
			?>
			<h3><?php echo __('Players');?></h3>
			<table id="players">
				<thead>
					<tr>
						<th><?php echo __('Storno');?></th>
						<th><?php echo __('Name');?></th>
						<th><?php echo __('Born');?></th>
						<th><?php echo __('Association');?></th>
						<th <?php if (count($linkedArticles) === 0) echo 'style="display:none;"';?> ><?php echo __('Articles');?></th>
						<th class="price"><?php echo __('Price');?></th>
						<th class="fee"><?php echo __('Fee');?></th>
						<th style="display:none;">Article ID</th>
					</tr>
				</thead>
				<tbody>
					<?php 
						$i = -1;
						foreach ($playerArticles as $pa) {
							$subTotal = $pa['price'];

							$showFee = empty($pa['cancelled']) || $isPaid && $pa['cancelled'] > $datePaid;						

							$person = unserialize($pa['detail']);
							
							$cancelled = null;
							if (!empty($pa['cancelled']))
								$cancelled = ' class="cancelled" ';

							$i++;
					?>
					<tr>
						<td id="storno">
							<?php 
								if ($isPaid && !empty($pa['cancelled'])) {
									echo $this->Form->hidden('Player.' . $i . '.storno', array('value' => true));
									echo $this->Form->checkbox('Player.' . $i . '.checked', array(
										'name' => false,
										'hiddenField' => false, 
										'onChange' => 'updatePartial(); return false;',
										'checked' => !empty($pa['cancelled']),
										'disabled' => $isPaid && !empty($pa['cancelled']) ? 'disabled' : false
									));

								} else {
									echo $this->Form->checkbox('Player.' . $i . '.storno', array(
										'hiddenField' => true, 
										'onChange' => 'updatePartial(); return false;',
										'checked' => !empty($pa['cancelled']),
										'disabled' => $isPaid && !empty($pa['cancelled']) ? 'disabled' : false
									));
								}
							?>
						</td>
						<td <?php echo $cancelled;?>><?php echo $person['last_name'] . ', ' . $person['first_name'];?></td>
						<td <?php echo $cancelled;?>><?php echo $this->formatDate($person['dob']);?></td>
						<td <?php echo $cancelled;?>><?php echo $nations[$person['nation_id']];?></td>
						<td <?php echo $cancelled;?><?php if (count($linkedArticles) === 0) echo 'style="display:none;"';?>>
							<?php
								$items = array();
								if (!empty($linkedArticles[$pa['person_id']])) {
									foreach ($linkedArticles[$pa['person_id']] as $item) {
										// Ignore items cancelled before the order was paid
										if (!empty($item['cancelled']) && (!$isPaid || $item['cancelled'] <= $datePaid))
											continue;

										$subTotal += $item['total'];
										$items[] = $item['Article']['name'];
									}
								}

								echo implode(', ', $items);
							?>
						</td>
						<td id="price" class="price">
							<?php echo $showFee ? number_format($subTotal, 2) : '';?>
						</td>
						<td id="fee" class="fee">
							<?php echo $showFee ? number_format($shopSettings['cancellation_fee'] * $subTotal / 100., 2) : '';?>
						</td>	
						<td style="display:none;">
							<?php 
								echo $this->Form->control('Player.' . $i . '.id', array(
									'type' => 'hidden',
									'value' => $pa['id']
								));
							?>
						</td>
					</tr>
					<?php
						}
					?>
				</tbody>
			</table>

			<br>

			<?php 		
				} 
			?>

			<?php 
				if (count($accArticles) > 0) { 
			?>

			<h3><?php echo __('Accompanying Persons');?></h3>
			<table id="accompanying">
				<thead>
					<tr>
						<th><?php echo __('Storno');?></th>
						<th><?php echo __('Name');?></th>
						<th><?php echo __('Association');?></th>
						<th <?php if (count($linkedArticles) === 0) echo 'style="display:none;"';?> ><?php echo __('Articles');?></th>
						<th class="price"><?php echo __('Price');?></th>
						<th class="fee"><?php echo __('Fee');?></th>
						<th style="display:none;">Article ID</th>
					</tr>
				</thead>
				<tbody>
					<?php 
						$i = -1;
						foreach ($accArticles as $aa) {
							$subTotal = $aa['price'];

							$showFee = empty($article['cancelled']) || $isPaid && $aa['cancelled'] > $datePaid;

							$person = unserialize($aa['detail']);

							$i++;
					?>
					<tr>
						<td id="storno">
							<?php
								if ($isPaid && !empty($aa['cancelled'])) {
									echo $this->Form->hidden('Accompanying.' . $i . '.storno', array('value' => true));
									echo $this->Form->checkbox('Accompanying.' . $i . '.checked', array(
										'name' => false,
										'hiddenField' => false, 
										'onChange' => 'updatePartial(); return false;',
										'checked' => !empty($aa['cancelled']),
										'disabled' => $isPaid && !empty($aa['cancelled']) ? 'disabled' : false
									));

								} else {
									echo $this->Form->checkbox('Accompanying.' . $i . '.storno', array(
										'hiddenField' => true, 
										'onChange' => 'updatePartial(); return false;',
										'checked' => !empty($aa['cancelled']),
										'disabled' => $isPaid && !empty($aa['cancelled']) ? 'disabled' : false
									));
								}
							?>
						</td>
						<td><?php echo $person['last_name'] . ', ' . $person['first_name'];?></td>
						<td><?php echo $nations[$person['nation_id']];?></td>
						<td <?php if (count($linkedArticles) === 0) echo 'style="display:none;"';?>>
							<?php
								$items = array();
								if (!empty($linkedArticles[$aa['person_id']])) {
									foreach ($linkedArticles[$aa['person_id']] as $item) {
										// Ignore items cancelled before the order was paid
										if (!empty($item['cancelled']) && (!$isPaid || $item['cancelled'] <= $datePaid))
											continue;

										$subTotal += $item['total'];
										$items[] = $articles[$item['article_id']]['name'];
									}
								}

								echo implode(', ', $items);
							?>
						</td>
						<td id="price" class="price">
							<?php echo $showFee ? number_format($subTotal, 2) : '';?>
						</td>
						<td id="fee" class="fee">
							<?php echo $showFee ? number_format($shopSettings['cancellation_fee'] * $subTotal / 100., 2) : '';?>
						</td>	
						<td style="display:none;">
							<?php 
								echo $this->Form->control('Accompanying.' . $i . '.id', array(
									'type' => 'hidden',
									'value' => $aa['id']
								));
							?>
						</td>
					</tr>
					<?php
						}
					?>
				</tbody>
			</table>

			<br>

			<?php 		
				} 
			?>

			<?php 
				if (count($unlinkedArticles) > 0) { 
			?>
			<h3><?php echo __('Additional Items');?></h3>
			<table id="items">
				<thead>
					<tr>
						<th><?php echo __('Storno');?></th>
						<th><?php echo __('Description');?></th>
						<th><?php echo __('Quantity');?></th>
						<th class="price"><?php echo __('Price');?></th>
						<th class="fee"><?php echo __('Fee');?></th>
						<th style="display:none;">Article ID</th>
					</tr>
				</thead>
				<tbody>
					<?php
						$i = -1;
						foreach ($unlinkedArticles as $ua) {
							if ( !$articles[$ua->article_id]['visible'] )
								continue;

							$showFee = empty($ua['cancelled']) || $isPaid && $ua['cancelled'] > $datePaid;
							$i++;
							$quantity = $ua['quantity'];
					?>
					<tr>
						<td id="storno">
							<?php
								if ($isPaid && !empty($ua['cancelled'])) {
									echo $this->Form->hidden('Item.' . $i . '.storno', array('value' => true));
									echo $this->Form->checkbox('Item.' . $i . '.checked', array(
										'name' => false,
										'hiddenField' => true, 
										'onChange' => 'onCancelItem($(this).closest("tr")); return false;',
										'checked' => !empty($ua['cancelled']),
										'disabled' => $isPaid && !empty($ua['cancelled']) ? 'disabled' : false
									));								
								} else {
									echo $this->Form->checkbox('Item.' . $i . '.storno', array(
										'hiddenField' => true, 
										'onChange' => 'onCancelItem($(this).closest("tr")); return false;',
										'checked' => !empty($ua['cancelled']),
										'disabled' => $isPaid && !empty($ua['cancelled']) ? 'disabled' : false
									));
								}
							?>
						</td>
						<td><?php echo $ua['description'];?></td>
						<td id="quantity">
							<?php 
								if (empty($ua['cancelled'])) {
									echo $this->Form->select(
										'Item.' . $i . '.quantity', 
										array_combine(range(1, $quantity), range(1, $quantity)), 
										array(
											'value' => $quantity,
											'empty' => false,
											'disabled' => 'disabled',
											'onChange' => 'updatePartial(); return false;'
										)
									);
								} else {
									echo $this->Form->control('Item.' . $i . '.quantity', array(
										'type' => 'hidden',
										'value' => $quantity
									));

									echo $quantity;
								}

								// Disabled fields are not included in the POST data,
								// but if we cancel this item it becomes enabled and will be included.
								// This would cause an "unexpcted field in POST data".
								$this->Form->unlockField('Item.' . $i . '.quantity');
							?>
						</td>
						<td id="price" class="price">
							<?php echo $showFee ? $ua['price'] : '';?>
						</td>
						<td id="fee" class="fee">
							<?php echo $showFee ? $shopSettings['cancellation_fee'] * $ua['price'] / 100. : '';?>
						</td>
						<td id="id" style="display:none">
							<?php 
								echo $this->Form->hidden('Item.' . $i . '.id', array(
									'value' => $ua['id']
								));
							?>
						</td>
					</tr>
					<?php
						}
					?>
				</tbody>
			</table>
			<br>
			<?php 
				} 
			?>
		</div>

		<div id="refund">
			<table id="calculation">
				<thead>
					<tr>
						<th></th>
						<th><?php echo __('Price') . ' ' . $shopSettings['currency'];?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php echo __('Refund');?></td>
						<td id="refund">0</td>
					</tr>
					<tr>
						<td><?php echo __('Storno Fee');?></td>
						<td id="fee">0</td>
					</tr>
					<tr>
						<td><?php echo __('Discount');?></td>
						<td id="discount">
						<?php 
							echo $this->Form->text('cancellation_discount', array(
								'onBlur' => "if ($('#storno-all-1').is(':checked')) updateAll(); else updatePartial(); return false;")
							);
						?>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr class="total">
						<td> <?php echo __('Total');?></td>
						<td id="total">0</td>
					</tr>
				</tfoot>
			</table>
			<br>
		</div>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('View Order'), array('action' => 'view', $order['id']));?></li>
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
