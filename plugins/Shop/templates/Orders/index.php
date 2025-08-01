<?php
	use Cake\Routing\Router;
?>

<div class="orders index">
	<h2><?php echo __('Orders');?></h2>
	<?php
		echo '<div class="filter">';
		echo '<fieldset>';
		echo '<legend>' . __d('user', 'Filter') . '</legend>';
		echo '<table>';
			echo $this->element('filter', [
				'label'=> __('Status'),
				'id' => 'order_status_id',
				'options' => $orderstatus
			]);

		// Filter nach Zahlweise
		echo $this->element('filter', [
			'label'=> __('Payment Method'),
			'id' => 'payment_method',
			'empty' => true,
			'options' => $payment_methods
		]);
		
		// Filter for payment date
		echo $this->element('filter', [
			'label' => __('Invoice paid'),
			'id' => 'invoice_paid',
			'options' => ['no' => __('No'), 'yes' => __('Yes')]
		]);
		
		// Filter for  Paid / Refunding ... 
		echo $this->element('filter', [
			'label' => 'Refund Status',
			'id' => 'refund_status',
			'options' => $refund_statuses
		]);
		
		// Filter nach Artikel
		echo $this->element('filter', [
			'label'=> __('Article'),
			'id' => 'article_id',
			'options' => $articles
		]);

		echo $this->element('filter', [
			'label'=> __('Country'),
			'id' => 'country_id',
			'options' => $countries
		]);

		echo '<tr><td><label class="filter">' . __('Name') .	'</td><td>';

		// echo print_r($allchars, true);
		foreach ($allchars as $idx => $chars) {
			if (count($chars) == 0)
				continue;

			if ($idx > 0)
				echo '<br>';

			if ($idx == 0) {
				if (isset($last_name))
					echo $this->Html->link(__('all'), ['?' => ['last_name' => '*']]);
				else
					echo __('all');
			} else {
				$name = str_replace(' ', '_', mb_convert_case(mb_strtolower(mb_substr($chars[0], 0, mb_strlen($chars[0]) - 1)), MB_CASE_TITLE));

				if (mb_strlen($last_name) >= mb_strlen($chars[0]))
					echo $this->Html->link($name, ['?' => ['last_name' => urlencode(str_replace(' ', '_', mb_substr($chars[0], 0, mb_strlen($chars[0]) - 1)))]]);
				else
					echo $name;
			}

			foreach ($chars as $char) {
				$name = str_replace(' ', '_', mb_convert_case(mb_strtolower($char), MB_CASE_TITLE));

				if (mb_substr($last_name, 0, mb_strlen($char)) == $char)
					echo ' ' . $name;
				else
					echo ' ' . $this->Html->link($name, ['?' => ['last_name' => urlencode(str_replace(' ', '_', $char))]]);
			}
		}
		echo '</td></tr>';

		echo '<tr></tr>';
		
		echo '<tr><td><label class="filter">' . __('Duplicates') .	'</td><td>';
		if (isset($duplicates))
			echo $this->Html->link(__('all'), ['?' => ['duplicates' => 'all']]);
		else
			echo __('all');

		if (isset($duplicates))
			echo ' ' . _('Duplicates');
		else
			echo ' ' . $this->Html->link(_('Duplicates'), ['?' => ['duplicates' => 'duplicates']]);

		echo '</td></tr>';

		if (!empty($user_id)) {
			echo '<tr><td><label class="filter">' . __d('user', 'Username') . '</label></td><td>';
			echo $this->Html->link(__d('user', 'all'), ['?' => ['user_id' => 'all']]);
			echo ' ' . $username;

			echo '</td></tr>';
		}

		echo '</td></tr>';
		
		echo '</table>' . "\n";
		echo '</fieldset></div>' . "\n";
	?>
	
	<table>
	<tr>
		<th><?php echo $this->Paginator->sort('Orders.invoice', __('Invoice'));?></th>
		<th><?php echo $this->Paginator->sort('OrderStatus.description', __('Status'));?></th>
		<th><?php echo $this->Paginator->sort('Orders.email', __('Email'));?></th>
		<th><?php echo $this->Paginator->sort('InvoiceAddresses.last_name', __('Name'));?></th>
		<th><?php echo __('Country');?></th>
		<th><?php echo $this->Paginator->sort('Orders.total', __('Amount'));?></th>
		<th><?php echo $this->Paginator->sort('Orders.created', __('Order date'));?></th>
		<th><?php echo $this->Paginator->sort('Orders.accepted', __('Accepted'));?></th>
		<th><?php echo $this->Paginator->sort('Orders.invoice_paid', __('Payment date'));?></th>
		<?php if (!isset($method))
			echo '<th>' . $this->Paginator->sort('Orders.payment_method', __('method')) . '</th>';
		?>
		<th><?php echo $this->Paginator->sort('Orders.invoice_paid', __('Cancelled'));?></th>
		<th><?php echo $this->Paginator->sort('Orders.refund', __('Refund'));?></th>
		<th class="actions" colspan="3"><?php echo __('Actions');?></th>
	</tr>

	<?php
		$allowView = $Acl->check($current_user, 'controllers/Shop/Orders/view');
		$allowStorno = $Acl->check($current_user, 'controllers/Shop/Orders/storno');
		$allowUnstorno = $Acl->check($current_user, 'controllers/Shop/Orders/unstorno');
		$allowPaid = $Acl->check($current_user, 'controllers/Shop/Shops/setPaid');
		$allowPending = $Acl->check($current_user, 'controllers/Shop/Shops/setPending');
	?>

	<?php
		foreach ($orders as $order) {
	?>
		<tr>
			<td><?php echo $order['invoice'];?></td>
			<td><?php echo $order['order_status']['description'];?></td>
			<td><?php echo $order['email'];?></td>
			<td>
			<?php 
				if (empty($order['invoice_address']['first_name']) && !empty($order['invoice_address']['last_name']))
					echo $order['invoice_address']['last_name'];
				else if (!empty($order['invoice_address']['display_name']))
					echo $order['invoice_address']['display_name'];
				else if (!empty($order['user']['username']))
					echo $order['user']['username'];
				else {
					foreach (($order['order_articles'] ?? []) as $article) {
						if ($article['article_id'] == $articles['PLA']) {
							$player = unserialize($article['detail']);
							echo $players['last_name'] . ', ' . $players['first_name'];
							break;
						}
					}
				}
			?>
			</td>
			<td>
			<?php
				if (!empty($order['invoice_address']['country_id']))
					echo $countries[$order['invoice_address']['country_id']];
			?>
			</td>
			<td class="currency">
				<?php
					// TODO: Discount beruecksichtigen
					echo $order['total'] . '&nbsp;' . $shopSettings['currency'];
				?>
			</td>
			<td><?php echo $order['created'];?></td>
			<td><?php echo $order['accepted'];?></td>
			<td><?php echo $order['invoice_paid'];?></td>
			<?php if (!isset($method))
				echo '<td>' . $order['payment_method'] . '</td>';
			?>
			<td><?php echo $order['invoice_cancelled'];?></td>
			<td><?= $order['refund'] ?></td>
			<td class="actions">
				<?php 
					if ($allowView) {
						echo $this->Html->link(__('View'), array('action' => 'view', $order['id']));
						// echo $this->Html->link(__('History'), array('action' => 'history', $order['Order']['id']));
					}
				?>
			</td>
			<td class="actions">
				<?php
					if ($order['order_status']['name'] === 'INIT') {
						if ($allowStorno)
							echo $this->Html->link(__('Storno'), array('action' => 'storno', $order['id']));
					}
					if ($order['order_status']['name'] === 'PEND') {
						if ($allowStorno)
							echo $this->Html->link(__('Storno'), array('action' => 'storno', $order['id']));
					}
					if ($order['order_status']['name'] === 'WAIT') {
						if ($allowStorno)
							echo $this->Html->link(__('Storno'), array('action' => 'storno', $order['id']));
					}
					if ($order['order_status']['name'] === 'ERR') {
						if ($allowPending)
							echo $this->Html->link(__('Pending'), array('controller' => 'shops', 'action' => 'setPending', $order['id']));
					}
					if ($order['order_status']['name'] === 'FRD') {
						if ($allowPending)
							echo $this->Html->link(__('Pending'), array('controller' => 'shops', 'action' => 'setPending', $order['id']));
					}
					if ($order['order_status']['name'] === 'PAID') {
						if ($allowStorno)
							echo $this->Html->link(__('Storno'), array('action' => 'storno', $order['id']));
					}
					if ($order['order_status']['name'] === 'INVO') {
						if ($allowStorno)
							echo $this->Html->link(__('Storno'), array('action' => 'storno', $order['id']));
					}
					if ($order['order_status']['name'] === 'CANC') {
						if ($allowUnstorno && empty($order['invoice_paid']))
							echo $this->Html->link(__('Pending'), array('action' => 'unstorno', $order['id']));
					}
				?>
			</td>
			<td class="actions">
				<?php
					if ($order['order_status']['name'] === 'INVO') {
						if ($allowPaid)
							echo $this->Html->link(__('Paid'), array('controller' => 'shops', 'action' => 'setPaid', $order['id']));
					}					
					if ($order['order_status']['name'] === 'INIT') {
						if ($allowPaid)
							echo $this->Html->link(__('Paid'), array('controller' => 'shops', 'action' => 'setPaid', $order['id']));
					}					
					if ($order['order_status']['name'] === 'WAIT') {
						if ($allowPending)
							echo $this->Html->link(__('Pending'), array('controller' => 'shops', 'action' => 'setPending', $order['id']));
					}					
					if ($order['order_status']['name'] === 'PEND') {
						if ($allowPaid)
							echo $this->Html->link(__('Paid'), array('controller' => 'shops', 'action' => 'setPaid', $order['id']));
					}
					if ($order['order_status']['name'] === 'DEL') {
						if ($allowPaid)
							echo $this->Html->link(__('Paid'), array('controller' => 'shops', 'action' => 'setPaid', $order['id']));
					}
				?>
			</td>

		</tr>
	<?php
		}
	?> 
	</table>
	<?php echo $this->element('paginator');?>
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
		<li><?php echo $this->Html->link(
				__('Mail Reminders'), 
				array('controller' => 'shops', 'action' => 'send_reminder')
			);
		?></li>
		<li><?php echo $this->Html->link(
				__('Mail Vouchers'), 
				array('controller' => 'shops', 'action' => 'sendVoucher'), 
				['confirm' => __('Are you sure you want to send vouchers to all?')]
			);
		?></li>
		<li><?php echo $this->Html->link(
				__('Cancel Pending'), 
				array('action' => 'storno_pending')
			);?>
		</li>
		<li><?php echo $this->Html->link(
				__('Process Waiting List'), 
				array('controller' => 'shops', 'action' => 'process_waiting_list'),
				['confirm' => __('Are you sure you want to accept the waiting list?')]
			);?>
		</li>
		<li><?php echo $this->Html->link(__('Import'), array('controller' => 'shops', 'action' => 'import'));?></li>
		<li><?php echo $this->Html->link(__('Export'), array('action' => 'export.csv'));?></li>
		<li><?php echo $this->Html->link(__('Export Players'), array('action' => 'export_players.csv'));?></li>
		<li><?php echo $this->Html->link(__('List Articles'), array('controller' => 'articles', 'action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('Settings'), array('action' => 'settings'));?></li>
	</ul>
<?php $this->end(); ?>
