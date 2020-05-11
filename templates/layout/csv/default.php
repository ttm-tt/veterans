<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<?php
	// Layout for csv files, just echo BOM and content
	if (false) {
		// Set the mime type according to RFC4180
		header('Content-Type: text/csv; charset=utf-8');

		echo chr(0xEF) . chr(0xBB) . chr(0xBF);
		echo str_replace("\t", ";", $this->fetch('content'));
	} else {
		// Set the mime type according to RFC4180
		header('Content-Type: text/csv; charset=utf-16le');

		// UTF-16LE
		echo chr(0xFF) . chr(0xFE);
		echo mb_convert_encoding($this->fetch('content'), 'UTF-16LE', 'UTF-8');
	}
?>
