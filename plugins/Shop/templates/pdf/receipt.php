<link rel='stylesheet' href='/css/pdf.css'>

<?php
	ob_start();
?>

* {
	font-family: 'DejaVu Sans', 'Noto Sans KR';
}

body {
	font-size: 10px;
}

@page {
	margin-top: 180px;
	margin-left: 50px;
	margin-right: 50px;
	margin-bottom: 100px;
}


div#header { 
	font-size: 60%;
	position: fixed; 
	left: 0px; 
	top: -180px; 
	right: 0px; 
	height: 120px; 
}

div#footer { 
	font-size: 55%;
	position: fixed; 
	left: 0px; 
	bottom: -80px;
	right: 0px; 
	height: 80px; 
}


div#shopAddress {
	width: 35%;
	float: right;
}

div#caption {
	width: 100%;
	margin-bottom: 2em;
	overflow-y: auto;
}

div#title {
	width: 55%;
	float: left;
}

div#caption h1 {
	margin-top: 0px;
	font-size: 150%;
	font-weight: bold;
}

div#transaction {
	width: 35%;
	float: right;
}

div#billingAddress {
	margin-bottom: 2em;
}

div#footer span.add-footer {
	display: block;
	width: 100%;
	text-align: center;
}

div#footer span.dl {
	display: inline-block;
	/* float:left; */
    width: 48%;
}

div#footer span.dl ~ span.dl {
    padding-left: 1em;
}
	
div#footer table.dl {
	width: 100%;
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
	vertical-align: top;
}

.dt:after {
	vertical-align: top;
	white-space: nowrap;
	content: ':';
}

.dd {
}

tr .number {
	text-align: right;
	padding-right: 0.5em;
}	

tr .currency {
	text-align: right;
	padding-right: 0.5em;
}	

tr .pos {
	width: 2em;
}

tr .qty {
	width: 3em;
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
	padding-top: 0em;
}

table tr.total td {
	border-top: 1px solid black;
	font-weight: bold;
}

table tr td.cancelled {
	text-decoration: line-through;
}

div.content h3 {
	page-break-after: avoid;
}

div.content table tr:first-child {
	page-break-after: avoid;
}

<?php
	$css = ob_get_clean();
	$this->append('css', '<style type="text/css">' . $css . '</style>');
?>
<?php 
	echo $this->element('Shop.receipt'); 
?>
