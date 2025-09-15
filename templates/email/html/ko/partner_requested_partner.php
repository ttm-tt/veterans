<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
use Cake\Routing\Router;
?>
<?php
	$name = $tournament['description'];
	$url = Router::url('/', true);
	$event = ucwords($field);
	$event_i18n = ($field === 'mixed' ? 'mixed' : 'double');
?>
안녕하세요,
<p>
<?php echo $partner['person']['display_name'];?> 님이 귀하를 <?php echo $event_i18n;?> 파트너로 선택하셨습니다.
</p>
<p>
수락을 원하실 경우 반드시 확인 절차를 진행해 주시기 바랍니다.
또한 원치 않으신다면 거절하실 수도 있습니다.
응답 방법은 다음과 같습니다:
<ul>
	<li><?php echo $url;?> 에 접속하여 귀하의 이메일 주소와 비밀번호로 로그인합니다.</li>
	<li>귀하의 이름 옆에 있는 "요청(Requests)" 버튼을 클릭합니다.</li>
	<li>요청을 수락하려면 "승인(Accept)", 거절하려면 "거절(Reject)" 버튼을 클릭합니다.</li>
</ul>
</p>
<p>
확인 메일은 귀하와 파트너 모두에게 전송됩니다.
</p>
<p>
요청을 수락하시면 귀하는 <?php echo $partner['participant'][$field]['description'];?> 연령부의 경기로 출전하게 됩니다.
</p>
<?php echo $name;?> 에서 좋은 성적을 거두시길 기원합니다.



