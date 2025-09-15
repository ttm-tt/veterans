<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	$name = $tournament['description'];
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'doubles');
?>
안녕하세요,
<p>
<?php echo $partner['person']['display_name'];?> 님은 귀하와 <?php echo $event_i18n;?> 경기를 참가하지 않기로 하였습니다.
<?php if ($tournament['modify_before'] < date('Y-m-d')) { ?>
귀하는 다시 "파트너 구함(Partner wanted)" 명단에 등록되었으며, 다른 <?php echo $event_i18n;?>  파트너를 직접 선택하실 수 있습니다.
<?php } else { ?>
귀하는 다시 "파트너 구함(Partner wanted)" 명단에 등록되었으며, 새로운 <?php echo $event_i18n;?> 파트너가 추첨을 통해 배정될 예정입니다.
<?php } ?>
</p>
<?php echo $name;?> 에서 좋은 경기를 치르시길 기원합니다.

