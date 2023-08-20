<?php
	$currency = $shopSettings['currency'];

	$isPaid = !empty($order) && !empty($order['invoice_paid']);
	$paidDate = $isPaid ? $order['invoice_paid'] : null;
	$isStorno = !empty($storno);

	$cancellation_discount = empty($order) ? 0. : $order['cancellation_discount'];		
	$paid = empty($order) ? 0. : $order['paid'];
	
	$hasTax = false;
	foreach ($articles as $article) {
		$hasTax |= $article['tax'] != 0.;
	}
?>

<?php 
	// Add block with the reason of a tax exemption, if no tax is stated
	if (!$hasTax && !empty($shopSettings['invoice_tax_exemption'])) {
		echo $shopSettings['invoice_tax_exemption'];
		echo '<br>';
	}
?>
<table class="ttm-table">
	<thead>
		<tr>
			<th <?php echo $isStorno ? '' : 'class="pos"';?>><?php echo $isStorno ? __d('user', 'Date') : __d('user', 'Pos');?></th>
			<th class="number qty"><?php echo __d('user', 'Qty')?></th>
			<th><?php echo __d('user', 'Description')?></th>
			<th class="currency"><?php echo $isStorno ? __d('user', 'Canc Fee') : __d('user', 'Each');?></th>
