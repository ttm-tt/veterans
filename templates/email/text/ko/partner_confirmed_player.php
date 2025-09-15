<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
안녕하세요,

귀하는 <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> 님을 귀하의 <?php echo $event_i18n;?> 파트너로 확정하셨습니다.
귀하는 <?php echo $registration['participant'][$field]['description'];?> 경기에 참가하시게 됩니다.

<?php echo $name;?> 에서 좋은 성적을 거두시길 기원합니다.


