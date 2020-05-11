<?php /* Copyright (c) 2020 Christoph Theis */ ?>
An exception has been thrown<?php if ($site): ?> on <?= $site ?><?php endif; ?><?php if ($environment): ?> (<?= $environment ?>)<?php endif; ?>

<?php if ($environment): ?><strong>Environment:</strong> <?= $environment ?><?php endif; ?>
Exception Url: <?= $this->Url->build($this->request->getRequestTarget(), true) ?>

Referrer: <?= $this->request->referer() ?>

Exception Class: <?= get_class($exception) ?>

Exception Message: <?= $exception->getMessage() ?>

Exception Code: <?= $exception->getCode() ?>

Client IP: <?= $this->request->clientIp() ?>

Request: <?= print_r($_REQUEST, true) ?>

Data: <?= print_r($this->request->getData(), true) ?>

Session: <?= print_r($this->request->getSession()->read(), true) ?>

In <?= $exception->getFile() ?> on line <?= $exception->getLine() ?>

Stack Trace:
<?= $exception->getTraceAsString() ?>
