<?php
use Cake\Routing\Router;
?>

<?php
	ob_start();
?>

@font-face {
  font-family: 'Noto Sans KR';
  font-weight: normal;
  font-style: normal;
  src: url('webroot/font/noto/NotoSansKR-Regular.ttf') format('truetype');
}

@font-face {
  font-family: 'Noto Sans KR';
  font-weight: bold;
  font-style: normal;
  src: url('webroot/font/noto/NotoSansKR-Bold.ttf') format('truetype');
}

* {
	font-family: 'DejaVu Sans', 'Noto Sans KR';
}

@page {
	margin: 150px 50px;
}


div#header { 
	position: fixed; 
	left: 0px; 
	top: -150px; 
	right: 0px; 
	height: 80px; 
}

div#footer { 
	position: fixed; 
	left: 0px; 
	bottom: -180px; 
	right: 0px; 
	height: 0px; 
}


div#logo {
	width: 50%;
	float: left;
	/* background-image: url('<?php echo Router::url('/img/logo.png', true);?>'); */
	background-repeat: no-repeat;
	background-size: contain;
	height: 80px;	
}

div#transaction {
	width: 35%;
	float: right;
	font-size: 85%;
}

div#caption {
	width: 100%;
	margin-bottom: 5em;
	overflow-y: auto;
}

div#caption div#title {
	width: 100%;
}


div#caption h1 {
	margin-top: 0px;
	font-size: 250%;
	text-align: center;
}

div#caption h2 {
	margin-top: 0px;
	font-size: 150%;
	text-align: center;
}

ul {
	list-style-type: none;
	padding-left: 0px;
}

.dl {
	line-height: 1em;
}

.dt {
	font-weight: bold;
	padding-right: 1em;
}

.dt:after {
	content: ':';
}

.dd {
}

tr .currency {
	text-align: right;
	padding-right: 0.5em;
}	

table {
	width: 100%;
}

table th {
	text-align: left;
}

table tr th {
	border-bottom: 1px solid black;
}

table tr td {
	padding-top: 5px;
}

table tr#rowtotal td {
	border-top: 1px solid black;
	font-size: 110%;
	font-weight: bold;
}

table tr td.cancelled {
	text-decoration: line-through;
}

<?php
	$css = ob_get_clean();
	$this->append('css', '<style type="text/css">' . $css . '</style>');
?>
<?php 
	echo $this->element('Shop.voucher'); 
?>
