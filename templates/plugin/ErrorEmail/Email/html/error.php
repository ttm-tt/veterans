<?php /* Copyright (c) 2020 Christoph Theis */ ?>
<h3 style="height: 40px; line-height: 40px; background-color: #ec5840; color: #ffffff;">An error has been thrown<?php if ($site): ?> on <?= $site ?><?php endif; ?><?php if ($environment): ?> (<?= $environment ?>)<?php endif; ?></h3>
<table class="emailExceptionTable" style="text-align: left;" border="0" cellpadding="3">
    <?php if ($environment): ?>
        <tr>
            <td><strong>Environment:</strong></td>
            <td><?= $environment ?></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td><strong>Error Url:</strong></td>
        <td><?= $this->Url->build($this->request->getRequestTarget(), true) ?></td>
    </tr>
    <tr>
        <td><strong>Referrer:</strong></td>
        <td><?= $this->request->referer() ?></td>
    </tr>
    <tr>
        <td><strong>Error Class:</strong></td>
        <td><?= get_class($error) ?></td>
    </tr>
    <tr>
        <td><strong>Error Message:</strong></td>
        <td><?= $error->getMessage() ?></td>
    </tr>
    <tr>
        <td><strong>Error Code:</strong></td>
        <td><?= $error->getCode() ?></td>
    </tr>
    <tr>
        <td><strong>Client IP:</strong></td>
        <td><?= $this->request->clientIp() ?></td>
    </tr>
	<tr>
		<td><strong>Request:</strong></td>
		<td><pre><?= print_r($_REQUEST, true) ?></pre></td>
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
        <td>In <?= $error->getFile() ?> on line <?= $error->getLine() ?></td>
    </tr>
</table>
<hr style="color: #f6f6f6;">
<table align="center" style="text-align: center;" border="0">
    <tr>
        <td><strong>Stack Trace:</strong></td>
    </tr>
    <tr>
        <td align="left" style="text-align: left;"><?= nl2br($error->getTraceAsString()) ?></td>
    </tr>
</table>
