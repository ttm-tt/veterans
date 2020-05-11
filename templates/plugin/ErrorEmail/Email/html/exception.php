<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<h3 style="height: 40px; line-height: 40px; background-color: #ec5840; color: #ffffff;">An exception has been thrown<?php if ($site): ?> on <?= $site ?><?php endif; ?><?php if ($environment): ?> (<?= $environment ?>)<?php endif; ?></h3>
<table class="emailExceptionTable" style="text-align: left;" border="0" cellpadding="3">
    <?php if ($environment): ?>
        <tr>
            <td><strong>Environment:</strong></td>
            <td><?= $environment ?></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td><strong>Exception Url:</strong></td>
        <td><?= $this->Url->build($this->request->getRequestTarget(), true) ?></td>
    </tr>
    <tr>
        <td><strong>Referrer:</strong></td>
        <td><?= $this->request->referer() ?></td>
    </tr>
    <tr>
        <td><strong>Exception Class:</strong></td>
        <td><?= get_class($exception) ?></td>
    </tr>
    <tr>
        <td><strong>Exception Message:</strong></td>
        <td><?= $exception->getMessage() ?></td>
    </tr>
    <tr>
        <td><strong>Exception Code:</strong></td>
        <td><?= $exception->getCode() ?></td>
    </tr>
    <tr>
        <td><strong>Client IP:</strong></td>
        <td><?= $this->request->clientIp() ?></td>
    </tr>
	<tr>
		<td><strong>Request:</strong></td>
		<td><?= print_r($_REQUEST, true) ?></td>
	</tr>
	<tr>
		<td><strong>Data:</strong></td>
		<td><pre><?= print_r($this->request->getData(), true) ?></pre></td>
	</tr>
	<tr>
		<td><strong>Session:</strong></td>
		<td><pre><?= print_r($this->request->getSession()->read(), true) ?></pre></td>
	</tr>
</table>
<hr style="color: #f6f6f6;">
<table align="center" style="text-align: center;" border="0">
    <tr>
        <td>In <?= $exception->getFile() ?> on line <?= $exception->getLine() ?></td>
    </tr>
</table>
<hr style="color: #f6f6f6;">
<table align="center" style="text-align: center;" border="0">
    <tr>
        <td><strong>Stack Trace:</strong></td>
    </tr>
    <tr>
        <td align="left" style="text-align: left;"><?= nl2br($exception->getTraceAsString()) ?></td>
    </tr>
</table>
