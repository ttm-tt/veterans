<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'doubles');
?>
안녕하세요,

<?php echo $partner['person']['display_name'];?> 님은 귀하와 함께 <?php echo $event_i18n;?> 경기에 출전하지 않기로 하였습니다. 

귀하는 다시 “파트너를 원합니다(Partner Wanted)”상태로 전환되었으며, 언제든 다른 파트너를 선택하실 수 있습니다.

<?php echo $name;?> 에서 좋은 성적을 거두시길 기원합니다.
