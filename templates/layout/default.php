<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Core\Configure;
use Cake\Routing\Router;
?>

<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<?php if (Configure::read('App.responsive')) 
		echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
	?>
	<title>
		<?php
			$prefix = '';
			if (!empty($tournament))
				$prefix = 
					$tournament['name'] . ' ' .
					$tournament['location'] . ' ';
			echo $prefix . __('Online Registration');
		?>
		<?php echo $this->fetch('title'); ?>
	</title>
	<?php
		echo $this->Html->css('foundation');
		// Reset some values from foundation 
		echo $this->Html->css('foundation-reset');
		echo $this->Html->css('cake.generic');
		echo $this->Html->css('onlineentries');
		echo $this->Html->css('ttmtable');

		echo $this->Html->script('jquery');
		echo $this->Html->script('foundation');
		echo $this->Html->script('ttmtable');
	?>
	
	<script type="text/javascript">
		$(document).ready(function() {
			TTMTable.init($('table.ttm-table'));
			
			$(document).foundation();
			$('select[required="required"]').parent('div.input').addClass('required');
			$('input[required="required"]').parent('div.input').addClass('required');
			
			<?php 
				if ( ($token = $this->request->getParam('_csrfToken')) !== false ||
					 ($token = $this->request->getCookie('csrfToken')) !== null ) { 
			?>
				$.ajaxSetup({
					'beforeSend' : function(xhr) {
						xhr.setRequestHeader('X-CSRF-Token', '<?= $token; ?>');
					}
				});
			<?php 			
				} 
			?>
					
			if (!$('#content div.sidbar').is(':visible')) {
				$('#content div.toggle-sidebar h3').html('&raquo;');
			}
					
			$('#content div.toggle-sidebar h3').on('click', function() {
				toggleSidebar(this);
				return false;
			});
			
			$('div.filter legend').on('click', function() {
				toggleFilter(this.parentElement.parentElement);
			});
		});
		
		function toggleSidebar(el) {
			$('#content div.sidebar').toggle();
			if ($('#content div.sidebar').is(':visible'))
				$(el).html('&laquo');
			else
				$(el).html('&raquo;');
				
		}
		
		function toggleFilter(filter) {
			$(filter).find('table').toggle();
		}
	</script>
	
	<?php /* To change the language in header */ ?>
	<script type="text/javascript">
		function onChangeLanguage(lang) {

			$.ajax({
				type: 'POST',
				async: true,
				cache: false,
				url: "<?php echo Router::url(array('plugin' => null, 'controller' => 'users', 'action' => 'onChangeLanguage')); ?>",
				data: {lang: lang},
				success: function() {
					location.reload();
				}
			});
		}
	</script>
	
	<?php
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body>
	<div id="container" class="grid-container fluid">
		<div id="header" class="cell">
			<?php echo $this->element('header'); ?>
		</div>
		<div id="content" class="cell">
			<div class="toggle-sidebar">
				<h3>&laquo;</h3>
			</div>
			<div class="sidebar actions">
				<h3><?php echo __d('user', 'Actions'); ?></h3>
				<?php echo $this->fetch('action'); ?>
			</div>
			<?php echo $this->fetch('content'); ?>
		</div>
		<div id="footer" class="cell">
			<?php echo $this->element('footer'); ?>
		</div>
	</div>	
</body>
</html>