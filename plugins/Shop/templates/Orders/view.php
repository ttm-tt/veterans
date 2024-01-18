<?php
?>
<?php
use Cake\Routing\Router;
use Cake\Utility\Hash;
?>

<?php
	function outputVal($key, $val) {
		if (!is_array($val)) {
			echo '<tr><td>' . $key . '</td><td>' . $val . '</td></tr>';
		} else if (Hash::numeric(array_keys($val))) {
			foreach ($val as $k => $v) {
				if ($k === 0)
					outputVal($key, $v);
				else
					outputVal('', $v);
			}
		} else {
			foreach ($val as $k => $v) {
				outputVal($key . '.' . $k, $v);
			}
		}
		
	}
?>

<div class="order view">
	<h2>
		<?php 
			$invoice = $order['invoice'];
			if (empty($invoice))
				$invoice = '#' . $order['id'];
			
			echo __('Order {0} from {1}', $invoice, $username) . '&nbsp;'; 

			if (!empty($revision))
				echo ' (' . $revision . ')';
			
			$hasStorno = false;
			foreach ($items as $item) {
				$hasStorno |= !empty($item['cancelled']);
				
				if ($hasStorno)
					break;
			}
		?>		
	</h2>
	<h3>
		<?php 
			echo __('Status:') . '&nbsp;' . $order['order_status']['description'];
			
			if ($order['order_status']['name'] === 'PAID')
				echo '&nbsp;(' . $order['invoice_paid'] . ')';
			else if ($order['order_status']['name'] === 'CANC') {
				if (!empty($order['invoice_cancelled']))
					echo '&nbsp;(' . $order['invoice_cancelled'] . ')';
			}
		?>
	</h3>
	<h3><?php echo __('Items');?></h3>
	<?php echo $this->element('shop_order', array('storno' => 0));?>	
	<br>
	<?php if ($hasStorno) { ?>
		<h3><?php echo __('Storno');?></h3>
		<?php echo $this->element('shop_order', array('storno' => 1));?>
		<br>	
	<?php } ?>
	<a href="javascript:void(0);" class="toggleDetail" onClick="$('div#orderPeople').toggle(); return false;"><h3><?php echo __('People');?></h3></a>
	<div id="orderPeople" class="detail" style="display:none">
	<?php
		$isPaid = $order['order_status']['name'] === 'PAID';
		$datePaid = $isPaid ? $order['invoice_paid'] : null;
		$mayUnstorno = in_array(
				$order['order_status']['name'], array(
					'CANC', 'PAID', 'DEL', 'PEND')
				);
		$canUnstorno = false;
		
		$linkedArticle = array();
		$unlinkedArticle = array();
		
		foreach ($items as $item) {
			$canUnstorno |= !empty($item['cancelled']);
			
			if ($item['article']['name'] == 'PLA') {
				continue;
			} else if ($item['article']['name'] == 'ACC') {
				continue;
			} else if ($item['article']['visible'] > 0 && !empty($item['person_id'])) {
				if (empty($linkedArticle[$item['person_id']]))
					$linkedArticle[$item['person_id']] = array();

				$linkedArticle[$item['person_id']][] = $item;
			} else {
				$unlinkedArticle[] = $item;
			}
		}
	?>

	<?php
		$editPerson = 
			$order['order_status']['name'] === 'WAIT' ||
			$order['order_status']['name'] === 'PEND'
		;
		
		$editRegistration = 
			$order['order_status']['name'] === 'INVO' ||
			$order['order_status']['name'] === 'PAID'
		;
		
		$players = array();
		$accs = array();
		foreach ($items as $item) {
			if ($articles[$item['article_id']]['name'] === 'PLA') {
				$person = unserialize($item['detail']);
				
				$idx = 0;
		
				$person['modified'] = $item['modified'];
				$person['cancelled'] = empty($item['cancelled']) ? null : ' class="cancelled"';
				$person['article_id'] = $item['id'];
				$person['idx'] = $idx;

				if (isset($item['person_id']))
					$person['person_id'] = $item['person_id'];
				else
					$person['person_id'] = null;

				// It happened that the names were not set
				if (!isset($person['last_name']))
					$person['last_name'] = "";
				if (!isset($person['first_name']))
					$person['first_name'] = "";

				$players[] = $person;
			}
			
			if ($articles[$item['article_id']]['name'] === 'ACC') {
				$person = unserialize($item['detail']);
				
				$idx = 0;
				$person['modified'] = $item['modified'];
				$person['cancelled'] = empty($item['cancelled']) ? null : ' class="cancelled"';
				$person['article_id'] = $item['id'];
				$person['idx'] = $idx;

				if (isset($item['person_id']))
					$person['person_id'] = $item['person_id'];
				else
					$person['person_id'] = null;

				// It happened that the names were not set
				if (!isset($person['last_name']))
					$person['last_name'] = "";
				if (!isset($person['first_name']))
					$person['first_name'] = "";

				$accs[] = $person;
			}			
		}
		
		if (!function_exists('cmp_order_view')) {
			function cmp_order_view($p1, $p2) {
				if ($p1['cancelled'] === null && $p2['cancelled'] !== null)
					return -1;
				if ($p1['cancelled'] !== null && $p2['cancelled'] === null)
					return +1;

				$ret = strcmp($p1['last_name'], $p2['last_name']);
				if ($ret == 0)
					$ret = strcmp($p1['first_name'], $p2['first_name']);

				return $ret;
			}
		}
		
		uasort($players, 'cmp_order_view');
		uasort($accs, 'cmp_order_view');
	?>
		
	<?php if (count($players) > 0) { ?>
	<table>
		<tr>
			<th><?php echo __('Pos');?></th>
			<th><?php echo __('Name');?></th>
			<th><?php echo __('Association');?></th>
			<th><?php echo __d('user', 'Sex');?></th>
			<th><?php echo __('Born');?></th>
			<th><?php echo __('Type');?></th>
			<?php if (count($linkedArticle) > 0) echo '<th>' . __('Articles') . '</th>';?>
			<th><?php echo __('Updated');?></th>
			<th></th>
		</tr>
		<?php 
			$idx = 0;
			foreach ($players as $person) {		
				++$idx;
				$cancelled = $person['cancelled'];

				$dob = $person['dob'];
				if (empty($dob))
					$dob = '';
				else if (is_array($dob))
					$dob = $dob['year'];
				else if (strlen($dob) === 4)
					$dob = $dob;
				else if (strpos($dob, '-00-00') !== false)
					$dob = substr ($dob, 0, 4);

				echo '<tr>';
				echo '<td ' . $cancelled . '>' . $idx . '</td>';
				echo '<td ' . $cancelled . '>' . $person['last_name'] . ', ' . $person['first_name'] . '</td>';
				echo '<td ' . $cancelled . '>' . $nations[$person['nation_id']] . '</td>';
				echo '<td ' . $cancelled . '>' . $person['sex'] . '</td>';
				echo '<td ' . $cancelled . '>' . $dob . '</td>';
				echo '<td ' . $cancelled . '>' . 'PLA' . '</td>';
				
				if (count($linkedArticle) > 0) {
					echo '<td ' . $cancelled . '>';
					$items = array();
					$pid = $person['person_id'];
					
					if (!empty($linkedArticle[$pid])) {
						foreach ($linkedArticle[$pid] as $item) {
							// Ignore items cancelled before the order was paid
							if (!empty($item['cancelled']) && (!$isPaid || $item['cancelled'] <= $datePaid))
								continue;

							$items[] = $item['article']['name'];
						}
					}
							
					echo implode(', ', $items);
					
					echo '</td>';
				}
				
				echo '<td ' . $cancelled . '>' . date('Y-m-d', strtotime($person['modified'])) . '</td>';

				if ($person['cancelled'] !== null) 
					echo '<td></td>';
				else if ($editPerson)
					echo '<td class="actions">' . $this->Html->link(__('Edit'), array('action' => 'edit_person', $person['article_id'], $person['idx'])) . '</td>';
				else if ($editRegistration && !empty($regids[$person['person_id']]))
					echo '<td class="actions">' . $this->Html->link(__('Edit'), array('plugin' => null, 'controller' => 'registrations', 'action' => 'edit_participant', $regids[$person['person_id']])) . '</td>';
				else
					echo '<td></td>';
				
				echo '</tr>';
			} 
		?>
	</table>
	<br>
	<?php } ?>
	
	<?php if (count($accs) > 0) { ?>
	<table>
		<tr>
			<th><?php echo __('Pos');?></th>
			<th><?php echo __('Name');?></th>
			<th><?php echo __('Association');?></th>
			<th><?php echo __d('user', 'Sex');?></th>
			<th><?php echo __('Type');?></th>
			<?php if (count($linkedArticle) > 0) echo '<th>' . __('Articles') . '</th>';?>
			<th><?php echo __('Updated');?></th>
			<th></th>
		</tr>
	
		<?php
			$idx = 0;
			
			foreach ($accs as $person) {
				++$idx;
				$cancelled = $person['cancelled'];
				
				echo '<tr>';
				echo '<td ' . $cancelled . '>' . $idx . '</td>';
				echo '<td ' . $cancelled . '>' . $person['last_name'] . ', ' . $person['first_name'] . '</td>';
				echo '<td ' . $cancelled . '>' . $nations[$person['nation_id']] . '</td>';
				echo '<td ' . $cancelled . '>' . $person['sex'] . '</td>';
				echo '<td ' . $cancelled . '>' . 'ACC' . '</td>';
				
				if (count($linkedArticle) > 0) {
					echo '<td ' . $cancelled . '>';
					$items = array();
					$pid = $person['person_id'];
					
					if (!empty($linkedArticle[$pid])) {
						foreach ($linkedArticle[$pid] as $item) {
							// Ignore items cancelled before the order was paid
							if (!empty($item['cancelled']) && (!$isPaid || $item['cancelled'] <= $datePaid))
								continue;

							$items[] = $item['article']['name'];
						}
					}
							
					echo implode(', ', $items);
					
					echo '</td>';
				}
				
				echo '<td ' . $cancelled . '>' . date('Y-m-d', strtotime($person['modified'])) . '</td>';

				if ($person['cancelled'] !== null) 
					echo '<td></td>';
				else if ($editPerson)
					echo '<td class="actions">' . $this->Html->link(__('Edit'), array('action' => 'edit_person', $person['article_id'], $person['idx'])) . '</td>';
				else if ($editRegistration && !empty($regids[$person['person_id']]))
					echo '<td class="actions">' . $this->Html->link(__('Edit'), array('plugin' => null, 'controller' => 'registrations', 'action' => 'edit_participant', $regids[$person['person_id']])) . '</td>';
				else
					echo '<td></td>';

				echo '</tr>';							
			}
		?>
	</table>
	<?php } ?>
	</div>
	<br>
	
	<?php if ($address !== null) { ?>
	<a href="javascript:void(0);" class="toggleDetail" onClick="$('div#orderAddress').toggle(); return false;"><h3><?php echo __('Address');?></h3></a>
	<div id="orderAddress" class="detail" style="display:none">
	<?php 
		echo $this->element('shop_address');
	?>
	</div>
	<br>
	<?php }	?>
	
	<?php if (!empty($orderDetails) && (is_object($orderDetails) ? $orderDetails->count() : count($orderDetails)) > 0) { ?>
		<a href="javascript:void(0);" class="toggleDetail" onClick="$('div#orderDetail').toggle(); return false;"><h3><?php echo __('Payment Details');?></h3></a>
		<div id="orderDetail" class="detail" style="display:none">
		<?php 
			$detailsOnClick = count($orderDetails) > 1 ? 
					'style="cursor:pointer;" onClick = "$(this).next(\'tbody\').toggle();"' :
					''
			;
			$detailsClass = count($orderDetails) > 1 ? 'style="display:none;"' : '';
		?>
		<?php foreach ($orderDetails as $d) { ?>
			<table>
				<thead <?= $detailsOnClick ?>><tr><th colspan="2"><?= $d['created']->format('Y-m-d H:i:s') ?></thead>
				<tbody <?= $detailsClass ?>>
				<?php
					// If not an array (so it is either object or string) then try to convert to one
					$detail = is_array($d) ? $d : $d->toArray();
					foreach ($detail as $key => $val) {
						if ($key === 'id')
							continue;
						if ($key === 'order_id')
							continue;
						if ($key === 'created')
							continue;
						if ($key === 'modified')
							continue;

						if (empty($val))
							continue;

						outputVal($key, $val);
					}
				?>
				</tbody></table>
		<?php } ?>
		</div>
		<br>
	<?php } ?>
	<?php if (count($order['order_comments']) > 0) { ?>
	<a href="javascript:void(0);" class="toggleDetail" onClick="$('div#orderComments').toggle(); return false;"><h3><?php echo __('Comments');?></h3></a>
	<div id="orderComments" class="detail" style="display:none">
		
	<?php 
		$idx = 0;
		foreach ($order['order_comments'] as $comment) {
			echo '#' . (++$idx) . ': [' . $comment['user']['username'] . ' ' . $comment['created'] . ']' . '<br>';
			echo $comment['comment'];
			echo '<p></p>';
		}
	?>
	</div>	
	<?php }	?>
