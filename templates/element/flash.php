<?php /* Copyright (c) 2020 Christoph Theis */ ?>
		<div id="flashMessages">
			<?php echo $this->MultipleFlash->flash(); ?>
		</div>

		<?php echo $this->Flash->render('auth'); ?>

		<?php echo $this->Flash->render('email'); ?>


