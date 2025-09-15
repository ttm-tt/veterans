<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
안녕하세요, 

귀하는 <?php echo $registration['participant'][$field .'_partner']['person']['display_name'];?> 님을 귀하의 <?php echo $event_i18n;?> 파트너로 요청하셨습니다
귀하는 <?php echo $registration['participant'][$field]['description'];?> 에서 경기를 하게 됩니다.

복식 파트너가 귀하를 자신의 <?php echo $event_i18n;?> 파트너로 확인하여야 최종 완료됩니다.

<?php echo $name;?> 에서의 성공을 기원합니다.



