<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
/*
	echo $this->element('filter', [
		'label' => __('Package'),
		'id' => 'package_id', 
		'options' => $packages,
		'empty' => true,
		'required' => true
	]);
 */
?>

<?php
	$class = empty($required) ? '' : ' class="required" ';

	echo '<tr><td ' . $class . '><label class="filter">' . $label . '</td><td>';
	if (!isset(${$id}) || ${$id} === false)
		echo __('all');
	else
		echo $this->Html->link(__('all'), ['?' => [$id => (empty($all) ? 'all' : $all)]]);

	if (!empty($empty)) {
		if (${$id} == 'null')
			echo ' ' . __('none');
		else
			echo ' ' . $this->Html->link(__('none'), ['?' => [$id => 'none']]);
	}
	
	foreach($options as $k => $v) {
		if ($k === null)
			continue;
		
		$l = $k;
		
		if (!empty($date)) {
			if ($v === null)
				continue;

			if (!is_object($v)) {
				$v = new Cake\I18n\FrozenDate($v);
			}
			
			$k = $v;	
			$l = $v->format('Y-m-d');
			$v = $v->format('D d');
		}
		
		if (isset(${$id}) && in_array($k, explode(',', ${$id})))
			echo ' ' . $v;
		else
			echo ' ' .$this->Html->link($v, ['?' => [$id => $l]]);
	}

	echo '</td></tr>' . "\n";

	echo '<tr/>' . "\n";
?>
