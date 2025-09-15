<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
안녕하세요, 
<p>
<?php echo $partner['person']['display_name'];?> 님이 귀하께 <?php echo $event_i18n;?> 파트너를 제안하셨습니다.
</p>
<p>
해당 파트너 요청은 반드시 귀하의 승인이 있어야하며, 귀하는 이를 수락하거나 거절하실 수 있습니다. 
</p>
<p>
수락하실 경우, 귀하는 <?php echo $partner['participant'][$field]['description'];?> 에서 경기를 치르게 됩니다.
</p>
<?php echo $name;?> 에서 좋은 성적을 거두시길 기원합니다.
