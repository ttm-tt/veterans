<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$this->Html->scriptStart(array('block' => true));
?>

$(document).ready(function() {
	// When submitting the form with an attached submit handler, 
	// the info which button was clicked is lost.
	// So add a temp element with button name and value, submit, 
	// and remove the temp element.
	$('input[type=submit]').click(function(e) {
		var self= $(this),
			form = self.closest('form'),
			tempElement = $("<input type='hidden'/>");

		// Remove old temp elements
		$('input#__temp__').remove();
		
		// clone the important parts of the button used to submit the form.
		tempElement
			.attr("id", "__temp__")
			.attr("name", this.name)
			.val(self.val())
			.appendTo(form);
	});		
	
	$('form').submit(function(e) {
		$('input[type=submit]').prop('disabled', true);

	});
});

<?php
	$this->Html->scriptEnd();
?>

<?php
	if (empty($save))
		$save = __d('user', 'Save');
	if (empty($cancel))
		$cancel = __d('user', 'Cancel');

	$this->Form->templater()->push();
	$this->Form->templater()->add(['submitContainer' => '{{content}}']);
	
	echo '<div class="submit">';
		echo $this->Form->submit($save, array('div' => false, 'id' => 'submit', 'name' => 'submit', 'formnovalidate' => true));
		echo '&nbsp;';
		if (!empty($continue)) {
			echo $this->Form->submit(__d('user', 'Save & Cont.'), array('div' => false, 'id' => 'continue', 'name' => 'continue', 'formnovalidate' => true));
			echo '&nbsp;';
		}
		echo $this->Form->submit($cancel, array('div' => false, 'id' => 'cancel', 'name' => 'cancel', 'formnovalidate' => true));
	echo '</div>';
	
	$this->Form->templater()->pop();
?>
