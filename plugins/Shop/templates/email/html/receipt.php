<?php
	ob_start();
?>

* {
	font-family: sans-serif;
}

table {
	width: 80%;
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

table tr.total td {
	border-top: 1px solid black;
	font-size: 110%;
	font-weight: bold;
}

table tr td.cancelled {
	text-decoration: line-through;
}

dl dt {
	display: none;
}

header#header {
	font-size: 80%;
}

footer#footer {
  font-size: 80%;
}

footer#footer span.add-footer {
	display: block;
	float: initial;
	width: 100%;
	text-align: center;
}

footer#footer span.dl {
	display: inline-block;
	float:left;
	width: 48%;
}

footer#footer span.dl ~ span.dl {
    padding-left: 1em;
}
	
footer#footer table {
  width: auto;
}

footer#footer table td {
  padding-top: 0px;
  padding-right: 2em;
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


<?php
	$css = ob_get_clean();
	$this->append('css', '<style type="text/css">' . $css . '</style>');
?>
<?php 
	echo $this->element('receipt'); 
?>
