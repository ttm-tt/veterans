<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
안녕하세요,

<?php echo $partner['person']['display_name'];?> 님이 귀하를 <?php echo $event_i18n;?> 파트너로 확정하였습니다.
귀하는 <?php echo $registration['participant'][$field]['description'];?> 경기에 참가하시게 됩니다.

<?php echo $name;?> 에서 좋은 성적을 거두시길 기원합니다.




