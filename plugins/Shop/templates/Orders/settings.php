<?php
	// Map old configuration to new
	if (empty($settings['order_cancellation_fees']) &&
		!empty($settings['cancellation_date_100'])) {
		$settings['order_cancellation_fees'] = array(
			[
				'shop_settings_id' => $settings['id'],
				'fee' => 50,
				'start' => $settings['cancellation_date_50']
			],
			[
				'shop_settings_id' => $settings['id'],
				'fee' => 100,
				'start' => $settings['cancellation_date_100']
			],
		);
	}
?>

<div class="order form">
<?php echo $this->Form->create($settings);?>
<fieldset class="has-tabs">
	<legend><?php echo __('Shop Settings');?></legend>
	<ul class="tabs" data-tabs id="edit-shop-settings">
		<li class="tabs-title is-active">
			<a href="#general-settings" aria-selected="true"><?= __('General') ?></a>
		</li>
		<li class="tabs-title">
			<a href="#contact-settings"><?= __('Contact') ?></a>
		</li>
		<li class="tabs-title">
			<a href="#invoice-settings"><?= __('Invoice') ?></a>
		</li>
		<li class="tabs-title">
			<a href="#payment-settings"><?= __('Payment') ?></a>
		</li>
		<li class="tabs-title">
			<a href="#account-settings"><?= __('Bank') ?></a>
		</li>
	</ul>
	<?php
		echo $this->Form->control('id', array('type' => 'hidden'));
		echo $this->Form->control('tournament_id', array('type' => 'hidden'));
	?>
	
	<div class="tabs-content" data-tabs-content="edit-shop-settings">
		<div class="tabs-panel is-active" id="general-settings">
			<div>
			<?php
				echo $this->Form->control('open_from', array(
					'type' => 'date',
					'empty' => [
						'year' => __('Year'), 
						'month' => __('Month'), 
						'day' => __('Day')
					],
					'label' => __('Open From')

				));
				echo $this->Form->control('open_until', array(
					'type' => 'date',
					'empty' => [
						'year' => __('Year'), 
						'month' => __('Month'), 
						'day' => __('Day')
					],
					'label' => __('Open Until')

				));
			?>
			</div>
			
			<div>
			<?php
				// Cancellation settings are put into a table
				// Thus we need to change our dateWidget settings
				$this->Form->templater()->push();
				$this->Form->templater()->add([
					'dateWidget' => '<span class="cell small-12 medium-12 large-12" {{attrs}}>{{year}}<span>&nbsp;&ndash;&nbsp;</span>{{month}}<span>&nbsp;&ndash;&nbsp;</span>{{day}}</span>'					
				]);
			?>
			
			<table id="fees" class="unstriped">
				<thead>
					<tr>
						<th></th>
						<th><?= __('Cancellation Fee [%]') ?></th>
						<th><?= __('Start') ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
					$count = count($settings['order_cancellation_fees']);
					
					for ($idx = 0; $idx < max($count + 1, 3); ++$idx) {
						echo '<tr>';

						echo '<td>';
						echo $this->Form->control('order_cancellation_fees.' . $idx . '.id', [
							'type' => 'hidden'
						]);
						echo '</td>';

						echo '<td>';
						echo $this->Form->control('order_cancellation_fees.' . $idx . '.fee', [
							'div' => false,
							'label' => false
						]);
						echo '</td>';

						echo '<td>';
						echo $this->Form->control('order_cancellation_fees.' . $idx . '.start', [
							'div' => false,
							'empty' => true,
							'label' => false
						]);				
						echo '</td>';

						echo '</tr>';
					}
				?>					
				</tbody>
			</table>
			
			<?php
				// Restore dateWidget template
				$this->Form->templater()->pop();
			?>	
			</div>
		</div>

		<div class="tabs-panel" id="contact-settings">
			<?php
				echo $this->Form->control('name', array('label' => __('Company')));
				echo $this->Form->control('street', array('label' => __('Street')));
				echo $this->Form->control('city', array('label' => __('City')));
				echo $this->Form->control('country', array('label' => __('Country')));
				echo $this->Form->control('vat', array('label' => __('VAT Reg ID')));
				echo $this->Form->control('email', array('label' => __('Email')));
				echo $this->Form->control('add_email', array('label' => __('Add. Email')));
				echo $this->Form->control('phone', array('label' => __('Phone')));
				echo $this->Form->control('fax', array('label' => __('Fax')));			
			?>
		</div>
		
		<div class="tabs-panel" id="invoice-settings">
			<?php
				echo $this->Form->control('invoice_no_prefix', array('label' => __('Invoice No. Prefix')));
				echo $this->Form->control('invoice_no_postfix', array('label' => __('Invoice No. Postfix')));
				echo $this->Form->control('invoice_header', array('type' => 'textarea', 'label' => __('Invoice Header')));
				echo $this->Form->control('invoice_title', array('label' => __('Invoice Caption')));
				echo $this->Form->control('invoice_date', array('label' => __('Invoice Date')));
				echo $this->Form->control('invoice_no', array('label' => __('Invoice No')));
				echo $this->Form->control('invoice_add_body_banktransfer', array('label' => __('Add. Body Bank Transfer'), 'type' => 'textarea'));
				echo $this->Form->control('invoice_add_body_top', array('label' => __('Add. Body Top'), 'type' => 'textarea'));
				echo $this->Form->control('invoice_tax_exemption', array('label' => __('Tax Exemption'), 'type' => 'textarea'));
				echo $this->Form->control('invoice_add_body_bottom', array('label' => __('Add. Body Bottom'), 'type' => 'textarea'));
				echo $this->Form->control('invoice_footer', array('type' => 'textarea', 'label' => __('Invoice Footer')));
				echo $this->Form->control('invoice_add_footer', array('label' => __('Add. Footer')));
			?>
		</div>
		
		<div class="tabs-panel" id="payment-settings">
			<?php
				echo $this->Form->control('currency', array(
					'label' => __('Currency'),
				));
				echo $this->Form->control('tax', array(
					'label' => __('Tax'),
				));
				echo $this->Form->control('creditcard', array('type' => 'checkbox', 'label' => __('Credit Card')));
				echo $this->Form->control('banktransfer', array('type' => 'checkbox', 'label' => __('Bank Transfer')));
			?>
		</div>
		
		<div class="tabs-panel" id="account-settings">
			<?php
				echo $this->Form->control('bank_name', array('label' => __('Bank Name')));
				echo $this->Form->control('bank_address', array('label' => __('Bank Address')));
				echo $this->Form->control('account_holder', array('label' => __('Account Holder')));
				echo $this->Form->control('iban', array('label' => __('IBAN')));
				echo $this->Form->control('bic', array('label' => __('BIC (SWIFT)')));
				echo $this->Form->control('aba', array('label' => __('ABA')));
				echo $this->Form->control('correspondent_bank', array('type' => 'textarea', 'label' => __('Corresp. Bank')));
			?>
		</div>
	</div>
</fieldset>
<?php
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List Orders'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
