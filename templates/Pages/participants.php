<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<?php
	$this->Html->script('datatables', array('block' => true));
	$this->Html->css('datatables', array('block' => true));
	
	$this->Html->scriptStart(array('block' => true));
?>

$(document).ready(function() {
	$('.datatable').DataTable({
		responsive: false,
		serverSide: true,
		oLanguage: {
			sInfo: "<?= Configure::read('CustomStrings.DataTable:oLanguage:sInfo') ?>",
			sInfoFiltered: "<?= Configure::read('CustomStrings.DataTable:oLanguage:sInfoFiltered') ?>"
		},
		ajax: {
			url: '<?php echo Router::url(array('action' => 'onParticipantData'), false);?>',
			type: 'POST',
			dataType: 'json'
		}
	});
});

<?php
	$this->Html->scriptEnd();
?>

<div class="index">
<h2><?php echo __d('user', 'Participants'); ?></h2>
<div class="hint">
	<?php echo __d('user', 'Only confirmed double partners are shown');?>
</div>

<table class="datatable display">
	<thead>
		<tr>
			<th><?php echo __d('user', 'Name');?></th>
			<th><?php echo __d('user', 'Association');?></th>
			<?php if (isset($types['S'])) { ?>
			<th><?php echo __d('user', 'Singles');?></th>
			<?php } ?>
			<?php if (isset($types['D'])) { ?>
			<th><?php echo __d('user', 'Doubles');?></th>
			<th><?php echo __d('user', 'Double Partner');?></th>
			<?php } ?>
			<?php if (isset($types['X'])) { ?>
			<th><?php echo __d('user', 'Mixed');?></th>
			<th><?php echo __d('user', 'Mixed Partner');?></th>
			<?php } ?>
			<?php if (isset($types['T'])) { ?>
			<th><?php echo __d('user', 'Teams');?></th>
			<?php } ?>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>
</div>



