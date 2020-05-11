<?php
use Shop\Model\Table\OrderStatusTable;
?>

<?php
	echo $this->Html->script('chart');
?>

<script>
	var labels = [];
	var paid = [], sumPaid = [], currSumPaid = 0;
	var pend = [], sumPend = [], currSumPend = 0;
	var canc = [], sumCanc = [], currSumCanc = 0;
	
	var chart = null;
	
	function onChangeChartType(radio) {
		var ctx = $('#chart');
		
		var val = radio.val();
		
		if (chart !== null)
			chart.destroy();
		
		chart = new Chart(ctx, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [
					{
						label: '<?php echo __('Paid');?>',
						data: radio.val() == 1 ? paid : sumPaid,
						backgroundColor: 'lightgreen'
					},
					{
						label: '<?php echo __('Pending');?>',
						data: radio.val() == 1 ? pend : sumPend,
						backgroundColor: 'lightyellow'
					},
					{
						label: '<?php echo __('Cancelled');?>',
						data: radio.val() == 1 ? canc : sumCanc,
						backgroundColor: 'salmon'
					}			
				]
			},
			options: {
				elements: {
					line: {
						tension: 0 // disable Bezier
					}
				},
				tooltips: {
					mode: 'index'
				},
				hover: {
					mode: 'index'
					},
				scales: {
					xAxes: [{
						type: 'time',
						time: {
							unit: 'week',
							displayFormats: {
								'day': 'MMM DD'
							}
						}
					}],
					yAxes: [{
						stacked: true
					}]
				}
			}
		});
	};
	$(document).ready(function() {
		var paidId = <?php echo OrderStatusTable::getPaidId();?>;
		var invoId = <?php echo OrderStatusTable::getInvoiceId();?>;
		var pendId = <?php echo OrderStatusTable::getPendingId();?>;
		var waitId = <?php echo OrderStatusTable::getWaitingListId();?>;
		var cancId = <?php echo OrderStatusTable::getCancelledId();?>;
		var delId  = <?php echo OrderStatusTable::getDelayedId();?>;
		
		var rawData = JSON.parse('<?php echo $data;?>');
		
		$.each(rawData, function(key, val) {
			labels.push(new Date(key));

			var tmp = 0;
			if (val.hasOwnProperty(paidId))
				tmp += parseInt(val[paidId]);
			if (val.hasOwnProperty(invoId))
				tmp += parseInt(val[invoId]);
			
			paid.push({x: new Date(key), y: tmp});
			sumPaid.push({x: new Date(key), y: currSumPaid += tmp});

			tmp = 0;
			if (val.hasOwnProperty(pendId))
				tmp += parseInt(val[pendId]);
			if (val.hasOwnProperty(waitId))
				tmp += parseInt(val[waitId]);
			if (val.hasOwnProperty(delId))
				tmp += parseInt(val[delId]);
			
			pend.push({x: new Date(key), y: tmp});
			sumPend.push({x: new Date(key), y: currSumPend += tmp});

			tmp = 0;
			if (val.hasOwnProperty(cancId))
				tmp += parseInt(val[cancId]);
			
			canc.push({x: new Date(key), y: val[cancId]});
			sumCanc.push({x: new Date(key), y: currSumCanc += tmp});
		});

		$('input#charttype-1').attr('checked', true);
		onChangeChartType($('input#charttype-1'));
	});
	
</script>

<?php
	// Must contain view so settings from base css are taken
?>
<div class="articles view chart">
<h2><?php echo $article['description'];?></h2>

<div id="charttype">
	<div class="grid-x grid-margin-x input radio" style="clear: both;">
		<?php
			echo $this->Form->radio('charttype', array(1 => __('Per Day')), array(
				'onChange' => 'window.onChangeChartType($(this)); return false;',
				'hiddenField' => false,
				'legend' => false,
				'style' => 'float: inherit'
			));
		?>
	</div>
	<div class="grid-x grid-margin-x input radio" style="clear: both;">
		<?php
			echo $this->Form->radio('charttype', array(2 => __('Total')), array(
				'onChange' => 'window.onChangeChartType($(this)); return false;',
				'hiddenField' => false,
				'legend' => false,
				'style' => 'float: inherit'
			));
		?>
	</div>
</div>
<canvas id="chart"></canvas>
</div>


<?php $this->start('action'); ?>
	<ul>
		<?php 
			if ($Acl->check($current_user, 'Articles/index')) 
				echo '<li>' . $this->Html->link(__('List Articles'), array('action' => 'index')) . '</li>'; 
			if ($Acl->check($current_user, 'Orders/index')) 
				echo '<li>' . $this->Html->link(__('List Orders'), array('controller' => 'Orders', 'action' => 'index')) . '</li>'; 
		?>
	</ul>
<?php $this->end(); ?>