</div>

<?php $this->start('action'); ?>
	<ul>		
		<li><?php 
			echo $this->Html->link(__('Search'), array(
					'action' => 'search'
				),
				array(
					'onclick' => 
						"var url = '" . Router::url(array('action' => 'search')) . "'; " .
						"var prompt = '" . __('Invoice Number') . "'; " .
						"var inv = window.prompt('' + prompt); " .
						"if (inv === null) return false; " .
						"this.href = url + '/?invoice=' + encodeURIComponent(inv); " .
						"return true; "
				) 
			);
		?></li>
		<?php 
			if ( $order['order_status']['name'] === 'WAIT' ||
				 $order['order_status']['name'] === 'INVO' ||
				 $order['order_status']['name'] === 'PEND' ||
				 $order['order_status']['name'] === 'DEL'  ||
				 $order['order_status']['name'] === 'PAID' ||
				 $order['order_status']['name'] === 'CANC') {
				echo '<li>' . $this->Html->link(__('Edit Invoice'), array('controller' => 'orders', 'action' => 'edit_invoice', $order['id'])) . '</li>';
			}
			
			echo '<li>' . $this->Html->link(__('Edit Address'), array('controller' => 'orders', 'action' => 'edit_address', $order['id'])) . '</li>';

			if ( $order['order_status']['name'] === 'PEND' ) {
				echo '<li>' . $this->Html->link(__('Set Delayed'), array('controller' => 'shops', 'action' => 'setDelayed', $order['id'])) . '</li>';
			}

			if ( $order['order_status']['name'] === 'INIT' ||
				 $order['order_status']['name'] === 'WAIT' ||
				 $order['order_status']['name'] === 'DEL'  ||
				 $order['order_status']['name'] === 'ERR'  ||
				 $order['order_status']['name'] === 'FRD') {
				echo '<li>' . $this->Html->link(__('Set Pending'), array('controller' => 'shops', 'action' => 'setPending', $order['id'])) . '</li>';
			}
			
			if ( $order['order_status']['name'] === 'PEND' ||
				 $order['order_status']['name'] === 'WAIT' ||
				 $order['order_status']['name'] === 'DEL' ) {
				echo '<li>' . $this->Html->link(__('Set Invoice'), 
					['controller' => 'shops', 'action' => 'setInvoice', $order['id']], 
					['confirm' => __('Are you sure to set this order to Invoice?')]
				) . '</li>';
			}
			
			if ( $order['order_status']['name'] === 'INIT' ||
				 $order['order_status']['name'] === 'INVO' ||
				 $order['order_status']['name'] === 'PEND' ||
				 $order['order_status']['name'] === 'DEL'  ||
				 $order['order_status']['name'] === 'FRD' ) {
				echo '<li>' . $this->Html->link(__('Set Paid'), array('controller' => 'shops', 'action' => 'setPaid', $order['id']));
			}

			if ( $order['order_status']['name'] === 'INVO' ||
				 $order['order_status']['name'] === 'PAID' ||
				 $order['order_status']['name'] === 'WAIT' ||
				 $order['order_status']['name'] === 'PEND' ||
				 $order['order_status']['name'] === 'DEL'  ||
				 $order['order_status']['name'] === 'CANC') {
				echo '<li>' . $this->Html->link(__('View Invoice'), array(
							'controller' => 'shops', 
							'action' => 'viewInvoice', 
							$order['id'] . '.pdf'
						), 
						array('target' => '_blank')
					) . '</li>';
				echo '<li>' . $this->Html->link(__('Mail Invoice'), array(
						'controller' => 'shops', 
						'action' => 'sendInvoice', 
						$order['id'] . '.pdf'
					)) . '</li>';
			} 
			
			if ( $order['order_status']['name'] === 'PEND' ||
				 $order['order_status']['name'] === 'DEL' ) {
				echo '<li>' . $this->Html->link(__('Mail Reminder'), array(
						'controller' => 'shops', 
						'action' => 'send_reminder', 
						$order['id']
					)) . '</li>';				
			}
			if ( $order['order_status']['name'] === 'PAID') {
				echo '<li>' . $this->Html->link(__('View Voucher'), array(
							'controller' => 'shops', 
							'action' => 'viewVoucher', 
							$order['id'] . '.pdf'
						),
						array('target' => '_blank')
					) . '</li>';
				echo '<li>' . $this->Html->link(__('Mail Voucher'), array(
						'controller' => 'shops', 
						'action' => 'sendVoucher', 
						$order['id'] . '.pdf'
					)) . '</li>';
			}
			if ( $order['order_status']['name'] === 'WAIT' ||
				 $order['order_status']['name'] === 'PAID' || 
				 $order['order_status']['name'] === 'INVO' || 
				 $order['order_status']['name'] === 'PEND' ||
				 $order['order_status']['name'] === 'DEL' ) {
				echo '<li>' . $this->Html->link(__('Storno'), array('action' => 'storno', $order['id'])) . '</li>';
			}
			if ($mayUnstorno && $canUnstorno) {
				echo '<li>' . $this->Html->link(__('Undo Storno'), array('action' => 'unstorno', $order['id'])) . '</li>';
			}
			
			
		?>
		<li><?php echo $this->Html->link(__('History'), array('action' => 'history', $order['id']));?></li>
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index'));?></li>
	</ul>

<?php $this->end(); ?>
