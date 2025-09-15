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
Dear Table Tennis friend!

<?php echo $partner['person']['display_name'];?> has selected you as his/her <?php echo $event_i18n;?> partner.

If you agree, it is necessary to confirm.
You can also reject the request.
In either case:
- Log on to <?php echo $url;?> with your email address and your password.

- Click on "Requests" next to your name.

- Click on "Accept" to accept the request or on "Reject" to reject the request.

A confirmation mail will be sent to you as well to your partner.

If you accept the request you will play in the age category <?php echo $partner['participant'][$field]['description'];?>

We wish you a successful <?php echo $name;?>


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

<?php echo $partner['person']['display_name'];?> 님이 귀하를 <?php echo $event_i18n;?> 파트너로 선택하셨습니다.
수락을 원하실 경우 반드시 확인 절차를 진행해 주시기 바랍니다.
또한 원치 않으신다면 거절하실 수도 있습니다.
응답 방법은 다음과 같습니다:
- <?php echo $url;?> 에 접속하여 귀하의 이메일 주소와 비밀번호로 로그인합니다.
- 귀하의 이름 옆에 있는 "요청(Requests)" 버튼을 클릭합니다.
- 요청을 수락하려면 "승인(Accept)", 거절하려면 "거절(Reject)" 버튼을 클릭합니다.

확인 메일은 귀하와 파트너 모두에게 전송됩니다.

요청을 수락하시면 귀하는 <?php echo $partner['participant'][$field]['description'];?> 연령부의 경기로 출전하게 됩니다.

<?php echo $name;?> 에서 좋은 성적을 거두시길 기원합니다.