<?php if ($hasTax) { ?>
			<th class="number"><?php echo __d('user', 'VAT') . ' (%)';?></th>
			<th class="currency"><?php echo __d('user', 'Total incl. VAT');?></th>
<?php } else { ?>
			<th class="currency"><?php echo __d('user', 'Total');?></th>
<?php } ?>
		</tr>
	</thead>
	<tbody>

		<?php
			$total = 0.;
			$fee = 0.;
			$refund = 0.;
			$i = 0;

			$summedArticles = array();
			$cancelledSummedArticles = array();

			foreach ($items as $item) {
				// Skip items which don't cost anything and are not visible
				if ($item['total'] == 0 && $item['article']['visible'] == false)
					continue;

				// key must use description because this contains the variant(s)
				// TODO: create a special, persisted key with the variants
				$key = $item['description'] . '-' . $item['price'];

				if (empty($item['cancelled']))
					$summedArticles[$key][] = $item;
				else {
					$key .= '-' . date('Y-m-d', strtotime($item['cancelled']));
					$cancelledSummedArticles[$key][] = $item;
				}
			}

			foreach ($items as $item) {
				if ($item['quantity'] == 0)
					continue;

				if (!empty($item['cancelled'])) {
					if ($isPaid && $item['cancelled'] > $paidDate) {
						$refund += ($item['total'] - $item['cancellation_fee']);
						$fee += $item['cancellation_fee'];
					}
				}

				// If not listing cancelled items, skip those which are cancelled.
				// If listing cancelled items, skip those which are not cancelled.
				if ($isStorno == empty($item['cancelled']))
					continue;

				// Skip items which don't cost anything and are not visible
				if ($item['total'] == 0 && $item['article']['visible'] == false)
					continue;

				$quantity = 0;
				$subTotal = 0;
				$cancellation_fee = 0;

				$key = $item['description'] . '-' . $item['price'];			
				if (empty($item['cancelled'])) {
					if (empty($summedArticles[$key]))
						continue;

					foreach ($summedArticles[$key] as $s) {
						$quantity += $s['quantity'];
						$subTotal += $s['total'];
					}

					unset($summedArticles[$key]);
				} else {
					$key .= '-' . date('Y-m-d', strtotime($item['cancelled']));

					if (empty($cancelledSummedArticles[$key]))
						continue;

					foreach ($cancelledSummedArticles[$key] as $s) {
						$quantity += $s['quantity'];
						$subTotal += $s['total'];
						$cancellation_fee += $s['cancellation_fee'];
					}

					unset($cancelledSummedArticles[$key]);
				}

				$class = null;
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}

				echo '<tr ' . $class . '>';
				if ($isStorno)
					echo '<td>' . date('Y-m-d', strtotime($item['cancelled'])) . '</td>';
				else
					echo '<td class="pos">' . $i . '</td>';
				echo '<td class="number qty">' . $quantity . '</td>';

				echo '<td>' . $item['description'] . '</td>';
								
				if (!$isStorno) {
					echo '<td class="currency">' . sprintf('% 6.2f %s', $item['price'], $currency) . '</td>';
					if ($hasTax)
						echo '<td class="number">' . sprintf('% 3.1f %%', $articles[$item['article_id']]['tax']);
					echo '<td class="currency">' . sprintf('% 6.2f %s', $subTotal, $currency) . '</td>';
				} else if ($isPaid && $item['cancelled'] > $paidDate) {
					if ($item['cancellation_fee'] > 0)
						echo '<td class="currency">' . sprintf('% 6.2f %s', $cancellation_fee, $currency) . '</td>';
					else
						echo '<td class="currency"></td>';
					if ($hasTax)
						echo '<td class="number">' . sprintf('% 3.1f %%', $articles[$item['article_id']]['tax']);
					echo '<td class="currency">' . sprintf('% 6.2f %s', $subTotal, $currency) . '</td>';
				} else {
					echo '<td class="currency"></td>';
					if ($hasTax)
						echo '<td class="number"></td>';
					echo '<td class="currency"></td>';					
				}
				echo '</tr>';	

				if (!$isStorno || $isPaid && $item['cancelled'] > $paidDate) {
					$total += $subTotal;
				}
			}

			if (!$isStorno && !empty($order) && $order['discount'] > 0) {
				echo '<tr>';
				echo '<td colspan="2"></td>';
				echo '<td>' .  __d('user', 'Discount') . '</td>';
				echo '<td></td>';
				if ($hasTax)
					echo '<td class="number"></td>';
				echo '<td class="currency">' . sprintf('%.2f %s', -$order['discount'], $currency) . '</td>';				
				echo '</tr>';

				$total -= $order['discount'];
			}

			if (!$isStorno && $fee > 0) {
				echo '<tr id="stornofee">';
				echo '<td colspan="2"></td>';
				echo '<td>' . __d('user', 'Cancellation Fee') . '</td>';
				echo '<td></td>';
				if ($hasTax)
					echo '<td class="number"></td>';
				echo '<td class="currency">' . sprintf('% 6.2f %s', $fee, $currency) . '</td>';

				$total += $fee;
			}

			if (!$isStorno && !empty($order)) {
				if ($cancellation_discount != 0) {
					echo '<tr id="discount">';
					echo '<td colspan="2"></td>';
					echo '<td>' . __d('user', 'Cancellation Discount') . '</td>';
					echo '<td></td>';
					if ($hasTax)
						echo '<td class="number"></td>';
					echo '<td class="currency">' . sprintf('%.2f %s', -$cancellation_discount, $currency) . '</td>';
					echo '</tr>';

					$total -= $cancellation_discount;	
				}
			}

			if ($total > 0) {
				echo '<tr id="rowtotal" class="total">';
				echo '<td colspan="4">';
				echo  __d('user', 'Total');
				echo '</td>';
				if ($hasTax)
					echo '<td class="number"></td>';
				echo '<td class="currency">' . sprintf('% 6.2f %s', $total, $currency) . '</td>';
				echo '</tr>';
			}

			if ($isStorno && $fee > 0) {
				echo '<tr id="stornofee">';
				echo '<td colspan="4">' . __d('user', 'Cancellation Fee') . '</td>';
				if ($hasTax)
					echo '<td class="number"></td>';
				echo '<td class="currency">' . sprintf('% .2f %s', -$fee, $currency) . '</td>';

				$total -= $fee;
			}

			if ($isStorno && !empty($order)) {
				if ($order['cancellation_discount'] != 0) {
					echo '<tr id="discount">';
					echo '<td colspan="4">' . __d('user', 'Cancellation Discount') . '</td>';
					if ($hasTax)
						echo '<td class="number"></td>';
					echo '<td class="currency">' . sprintf('%.2f %s', $order['cancellation_discount'], $currency) . '</td>';
					echo '</tr>';

					$total += $order['cancellation_discount'];	
				}
			}

			if ($isStorno && ($refund > 0 || $fee > 0)) {
				echo '<tr id="refund" class="total">';
				echo '<td colspan="4">' . __d('user', 'Refund') . '</td>';
				if ($hasTax)
					echo '<td class="number"></td>';
				echo '<td class="currency">' . sprintf('%.2f %s', $total, $currency) . '</td>';
				echo '</tr>';
			}

			if (!$isStorno && !empty($order)) {
				$needDue = false;

				if ($paid > 0) {
					echo '<tr id="paid">';
					echo '<td colspan="4">' . __d('user', 'Paid') . '</td>';
					if ($hasTax)
						echo '<td class="number"></td>';
					echo '<td class="currency">' . sprintf('%.2f %s', -$paid, $currency) . '</td>';
					echo '</tr>';

					$total -= $paid;	
					$needDue = true;
				}

				if ($refund > 0) {
					echo '<tr id="refund">';
					echo '<td colspan="4">' . __d('user', 'Refund') . '</td>';
					if ($hasTax)
						echo '<td class="number"></td>';
					echo '<td class="currency">' . sprintf('%.2f %s', $refund + $cancellation_discount, $currency) . '</td>';
					echo '</tr>';

					$total += $refund + $cancellation_discount;
					$needDue = true;
				}

				if ($needDue) {
					echo '<tr id=rowdue" class="total">';
					echo '<td colspan="4">';
					echo __d('user', 'Due');
					echo '</td>';
					if ($hasTax)
						echo '<td class="number"></td>';
					echo '<td class="currency">' . sprintf('% 6.2f %s', $total, $currency) . '</td>';
					echo '</tr>';
				}
			}
		?>
	</tbody>
</table>

