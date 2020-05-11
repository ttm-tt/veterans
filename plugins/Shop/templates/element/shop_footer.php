<?php
	$this->Form->templater()->push();
	$this->Form->templater()->add(['submitContainer' => '{{content}}']);	
?>

<div class="submit">
	<?php
		$steps = $this->Wizard->config('steps');
		$stepNumber = $this->Wizard->stepNumber();
		$stepTotal = $this->Wizard->stepTotal();
		
		if (empty($next))
			$next = __d('user', 'Next');
		if (empty($nextForce))
			$nextForce = false;
	?>
	<?php 
		echo $this->Form->submit(__d('user', 'Cancel'), array('name' => 'Cancel', 'div' => false, 'formnovalidate' => true)); 
	?>
	<?php 
		if ($stepNumber > 1) 
			echo $this->Form->submit(__d('user', 'Previous'), array('name' => 'Previous', 'div' => false, 'formnovalidate' => true)); 
	?>
	<?php
		if (!empty($before))
			echo $this->Form->submit($before[0], $before[1]);
	?>
	<?php 
	    if (!empty($nextOptions))
			$nextOptions = $nextOptions + array('name' => __d('user', 'Next'));
		else
			$nextOptions = array('name' => __d('user', 'Next'));
		
		if (!empty($nextScript))
			$nextOptions['onclick'] = $nextScript;
		
		// Only if there are more steps
		// In testing we can go directly there so we need to force that button
		if ($nextForce || $stepNumber < $stepTotal)
			echo $this->Form->submit($next, $nextOptions); 
	?>
	<?php
		if (!empty($after))
			echo $after;
	?>
</div>

<?php
	$this->Form->templater()->pop();
?>

