<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php $this->Html->scriptStart(array('block' => true)); ?>
function camelizeName(name) {
	name = name.trim();
	if (name === name.toUpperCase() || name === name.toLowerCase()) {
		name = name.replace(/\w+/g, function(txt){
			return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
		});
	}
	
	// Special cases
	// TODO
	return name;
}

<?php $this->Html->scriptEnd(); ?>

<div class="people form">
<?php echo $this->Form->create($person);?>
	<fieldset>
 		<legend><?php echo __('Add Person'); ?></legend>
	<?php
		echo $this->Form->control('first_name', array(
			'label' => __('Given Name'), 
			'onBlur' => 'this.value = camelizeName(this.value);'
        ));
		echo $this->Form->control('last_name', array(
			'label' => __('Family Name'), 
			'onBlur' => 'this.value = $.trim(this.value.toUpperCase());'
        ));
		if ($hasRootPrivileges) {
			echo $this->Form->control('display_name', array(
                'onBlur' => 'this.value = $.trim(this.value);'
            ));
		} else{
			echo $this->Form->control('display_name', array('type' => 'hidden'));
		}

		if ($hasRootPrivileges)
			echo $this->Form->control('username', array('value' => $username));

		echo $this->Form->control('sex', array(
			'type' => 'select', 
			'options' =>array('F' => __('Woman'), 'M' => __('Man')
		)));

		if ($hasRootPrivileges) {
			echo $this->Form->control('dob', array(
				'type' => 'date',
				'dateFormat' => 'YMD',
				'separator' => '<span>-</span>',
				'empty' => [
					'year' => __('Year'), 
					'month' => __('Month'), 
					'day' => __('Day')
				],
				'required' => 'required',
				'label' => __('Date Born'),
				'maxYear' => date('Y'),
				'minYear' => date('Y') - 120
			));
			
			// Extern ID: Only visible to root
			echo $this->Form->control('extern_id', array('type' => 'text', 'label' => __('Extern ID')));
		}

		if (empty($current_user['nation_id'])) {
			// Association: Only root or anyone without a set nation_id may select this
			$options = array();
			if ($this->request->getSession()->check('Nations.id'))
				$options = array('default' => $this->request->getSession()->read('Nations.id'));
			else
				$options = array('empty' => __('Select Association'));
			$options['label'] = __('Association');
			echo $this->Form->control('nation_id', $options);
		} else {
			// Non-root: This is the users association
			echo $this->Form->control('nation_id', array('type' => 'hidden', 'value' => $current_user['User']['nation_id']));
		}

	?>
	</fieldset>
<?php 
	echo $this->element('savecancel');
	echo $this->Form->end();
?>
</div>

<?php $this->start('action'); ?>
	<ul>
		<li><?php echo $this->Html->link(__('List People'), array('action' => 'index'));?></li>
	</ul>
<?php $this->end(); ?>
