<?php
use App\Model\Table\GroupsTable;
?>

<div id="header">
	<?php 
		if (!empty($shopSettings['invoice_header'])) {
			echo $shopSettings['invoice_header'];
		} else {
			echo '<div id="shopAddress">';
			echo '<ul>';
				if (!empty($shopSettings['name']))
					echo '<li>' . $shopSettings['name'] . '</li>';
				if (!empty($shopSettings['street']))
					echo '<li>' . $shopSettings['street'] . '</li>';
				if (!empty($shopSettings['city']))
					echo '<li>' . $shopSettings['city'] . '</li>';
				if (!empty($shopSettings['country']))
					echo '<li>' . $shopSettings['country'] . '</li>';
			echo '</ul>';
			echo '</div>';
		}
	?>
	<br style="clear:both;">
</div>
<div id="caption">
	<div id="title">
		<h1><?php echo ($shopSettings['invoice_title'] ?? __d('user', 'Proforma Invoice'));?></h1>
	</div>
	<div id="transaction">
		<table class="dl">
			<tr>
				<td class="dt"><?php echo ($shopSettings['invoice_date'] ?? __d('user', 'Invoice date'));?></td>
				<td class="dd"><?php echo date('d.m.Y', strtotime($order['created']));?></td>
			</tr>
			<tr>
				<td class="dt"><?php echo ($shopSettings['invoice_no'] ?? __d('user', 'Invoice no'));?></td>
				<td class="dd"><?php echo $order['invoice'];?></td>
			</tr>
		</table>
	</div>
	<br style="clear:both;">
</div>
<div id="billingAddress">
	<ul>
		<?php if (!empty($address) && !empty($address['id'])) { ?>
			<li><?php 
				if (empty($address['first_name']))
					echo $address['last_name'];
				else if (empty($address['last_name']))
					echo $address['first_name'];
				else
					echo $address['first_name'] . ' ' . $address['last_name'];
			?></li>
			<li><?php echo $address['street'];?></li>
			<li><?php echo $address['city'];?></li>
			<li><?php echo $countries[$address['country_id']];?></li>
		<?php } ?>
	</ul>
</div>
<div id="content">
	<h2><?php echo __d('user', 'Order');?></h2>
	<?php echo $this->element('Shop.shop_order');?>
	<br>
	<?php 
		// At the moment invoices from groups can grow to large to be generated at all
		if (empty($order['user']['group_id']) || $order['user']['group_id'] != GroupsTable::getTourOperatorId()) {
			if (!empty($people)) {
				echo $this->element('Shop.shop_people', array('edit' => false));
				echo '<br>';
			}
		}
	?>
</div>
<div id="footer">
	<?php 
		if (!empty($shopSettings['footer'])) {
			echo $shopSettings['footer'];
		} else {
			echo '<span class="dl"><table class="dl">';
				if (!empty($shopSettings['bank_name'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Bank') . '</td>';
					echo '<td class="dd">' . $shopSettings['bank_name'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['bank_address'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Bank Address') . '</td>';
					echo '<td class="dd">' . $shopSettings['bank_address'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['iban'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'IBAN') . '</td>';
					echo '<td class="dd">' . $shopSettings['iban'] . '</td>';
					echo '</tr>';
				}
				else if (!empty($shopSettings['account_no'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Account No') . '</td>';
					echo '<td class="dd">' . $shopSettings['account_no'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['bic'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'BIC (SWIFT)') . '</td>';
					echo '<td class="dd">' . $shopSettings['bic'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['aba'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'ABA') . '</td>';
					echo '<td class="dd">' . $shopSettings['aba'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['correspondent_bank'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Correspondent Bank') . '</td>';
					echo '<td class="dd">' . implode('<br>', explode("\n", $shopSettings['correspondent_bank'])) . '</td>';
					echo '</tr>';
				}
			echo '</table></span>';
			echo '<span class="dl"><table class="dl">';
				if (!empty($shopSettings['account_holder'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Account Holder') . '</td>';
					echo '<td class="dd">' . $shopSettings['account_holder'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['email'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Email') . '</td>';
					echo '<td class="dd">' . $shopSettings['email'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['phone'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Phone') . '</td>';
					echo '<td class="dd">' . $shopSettings['phone'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['fax'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'Fax') . '</td>';
					echo '<td class="dd">' . $shopSettings['fax'] . '</td>';
					echo '</tr>';
				}
				if (!empty($shopSettings['vat'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'VAT Reg Id') . '</td>';
					echo '<td class="dd">' . $shopSettings['vat'] . '</td>';
					echo '</tr>';
				}
			echo '</table></span>';
			
			if (!empty($shopSettings['invoice_add_footer'])) {
				echo '<span class="add-footer">';
				echo $shopSettings['invoice_add_footer'];
				echo '</span>';
			}
		}
	?>
</div>
