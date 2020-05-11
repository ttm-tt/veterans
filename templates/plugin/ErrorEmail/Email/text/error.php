<?php /* Copyright (c) 2020 Christoph Theis */ ?>
An error has been thrown<?php if ($site): ?> on <?= $site ?><?php endif; ?><?php if ($environment): ?> (<?= $environment ?>)<?php endif; ?>

<?php if ($environment): ?>Environment: <?= $environment ?><?php endif; ?>
Error Url: <?= $this->Url->build($this->request->getRequestTarget(), true) ?>

Referrer: <?= $this->request->referer() ?>

Error Class: <?= get_class($error) ?>

Error Message: <?= $error->getMessage() ?>

Error Code: <?= $error->getCode() ?>

Client IP: <?= $this->request->clientIp() ?>

Request: <?= print_r($_REQUEST, true) ?>

Data: <?= print_r($this->request->getData(), true) ?>

Session: <?= print_r($this->request->getSession()->read(), true) ?>


In <?= $error->getFile() ?> on line <?= $error->getLine() ?>

Stack Trace:
<?= $error->getTraceAsString() ?>
