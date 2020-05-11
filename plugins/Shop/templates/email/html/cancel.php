<?php
	ob_start();
?>

* {
	font-family: sans-serif;
	font-size: 16px;
}

ul {
  list-item-style: none;
}

table {
	width: 80%;
}

table th {
	text-align: left;
}

table tr th {
	border-bottom: 1px solid black;
}

table tr td {
	padding-top: 5px;
}

table tr.total td {
	border-top: 1px solid black;
	font-size: 110%;
	font-weight: bold;
}

table tr td.cancelled {
	text-decoration: line-through;
}

table tr td.currency {
	text-align: right;
}

table tr td.number {
	text-align: right;
}

dl dt {
	display: none;
}

div#header {
	font-size: 80%;
}

div#footer {
  font-size: 80%;
}

div#footer span.add-footer {
	display: block;
	float: initial;
	width: 100%;
	text-align: center;
}

div#footer span.dl {
	display: inline-block;
	float:left;
	width: 48%;
}

div#footer span.dl ~ span.dl {
    padding-left: 1em;
}
	
div#footer table {
  width: auto;
}

div#footer table td {
  font-size: 80%;
  padding-top: 0px;
  padding-right: 2em;
}

.dl {
	line-height: 1em;
}

.dt {
	font-weight: bold;
	padding-right: 1em;
	vertical-align: top;
}

.dt:after {
	vertical-align: top;
	white-space: nowrap;
	content: ':';
}

.dd {
}

<?php
	$css = ob_get_clean();
	$this->append('css', '<style type="text/css">' . $css . '</style>');
?>

<?php 
	if ($order['invoice_address']['title'] === 'Mr') {
		echo __d('user', 'Dear Mr. {0} {1},', $order['invoice_address']['first_name'], $order['invoice_address']['last_name']);
		echo '<br><br>';
	} else if ($order['invoice_address']['title'] === 'Mrs') {
		echo __d('user', 'Dear Mrs. {0} {1},', $order['invoice_address']['first_name'], $order['invoice_address']['last_name']);		
		echo '<br><br>';
	} 
	if ($onRequest) {
		echo __d('user', 'We have cancelled your registration {0} on your request.', $order['invoice']);
	} else {
		echo __d('user', 'We have cancelled your registration {0} because we have not received your payment in due time.', $order['invoice']);
		echo '<br>';
		echo __d('user', 'If you think this is an error please contact {0} immediately!', $shopSettings['email']);
	}
?>
<p></p>
<?php echo __d('user', 'Details of your registration:'); ?>
<br>
<?php echo $this->element('shop_order');?>
<?php if (!empty($people)) { ?>
<p></p>
<?php echo __d('user', 'You had entered the following players and accompanying persons:'); ?>
<?php echo $this->element('shop_people', array('edit' => false)); ?>
<?php } ?>
<p></p>
<?php echo __d('user', 'Best regards'); ?>
<p></p>	

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
				if (!empty($shopSettings['bic'])) {
					echo '<tr>';
					echo '<td class="dt">' . __d('user', 'BIC') . '</td>';
					echo '<td class="dd">' . $shopSettings['bic'] . '</td>';
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
			echo '</table>';
			
			if (!empty($shopSettings['add_footer'])) {
				echo '<span class="add-footer">';
				echo $shopSettings['add_footer'];
				echo '</span>';
			}
		}
	?>
</div>
	

