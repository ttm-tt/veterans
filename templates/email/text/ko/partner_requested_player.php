<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
안녕하세요

귀하는 <?php echo $registration['participant'][$field . '_partner']['person']['display_name'];?> 님을 <?php echo $event_i18n;?> 파트너로 요청하셨습니다. 
귀하는 <?php echo $registration['participant'][$field]['description'];?> 경기에 참가하시게 됩니다.

다만, 귀하의 요청은 <?php echo $event_i18n;?> 파트너인 상대방의 승인이 있어야 최종 확정됩니다.

<?php echo $name;?> 에서 좋은 경기를 치르시길 기원합니다.




